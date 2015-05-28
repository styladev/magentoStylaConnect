<?php
class Styla_Connect_Block_Magazine extends Mage_Core_Block_Abstract
{
    protected $_magazineData;
    
    /**
     * 
     * @return Varien_Object|null
     */
    public function getMagazineData()
    {
        if(!$this->_magazineData) {
            $this->_magazineData = Mage::registry('current_magazine_data');
        }
        
        return $this->_magazineData;
    }
    
    /**
     * 
     * @return Styla_Connect_Helper_Config
     */
    public function getConfigHelper()
    {
        return Mage::helper('styla_connect/config');
    }
    
    
}