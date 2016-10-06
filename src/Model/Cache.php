<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model;

use IntegerNet\SolrSuggest\Implementor\Factory\AppFactory;

class Cache
{
    /**
     * @var AppFactory
     */
    private $appFactory;

    public function __construct(AppFactory $appFactory)
    {
        $this->appFactory = $appFactory;
    }

    public function regenerate()
    {
        $this->appFactory->getCacheWriter()->write($this->appFactory->getStoreConfig());
    }
}