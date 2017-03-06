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

use IntegerNet\Solr\Model\StatusMessages;
use Magento\Framework\View\Element\Template;

class Status extends Template
{
    protected $_messages = null;
    /**
     * @var StatusMessages
     */
    protected $statusMessages;

    /**
     * @return string[]
     */
    public function getSuccessMessages()
    {
        return $this->_getMessages('success');
    }

    /**
     * Constructor
     *
     * @param StatusMessages $statusMessages
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(StatusMessages $statusMessages, Template\Context $context, array $data = [])
    {
        $this->statusMessages = $statusMessages;
        parent::__construct($context, $data);
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
        if ($storeCode = $this->getRequest()->getParam('store')) {
            $storeId = $this->_storeManager->getStore($storeCode)->getId();
        } else {
            if ($websiteCode = $this->getRequest()->getParam('website')) {
                $storeId = $this->_storeManager->getWebsite($websiteCode)->getDefaultStore()->getId();
            }
        }
        $this->_messages = $this->statusMessages->getMessages($storeId);
    }
}