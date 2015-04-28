<?php
$this->startSetup();

try {
    $this->run("
        CREATE TABLE IF NOT EXISTS `{$this->getTable('tommy_debuginfo/log')}` (
            `id` DECIMAL(20, 8) NOT NULL,
            `url` varchar(2048),
            `name` varchar(255),
            `user` varchar(80),
            `max-time` varchar(20),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
} catch (Exception $ex) {
    Mage::logException($ex);
}
$this->endSetup();