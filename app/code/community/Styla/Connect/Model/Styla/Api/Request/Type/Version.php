<?php

/**
 * Class Styla_Connect_Model_Styla_Api_Request_Type_Version
 */
class Styla_Connect_Model_Styla_Api_Request_Type_Version extends Styla_Connect_Model_Styla_Api_Request_Type_Abstract
{
    const URL_API_VERSION = "http://live.styla.com/api/version/%s";
    protected $_requestType = Styla_Connect_Model_Styla_Api::REQUEST_TYPE_VERSION;

    public function getApiUrl()
    {
        $username = $this->getConfigHelper()->getUsername();
        return sprintf(self::URL_API_VERSION, $username);
    }


}