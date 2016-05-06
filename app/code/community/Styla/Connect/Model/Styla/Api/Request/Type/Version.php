<?php

/**
 * Class Styla_Connect_Model_Styla_Api_Request_Type_Version
 */
class Styla_Connect_Model_Styla_Api_Request_Type_Version extends Styla_Connect_Model_Styla_Api_Request_Type_Abstract
{
    /** @deprecated URL_API_VERSION there's now a separate url for the live and stage mode of operation */
    const URL_API_VERSION = "http://live.styla.com/api/version/%s";
    protected $_requestType = Styla_Connect_Model_Styla_Api::REQUEST_TYPE_VERSION;
    
    protected $_apiVersionUrl = array(
        Styla_Connect_Helper_Config::MODE_PRODUCTION => "http://live.styla.com/api/version/%s",
        Styla_Connect_Helper_Config::MODE_STAGE => "http://dev.styla.com/api/version/%s",
    );

    /**
     * Get the versioning api url, according to the current store and mode of operation
     * 
     * @return string
     */
    public function getApiUrl()
    {
        $config = $this->getConfigHelper();
        $mode = $config->getCurrentMode();
        
        //get the proper url, default to stage version
        $url = isset($this->_apiVersionUrl[$mode]) ? $this->_apiVersionUrl[$mode] : $this->_apiVersionUrl[Styla_Connect_Helper_Config::MODE_STAGE];
        
        $username = $this->getConfigHelper()->getUsername();
        return sprintf($url, $username);
    }
}