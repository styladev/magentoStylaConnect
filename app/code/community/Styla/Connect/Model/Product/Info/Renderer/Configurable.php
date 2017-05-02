<?php

/**
 * Class Styla_Connect_Model_Product_Info_Renderer_Configurable
 */
class Styla_Connect_Model_Product_Info_Renderer_Configurable
    extends Styla_Connect_Model_Product_Info_Renderer_Abstract
{
    protected $_product;

    public function getProduct()
    {
        return $this->_product;
    }

    public function getProductPrices(Mage_Catalog_Model_Product $product)
    {
        $basePrice = $product->getFinalPrice();

        /** @var Mage_Catalog_Model_Product_Type_Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance(true);

        $useProducts            = $typeInstance->getUsedProducts(null, $product);
        $configurableAttributes = $typeInstance->getConfigurableAttributes($product);

        $productPrices = [];
        $priceData     = [];

        //find all option pricing data
        foreach ($configurableAttributes as $attribute) {
            /** @var Mage_Catalog_Model_Product_Type_Configurable_Attribute $attribute */
            $prices = $attribute->getData('prices');
            if (!$prices) {
                continue;
            }
            $attributeId = $attribute['attribute_id'];
            foreach ($prices as $price) {
                $priceData[$attributeId][$price['value_index']] = $price;
            }
        }

        foreach ($useProducts as $childProduct) {
            /** @var Mage_Catalog_Model_Product $childProduct */
            $productId = $childProduct->getId();

            $price = $basePrice;
            foreach ($configurableAttributes as $attribute) {
                $attributeId    = $attribute['attribute_id'];
                $attributeCode  = $attribute['product_attribute']->getAttributeCode();
                $attributeValue = $childProduct->getData($attributeCode);

                if (isset($priceData[$attributeId][$attributeValue])) {
                    $optionPriceData = $priceData[$attributeId][$attributeValue];

                    $price = $price + $this->_calculatePriceAddition(
                            $basePrice,
                            $optionPriceData['pricing_value'],
                            $optionPriceData['is_percent']
                        );
                }
            }
            $product->setConfigurablePrice($price);
            $product->setParentId(true);
            Mage::dispatchEvent(
                'catalog_product_type_configurable_price',
                array('product' => $product)
            );

            $productPrices[$productId] = $this->_convertPrice($product->getConfigurablePrice(), true);
        }

        return $productPrices;
    }

    /**
     * percentage price adjustments are relative to the "base price" not the option prices
     *
     * @param $basePrice
     * @param $value
     * @param $isPercent
     * @return float|int
     */
    protected function _calculatePriceAddition($basePrice, $value, $isPercent)
    {
        if ($isPercent && !empty($value)) {
            return $basePrice * $value / 100;
        }

        return $value;
    }

    /**
     * Add configurable product's options data to the product info array.
     * This method is basically the same logic that's used for generating the options selects on the product view page.
     * For reference, see ->getJsonConfig() method of the product view block.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array                      $productInfo
     * @return array
     */
    protected function _collectAdditionalProductInfo($product, $productInfo)
    {
        parent::_collectAdditionalProductInfo($product, $productInfo);

        /**
         * the following configurable-data collecting code is a simplified version
         * of the logic used to gather product options on a catalog/product/view details page
         */
        $this->_product = $product;

        $productPrices = $this->getProductPrices($product);


        //add configurable-specific data.
        $configurableInfo = array();

        //we're loading each configurable attribute

        //these are the simple products this configurable product contains:
        $simpleChildren         = array();
        $configurableAttributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);


        $useProducts = $product->getTypeInstance(true)
            ->getUsedProducts(null, $product);

        foreach ($useProducts as $product) {
            $productId = $product->getId();

            foreach ($configurableAttributes as $attribute) {
                $productAttribute   = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue     = $product->getData($productAttribute->getAttributeCode());
                if (!isset($simpleChildren[$productAttributeId])) {
                    $simpleChildren[$productAttributeId] = array();
                }

                if (!isset($simpleChildren[$productAttributeId][$attributeValue])) {
                    $simpleChildren[$productAttributeId][$attributeValue] = array();
                }

                $simpleChildren[$productAttributeId][$attributeValue][$productId] = array(
                    'id'       => $productId,
                    'sku'      => $product->getSku(),
                    'saleable' => $product->isSaleable(),
                    'price'    => $productPrices[$productId]
                );
            }
        }


        //and for each attribute, all it's options and simples associated to them
        foreach ($configurableAttributes as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId      = $productAttribute->getId();

            $attributeInfo = array(
                'id'    => $attributeId,
                'label' => $productAttribute->getFrontendLabel(),
            );

            $attributeOptions = array();
            $prices           = $attribute->getPrices();

            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if (isset($simpleChildren[$attributeId][$value['value_index']])) {
                        $productsIndex = $simpleChildren[$attributeId][$value['value_index']];
                    } else {
                        $productsIndex = array();
                    }

                    $attributeOptions[] = array(
                        'id'       => $value['value_index'],
                        'label'    => $value['label'],
                        'products' => $productsIndex
                    );
                }
            }

            $attributeInfo['options'] = $attributeOptions;

            $configurableInfo[] = $attributeInfo;
        }

        //merge this new configurable info with the basic info array
        $productInfo['attributes'] = $configurableInfo;

        return $productInfo;
    }

    /**
     * Calculation real price
     *
     * @param float $price
     * @param bool  $isPercent
     * @return mixed
     */
    protected function _preparePrice($price, $isPercent = false)
    {
        if ($isPercent && !empty($price)) {
            $price = $this->getProduct()->getFinalPrice() * $price / 100;
        }

        return $this->_registerJsPrice($this->_convertPrice($price, true));
    }

    /**
     * Calculation price before special price
     *
     * @param float $price
     * @param bool  $isPercent
     * @return mixed
     */
    protected function _prepareOldPrice($price, $isPercent = false)
    {
        if ($isPercent && !empty($price)) {
            $price = $this->getProduct()->getPrice() * $price / 100;
        }

        return $this->_registerJsPrice($this->_convertPrice($price, true));
    }

    /**
     * Replace ',' on '.' for js
     *
     * @param float $price
     * @return string
     */
    protected function _registerJsPrice($price)
    {
        return str_replace(',', '.', $price);
    }

    /**
     * Convert price from default currency to current currency
     *
     * @param float   $price
     * @param boolean $round
     * @return float
     */
    protected function _convertPrice($price, $round = false)
    {
        if (empty($price)) {
            return 0;
        }

        $price = $this->_getStore()->convertPrice($price);
        if ($round) {
            $price = $this->_getStore()->roundPrice($price);
        }

        return $price;
    }
}
