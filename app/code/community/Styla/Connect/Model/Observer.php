<?php

class Styla_Connect_Model_Observer
{
    public function addNavigationLink(Varien_Event_Observer $observer)
    {
        $magazines = $this->getMagazines();
        /** @var Varien_Data_Tree_Node $menu */
        $menu      = $observer->getMenu();
        /** @var Varien_Data_Tree $tree */
        $tree      = $menu->getTree();

        /** @var Styla_Connect_Helper_Data $helper */
        $helper = Mage::helper('styla_connect');

        foreach ($magazines as $magazine) {
            /** @var Styla_Connect_Model_Magazine $magazine */

            $magazineUrl      = $helper->getAbsoluteMagazineUrl($magazine);
            $magazineMenuNode = new Varien_Data_Tree_Node(
                array(
                    'name' => $magazine->getNavigationLabel(),
                    'id'   => 'styla-magazine-' . $magazine->getId(),
                    'url'  => $magazineUrl,
                ),
                'id',
                $tree,
                $menu
            );

            $menu->addChild($magazineMenuNode);
        }
    }

    protected function getMagazines()
    {
        /** @var Styla_Connect_Model_Resource_Magazine_Collection $collection */
        $collection = Mage::getResourceModel('styla_connect/magazine_collection');

        $collection->addTopNavigationFilter(Mage::app()->getStore()->getId());

        return $collection;
    }
    
    public function stylaHomepageGetData($observer)
    {
        if(!Mage::getStoreConfig('styla_connect/homepage/enabled', Mage::app()->getStore())) {
            return false;
        }
        
        $routeName = Mage::app()->getRequest()->getRouteName();
        $identifier = Mage::getSingleton('cms/page')->getIdentifier();

        if($routeName == 'cms' && $identifier == 'home') {
            $observer->getEvent()->getLayout()->getUpdate()
                ->addHandle('styla_homepage');

            $frontName = '';
            
            $magazine = Mage::getModel('styla_connect/magazine')->loadByFrontName($frontName);
            if (!$magazine || !$magazine->isActive()) {
                return false;
            }

            Mage::register('current_magazine', $magazine);
            
            $path = '/';
            Mage::register('current_styla_api_path', $path);

            $page = Mage::getModel('styla_connect/page')
                ->load($path);

            if (!$page->exist()) {
                $this->_forward('noRoute');

                return;
            }

            Mage::register('current_magazine_page', $page);
        }
    }
    
    public function stylaHomepageAddOptions($observer)
    {
        if(!Mage::getStoreConfig('styla_connect/homepage/enabled', Mage::app()->getStore())) {
            return false;
        }
        
        $routeName = Mage::app()->getRequest()->getRouteName();
        $identifier = Mage::getSingleton('cms/page')->getIdentifier();

        if($routeName == 'cms' && $identifier == 'home') {
            $layout = $observer->getEvent()->getLayout();
            $page = Mage::registry('current_magazine_page');
            $layoutHelper = Mage::helper('styla_connect/layout');

            $layoutHelper->setStylaData($page, $layout);
        }
    }
}
