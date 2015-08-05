<?php
class Styla_Connect_Model_Styla_Api_Response_Type_Seo extends Styla_Connect_Model_Styla_Api_Response_Type_Abstract
{
    protected $_contentType = self::CONTENT_TYPE_JSON;
    
    /**
     * Same as parent class, but doesn't throw exceptions if there's no result for this page
     * 
     * @return string
     */
    public function getResult() {
        $result = new stdClass();
        
        if($this->getHttpStatus() != 200) {
            return $result;
        }
        
        $result = $this->getProcessedResult();
        return $result;
    }
}