<?php
class Styla_Connect_Model_Styla_Api_Cache
{
    const CACHE_TAG = "STYLA_CONNECT";
    const CACHE_GROUP = 'styla_connect';
    
    protected $_cache;
    protected $_api;
    
    /**
     * 
     * @return Zend_Cache_Core
     */
    public function getCache()
    {
        if(!$this->_cache) {
            $this->_cache = Mage::app()->getCache();
        }
        
        return $this->_cache;
    }
    
    /**
     * Is this cache type enabled
     * 
     * @return bool
     */
    public function isEnabled()
    {
        $useCache = Mage::app()->useCache(self::CACHE_GROUP);
        
        return $useCache;
    }
    
    /**
     * 
     * @return Styla_Connect_Model_Styla_Api
     */
    public function getApi()
    {
        if(!$this->_api) {
            $this->_api = Mage::getSingleton('styla_connect/styla_api');
        }
        return $this->_api;
    }
    
    /**
     * Store the api response in cache, if possible
     * 
     * @param Styla_Connect_Model_Styla_Api_Request_Type_Abstract $request
     * @param Styla_Connect_Model_Styla_Api_Response_Type_Abstract $response
     */
    public function storeApiResponse($request, $response)
    {
        if(!$this->isEnabled() || $response->getHttpStatus() !== 200) {
            return;
        }
        
        $cachedData = serialize($response->getRawResult());
        $cacheKey = $this->getCacheKey($request);

        $this->getCache()->save($cachedData, $cacheKey, array(self::CACHE_TAG), $this->getCacheLifetime());
    }
    
    public function getCacheLifetime()
    {
        return Mage::helper('styla_connect/config')->getCacheLifetime();
    }
    
    public function getApiVersion()
    {
        return $this->getApi()->getCurrentApiVersion();
    }
    
    /**
     * 
     * @param Styla_Connect_Model_Styla_Api_Request_Type_Abstract $request
     * @return string
     */
    public function getCacheKey($request)
    {
        $key = $request->getRequestType() . $request->getRequestPath() . "_" . $this->getApiVersion();
        
        return $key;
    }
    
    /**
     * If possible, load a cached response
     * 
     * @param Styla_Connect_Model_Styla_Api_Request_Type_Abstract $request
     * @return boolean|Styla_Connect_Model_Styla_Api_Response_Type_Abstract
     */
    public function getCachedApiResponse($request)
    {
        if(!$this->isEnabled()) {
            return false;
        }
        
        $key = $this->getCacheKey($request);
        $cached = $this->getCache()->load($key);
        if(!$cached) {
            return false;
        }
        
        //rebuild the response object
        $response = $this->getApi()->getResponse($request);
        $response->setHttpStatus(200);
        $response->setRawResult(unserialize($cached));
        
        return $response;
    }
}