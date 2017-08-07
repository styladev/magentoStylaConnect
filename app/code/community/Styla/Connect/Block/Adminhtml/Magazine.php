<?php

/**
 * Styla_Connect_Block_Adminhtml_Magazine
 *
 */
class Styla_Connect_Block_Adminhtml_Magazine extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        //where is the controller
        $this->_controller = 'adminhtml_magazine';
        $this->_blockGroup = 'styla_connect';
        //text in the admin header
        $this->_headerText = $this->__('Magazine List');
        //value of the add button
        $this->_addButtonLabel = $this->__('Add New');
        parent::__construct();
    }


}
