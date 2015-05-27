<?php
class Styla_Connect_Model_Styla_Api_Request_Type_Seo extends Styla_Connect_Model_Styla_Api_Request_Type_Abstract
{
    const API_URL_SEO = "http://seo.styla.com/clients/%s?lang=%s&url=%s";
    
    protected $_requestType = Styla_Connect_Model_Styla_Api::REQUEST_TYPE_SEO;
    
    public function getApiUrl()
    {
        $apiUrl = self::API_URL_SEO;
        $clientName = $this->getConfigHelper()->getUsername();
        $languageCode = $this->getConfigHelper()->getLanguageCode();
        $requestPath = $this->getRequestPath();
        
        $apiUrl = sprintf($apiUrl, $clientName, $languageCode, $requestPath);
        return $apiUrl;
    }
}