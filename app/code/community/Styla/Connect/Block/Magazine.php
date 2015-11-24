<?php
class Styla_Connect_Block_Magazine extends Mage_Core_Block_Template
{
    /**
     * 
     * @return Styla_Connect_Model_Page
     */
    public function getPage()
    {
        return Mage::helper('styla_connect')->getCurrentPage();
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