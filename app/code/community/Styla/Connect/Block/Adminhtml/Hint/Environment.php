<?php

/**
 * Class Styla_Connect_Block_Adminhtml_Hint_Environment
 */
class Styla_Connect_Block_Adminhtml_Hint_Environment
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'styla/connect/adminhtml/hint/environment.phtml';

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

    /**
     *
     * @return bool
     */
    public function userNeedsToRegister()
    {
        //check if we have a default magazine
        $magazine = Mage::getModel('styla_connect/magazine')->loadDefault();

        return $magazine->getId() ? false : true;
    }

    /**
     * Get styla connect assistant url
     *
     * @return string
     */
    public function getConnectUrl()
    {
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/styla_api/index');

        return $url;
    }
}
