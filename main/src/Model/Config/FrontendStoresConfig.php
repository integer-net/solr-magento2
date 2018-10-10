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
use IntegerNet\Solr\Model\Bridge\ConfigFactory;
use Magento\Store\Model\StoreManagerInterface;

class FrontendStoresConfig  extends \ArrayIterator
{
    public function __construct(StoreManagerInterface $storeManager, ConfigFactory $configFactory)
    {
        $configByStore = [];
        foreach ($storeManager->getStores(false, false) as $storeId => $store) {
            $configByStore[$storeId] = $configFactory->create([
                Config::PARAM_STORE_ID => $storeId
            ]);
        }
        parent::__construct($configByStore);
    }

    public function byStoreId($storeId): Config
    {
        return $this[$storeId];
    }
}