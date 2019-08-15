<?php
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->getConnection()
    ->addColumn($installer->getTable('catalog/product_option_type_value'), 'image', 'VARCHAR(255) NULL');
$installer->endSetup();
