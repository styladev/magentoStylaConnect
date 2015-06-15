<?php

/**
 * Class Styla_Connect_Model_Api2_Converter_Product_ImageCollection
 */
class Styla_Connect_Model_Api2_Converter_Product_ImageCollection
    extends Styla_Connect_Model_Api2_Converter_Product_Image
{
    public function runConverter(Varien_Object $dataObject)
    {
        $objectImageSrcs   = $this->getImages($dataObject);
        $objectImageCaptions = $this->getImageCaptions($dataObject);

        if (!$objectImageSrcs) {
            return;
        }

        $images = [];

        foreach ($objectImageSrcs as $index => $src) {
            $image = array(
                'src' => $src,
                'caption' => ''
            );
            if (isset($objectImageCaptions[$index])) {
                $image['caption'] = $objectImageCaptions[$index];
            }
            $images[] = $image;
        }

        if ($this->getArgument('limit')) {
            $images = array_slice($images, 0, $this->getArgument('limit'));
        }

        $stylaField = $this->getStylaField();
        $dataObject->setData($stylaField, $images);
    }
}