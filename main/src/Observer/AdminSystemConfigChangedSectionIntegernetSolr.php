<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Observer;


use IntegerNet\Solr\Model\Cache;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message;

class AdminSystemConfigChangedSectionIntegernetSolr implements ObserverInterface
{
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var Message\ManagerInterface
     */
    private $messageManager;

    public function __construct(Cache $cache, Message\ManagerInterface $messageManager)
    {
        $this->cache = $cache;
        $this->messageManager = $messageManager;
    }
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->cache->regenerate();
        $this->messageManager->addSuccessMessage(__('IntegerNet_Solr configuration cache regenerated'));
    }

}