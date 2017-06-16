<?php

class Styla_Connect_Model_Styla_Api
{
    const REQUEST_CLASS_ALIAS  = 'styla_connect/styla_api_request_type_';
    const RESPONSE_CLASS_ALIAS = 'styla_connect/styla_api_response_type_';

    const REQUEST_TYPE_SEO                  = 'seo';
    const REQUEST_TYPE_VERSION              = 'version';
    const REQUEST_TYPE_REGISTER_MAGENTO_API = 'register';

    protected $_service;
    protected $_currentApiVersion;
    protected $_cache;

    /**
     * these options are used for initializing the connector to api service
     */
    protected $_apiConnectionOptions = array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HTTPHEADER     =>
            array(
                'Accept: application/json',
            ),
    );

    /**
     * Use this method to get all the magazine-related data in one call.
     *
     * It returns a Varien_Object with the SEO data of the magazine, and the
     * current url to magazine's js script.
     *
     * @param string $requestPath
     * @return \Varien_Object|boolean
     * @throws Styla_Connect_Exception
     */
    public function requestPageData($requestPath = '/')
    {
        if (!$requestPath) {
            $requestPath = '/';
        }

        try {
            $data = $this->getPageSeoData($requestPath);
            if (isset($data['status']) && $data['status'] !== 200) {
                return false;
            }
            unset($data['code'], $data['status']);

            return $data;
        } catch (Styla_Connect_Exception $e) {
            Mage::logException($e);

            return false;
        }
    }

    /**
     * Get the magazine's SEO data - header, noscript tag, etc.
     *
     * Can return an empty array, if the SEO api is not responding.
     *
     * @param string $requestPath
     * @return mixed
     */
    public function getPageSeoData($requestPath)
    {
        /** @var Styla_Connect_Model_Styla_Api_Seodata $seo */
        $seo = Mage::getSingleton('styla_connect/styla_api_seodata');
        return $seo->getSeoData($requestPath);
    }

    /**
     * Get the current cache version number from the Styla api
     *
     * @return string
     */
    public function getCurrentApiVersion()
    {
        if (!$this->_currentApiVersion) {
            $cache      = $this->getCache();
            $apiVersion = $cache->load('styla-api-version');

            if (!$apiVersion) {
                $request = $this->getRequest(self::REQUEST_TYPE_VERSION);

                try {
                    $response   = $this->callService($request, false, true);
                    $apiVersion = $response->getResult();

                    //if returned by the response, use the cache-control set lifetime
                    $cacheTime = $response->getCacheControlValue();

                    if (false === $cacheTime) {
                        $cacheTime = "3600";
                    }

                    //cache for $cacheTime seconds
                    $cache->save(
                        $apiVersion,
                        'styla-api-version',
                        array(Styla_Connect_Model_Styla_Api_Cache::CACHE_TAG),
                        $cacheTime
                    );
                } catch (Styla_Connect_Exception $e) {
                    //this request might possibly fail, for example when wrong url is set in developer mode

                    Mage::logException($e);
                    $apiVersion = 1;
                }
            }
            $this->_currentApiVersion = $apiVersion;
        }

        return $this->_currentApiVersion;
    }

    /**
     * Make a call to the Styla Api
     *
     * @param Styla_Connect_Model_Styla_Api_Request_Type_Abstract $request
     * @param bool                                                $canUseCache
     * @param bool                                                $useResultHeadersInResponse add the http headers to response
     * @return Styla_Connect_Model_Styla_Api_Response_Type_Abstract
     * @throws Styla_Connect_Exception
     */
    public function callService(
        Styla_Connect_Model_Styla_Api_Request_Type_Abstract $request,
        $canUseCache = true,
        $useResultHeadersInResponse = false
    )
    {
        $cache = $this->getCache();
        if ($canUseCache && $cachedResponse = $cache->getCachedApiResponse($request)) {
            return $cachedResponse;
        }

        $requestApiUrl = $request->getApiUrl();
        /** @var Varien_Http_Adapter_Curl $service */
        $service = $this->getService();

        //include the request timeout, if set
        $requestTimeoutOptions = $request->getConnectionTimeoutOptions();
        if ($requestTimeoutOptions) {
            $service->setOptions($requestTimeoutOptions);
        }

        //fill in the post params, if this is a POST request
        $requestMethod = $request->getConnectionType();
        $requestBody   = '';

        if ($requestMethod == Zend_Http_Client::POST) {
            $requestBody = $request->getParams();
        }

        $service->write(
            $request->getConnectionType(),
            $requestApiUrl,
            '1.1',
            array('Accept: application/json'),
            $requestBody
        );

        $result = $service->read();
        if (!$result) {
            throw new Styla_Connect_Exception("Couldn't get a result from the API.");
        }

        /**
         * the result can contain both the body and http headers, if the $addResultHeaders var is active.
         * we'll need to parse this info, before giving it to the response object
         */
        $resultBody    = $result;
        $resultHeaders = false;
        if ($useResultHeadersInResponse) {
            $result        = $this->parseHttpResponse($result);
            $resultBody    = $result['body'];
            $resultHeaders = $result['headers'];
        }

        $response = $this->getResponse($request);
        $response->initialize($resultBody, $service);

        if ($resultHeaders) {
            $response->setResponseHeaders($resultHeaders);
        }

        if ($canUseCache && $response->getHttpStatus() === 200) {
            $cache->storeApiResponse($request, $response);
        }

        return $response;
    }

    /**
     * Parse a http response, containing both the headers and content and return it as array
     *
     * @param string $response
     * @return array
     */
    public function parseHttpResponse($response)
    {
        $headers = array();
        if (false === strpos($response, "\r\n\r\n")) {
            return array('headers' => array(), 'body' => $response);
        }

        list($headerContent, $bodyContent) = explode("\r\n\r\n", $response, 2);

        foreach (explode("\r\n", $headerContent) as $i => $header) {
            if ($i === 0) {
                $headers['http_code'] = $header;
            } else {
                list($headerName, $value) = explode(': ', $header);
                $headers[$headerName] = $value;
            }
        }

        return array(
            'headers' => $headers,
            'body'    => $bodyContent,
        );
    }

    /**
     * Get a new response class related to this request.
     *
     * @param Styla_Connect_Model_Styla_Api_Request_Type_Abstract $request
     * @return Styla_Connect_Model_Styla_Api_Response_Type_Abstract
     * @throws Styla_Connect_Exception
     */
    public function getResponse(Styla_Connect_Model_Styla_Api_Request_Type_Abstract $request)
    {
        $responseType = $request->getResponseType();
        $response     = Mage::getModel(self::RESPONSE_CLASS_ALIAS . $responseType);
        if (!$response) {
            throw new Styla_Connect_Exception('Unknown response type requested: ' . $responseType);
        }

        return $response;
    }

    /**
     * Get the api service connector
     *
     * @return Varien_Http_Adapter_Curl
     */
    public function getService($addResultHeaders = false)
    {
        if (!$this->_service) {
            $this->_service = new Varien_Http_Adapter_Curl();

            $this->_service->setOptions($this->_apiConnectionOptions);
        }

        //this will tell curl to omit headers in result, if false
        $this->_service->setConfig(array('header' => $addResultHeaders));

        return $this->_service;
    }

    /**
     * Get a new request object, by the request type
     *
     * @param string $requestType
     * @return Styla_Connect_Model_Styla_Api_Request_Type_Abstract
     * @throws Styla_Connect_Exception
     */
    public function getRequest($requestType)
    {
        $request = Mage::getModel(self::REQUEST_CLASS_ALIAS . $requestType);
        if (!$request) {
            throw new Styla_Connect_Exception('Unknown request type: ' . $requestType);
        }

        return $request;
    }

    /**
     * @return Styla_Connect_Model_Styla_Api_Cache
     */
    public function getCache()
    {
        if (!$this->_cache) {
            $this->_cache = Mage::getSingleton('styla_connect/styla_api_cache');
        }

        return $this->_cache;
    }
}
