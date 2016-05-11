<?php

/**
 * Class Styla_Connect_Model_Adminhtml_System_Config_Source_Mode
 *
 */
class Styla_Connect_Model_Adminhtml_System_Config_Source_Product_Attribute
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        /** @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Collection $collection */
        $collection = Mage::getResourceModel('catalog/eav_mysql4_product_attribute_collection');
        $options    = array();


        foreach ($collection as $attribute) {
            /** @var Mage_Catalog_Model_Entity_Attribute $attribute */
            $options[] = array(
                'label' => $attribute->getStoreLabel(0),
                'value' => $attribute->getAttributeCode()
            );

        }
        return $options;
    }
}
