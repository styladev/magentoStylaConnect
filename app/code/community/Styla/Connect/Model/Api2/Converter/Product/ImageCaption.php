<?php

/**
 * Class Styla_Connect_Model_Api2_Converter_Product_ImageCaption
 */
class Styla_Connect_Model_Api2_Converter_Product_ImageCaption
    extends Styla_Connect_Model_Api2_Converter_Product_ImageAbstract
{
    /**
     * @param Varien_Object $dataObject
     */
    public function runConverter(Varien_Object $dataObject)
    {
        $imageCaptions = $this->getImageCaptions($dataObject);

        //in this version of styla api, we actually only care about the first image's caption, so:
        if (is_array($imageCaptions)) {
            $imageCaptions = reset($imageCaptions);
        }

        $stylaField = $this->getStylaField();
        $dataObject->setData($stylaField, $imageCaptions);
    }

}