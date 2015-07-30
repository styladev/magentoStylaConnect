<?php
class Styla_Connect_Model_Product_Info_Renderer_Configurable extends Styla_Connect_Model_Product_Info_Renderer_Abstract
{
    /**
     * Add configurable product's options data to the product info array.
     * This method is basically the same logic that's used for generating the options selects on the product view page.
     * For reference, see ->getJsonConfig() method of the product view block.
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param array $productInfo
     * @return array
     */
    protected function _collectAdditionalProductInfo($product, $productInfo)
    {
        parent::_collectAdditionalProductInfo($product, $productInfo);
        
        /**
         * the following configurable-data collecting code is a simplified version
         * of the logic used to gather product options on a catalog/product/view details page
         */
        
        //add configurable-specific data.
        $configurableInfo = array();
        
        //we're loading each configurable attribute
        
        //these are the simple products this configurable product contains:
        $simpleChildren = array();
        $configurableAttributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
        
        foreach ($product->getTypeInstance(true)
                ->getUsedProducts(null, $product) as $product) {
            $productId  = $product->getId();

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
                $simpleChildren[$productAttributeId][$attributeValue][] = array('id' => $productId, 'saleable' => $product->isSaleable());
            }
        }
        
        //and for each attribute, all it's options and simples associated to them
        foreach($configurableAttributes as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();
            
            $attributeInfo = array(
                'id' => $attributeId,
                'label' => $productAttribute->getName(),
            );
            
            $attributeOptions = array();
            $prices = $attribute->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if (isset($simpleChildren[$attributeId][$value['value_index']])) {
                        $productsIndex = $simpleChildren[$attributeId][$value['value_index']];
                    } else {
                        $productsIndex = array();
                    }
                    
                    $attributeOptions[] = array(
                        'id' => $value['value_index'],
                        'label' => $value['label'],
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
}