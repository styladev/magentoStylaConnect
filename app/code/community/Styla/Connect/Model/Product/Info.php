<?php

/**
 * Class Styla_Connect_Model_Product_Info
 */
class Styla_Connect_Model_Product_Info
{
    const RENDERER_ALIAS     = 'styla_connect/product_info_renderer_';
    const EVENT_GET_RENDERER = 'styla_connect_get_product_info_renderer';

    protected $_product;

    /**
     * Get the product details as array
     *
     * @return array
     */
    public function getInfo()
    {
        return $this->_getProductInfoRenderer()->render($this->getProduct());
    }

    /**
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function setProduct(Mage_Catalog_Model_Product $product)
    {
        $this->_product = $product;
    }

    /**
     *
     * @return Mage_Catalog_Model_Product
     * @throws Exception
     */
    public function getProduct()
    {
        if (!$this->_product) {
            throw new Exception('No product was set.');
        }

        return $this->_product;
    }

    /**
     *
     * @return Styla_Connect_Model_Product_Info_Renderer_Abstract
     */
    protected function _getProductInfoRenderer()
    {
        $productType   = $this->getProduct()->getTypeId();
        $rendererAlias = self::RENDERER_ALIAS;

        switch ($productType) {
            case 'configurable':
                $rendererAlias .= 'configurable';
                break;
            default:
                $rendererAlias .= 'abstract';
        }

        $transportObject = new Varien_Object();
        $transportObject->setData('renderer_alias', $rendererAlias);
        $transportObject->setData('product', $this->getProduct());
        Mage::dispatchEvent(self::EVENT_GET_RENDERER, array('transport_object' => $transportObject));

        $rendererAlias = $transportObject->getData('renderer_alias');
        $renderer      = Mage::getSingleton($rendererAlias);

        return $renderer;
    }
}