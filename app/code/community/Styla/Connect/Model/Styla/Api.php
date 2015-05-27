<?php
class Styla_Connect_Model_Styla_Api
{
    const REQUEST_CLASS_ALIAS = 'styla_connect/styla_api_request_type_';
    const RESPONSE_CLASS_ALIAS = 'styla_connect/styla_api_response_type_';
    
    const JAVASCRIPT_URL = 'http://cdn.styla.com/scripts/clients/%s.js?v=%s';
    
    const REQUEST_TYPE_SEO = 'seo';
    const REQUEST_TYPE_VERSION = 'version';
    
    protected $_service;
    protected $_currentApiVersion;
    
    /**
     * these options are used for initializing the connector to api service
     */
    protected $_apiConnectionOptions = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json'
            )
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
    public function requestPageData($requestPath)
    {
        if(!$requestPath) {
            throw new Styla_Connect_Exception('No request path defined.');
        }
        
        try {
            $pageData = new Varien_Object();

            $seoData = $this->getPageSeoData($requestPath);
            $pageData->setSeoData($seoData);

            $scriptUrl = $this->getScriptUrl();
            $pageData->setScriptUrl($scriptUrl);
        } catch(Styla_Connect_Exception $e) {
            Mage::logException($e);
            
            return false;
        }
        
        return $pageData;
    }
    
    /**
     * Get the current url for Styla's JS script, used for loading the magazine page
     * 
     * @return string
     */
    public function getScriptUrl()
    {
        $scriptUrl = self::JAVASCRIPT_URL;
        $clientName = $this->getConfigHelper()->getUsername();
        $apiVersion = $this->getCurrentApiVersion();
        
        $scriptUrl = sprintf($scriptUrl, $clientName, $apiVersion);
        return $scriptUrl;
    }
    
    /**
     * Get the magazine's SEO data - header, noscript tag, etc.
     * 
     * @param string $requestPath
     * @return string
     */
    public function getPageSeoData($requestPath)
    {
        $seoRequest = $this->getRequest(self::REQUEST_TYPE_SEO)
                ->initialize($requestPath);
        
        $response = $this->callService($seoRequest);
        return $response->getResult();
    }
    
    /**
     * Get the current cache version number from the Styla api
     * 
     * @return string
     */
    public function getCurrentApiVersion()
    {
        if(!$this->_currentApiVersion) {
            $request = $this->getRequest(self::REQUEST_TYPE_VERSION);

            $response = $this->callService($request);
            $this->_currentApiVersion = $response->getResult();
        }
        
        return $this->_currentApiVersion;
    }
    
    /**
     * Make a call to the Styla Api
     * 
     * @param Styla_Connect_Model_Styla_Api_Request_Type_Abstract $request
     * @return Styla_Connect_Model_Styla_Api_Response_Type_Abstract
     * @throws Styla_Connect_Exception
     */
    public function callService(Styla_Connect_Model_Styla_Api_Request_Type_Abstract $request)
    {
        $requestApiUrl = $request->getApiUrl();
        $service = $this->getService();
        
        $service->write(Zend_Http_Client::GET, $requestApiUrl, '1.1', array(
                'Content-Type: application/json',
                'Accept: application/json'
            ));
        
        $result = $service->read();
        if(!$result) {
            throw new Styla_Connect_Exception("Couldn't get a result from the API.");
        }
        
        $response = $this->getResponse($request);
        $response->initialize($result, $service);
        
        return $response;
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
        $response = Mage::getModel(self::RESPONSE_CLASS_ALIAS . $responseType);
        if(!$response) {
            throw new Styla_Connect_Exception("Unknown response type requested: " . $responseType);
        }
        
        return $response;
    }
    
    /**
     * Get the api service connector
     * 
     * @return Varien_Http_Adapter_Curl
     */
    public function getService()
    {
        if(!$this->_service) {
            $this->_service = new Varien_Http_Adapter_Curl();
            
            $this->_service->setOptions($this->_apiConnectionOptions);            
            $this->_service->setConfig(array('header' => false)); //this will tell curl to omit headers in result
        }
        
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
        if(!$request) {
            throw new Styla_Connect_Exception("Unknown request type: " . $requestType);
        }
        
        return $request;
    }
    
    /**
     * 
     * @return Styla_Connect_Helper_Config
     */
    public function getConfigHelper()
    {
        return Mage::helper('styla_connect/config');
    }
}