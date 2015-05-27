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
        return 'ecocodedev'; //TODO
    }
    
    /**
     * Get the route name for the router
     * 
     * @return string
     */
    public function getRouteName()
    {
        return trim(self::DEFAULT_ROUTE_NAME, "/") . "/"; //TODO
    }
    
    /**
     * Get the content language code
     * 
     * @return string
     */
    public function getLanguageCode(){
        return Mage::getStoreConfig('general/locale/code', Mage::app()->getStore()->getId());
    }
}