<?php
class Styla_Connect_Model_Styla_Api_Response_Type_Register extends Styla_Connect_Model_Styla_Api_Response_Type_Abstract
{
    /**
     * TODO: THIS IS THE BIG UNKNOWN UNTIL I CAN GET SOME INFO FROM STYLA
     * 
     * AT THE MOMENT THIS DOENS'T WORK PROPERLY
     */
    
    protected $_contentType = self::CONTENT_TYPE_JSON;
    
    /**
     * Same as parent class, but doesn't throw exceptions if there's no result for this page
     * 
     * @return string
     */
    public function getResult() {
        $result = parent::getResult();
        
        return $result;
    }
}