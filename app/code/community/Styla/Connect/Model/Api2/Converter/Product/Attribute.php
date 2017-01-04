<?php

/**
 * Class Styla_Connect_Model_Api2_Converter_Product_Attribute
 */
class Styla_Connect_Model_Api2_Converter_Product_Attribute extends Styla_Connect_Model_Api2_Converter_Abstract
{
    const ARGUMENT_ATTRIBUTE_CODE = 'attribute_code';

    /**
     * @param Varien_Object $dataObject
     */
    public function runConverter(Varien_Object $dataObject)
    {
        /** @var Mage_Catalog_Model_Product $dataObject */

        $stylaField   = $this->getStylaField();
        $attribute    = $this->getAttribute();
        $magentoValue = null;

        if ($attribute) {
            $magentoValue = $attribute->getFrontend()->getValue($dataObject);
        }

        $dataObject->setData($stylaField, $magentoValue);
    }

    /**
     * @return false|Mage_Eav_Model_Entity_Attribute_Abstract
     */
    protected function getAttribute()
    {
        $attributeCode = $this->getAttributeCode();

        return Mage::getSingleton('eav/config')->getAttribute(
            Mage_Catalog_Model_Product::ENTITY,
            $attributeCode
        );
    }

    /**
     * @return null
     */
    public function getAttributeCode()
    {
        return $this->getArgument(self::ARGUMENT_ATTRIBUTE_CODE);
    }
}
