<?php

class Tommy_DebugInfo_Model_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract
{
    protected $_isPkAutoIncrement = false;
    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct() {
        $this->_init('tommy_debuginfo/log', 'id');
    }
}