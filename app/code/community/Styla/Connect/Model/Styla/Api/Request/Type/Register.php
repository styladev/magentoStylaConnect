<?php
class Styla_Connect_Model_Styla_Api_Request_Type_Register extends Styla_Connect_Model_Styla_Api_Request_Type_Abstract
{
    protected $_requestType = Styla_Connect_Model_Styla_Api::REQUEST_TYPE_REGISTER_MAGENTO_API;
    
    public function getApiUrl() {
        return Styla_Connect_Model_Styla_Api_Oauth_Connector::STYLA_API_CONNECTOR_URL;
    }
}