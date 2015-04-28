<?php

class Tommy_DebugInfo_Model_SourceConfig
{
    const DISABLED = 0;
    const CACHE = 1;
    const DB = 3;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::CACHE, 'label' => Mage::helper('adminhtml')->__('Yes (cache)')),
            array('value' => self::DB, 'label' => Mage::helper('adminhtml')->__('Yes (db & cache)')),
            array('value' => self::DISABLED, 'label' => Mage::helper('adminhtml')->__('No')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            self::CACHE => Mage::helper('adminhtml')->__('Yes (cache)'),
            self::DB => Mage::helper('adminhtml')->__('Yes (db & cache)'),
            self::DISABLED => Mage::helper('adminhtml')->__('No'),
        );
    }

}