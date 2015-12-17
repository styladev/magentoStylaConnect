<?php

/**
 * Class Styla_Connect_Model_Styla_Api_Request_Type_Register
 */
class Styla_Connect_Model_Styla_Api_Request_Type_Register extends Styla_Connect_Model_Styla_Api_Request_Type_Abstract
{
    protected $_requestType = Styla_Connect_Model_Styla_Api::REQUEST_TYPE_REGISTER_MAGENTO_API;

    public function getApiUrl()
    {
        return Mage::getSingleton('styla_connect/styla_api_oauth_connector')->getConnectorApiUrl();
    }
}