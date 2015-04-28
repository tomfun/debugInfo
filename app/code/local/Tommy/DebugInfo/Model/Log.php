<?php

/**
 * @method float getId()
 * @method setId(float $id)
 * @method string getUrl()
 * @method setUrl(string $url)
 * @method string getName()
 * @method setName(string $user)
 * @method string getUser()
 * @method setUser(string $data)
 * @method float|null|string getMaxTime()
 * @method setMaxTime(float $time)
 * Class Tommy_DebugInfo_Model_Log
 */
class Tommy_DebugInfo_Model_Log extends Mage_Core_Model_Abstract
{
    public function _construct() {
        $this->_init('tommy_debuginfo/log');
    }
}