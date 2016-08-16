<?php

class Styla_Connect_Model_Observer
{
    public function addNavigationLink(Varien_Event_Observer $observer)
    {
        /** @var Styla_Connect_Helper_Config $configHelper */
        $configHelper = Mage::helper('styla_connect/config');

        if (!$configHelper->isNavigationLinkEnabled()) {
            return;
        }

        $menu = $observer->getMenu();
        $tree = $menu->getTree();

        $magazineUrl      = $configHelper->getFullMagazineUrl();
        $magazineMenuNode = new Varien_Data_Tree_Node(
            array(
                'name' => $configHelper->getNavigationLinkLabel(),
                'id'   => 'styla-magazine',
                'url'  => $magazineUrl,
            ), 'id', $tree, $menu
        );

        $menu->addChild($magazineMenuNode);
    }
}