<?php
class Styla_Connect_Helper_Config
{
    const DEFAULT_ROUTE_NAME = 'magazin';
    
    /**
     * Get the global Client's username
     * 
     * @return string
     */
    public function getUsername()
    {
        return Mage::getStoreConfig('styla_connect/basic/username');
    }
    
    /**
     * Get the route name for the router
     * 
     * @return string
     */
    public function getRouteName()
    {
        $configuredRouteName = Mage::getStoreConfig('styla_connect/basic/frontend_name');
        $routeName = $configuredRouteName ? $configuredRouteName : self::DEFAULT_ROUTE_NAME;
        
        return trim($routeName, "/") . "/";
    }
    
    /**
     * Get the content language code
     * 
     * @return string
     */
    public function getLanguageCode()
    {
        return Mage::getStoreConfig('general/locale/code', Mage::app()->getStore()->getId());
    }
    
    public function getCacheLifetime()
    {
        return Mage::getStoreConfig('styla_connect/basic/cache_lifetime');
    }
    
    public function isUsingMagentoLayout()
    {
        return (bool)Mage::getStoreConfig('styla_connect/basic/use_magento_layout');
    }
    
    public function isModuleEnabled()
    {
        return (bool)Mage::getStoreConfig('styla_connect/basic/enabled');
    }
    
    public function getApiSeoUrl()
    {
        return $this->parseUrl(Mage::getStoreConfig('styla_connect/basic/seo_url'));
    }
    
    public function getApiJsUrl()
    {
        return Mage::getStoreConfig('styla_connect/basic/js_url');
    }
    
    public function parseUrl($url)
    {
        return rtrim($url, "/") . "/";
    }
}