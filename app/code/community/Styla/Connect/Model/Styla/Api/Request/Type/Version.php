<?php

/**
 * Class Styla_Connect_Model_Styla_Api_Request_Type_Version
 */
class Styla_Connect_Model_Styla_Api_Request_Type_Version extends Styla_Connect_Model_Styla_Api_Request_Type_Abstract
{
    protected $_requestType = Styla_Connect_Model_Styla_Api::REQUEST_TYPE_VERSION;

    /**
     * Get the versioning api url, according to the current store and mode of operation
     *
     * @return string
     */
    public function getApiUrl()
    {
        $config     = $this->getHelper();
        $versionUrl = $config->getApiVersionUrl();

        return $versionUrl;
    }
}
