<?php

class Styla_Connect_Model_Adminhtml_Observer
{
    public function checkModuleConfiguration($observer)
    {
        $event = $observer->getEvent();
        $configHelper = Mage::helper('styla_connect/config');

        /**
         * on each configuration re-save, when changing the mode, we should try automatically retrieving the proper module configuration.
         * if we fail to do that, the user will be shown an info box nagging him to register
         * 
         */
        if(!$configHelper->isConfiguredForThisMode()) {
            $connector = Mage::getSingleton('styla_connect/styla_api_oauth_connector');
            
            $connector->tryUpdatingStylaAccessConfiguration();
        }
    }
}