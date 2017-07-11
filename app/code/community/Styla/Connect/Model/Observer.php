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
}
