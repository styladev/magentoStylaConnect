<?php
class Styla_Connect_Block_Magazine extends Mage_Core_Block_Template
{
    /**
     * 
     * @return Varien_Object|null
     */
    public function getMagazineData()
    {
        return Mage::helper('styla_connect')->getMagazineData();
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