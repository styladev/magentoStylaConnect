<?php
class Styla_Connect_Model_Adminhtml_System_Config_Source_Category_Levels
{
    /**
     * 
     * @return string
     */
    public function toOptionArray()
    {
        $options = array();
        $options[] = array('label' => Mage::helper('styla_connect')->__("No limit - load all categories in a single API call"), 'value' => 0);
        
        $level = 1;
        $maxLevels = $this->getMaxCategoryLevel();
        if(!$maxLevels) {
            return $options;
        }
        
        while($level <= $maxLevels) {
            $options[] = array('label' => Mage::helper('styla_connect')->__("Max %s level(s) at once", $level), 'value' => $level);
            
            ++$level;
        }
        
        return $options;
    }
    
    /**
     * Return the highest used category depth level
     * 
     * @return int
     */
    public function getMaxCategoryLevel()
    {
        $resource = Mage::getSingleton('core/resource');
        $table = $resource->getTableName('catalog/category');
        
        $conn = $resource->getConnection('core_read');
        $select = $conn->select()
                ->from($table, new Zend_Db_Expr('MAX(level)'));
        
        $maxLevel = $conn->fetchCol($select);
        return reset($maxLevel);
    }
}