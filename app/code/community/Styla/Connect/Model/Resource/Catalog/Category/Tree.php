<?php
class Styla_Connect_Model_Resource_Catalog_Category_Tree extends Mage_Catalog_Model_Resource_Category_Tree
{
    /**
     * Load a category tree, with up to $maxLevel of children category levels.
     * If 1, then only loads the first level of children, etc.
     * 
     * @param int $parentId
     * @param int $maxLevels
     * @return \Styla_Connect_Model_Resource_Catalog_Category_Tree|boolean
     * @throws Exception
     */
    public function loadMaxChildrenLevels($parentId, $additionalAttributes = array(), $maxLevels = 0)
    {
        if(!$parentId) {
            throw new Exception("Parent id for loading the tree is missing.");
        }
        
        $levelField = $this->_conn->quoteIdentifier('level');
        $pathField  = $this->_conn->quoteIdentifier('path');
        
        //we want the parent's level
        $select = $this->_conn->select()
                ->from($this->_table, array('level', 'path'))
                ->where('entity_id = ?', $parentId);
        $parentData = $this->_conn->fetchRow($select);
        
        $collection = $this->_createCollectionDataSelect(true, $additionalAttributes);
        
        //the categories we take may only be the children of our parent, up to the max level we're interested in
        if($maxLevels) {
            $collection->where("{$levelField} <= ?", $maxLevels + $parentData['level']); //desiredLevels + our current level we're on
        }        
        $collection->where("{$pathField} LIKE '{$parentData['path']}%'");
        
        //add all parents of our category, as well
        //as they're needed for building the full tree, later on
        $parentPath = $parentData['path'];
        $grandParents = explode("/", $parentPath);
        if(!empty($grandParents)) {
            $collection->orWhere('e.entity_id in (?)', $grandParents);
        }

        // get array of records and add them as nodes to the tree
        // @see method ->loadByIds()
        $arrNodes = $this->_conn->fetchAll($collection);
        if (!$arrNodes) {
            return false;
        }
        $childrenItems = array();
        foreach ($arrNodes as $key => $nodeInfo) {
            $pathToParent = explode('/', $nodeInfo[$this->_pathField]);
            array_pop($pathToParent);
            $pathToParent = implode('/', $pathToParent);
            $childrenItems[$pathToParent][] = $nodeInfo;
        }
        
        $this->addChildNodes($childrenItems, '', null);
        return $this;
    }
}