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

use IntegerNet\Solr\Model\Config\CurrentStoreConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;

class LayoutGenerateBlocksAfter implements ObserverInterface
{
    /**
     * @var CurrentStoreConfig
     */
    private $currentStoreConfig;

    public function __construct(CurrentStoreConfig $currentStoreConfig)
    {
        $this->currentStoreConfig = $currentStoreConfig;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var LayoutInterface $layout */
        $layout = $observer->getData('layout');
        $this->setTopSearchTemplate($layout);
    }
    /**
     * @param $layout
     */
    private function setTopSearchTemplate(LayoutInterface $layout)
    {
        $searchBlock = $layout->getBlock('top.search');
        if ($searchBlock instanceof Template) {
            if (
                $this->currentStoreConfig->getGeneralConfig()->isActive() &&
                $this->currentStoreConfig->getAutosuggestConfig()->isActive()
            ) {
                $searchBlock->setTemplate('IntegerNet_Solr::form.mini.phtml');
            }
        }
    }
}