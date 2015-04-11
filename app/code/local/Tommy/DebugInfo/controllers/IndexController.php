<?php

class Tommy_DebugInfo_IndexController extends Mage_Core_Controller_Front_Action
{
    protected function _checkClient() {
        return Tommy_DebugInfo_Helper_Data::getMe()->getEnabledController();
    }

    public function norouteAction($coreRoute = null)
    {
        if (!$this->_checkClient()) {
            $this->getResponse()->setRedirect('/', 302);
        }
        $this->getResponse()->setRedirect(Mage::getUrl('*/*/'), 302);
    }

    public function indexAction() {
        if (!$this->_checkClient()) {
            $this->getResponse()->setRedirect('/', 302);
        }
        $helper = Tommy_DebugInfo_Helper_Data::getMe();
        if (!$helper->getEnabledSession()) {
            echo ' enable session in admin panel';
        }
        $helper->listSessionFrontend();
    }

    public function viewAction() {
        if (!$this->_checkClient()) {
            $this->getResponse()->setRedirect('/', 302);
        }
        $helper = Tommy_DebugInfo_Helper_Data::getMe();
        if (!$helper->getEnabledSession()) {
            echo ' enable session in admin panel';
        }
        $id = (int)$this->getRequest()->getParam('sessionId');
        if ($id < 0) {
            echo 'not valid id';
        }
        $helper->viewSessionFrontend($id);
    }
}