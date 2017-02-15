<?php

class Styla_Connect_Model_Styla_Api_Seodata
{
    const SEO_CACHE_INDICATOR           = 'styla_connect_seo_indicator';
    const SEO_CACHE_INDICATOR_LIFE      = 3600; //an hour
    const SEO_CACHE_SUSPENDED           = 'styla_connect_seo_suspended';
    const SEO_CACHE_SUSPENDED_LIFE      = 300; //5 mins
    const SEO_CACHE_SEO_CONTENT         = 'styla_connect_seo_content';
    const SEO_CACHE_SEO_CONTENT_LIFE    = false; //indefinitely
    
    protected $_cache;
    protected $_api;
    
    /**
     * Get the SEO Response for a $requestPath of a magazine page.
     * Will always try to load a locally-cached copy first, if possible.
     * 
     * @param string $requestPath
     * @return bool|array
     */
    public function getSeoData($requestPath)
    {
        $data = false;
        $seoRequest = $this->getApi()->getRequest(Styla_Connect_Model_Styla_Api::REQUEST_TYPE_SEO)
            ->initialize($requestPath);
        
        if($this->hasValidCachedContentIndicator($requestPath)) {
            //the indicator tells me we should already have valid seo content cached
            //so we can try retrieving it directly.
            $data = $this->_tryCachedSeoData($seoRequest);
            
            if($data === false) { //actually, we didn't, so we re-request it
                //there's no cached content, we need to get new stuff from seo api:
                $data = $this->requestNewSeoDataFromApi($seoRequest);
            }
        } else {
            //there's no "cached content" indicator flag, so even if we do have
            //a local copy of SEO, we consider it outdated and will request a new copy
            $data = $this->_refreshCachedSeoDataIfPossible($seoRequest);
        }
        
        if(false === $data) {
            //the SEO content couldn't be retrieved in any way.
            return array();
        }
        
        return $data;
    }
    
    /**
     * If possible, make a new request to the remote SEO server and cache the result.
     * If a new request is temporarily not possible, serve the locally-cached version.
     * 
     * Returns FALSE on error.
     * 
     * @param Styla_Connect_Model_Styla_Api_Request_Type_Seo $seoRequest
     * @return bool|array
     */
    protected function _refreshCachedSeoDataIfPossible($seoRequest)
    {
        $data = false;
        
        if(!$this->isSuspendingSeoRequests($seoRequest->getRequestPath())) {
            //try getting the new data from seo server:
            $data = $this->requestNewSeoDataFromApi($seoRequest);
        } else {
            //the system tells me that we're temporarily not allowed to make new
            //seo requests, because most likely the remote seo sever is down.
            //so, as a last resort, we'll try loading our outdated local copy:
            $data = $this->_tryCachedSeoData($seoRequest);
        }
        
        return $data;
    }
    
    /**
     * Load cached SEO data
     * 
     * Returns FALSE on error.
     * 
     * @param Styla_Connect_Model_Styla_Api_Request_Type_Seo $seoRequest
     * @return bool|array
     */
    protected function _tryCachedSeoData($seoRequest)
    {
        $cachedResponse = $this->getCache()->getCachedApiResponse($seoRequest);
        return $cachedResponse ? $cachedResponse->getResult() : false;
    }
    
    /**
     * Make a new request to the remote server and get the Seo Data from it.
     * 
     * Returns FALSE on error.
     * 
     * @param Styla_Connect_Model_Styla_Api_Request_Type_Seo $seoRequest
     * @return boolean|array
     */
    public function requestNewSeoDataFromApi($seoRequest)
    {
        $error = false;
        
        try {
            $response = $this->getApi()->callService($seoRequest, false); //do not use cache, we want an immediate new result
        } catch (Styla_Connect_Exception $e) {
            $error = true;
        }
        
        if(!$error) {
            //save the new response to cache, with no limit on lifetime
            $this->getCache()->storeApiResponse($seoRequest, $response, self::SEO_CACHE_SEO_CONTENT_LIFE);

            //update the cache indicator, to tell the system we just saved some fresh seo content
            //and we can serve the local version, next time we need it
            $this->setValidCachedContentIndicator($seoRequest->getRequestPath());
            
            return $response->getResult();
        } else {
            //there was an error, so we have no valid data to return
            //we will suspend further seo calls for some time:
            $this->suspendSeoRequests();
            
            return false;
        }
    }
    
    /**
     * 
     * @param string $forPath
     * @return string
     */
    protected function _getSuspendSeoRequestCacheId($forPath)
    {
        return self::SEO_CACHE_SUSPENDED . $forPath;
    }
    
    /**
     * Are we currently banned from making requests to the remote SEO Api server?
     * 
     * @param string $forPath
     * @return bool
     */
    public function isSuspendingSeoRequests($forPath)
    {
        return (bool)$this->getCache()->load($this->_getSuspendSeoRequestCacheId($forPath));
    }
    
    /**
     * Temporarily ban making new seo request to the remote server.
     * This will make us only serve cached content, for a while.
     * 
     * @param string $forPath
     * @return \Styla_Connect_Model_Styla_Api_Seodata
     */
    public function suspendSeoRequests($forPath)
    {
        $this->getCache()->save("1", $this->_getSuspendSeoRequestCacheId($forPath), array(), self::SEO_CACHE_SUSPENDED_LIFE);
        
        return $this;
    }
    
    /**
     * Do we have up-to-date seo content cached in the local system?
     * 
     * @param string $forPath
     * @return bool
     */
    public function hasValidCachedContentIndicator($forPath)
    {
        $indicator = $this->_getCacheIndicatorName($forPath);

        return (bool)$this->getCache()->load($indicator);
    }
    
    /**
     * 
     * @param string $forPath
     * @return string
     */
    protected function _getCacheIndicatorName($forPath)
    {
        return self::SEO_CACHE_INDICATOR . $forPath;
    }
    
    /**
     * Set a flag that will tell us there's a locally-cached seo content
     * available for re-using
     * 
     * @param string $forPath
     * @return \Styla_Connect_Model_Styla_Api_Seodata
     */
    public function setValidCachedContentIndicator($forPath)
    {
        $indicator = $this->_getCacheIndicatorName($forPath);
        //save a simple flag that we do have cache for this path:
        $this->getCache()->save("1", $indicator, array(), self::SEO_CACHE_INDICATOR_LIFE);
        
        return $this;
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
    
    /**
     *
     * @return Styla_Connect_Model_Styla_Api
     */
    public function getApi()
    {
        if (!$this->_api) {
            $this->_api = Mage::getSingleton('styla_connect/styla_api');
        }

        return $this->_api;
    }
}
