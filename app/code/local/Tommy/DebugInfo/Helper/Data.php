<?php

/**
 * Data.php
 *
 * Magento helper for debugging, logging, performance calculation
 * Written by Tomfun tomfun1990@gmail.com
 *
 * @category   Tommy
 * @package    Tommy_DebugInfo
 * @author     turumburum.com
 */
class Tommy_DebugInfo_Helper_Data extends Mage_Core_Helper_Abstract
{
    const SESSION_KEY = 'Tommy_DebugInfo_Helper_Data';
    const FRONT_NAME = 'debug_info';
    const FRONT_VIEW_ACTION_NAME = 'view';

    static         $_i          = 1;
    static         $_blocksInfo = array();
    private static $_me         = null;

    protected $_performance       = array();
    protected $_debugOutput       = array();
    protected $_directOutput      = array();
    protected $_enabledController = null;
    protected $_enabledSession    = null;
    protected $_debugCompareHtml  = null;
    protected $_debugCustomHints  = null;
    protected $_sessionId         = null;
    protected $_sessionTags       = array('debug_data');

    /** @var bool Using cache flag */
    protected $_useCache = true;

    /**
     * @return $this|Tommy_DebugInfo_Helper_Data|null
     */
    public static function getMe() {
        if (self::$_me) {
            return self::$_me;
        } else {
            self::$_me = Mage::helper('tommy_debuginfo');
        }
        return self::$_me;
    }

    public function __construct() {
        // rewrite magento
        if (property_exists('Mage_Core_Block_Abstract', '_switcher')) {
            Mage_Core_Block_Abstract::$_switcher = Mage::getStoreConfig('debug_info/debug_info/out_rewrite');
        }
        // rewrite magento
        if ($this->getDebugCustomHints() && property_exists('Mage_Core_Block_Abstract', '_switcher')) {
            Mage_Core_Block_Template::$_switcher = Mage::getStoreConfig('debug_info/debug_info/out_rewrite');
        }
        $this->_enabledSession = (bool) Mage::getStoreConfig('debug_info/debug_info/save_statistic');
        $this->_enabledController = (bool) Mage::getStoreConfig('debug_info/debug_info/frontend_controller');
        $this->_debugCustomHints = (bool) Mage::getStoreConfig('debug_info/debug_info/out_hints');
        $this->_debugCompareHtml = (bool) Mage::getStoreConfig('debug_info/debug_info/out_compare');
    }

    /** @return bool|null */
    public function getEnabledController() {
        return $this->_enabledController;
    }

    /** @return bool|null */
    public function getEnabledSession() {
        return $this->_enabledSession;
    }

    /** @return bool|null */
    public function getDebugCustomHints() {
        return $this->_debugCustomHints;
    }

    /** @return bool|null */
    public function getDebugCompareHtml() {
        return $this->_debugCompareHtml;
    }

    /**
     * @param string $unique
     * @param string $label
     * @param null   $microTime
     */
    public function addPerformanceLog($unique, $label = 'all', $microTime = null) {
        if (!isset($this->_performance[$unique])) {
            $this->_performance[$unique] = array();
        }
        if (!$microTime) {
            $microTime = microtime(true);
        }
        $this->_performance[$unique][$label] = $microTime;
    }

    public function translateJsonError($code = null) {
        if ($code === null) {
            $code = json_last_error_msg();
        }
        return $this->__($code);
    }

    /**
     * @param mixed|serializable $data
     * @param string             $varName
     * @param int                $depth
     * @param int                $jsonOptions
     * @throws Exception
     */
    public function addDirectOutput($data, $varName = '', $depth = 10, $jsonOptions = 0) {
        //array_column()
        if ($varName && count($this->_directOutput) && Mage::getIsDeveloperMode()) {
            $collision = array_filter($this->_directOutput,
                function ($a) use ($varName) {
                    return $a['varName'] == $varName;
                });
            if (count($collision)) {
                throw new Exception('This variable name already present in output');
            }
        }
        $this->_directOutput[] = array(
            'varName' => $varName ? $varName : false,
            'object'  => json_encode($data, $jsonOptions, $depth),
            'error' => $this->translateJsonError(),
            'export' => var_export($data, true),
        );
    }

    /**
     * @param        $section
     * @param        $message
     * @param null   $blockId
     * @param string $class (example: strict)
     */
    public function addDebugOutput($section, $message, $blockId = null, $class = '') {
        if (!isset($this->_debugOutput[$section])) {
            $this->_debugOutput[$section] = array();
        }
        $res = array(
            'message' => (string) $message,
            'class'   => (string) $class,
        );
        if ($blockId > 0) {
            $res['blockId'] = (int) $blockId;
        }
        $this->_debugOutput[$section][] = $res;
    }

    protected function _getSession() {
        return $this->loadFromCache(self::SESSION_KEY);
    }

    /**
     * @return null|int
     */
    public function getSessionId() {
        if (!$this->_sessionId) {
            if ($this->getEnabledSession()) {
                //                $max = 0;
                //                if (($sessions = $this->_getSession()) && count($sessions)) {
                //                    foreach ($sessions as $session) {
                //                        if ($max < $session['id']) {
                //                            $max = $session['id'];
                //                        }
                //                    }
                //                }
                //                $this->_sessionId = $max + 1;
                $this->_sessionId = time();
            }
        }
        return $this->_sessionId;
    }

    public function flushSessionData() {
        if (!$this->_enabledSession) {
            return;
        }
        $data = array(
            'performance'   => $this->_performance,
            'blocksInfo'    => self::$_blocksInfo,
            '_debugOutput'  => $this->_debugOutput,
            '_directOutput' => $this->_directOutput,
            'timers'        => Varien_Profiler::getTimers(),
        );
        /** @var Mage_Customer_Model_Session $modelSession */
        $modelSession = Mage::getSingleton('customer/session');
        if ($modelSession->isLoggedIn()) {
            $sessionName = 'user ' . $modelSession->getCustomerId();
            $user = $modelSession->getCustomer()->getName();
        } else {
            /** @var Mage_Log_Model_Visitor $visitor */
            $visitor = Mage::getSingleton('log/visitor');
            $sessionName = 'visitor ' . $visitor->getId();
            $user = ' (' . $visitor->getOnlineMinutesInterval() . 'm)';
        }
        /** @var Mage_Core_Helper_Url $modelUrl */
        $modelUrl = Mage::helper('core/url');
        $session = array(
            'id'   => $this->getSessionId(),
            'name' => $sessionName,
            'url'  => $modelUrl->getCurrentUrl(),
            'user' => $user,
        );
        $sessions = $this->loadFromCache(self::SESSION_KEY);
        if (!$sessions || !count($sessions)) {
            $sessions = array();
        }
        $sessions[] = $session;
        $this->saveInCache($sessions, self::SESSION_KEY, $this->_sessionTags);
        $session['data'] = $data;
        $this->saveInCache($session, self::SESSION_KEY . $session['id'], $this->_sessionTags);
    }

    public function __destruct() {
        $now = microtime(true);
        if (count($this->_performance)) {
            foreach ($this->_performance as &$info) {
                if (!isset($info['finish'])) {
                    $info['finish'] = $now;
                }
            }
            foreach ($this->_performance as $unique => &$info) {
                $res = $unique . ': ';
                $start = null;
                foreach ($info as $time) {
                    $start = $time;
                    break;
                }
                foreach ($info as $label => $time) {
                    if ($start == $time) {
                        continue;
                    }
                    $res .= ' ' . $label . ': +' . ($time - $start) * 1000 . '(ms)';
                    $start = $time;
                }
                Mage::log($res, null, 'perfomance.log', true);
            }
        }
        if (!empty($this->_performance)) {
            $showJs = Mage::getStoreConfig('debug_info/debug_info/out_js');
            /** @var Mage_Core_Helper_Url $helperUrl */
            $helperUrl = Mage::helper('core/url');
            $magentoCurrentUrl = $helperUrl->getCurrentUrl();
            $paramForce = Mage::getStoreConfig('debug_info/debug_info/out_force');

            if ($showJs || ($paramForce && strpos($magentoCurrentUrl, $paramForce) !== false)) {
                $this->viewDebugDataFrontend(self::$_blocksInfo,
                                             $this->_performance,
                                             $this->_debugOutput,
                                             $this->_directOutput,
                                             Varien_Profiler::getTimers());
            }
        }
        $this->flushSessionData();
    }

    public function viewSessionFrontend($id) {
        $session = $this->loadFromCache(self::SESSION_KEY . $id);
        if (!$session) {
            echo 'empty';
            return;
        }
        $data = $session['data'];
        echo '<html><head><script type="text/javascript" src="' . Mage::getStoreConfig('debug_info/debug_info/jquery')
            . '"></script></head><body>';
        echo '<a href="' . Mage::getUrl(self::FRONT_NAME) . '">l i s t</a><hr/>';
        $this->listSessionFrontend(true);
        echo '<hr/><h2>' . $session['name'] . '&nbsp;' . $session['url'] . '</h2>';
        $this->viewDebugDataFrontend($data['blocksInfo'],
                                     $data['performance'],
                                     $data['_debugOutput'],
                                     $data['_directOutput'],
                                     $data['timers']);
        echo '</body></html>';
    }

    /**
     * Render html list of saved debug data
     * @param bool $disableHtml
     */
    public function listSessionFrontend($disableHtml = false) {
        $sessions = $this->loadFromCache(self::SESSION_KEY);
        if (!$sessions || !count($sessions)) {
            echo 'empty';
            return;
        }
        $html = '';
        if (!$disableHtml) {
            $html = '<html><body>';
        }
        $html .= '<table>';
        $preUrl = self::FRONT_NAME . '/index/' . self::FRONT_VIEW_ACTION_NAME;
        foreach ($sessions as $session) {
            $url = Mage::getUrl($preUrl, array('sessionId' => $session['id']));
            $html .= '<tr><td><a href="' . $url . '">'
                . $session['name'] . ' : <span>'
                . $session['url'] . '</span> <span>'
                . $session['user'] . '</span></td></tr>';
        }
        $html .= '</table>';
        if ($disableHtml) {
            $html .= '</body></html>';
        }
        echo $html;
    }

    public function viewDebugDataFrontend($blocksId, $performance, $log, $direct, $timers) {
        echo '<script type="text/javascript">debugInfoIds = '
            . Zend_Json::encode($blocksId) . ';'
            . 'debugInfoPerformance = '
            . Zend_Json::encode($performance) . ';'
            . 'debugInfoOutLog = '
            . Zend_Json::encode($log) . ';'
            . 'debugDirectOutLog = '
            . Zend_Json::encode($direct) . ';'
            . 'debugInfoProfiler = '
            . Zend_Json::encode($timers) . ';
                     </script>';
        echo '<script type="text/javascript" src="/debug-performance.js"></script>';
    }

    /**
     * @param Serializable|array $data
     * @param string             $key
     * @param array              $tags
     * @param int                $lifetime
     */
    public function saveInCache($data, $key, $tags, $lifetime = 36000) {
        if (!$this->_useCache) {
            return;
        }
        /** @var Mage_Core_Model_App $app */
        $app = Mage::app();
        /** @var Mage_Core_Model_Cache $cache */
        $cache = $app->getCacheInstance();
        $data = serialize($data);
        $cache->save($data, $key, $tags, $lifetime);
    }

    /**
     * @param string $key
     * @return mixed|null|string
     */
    public function loadFromCache($key) {
        if (!$this->_useCache) {
            return null;
        }
        /** @var Mage_Core_Model_App $app */
        $app = Mage::app();
        /** @var Mage_Core_Model_Cache $cache */
        $cache = $app->getCacheInstance();
        $data = $cache->load($key);
        if ($data) {
            $data = unserialize($data);
            return $data;
        }
        return null;
    }
}