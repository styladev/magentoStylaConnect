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
            $routeSettings = $this->getRouteSettings($path);

            $request->setModuleName('styla_connect')
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
    public function getRouteSettings($path)
    {
        //the path should not contain the trailing slash, the styla api is not expecting it
        $path = rtrim(str_replace($this->getRouteName(), "", $path), '/');

        return $path;
    }

    /**
     * Can this request's path be processed by this router?
     *
     * @param Zend_Controller_Request_Http $request
     * @return string|boolean
     */
    protected function _isValidPath(Zend_Controller_Request_Http $request)
    {
        $path = trim($request->getPathInfo(), '/').'/';

        if (strpos($path, $this->getRouteName()) === 0) {
            return $path;
        }

        return false;
    }
}