<?php

/**
 * Class Styla_Connect_Model_Product_Info_Renderer_Grouped
 */
class Styla_Connect_Model_Product_Info_Renderer_Grouped
    extends Styla_Connect_Model_Product_Info_Renderer_Abstract
{

    /**
     * Add grouped product's options data to the product info array.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $productInfo
     *
     * @return array
     */
    protected function _collectAdditionalProductInfo(Mage_Catalog_Model_Product $product, $productInfo)
    {
        parent::_collectAdditionalProductInfo($product, $productInfo);

        $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);

        $productInfo['price'] = $this->getGroupedPrice($associatedProducts);
        $productInfo['products'] = $this->getFormattedAssociatedProducts($associatedProducts);

        return $productInfo;
    }

    /**
     * Returns grouped product price.
     *
     * @param array $associatedProducts
     *
     * @return string
     */
    protected function getGroupedPrice($associatedProducts)
    {
        $finalPrice = 0;
        foreach ($associatedProducts as $associatedProduct) {
            if ($associatedProduct->isSaleable()) {
                $price = Mage::helper('tax')
                    ->getPrice($associatedProduct, $associatedProduct->getFinalPrice());

                $qty = (int) $associatedProduct->getQty() === 0 ? 1 : $associatedProduct->getQty();
                $finalPrice += $price * $qty;
            }
        }

        return number_format(
            (float) $finalPrice,
            2,
            '.',
            ''
        );
    }

    /**
     * Returns formatted associated products.
     *
     * @param array $associatedProducts
     *
     * @return array
     */
    protected function getFormattedAssociatedProducts($associatedProducts)
    {
        $data = [];
        foreach ($associatedProducts as $associatedProduct) {
            $data[] = [
                'id'       => $associatedProduct->getId(),
                'name'     => $associatedProduct->getName(),
                'saleable' => (bool) $associatedProduct->isSaleable(),
            ];
        }

        return $data;
    }

}
