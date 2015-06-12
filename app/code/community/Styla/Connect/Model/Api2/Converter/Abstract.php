<?php
abstract class Styla_Connect_Model_Api2_Converter_Abstract
{
    const ARGUMENT_STYLA_FIELD = "styla_field";
    const ARGUMENT_MAGENTO_FIELD = "magento_field";
    
    const REQUIREMENTS_TYPE = ""; //this is an identifier for collection requirements. used to only add the same reqs once
    
    protected $_arguments;
    
    public function setArguments(array $arguments)
    {
        $this->_arguments = $arguments;
        
        return $this;
    }
    
    public function getArguments()
    {
        return $this->_arguments;
    }
    
    public function getArgument($name)
    {
        return isset($this->_arguments[$name]) ? $this->_arguments[$name] : null;
    }
    
    public function getStylaField()
    {
        return $this->getArgument(self::ARGUMENT_STYLA_FIELD);
    }
    
    public function getMagentoField()
    {
        return $this->getArgument(self::ARGUMENT_MAGENTO_FIELD);
    }
    
    protected function _emulateFrontend()
    {
        $defaultStoreId = Mage::app()
            ->getWebsite()
            ->getDefaultGroup()
            ->getDefaultStoreId();

        $appEmulation = Mage::getSingleton('core/app_emulation');
        return $appEmulation->startEnvironmentEmulation($this->_getDefaultStoreViewId());
    }
    
    protected function _getDefaultStoreViewId()
    {
        return Mage::app()->getDefaultStoreView()->getId();
    }
    
    protected function _stopEmulation($environmentInfo)
    {
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $appEmulation->stopEnvironmentEmulation($environmentInfo);
    }
    
    /**
     * If a converter needs some special data from the collection to work,
     * it should be added to it here.
     * 
     * This is ONLY ADDED in case of collection type data (see ResponseConfig::prepareStylaApiResponse() )
     * 
     * @param mixed $dataCollection
     */
    public function addRequirementsToDataCollection($dataCollection)
    {
        return $this;
    }
    
    /**
     * use this method in your own converter, to process input data object and set styla fields in it
     */
    abstract public function runConverter(Varien_Object $dataObject);
}