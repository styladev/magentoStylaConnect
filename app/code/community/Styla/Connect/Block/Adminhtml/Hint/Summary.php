<?php

class Styla_Connect_Block_Adminhtml_Hint_Summary extends Mage_Adminhtml_Block_Store_Switcher
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_summaryFields;
    
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

    public function __construct()
    {
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
     * @param mixed  $website
     * @param mixed  $store
     * @return string
     */
    public function getConfiguration($website = null, $store = null)
    {
        $helper = $this->_getConfigHelper();

        $scopeModel         = $helper->resolveScope($website, $store);
        $scope              = $scopeModel->getScope();
        $scopeId            = $scopeModel->getScopeId();
        $notConfiguredNoted = Mage::helper('styla_connect')->__('Not Configured.');


        $fields = '<ul>';
        foreach ($this->_getSummaryConfigurationFields() as $fieldName => $fieldLabel) {
            $fieldPath = $helper->getApiConfigurationField($fieldName);

            $node = $helper->getConfigurationNode($fieldPath, $scope, $scopeId);
            $fields .= '<li>' . $fieldLabel . ': ' . ($node ? $node : '<i>' . $notConfiguredNoted . '</i>') . '</li>';
        }

        $fields .= "</ul>";

        return $fields;
    }
    
    /**
     * Get the configuration of a single field, within the given scope
     * 
     * @param string $fieldConfigurationPath
     * @param mixed $website
     * @param mixed $store
     * @return string|null
     */
    public function getFieldConfiguration($fieldConfigurationPath, $website = null, $store = null)
    {
        $helper = $this->_getConfigHelper();

        return $helper->getFieldConfiguration($fieldConfigurationPath, $website, $store);
    }
    
    /**
     * 
     * @return array
     */
    protected function _getSummaryConfigurationFields()
    {
        if(null === $this->_summaryFields) {
            $this->_summaryFields = array(
                'client' => $this->__('Client Name'),
                'rootpath' => $this->__('Magazine Url'),
            );
        }
        return $this->_summaryFields;
    }
}