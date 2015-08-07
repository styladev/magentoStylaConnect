<?php
class Styla_Connect_Model_Styla_Api_Request_Type_Version extends Styla_Connect_Model_Styla_Api_Request_Type_Abstract
{
    const URL_API_VERSION = "live.styla.com/scripts/version";
    protected $_requestType = Styla_Connect_Model_Styla_Api::REQUEST_TYPE_VERSION;
    
    public function getApiUrl()
    {
        return self::URL_API_VERSION;
    }
}