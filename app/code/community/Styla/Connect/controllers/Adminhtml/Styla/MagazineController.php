<?php

/**
 * Styla_Connect_Adminhtml_Styla_MagazineController
 *
 */
class Styla_Connect_Adminhtml_Styla_MagazineController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->_title($this->__('Styla pages List'));

        $this->loadLayout()
            ->_setActiveMenu('cms/page')
            ->_addBreadcrumb(Mage::helper('cms')->__('CMS'), Mage::helper('cms')->__('CMS'))
            ->_addBreadcrumb(Mage::helper('cms')->__('Manage Styla Pages'), Mage::helper('cms')->__('Manage Styla Pages'));

        $this->renderLayout();
    }

    /**
     * Create a new entry.
     */
    public function newAction()
    {
        $this->_forward("edit");
    }

    /**
     * Edit an entry.
     */
    public function editAction()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        /** @var Styla_Connect_Model_Magazine $magazine */
        $magazine = Mage::getModel('styla_connect/magazine');

        // 2. Initial checking
        if ($id) {
            $magazine->load($id);
            if (!$magazine->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('styla_connect')->__('This Styla Page does no longer exists.')
                );
                $this->_redirect('*/*/');

                return;
            }
        }

        // 3. Set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $magazine->setData($data);
        }

        // 4. Register model to use later in blocks
        Mage::register('current_magazine', $magazine);

        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveAction()
    {
        /** @var Styla_Connect_Model_Magazine $magazine */
        $magazine     = Mage::getModel('styla_connect/magazine');
        $magazineData = $this->getRequest()->getParams();

        if (isset($magazineData['id'])) {
            $magazine->load((int)$magazineData['id']);
            unset($magazineData['id']);
        }
        $magazine->addData($magazineData);

        try {
            $magazine->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Styla Page successfully saved')
            );
            Mage::getSingleton('adminhtml/session')->setFormData(false);

            if ($this->getRequest()->getParam('continue_edit') && $magazine->getId()) {
                $this->_redirect('*/*/edit', array('id' => $magazine->getId()));
            } else {
                $this->_redirect('*/*/');
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->setFormData($this->getRequest()->getParams());
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__($e->getMessage())
            );
            $this->_getSession()->setFormData($magazineData);
            $this->_redirect('*/*/edit', array('id' => $magazine->getId()));
        }

    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('styla_connect/adminhtml_magazine_grid')->toHtml()
        );
    }

    public function deleteAction()
    {
        $magazineId = $this->getRequest()->getParam('id', false);

        /** @var Styla_Connect_Model_Magazine $magazine */
        $magazine = Mage::getModel('styla_connect/magazine')->load($magazineId);
        if ($magazine->getId()) {
            if ($magazine->isDefault()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    $this->__('Deleting the Styla Page is not supported!')
                );
                return $this->_redirect('*/*/edit', array('id' => $magazine->getId()));
            } else {
                $magazine->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Styla Page successfully deleted!')
                );
            }
        }

        // go to grid
        $this->_redirect('*/*');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('cms/styla_magazine');
    }
}
