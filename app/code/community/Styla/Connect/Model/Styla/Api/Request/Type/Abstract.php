<?php
abstract class Styla_Connect_Model_Styla_Api_Request_Type_Abstract
{
    protected $_cacheLifetime = 3600;
    protected $_requestPath;
    protected $_requestType;
    
    /**
     * 
     * @return string
     */
    abstract public function getApiUrl();
    
    /**
     * Initialize this request with data to pass on to the api service
     * 
     * @param string $requestPath
     * @return \Styla_Connect_Model_Styla_Api_Request_Type_Abstract
     */
    public function initialize($requestPath)
    {
        $this->_requestPath = $requestPath;
        
        return $this;
    }
    
    /**
     * 
     * @return string
     */
    public function getRequestPath()
    {
        return $this->_requestPath;
    }
    
    /**
     * Get the type name of this request
     * 
     * @return string
     */
    public function getRequestType()
    {
        return $this->_requestType;
    }
    
    /**
     * Get the class type name for the response type for this request
     * 
     * @return string
     */
    public function getResponseType()
    {
        return $this->_requestType;
    }
    
    /**
     * 
     * @return Styla_Connect_Model_Styla_Api
     */
    public function getApi()
    {
        return Mage::getSingleton('styla_connect/styla_api');
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