<?php

/**
 * Class Styla_Connect_Model_Product_Info_Renderer_Bundle
 */
class Styla_Connect_Model_Product_Info_Renderer_Bundle
    extends Styla_Connect_Model_Product_Info_Renderer_Abstract
{

    /**
     * @var Mage_Bundle_Model_Resource_Option_Collection
     */
    private $optionCol;

    /**
     * Add bundle product's options data to the product info array.
     * @param Mage_Catalog_Model_Product $product
     * @param array $productInfo
     * @return array
     */
    protected function _collectAdditionalProductInfo(Mage_Catalog_Model_Product $product, $productInfo)
    {
        parent::_collectAdditionalProductInfo($product, $productInfo);

        $this->prepareOptionCol($product);

        $productInfo['price'] = $productInfo['priceMin'] = $this->getBundlePrice($product);
        $productInfo['priceMax'] = $this->getBundlePrice($product, 'max');
        $productInfo['bundles'] = $this->getBundleProductsInfo($product);

        return $productInfo;
    }

    /**
     * Returns formatted associated products.
     *
     * @return array
     */
    protected function getBundleProductsInfo()
    {
        $data = [];
        foreach ($this->optionCol as $option) {
            if ($option->required) {
                $data[] = [
                    'id'       => $option->getId(),
                    'type'     => $option->getType(),
                    'required' => (bool) $option->getRequired(),
                    'position' => (int) $option->getPosition(),
                    'products' => $this->getFormattedAssociatedProducts($option->getSelections()),
                ];
            }
        }

        return $data;
    }

    /**
     * Returns min or max price of the bundle product.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $type
     *
     * @return string
     */
    private function getBundlePrice($product, $type = 'min')
    {
        if ($product->getFinalPrice()) {
            return $product->getFormatedPrice();
        }

        $price = $product->getPrice();

        foreach ($this->optionCol as $option) {
            if ($option->required) {
                $selections = $option->getSelections();
                if ($type === 'min') {
                    $minPrice = $this->getMinPrice($selections);
                } else {
                    $minPrice = $this->getMaxPrice($selections);
                }
                $price += round($minPrice, 2);
            }
        }

        return number_format(
            $price,
            2,
            '.',
            ''
        );
    }

    /**
     * Returns max price of given products.
     *
     * @param array $selections
     *
     * @return int
     */
    private function getMaxPrice($selections)
    {
        $maxPrice = 0;
        foreach ($selections as $product) {
            if ((float) $product->getFinalPrice() > $maxPrice) {
                $maxPrice = $product->getFinalPrice();
            }
        }

        return $maxPrice;
    }

    /**
     * Returns min price of given products.
     *
     * @param array $selections
     *
     * @return int
     */
    private function getMinPrice($selections)
    {
        $minPrice = PHP_INT_MAX;
        foreach ($selections as $product) {
            if ((float) $product->getFinalPrice() < $minPrice) {
                $minPrice = $product->getFinalPrice();
            }
        }

        return $minPrice;
    }

    /**
     * Returns formatted associated products.
     *
     * @param array $selections
     *
     * @return array
     */
    private function getFormattedAssociatedProducts($selections)
    {
        $data = [];
        foreach ($selections as $selection) {
            $data[] = [
                'id'       => $selection->getId(),
                'name'     => $selection->getName(),
                'oldPrice' => $this->getOldPrice($selection) ?: null,
                'price'    => Mage::helper('tax')->getPrice($selection, $selection->getFinalPrice()),
            ];
        }

        return $data;
    }

    /**
     * Prepares option collection for bundle product.
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return void
     */
    private function prepareOptionCol($product)
    {
        $this->optionCol = $product->getTypeInstance(true)
            ->getOptionsCollection($product);
        $selectionCol = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        $this->optionCol->appendSelections($selectionCol);
    }

}
