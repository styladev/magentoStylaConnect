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
}