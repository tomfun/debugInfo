<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Base html block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Core_Block_Text_List extends Mage_Core_Block_Text
{
    public function setChild($alias, $block) {
        parent::setChild($alias, $block);
    }
    protected function _toHtml()
    {
        $helperInfo = Tommy_DebugInfo_Helper_Data::getMe();
        $helperInfo->addPerformanceLog($this->getNameInLayout());
        $this->setText('');
        $i = 0;
        foreach ($this->getSortedChildren() as $name) {
            $block = $this->getLayout()->getBlock($name);
            if (!$block) {
                Mage::throwException(Mage::helper('core')->__('Invalid block: %s', $name));
            }
            $this->addText($block->toHtml());
            $helperInfo->addPerformanceLog($this->getNameInLayout(), $block->getTemplate());
        }
        $helperInfo->addPerformanceLog($this->getNameInLayout(), 'getText');
        $html = parent::_toHtml();
        $helperInfo->addPerformanceLog($this->getNameInLayout(), 'finish');
        return $html;
    }
}
