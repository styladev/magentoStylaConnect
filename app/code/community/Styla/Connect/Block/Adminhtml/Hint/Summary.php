<?php
class Styla_Connect_Block_Adminhtml_Hint_Summary extends Mage_Adminhtml_Block_Store_Switcher
implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
    
    public function __construct() {
        parent::__construct();
        
        $this->setTemplate('styla/connect/adminhtml/hint/summary.phtml');
    }
    
    protected function _getConfigHelper()
    {
        return Mage::helper('styla_connect/config');
    }
    
    /**
     * Get the html for the configuration of the module, per specific website and store scope
     * 
     * @param string $mode
     * @param mixed $website
     * @param mixed $store
     * @return string
     */
    public function getConfiguration($mode, $website = null, $store = null)
    {
        $helper = $this->_getConfigHelper();
        
        $scopeModel = $helper->resolveScope($website, $store);
        $scope = $scopeModel->getScope();
        $scopeId = $scopeModel->getScopeId();
        
        $configuration = Mage::getConfig();
        
        $fields = "<ul>";
        foreach(array_keys($helper->getApiConfigurationFields()) as $fieldName) {
            $fieldPath = $helper->getApiConfigurationFieldByMode($fieldName, $mode);

            $node = $helper->getConfigurationNode($fieldPath, $scope, $scopeId);
            $fields .= "<li>" . $fieldName . ": " . ($node ? $node : "<i>" . Mage::helper('styla_connect')->__("Not Configured.") . "</i>") . "</li>";
        }
        
        $fields .= "</ul>";
        
        return $fields;
    }
}