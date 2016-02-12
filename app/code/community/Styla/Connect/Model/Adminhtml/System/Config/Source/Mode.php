<?php

/**
 * Class Styla_Connect_Model_Adminhtml_System_Config_Source_Mode
 *
 */
class Styla_Connect_Model_Adminhtml_System_Config_Source_Mode
{

    /**
     * Options getter
     *
     * @return array
     */
    static public function toOptionArray()
    {
        return array(
            array(
                'value' => Styla_Connect_Helper_Config::MODE_PRODUCTION,
                'label' => Mage::helper('styla_connect')->__('Production'),
            ),
            array(
                'value' => Styla_Connect_Helper_Config::MODE_STAGE,
                'label' => Mage::helper('styla_connect')->__('Stage'),
            ),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            Styla_Connect_Helper_Config::MODE_PRODUCTION => Mage::helper('styla_connect')->__('Production'),
            Styla_Connect_Helper_Config::MODE_STAGE      => Mage::helper('styla_connect')->__('Stage'),
        );
    }

}
