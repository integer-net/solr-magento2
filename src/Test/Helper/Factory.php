<?php
use IntegerNet\Solr\Config\IndexingConfig;
use IntegerNet\Solr\Config\ServerConfig;
use IntegerNet\Solr\Exception;
use IntegerNet\Solr\Implementor\Config;
use IntegerNet\Solr\Resource\ResourceFacade;

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
class Integer\Net\Solr\Test\Helper\Factory extends Ecom\Dev\PHPUnit\Test\CaseTest
{
    /**
     * @test
     */
    public function shouldCreateSolrResourceWithStoreConfiguration()
    {
        $resource = $this->_helperFactory->getSolrResource();
        $this->assertInstanceOf(ResourceFacade::class, $resource);
        $storeConfigs = [
            $resource->getStoreConfig(1),   // default store view
            $resource->getStoreConfig(0),   // admin store view
            $resource->getStoreConfig(null) // admin store view
        ];
        foreach ($storeConfigs as $storeConfig) {
            $this->assertInstanceOf(Config::class, $storeConfig);
            $this->assertInstanceOf(IndexingConfig::class, $storeConfig->getIndexingConfig());
            $this->assertInstanceOf(ServerConfig::class, $storeConfig->getServerConfig());
        }

        $this->setExpectedException(Exception::class, "Store with ID -1 not found.");
        $resource->getStoreConfig(-1);
    }

}