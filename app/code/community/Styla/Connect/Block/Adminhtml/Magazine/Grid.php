<?php

/**
 * Styla_Connect_Block_Adminhtml_Magazine_Grid
 *
 */
class Styla_Connect_Block_Adminhtml_Magazine_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();

        // Set some defaults for our grid
        $this->setDefaultSort('id');
        $this->setId('styla_magazine_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('styla_magazine_grid_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('styla_connect/magazine_collection');
        $collection->joinStoreCode();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {
        $this->addColumn(
            'is_active',
            array(
                'header' => $this->__('Active'),
                'index' => 'is_active',
                'type' => 'options',
                'options' => array(
                    '1' => $this->__('Yes'),
                    '0' => $this->__('No'),
                )
            )
        );

        $this->addColumn(
            'store_code',
            array(
                'header' => $this->__('Store'),
                'index' => 'store_code'
            )
        );

        $this->addColumn(
            'is_default',
            array(
                'header' => $this->__('Default'),
                'index' => 'is_default',
                'type' => 'options',
                'options' => array(
                    '1' => $this->__('Yes'),
                    '0' => $this->__('No'),
                )
            )
        );


        $this->addColumn(
            'front_name',
            array(
                'header' => $this->__('Front Name'),
                'index' => 'front_name'
            )
        );


        $this->addColumn(
            'client_name',
            array(
                'header' => $this->__('Client Name'),
                'index' => 'client_name'
            )
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {

        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

}
