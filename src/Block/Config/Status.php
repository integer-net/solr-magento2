<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
namespace IntegerNet\Solr\Block\Config;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class Status extends Template
{
    protected $_messages = null;
    /**
     * @var RequestInterface
     */
    protected $_appRequestInterface;
    /**
     * @var StoreManagerInterface
     */
    protected $_modelStoreManagerInterface;
    /**
     * @var Configuration
     */
    protected $_solrConfiguration;

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
        return; //TODO implement
        $storeId = null;
        if ($storeCode = $this->_appRequestInterface->getParam('store')) {
            $storeId = $this->_modelStoreManagerInterface->getStore($storeCode)->getId();
        } else {
            if ($websiteCode = $this->_appRequestInterface->getParam('website')) {
                $storeId = $this->_modelStoreManagerInterface->getWebsite($websiteCode)->getDefaultStore()->getId();
            }
        }
        $this->_messages = $this->_solrConfiguration->getMessages($storeId);
    }
}