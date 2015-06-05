<?php
abstract class Styla_Connect_Model_Styla_Api_Response_Type_Abstract
{
    const CONTENT_TYPE_PLAIN = 'plain';
    const CONTENT_TYPE_JSON = 'json';
    
    protected $_httpStatus;
    protected $_error;
    protected $_result;
    
    protected $_contentType = self::CONTENT_TYPE_PLAIN;
    
    /**
     * Get the final result of an Api call. If the api response is in json, it wll be processed, first.
     * 
     * @return string
     * @throws Styla_Connect_Exception
     */
    public function getResult()
    {
        if($this->getHttpStatus() != 200) {
            throw new Styla_Connect_Exception("The Styla Api request didn't return results: " . $this->getHttpStatus() . " - " . $this->getError());
        }
        
        $result = $this->getProcessedResult();
        return $result;
    }
    
    /**
     * Did this request return a normal, valid response?
     * 
     * @return boolean
     */
    public function isOk()
    {
        if($this->getHttpStatus() == 200) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get the API response data as-is, without any processing
     * 
     * @return mixed
     */
    public function getRawResult()
    {
        return $this->_result;
    }
    
    public function setRawResult($result)
    {
        $this->_result = $result;
    }
    
    public function setHttpStatus($status)
    {
        $this->_httpStatus = $status;
    }
    
    /**
     * 
     * @param mixed $apiCallResult
     * @param Varien_Http_Adapter_Curl $apiService
     */
    public function initialize($apiCallResult, Varien_Http_Adapter_Curl $apiService)
    {
        $this->_result = $apiCallResult;
        $this->_error = $apiService->getError();
        $this->_httpStatus = $apiService->getInfo(CURLINFO_HTTP_CODE);
    }
    
    public function getHttpStatus()
    {
        return $this->_httpStatus;
    }
    
    public function getError()
    {
        return $this->_error;
    }
    
    public function getProcessedResult()
    {
        switch($this->_contentType) {
            case self::CONTENT_TYPE_JSON:
                return $this->getJsonResult();
            case self::CONTENT_TYPE_PLAIN:
                return $this->_result;
        }
    }
    
    public function getJsonResult()
    {
        $jsonResult = json_decode($this->_result);
        if($jsonResult === null) {
            throw new Exception("Error parsing a JSON Api result.");
        }
        
        return $jsonResult;
    }
}