<?php
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;


$installer->startSetup();

$table               = $installer->getTable('styla_connect/magazine');
$storeIndexName      = $installer->getIdxName('styla_connect/magazine', 'store_id');
$storeForeignKeyName = $installer->getFkName(
    'styla_connect/magazine', 'store_id',
    'core/store', 'store_id'
);

//set a unique index on store + front_name to prevent a having multiple magazines
//on the same store with the same url
$storeFrontNameIndex = $installer->getIdxName(
    'styla_connect/magazine',
    ['store_id', 'front_name'],
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->run("CREATE TABLE `$table` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` SMALLINT(5) UNSIGNED NULL,
  `is_active` TINYINT(1) UNSIGNED NULL,
  `is_default` TINYINT(1) UNSIGNED NULL,
  `use_magento_layout` TINYINT(1) UNSIGNED NULL,
  `include_in_navigation` TINYINT(1) UNSIGNED NULL,
  `navigation_label` VARCHAR(255) NULL,
  `front_name` VARCHAR(255) NULL,
  `client_name` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX `$storeIndexName` (`store_id` ASC),
  CONSTRAINT `$storeForeignKeyName`
    FOREIGN KEY (`store_id`)
    REFERENCES `{$installer->getTable('core/store')}` (`store_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE);
");


$installer->run("
    ALTER TABLE `$table`
    ADD INDEX `$storeFrontNameIndex` (`store_id` ASC, `front_name` ASC);
");


$installer->endSetup();

