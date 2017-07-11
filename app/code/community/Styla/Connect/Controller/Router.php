<?php

/**
 * Class Styla_Connect_Controller_Router
 */
class Styla_Connect_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{
    public function match(Zend_Controller_Request_Http $request)
    {
        $path = $this->_getRequestPath($request);
        if ($path === false) {
            return false;
        }

        $frontName = $this->_getFrontName($path);
        if (!$frontName) {
            return false;
        }

        $magazine = Mage::getModel('styla_connect/magazine')->loadByFrontName($frontName);

        if (!$magazine || !$magazine->isActive()) {
            return false;
        }

        Mage::register('current_magazine', $magazine);
        $routeSettings = $this->getRouteSettings($magazine, $path, $request);

        //setModule name is the front name
        $request
            ->setModuleName('styla')
            ->setControllerName('magazine')
            ->setActionName('index')
            ->setParam('path', $routeSettings);

        return true;
    }

    /**
     * Get only the last part of the route, leading up to a specific page
     *
     * @param Styla_Connect_Model_Magazine $magazine
     * @param string                       $path
     * @param                              $request
     * @return string
     */
    public function getRouteSettings(Styla_Connect_Model_Magazine $magazine, $path, $request)
    {
        //the path should not contain the trailing slash, the styla api is not expecting it
        $path = rtrim(str_replace($magazine->getFrontName(), '', $path), '/');

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

    protected function _getRequestPath(Zend_Controller_Request_Http $request)
    {
        return  trim($request->getRequestString(), '/');
    }

    /**
     * Can this request's path be processed by this router?
     *
     * @param Zend_Controller_Request_Http $request
     * @return string|boolean
     */
    protected function _getFrontName($path)
    {
        //we expect the magazine's frontend name to be the first element in the path_info
        $path     = trim($path, '/') . '/';
        $elements = explode('/', $path, 2);

        $frontendName = reset($elements);
        return trim($frontendName, '/');
    }
}
