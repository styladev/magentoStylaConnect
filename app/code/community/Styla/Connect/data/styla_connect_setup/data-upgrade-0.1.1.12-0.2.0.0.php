<?php
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();
$configCollection = Mage::getResourceModel('core/config_data_collection');
$basePath         = 'styla_connect/basic';

$configCollection->addPathFilter($basePath);
$basePath .= '/';

$configs = [];
if ($configCollection->count()) {
    //only do this if we have a config at all

    //find out where we do have store/website configurations for
    foreach ($configCollection as $config) {
        $scope = $config->getData('scope');
        if ($scope === 'default') {
            continue;
        }
        if (!isset($configs[$scope])) {
            $configs[$scope] = array();
        }

        $configs[$scope][] = $config->getData('scope_id');
    }

    $storesWithCustomConfig = array();
    if (isset($configs['stores'])) {
        $storesWithCustomConfig = $configs['stores'];
    }

    if (isset($configs['websites'])) {
        foreach ($configs['websites'] as $websiteId) {
            $websiteStoreIds        = Mage::app()->getWebsite($websiteId)->getStoreIds();
            $storesWithCustomConfig = array_merge($storesWithCustomConfig, $websiteStoreIds);
        }
    }

    $storesWithCustomConfig = array_unique($storesWithCustomConfig);

    //create the default Magazine
    /** @var Styla_Connect_Model_Magazine $magazine */
    $magazine = Mage::getModel('styla_connect/magazine');
    $magazine->setData(
        array(
            'is_default'            => 1,
            'is_active'             => Mage::getStoreConfig('styla_connect/basic/enabled', 0) ? 1 : 0,
            'client_name'           => Mage::getStoreConfig('styla_connect/basic/username', 0),
            'front_name'            => Mage::getStoreConfig('styla_connect/basic/frontend_name', 0),
            'include_in_navigation' => Mage::getStoreConfig('styla_connect/frontend/menu_link_enabled', 0) ? 1 : 0,
            'navigation_label'      => Mage::getStoreConfig('styla_connect/frontend/menu_link_label', 0),
            'use_magento_layout'    => Mage::getStoreConfig('styla_connect/basic/use_magento_layout', 0),
        )
    );
    $magazine->save();


    foreach ($storesWithCustomConfig as $storeId) {
        $magazine = Mage::getModel('styla_connect/magazine');
        $magazine->setData(
            array(
                'store_id'              => $storeId,
                'is_default'            => 0,
                'is_active'             => Mage::getStoreConfig('styla_connect/basic/enabled', $storeId) ? 1 : 0,
                'client_name'           => Mage::getStoreConfig('styla_connect/basic/username', $storeId),
                'front_name'            => Mage::getStoreConfig('styla_connect/basic/frontend_name', $storeId),
                'include_in_navigation' => Mage::getStoreConfig(
                    'styla_connect/frontend/menu_link_enabled',
                    $storeId
                ) ? 1 : 0,
                'navigation_label'      => Mage::getStoreConfig('styla_connect/frontend/menu_link_label', $storeId),
                'use_magento_layout'    => Mage::getStoreConfig('styla_connect/basic/use_magento_layout', $storeId),
            )
        );
        $magazine->save();
    }
}
$installer->endSetup();
