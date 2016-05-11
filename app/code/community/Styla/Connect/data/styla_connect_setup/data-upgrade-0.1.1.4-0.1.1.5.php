<?php
$installer = $this;
$installer->startSetup();

/**
 * if the store was previously set to the production mode, we'll need to
 * copy the configuration values to the new _prod paths
 *
 */
$configHelper = Mage::helper('styla_connect/config');
$currentlyConfiguredFor = Mage::getStoreConfig('styla_connect/basic/mode');

if ($currentlyConfiguredFor == "prod") {
    $configuration = $configHelper->getConfiguration();

    //there's a current configuration for prod enabled, so we'll need to fix the paths for it
    foreach ($configHelper->getApiConfigurationFields() as $fieldName => $field) {
        $fieldValue = Mage::getStoreConfig($field);
        if ($fieldValue) {
            $configuration->saveConfig(
                $configHelper->getApiConfigurationFieldByMode($field, 'prod', true),
                $fieldValue
            );
        }
    }
}

$installer->endSetup();