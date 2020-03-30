<?php

class Styla_Connect_Block_Adminhtml_Magazine_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'adminhtml_magazine';
        $this->_blockGroup = 'styla_connect';

        /** @var Styla_Connect_Model_Magazine $magazine */
        $magazine = Mage::registry('current_magazine');

        $this->_updateButton('delete', 'label', $this->__('Delete Styla Page'));


        if ($magazine && $magazine->getId()) {
            $this->_addButton('save_and_continue', array(
                'label'   => $this->__('Save And Continue Edit'),
                'onclick' => "$('edit_form').writeAttribute('action', '" . $this->getUrl('*/*/save', array('continue_edit' => 1)) . "'); editForm.submit()",
                'class'   => 'save'
            ), -100);
        }

        if ($magazine->isDefault()) {
            $this->removeButton('delete');
        }
    }


    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }

        return $this->getUrl('*/*/save');
    }

    public function getHeaderText()
    {
        $magazine = Mage::registry('current_magazine');

        if ($magazine && $magazine->getId()) {
            return $this->__('Edit Styla Page');
        } else {
            return $this->__('Create new Styla Page');
        }
    }
}
