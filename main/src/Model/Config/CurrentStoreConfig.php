<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Config;

use IntegerNet\Solr\Model\Bridge\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;

class CurrentStoreConfig extends Config
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param DirectoryList $directoryList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DirectoryList $directoryList,
        SerializerInterface $serializer
    ) {
        parent::__construct(
            $scopeConfig,
            $storeManager,
            $directoryList,
            $storeManager->getStore()->getId(),
            $serializer
        );
    }
}
