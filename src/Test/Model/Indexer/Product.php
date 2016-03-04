<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Exception;
use IntegerNet\Solr\Resource\ResourceFacade;

/**
 * @loadFixture config
 */
class Integer\Net\Solr\Test\Model\Indexer\Product extends Ecom\Dev\PHPUnit\Test\CaseTest
{
    /**
     * @param array $config
     * @test
     * @dataProvider dataProvider
     * @dataProviderFile invalid-config.yaml
     * @expectedException Exception
     */
    public function invalidSwapConfigurationShouldThrowException(array $config)
    {
        foreach ($this->_modelStoreManagerInterface->getStores(true) as $store) {
            $store->resetConfig();
        }
        foreach ($config as $path => $value) {
            Mage::getConfig()->setNode($path, $value);
        }
        $this->_helperFactory->getProductIndexer()->reindex();
    }
}