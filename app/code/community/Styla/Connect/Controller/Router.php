<?php

/**
 * Class Styla_Connect_Controller_Router
 */
class Styla_Connect_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{
    public function match(Zend_Controller_Request_Http $request)
    {
        //checking before even try to find out that current module
        //should use this router
        if (!$this->_beforeModuleMatch() || !$this->isModuleEnabled()) {
            return false;
        }

        if ($path = $this->_isValidPath($request)) {
            $routeSettings = $this->getRouteSettings($path, $request);

            //setModule name is the front name
            $request->setModuleName('styla')
                ->setControllerName('magazine')
                ->setActionName('index')
                ->setParam('path', $routeSettings);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the currently configured starting route name for this router
     *
     * @return string
     */
    public function getRouteName()
    {
        return Mage::helper('styla_connect/config')->getRouteName();
    }

    public function isModuleEnabled()
    {
        return Mage::helper('styla_connect/config')->isModuleEnabled();
    }

    /**
     * Get only the last part of the route, leading up to a specific page
     *
     * @param string $path
     * @return string
     */
    public function getRouteSettings($path, $request)
    {
        //the path should not contain the trailing slash, the styla api is not expecting it
        $path = rtrim(str_replace($this->getRouteName(), '', $path), '/');
        
        //all the get params should be retained
        $requestParameters = $this->_getRequestParamsString($request);

        $route = $path . ($requestParameters ? '?' . $requestParameters : '');
        return $route;
    }
    
    /**
     * 
     * @param Zend_Controller_Request_Http $request
     * @return string
     */
    protected function _getRequestParamsString(Zend_Controller_Request_Http $request)
    {
        $allRequestParameters = $request->getQuery();
        
        return count($allRequestParameters) ? http_build_query($allRequestParameters) : '';
    }
    
    /**
     * Check if can be store code as part of url
     *
     * @return bool
     */
    protected function _canBeStoreCodeInUrl()
    {
        return Mage::isInstalled() && Mage::getStoreConfigFlag(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL);
    }

    /**
     * Can this request's path be processed by this router?
     *
     * @param Zend_Controller_Request_Http $request
     * @return string|boolean
     */
    protected function _isValidPath(Zend_Controller_Request_Http $request)
    {
        /**
         * here i'm cheking if the "store code in URL" option is enabled, and if it is,
         * i'm making sure that the store code actually is the first part of the URI we got.
         * if it isn't, we return FALSE and the match fails.
         */
        if($this->_canBeStoreCodeInUrl()) {
            $uri = explode('/', trim($request->getRequestUri(), '/')); //only here the store code will be available for lookup. uri should be {STORE_CODE}/{FRONTENDNAME}/[...]
            $storeCode = reset($uri);
            
            $stores = $stores = Mage::app()->getStores(true, true);
            if(!isset($stores[$storeCode])) {
                return false; //"store codes in URL are enabled", and the store code isn't the first element in the URI
            }
        }
        
        //we expect the magazine's frontend name to be the first element in the path_info
        $path = trim($request->getPathInfo(), '/') . '/';
        $elements = explode('/', $path);
        $frontendName = trim($this->getRouteName(), '/');
        if (isset($elements[0]) && ($elements[0] === $frontendName)) { //the first element in the path must be our 
            return $path;
        }

        return false;
    }
}