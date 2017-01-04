<?php

/**
 * Class Styla_Connect_Model_Api2_Converter_Product_Image
 */
class Styla_Connect_Model_Api2_Converter_Product_Image
    extends Styla_Connect_Model_Api2_Converter_Product_ImageAbstract
{
    /**
     * @param Varien_Object $dataObject
     */
    public function runConverter(Varien_Object $dataObject)
    {
        /** @var Mage_Catalog_Model_Product $dataObject */

        $objectImages = $this->getImages($dataObject);
        if (!$objectImages) {
            return;
        }

        //single image if you want more use the image collection
        if (is_array($objectImages)) {
            $objectImages = reset($objectImages);
        }

        $stylaField = $this->getStylaField();
        $dataObject->setData($stylaField, $objectImages);
    }

    /**
     * Loads the image, but allows you to define a specific collection column to load it from.
     * Used to get the "main_image" from the collection.
     *
     * @param \Varien_Object $dataObject
     * @return array|boolean
     */
    public function getImages(\Varien_Object $dataObject)
    {
        if ($specificImageColumn = $this->getArgument('collection_field')) {
            return array($this->getImageUrl($dataObject->getData($specificImageColumn)));
        }

        return parent::getImages($dataObject);
    }
}
