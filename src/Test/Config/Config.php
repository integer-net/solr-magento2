<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Test\Config\Config extends Ecom\Dev\PHPUnit\Test\CaseTest\Config
{
    /**
     * @test
     * @loadExpections
     */
    public function globalConfig()
    {
        $this->assertModuleVersionGreaterThanOrEquals('0.1.0');
        $this->assertModuleCodePool('community');
    }

    /**
     * @test
     */
    public function modelConfig()
    {
        $this->assertModelAlias('integernet_solr/indexer', 'Integer\Net\Solr\Model\Indexer');
        $this->assertModelAlias('integernet_solr/resource_indexer', 'Integer\Net\Solr\Model\ResourceModel\Indexer');
    }

    /**
     * @test
     */
    public function helperConfig()
    {
        $this->assertHelperAlias('integernet_solr', 'Integer\Net\Solr\Helper\Data');
    }

    /**
     * @test
     */
    public function translationConfig()
    {
        $this->assertConfigNodeValue('adminhtml/translate/modules/integernet_solr/files/default', 'IntegerNet_Solr.csv');
    }

    /**
     * @test
     */
    public function indexerConfig()
    {
        $this->assertConfigNodeValue('global/index/indexer/integernet_solr/model', 'integernet_solr/indexer');
    }

    /**
     * @test
     */
    public function storeConfigShouldContainBaseUrl()
    {
        $config = \Magento\Framework\App\ObjectManager::getInstance()->create('integernet_solr/config_store', 1);
        $this->assertEquals($this->_modelStoreManagerInterface->getStore(1)->getBaseUrl(), $config->getStoreConfig()->getBaseUrl());
    }

    /**
     * The SerializableConfig interface is part of the SolrSuggest package
     * @test
     */
    public function configShouldBeSerializable()
    {
        // use different fuzzy config for search and autosuggest to make sure, both are serialized independently
        $this->_modelStoreManagerInterface->getStore(1)->setConfig('integernet_solr/fuzzy/is_active', '1');
        $this->_modelStoreManagerInterface->getStore(1)->setConfig('integernet_solr/fuzzy/is_active_autosuggest', '0');

        /**
         * @var $configFromSerialized IntegerNet\Solr\Implementor\Config
         */
        $config = \Magento\Framework\App\ObjectManager::getInstance()->create('integernet_solr/config_store', 1);
        $configFromSerialized = unserialize(serialize(\IntegerNet\SolrSuggest\Plain\Config::fromConfig($config)));

        $this->assertInstanceOf(\IntegerNet\SolrSuggest\Plain\Config::class, $configFromSerialized);
        $this->assertEquals($config->getStoreConfig(), $configFromSerialized->getStoreConfig());
        $this->assertEquals($config->getGeneralConfig(), $configFromSerialized->getGeneralConfig());
        $this->assertEquals($config->getResultsConfig(), $configFromSerialized->getResultsConfig());
        $this->assertEquals($config->getFuzzyAutosuggestConfig(), $configFromSerialized->getFuzzyAutosuggestConfig());
        $this->assertEquals($config->getFuzzySearchConfig(), $configFromSerialized->getFuzzySearchConfig());
        $this->assertEquals($config->getIndexingConfig(), $configFromSerialized->getIndexingConfig());
        $this->assertEquals($config->getServerConfig(), $configFromSerialized->getServerConfig());
    }
}