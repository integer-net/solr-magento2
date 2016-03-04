<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Block\Config\Status extends \Magento\Framework\View\Element\Template
{
    protected $_messages = null;

    /**
     * @return string[]
     */
    public function getSuccessMessages()
    {
        return $this->_getMessages('success');
    }

    /**
     * @return string[]
     */
    public function getErrorMessages()
    {
        return $this->_getMessages('error');
    }

    /**
     * @return string[]
     */
    public function getWarningMessages()
    {
        return $this->_getMessages('warning');
    }

    /**
     * @return string[]
     */
    public function getNoticeMessages()
    {
        return $this->_getMessages('notice');
    }

    /**
     * @param string $type
     * @return string[]
     */
    protected function _getMessages($type)
    {
        if (is_null($this->_messages)) {
            $this->_createMessages();
        }
        if (isset($this->_messages[$type])) {
            return $this->_messages[$type];
        }

        return [];
    }

    protected function _createMessages()
    {
        $storeId = null;
        if ($storeCode = $this->_appRequestInterface->getParam('store')) {
            $storeId = $this->_modelStoreManagerInterface->getStore($storeCode)->getId();
        } else {
            if ($websiteCode = $this->_appRequestInterface->getParam('website')) {
                $storeId = $this->_modelStoreManagerInterface->getWebsite($websiteCode)->getDefaultStore()->getId();
            }
        }
        $this->_messages = $this->_modelConfiguration->getMessages($storeId);
    }
}