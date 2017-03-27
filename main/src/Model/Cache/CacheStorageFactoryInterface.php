<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Cache;

use IntegerNet\SolrSuggest\Plain\Cache\CacheStorage;

interface CacheStorageFactoryInterface
{
    /**
     * @return CacheStorage
     */
    public function create();
}