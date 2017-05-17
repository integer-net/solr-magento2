<?php
/**
 * integer_net Magento Module
 *
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\Solr\Plugin\Helper;

use Magento\Search\Helper\Data as Subject;
use Magento\Store\Model\StoreManagerInterface;

class DataPlugin
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @param Subject $subject
     * @return string
     */
    public function afterGetSuggestUrl(Subject $subject)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();
        return $store->getBaseUrl() . 'autosuggest.php?store_id=' . $store->getId();
    }
}