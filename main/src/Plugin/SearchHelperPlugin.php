<?php
/**
 * integer_net Magento Module
 *
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\Solr\Plugin;

use Magento\Search\Helper\Data as Subject;
use Magento\Store\Model\StoreManagerInterface;

class SearchHelperPlugin
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
     * @return string
     */
    public function aroundGetSuggestUrl()
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();
        return $store->getBaseUrl() . 'autosuggest.php?store_id=' . $store->getId();
    }
}