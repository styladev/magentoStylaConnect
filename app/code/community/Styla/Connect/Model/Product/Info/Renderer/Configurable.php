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

        //add configurable-specific data.
        $configurableInfo = array();

        //we're loading each configurable attribute

        //these are the simple products this configurable product contains:
        $simpleChildren         = array();
        $configurableAttributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);

        foreach ($product->getTypeInstance(true)
                     ->getUsedProducts(null, $product) as $product) {
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
                $simpleChildren[$productAttributeId][$attributeValue][] = array('id'       => $productId,
                                                                                'sku'       => $product->getSku(),
                                                                                'saleable' => $product->isSaleable(),
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

                    $product->setConfigurablePrice(
                        $this->_preparePrice($value['pricing_value'], $value['is_percent'])
                    );
                    $product->setParentId(true);
                    Mage::dispatchEvent(
                        'catalog_product_type_configurable_price',
                        array('product' => $product)
                    );
                    $configurablePrice = $product->getConfigurablePrice();

                    $attributeOptions[] = array(
                        'id'       => $value['value_index'],
                        'label'    => $value['label'],
                        'products' => $productsIndex,
                        'price'    => $configurablePrice,
                        'oldPrice' => $this->_prepareOldPrice($value['pricing_value'], $value['is_percent']),
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
