<?php
class Styla_Connect_Model_Styla_Api_Request_Type_Seo extends Styla_Connect_Model_Styla_Api_Request_Type_Abstract
{
    const API_URL_SEO = "http://seoapitest1.magalog.net/clients/%s?url=%s";
    
    protected $_requestType = Styla_Connect_Model_Styla_Api::REQUEST_TYPE_SEO;
    
    public function getApiUrl()
    {
        $apiUrl = self::API_URL_SEO;
        $clientName = $this->getConfigHelper()->getUsername();
        $languageCode = $this->getConfigHelper()->getLanguageCode();
        $requestPath = $this->getRequestPath();
        
        if(strlen($requestPath) > 1) {
            $requestPath = rtrim($requestPath, "/");
        }
        
        $apiUrl = sprintf($apiUrl, $clientName, $requestPath);
        return $apiUrl;
    }
}