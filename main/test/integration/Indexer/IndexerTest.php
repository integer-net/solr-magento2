<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Indexer;

use IntegerNet\Solr\Fixtures\SolrConfig;
use IntegerNet\Solr\Model\Bridge\RequestFactory;
use IntegerNet\Solr\Model\Indexer\Console;
use IntegerNet\Solr\Model\Indexer\Fulltext;
use Magento\Search\Model\QueryFactory;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Catalog\ProductFixture;
use TddWizard\Fixtures\Catalog\ProductFixtureRollback;

class IndexerTest extends TestCase
{
    private $productFixtures = [];

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
        //TODO fix test on Magento 2.2.5
        /** @var ProductMetadataInterface $productMetadata */
        $productMetadata = $this->objectManager->get(\Magento\Framework\App\ProductMetadataInterface::class);
        if (version_compare($productMetadata->getVersion(), '2.2.5', '>=')) {
            $this->markTestSkipped(
                'This test does not terminate on Magento 2.2.5 because of https://github.com/tddwizard/magento2-fixtures/issues/13'
            );
        }
        $this->createProductFixture();
    }
    protected function tearDown()
    {
        $this->deleteProductFixture();
    }
    public function testIndexerInstantiation()
    {
        $indexer = $this->objectManager->create(Fulltext::class);
        $this->assertInstanceOf(Fulltext::class, $indexer);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testReindex()
    {
        $storeId = 1;
        /** @var Console $indexer */
        $indexer = $this->objectManager->create(Console::class);
        $indexer->executeStoresForceEmpty([$storeId]);
        $searchResponse = $this->search('potato');
        $this->assertCount(
            3,
            $searchResponse->documents(),
            'There are three products matching "potato"'
        );
    }
    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testReindexSingleSlice()
    {
        $storeId = 1;
        /** @var Console $indexer */
        $indexer = $this->objectManager->create(Console::class);
        $indexer->clearStores([$storeId]);
        $indexer->executeStoresSlice(new Slice(2, 3), [$storeId]);
        $searchResponse = $this->search('potato');
        $this->assertCount(
            1,
            $searchResponse->documents(),
            'There are three products with "potato", but only one should be indexed after single slice 2/3'
        );
    }
    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testReindexAllSlices()
    {
        $storeId = 1;
        /** @var Console $indexer */
        $indexer = $this->objectManager->create(Console::class);
        $indexer->clearStores([$storeId]);
        $indexer->executeStoresSlice(new Slice(1, 2), [$storeId]);
        $indexer->executeStoresSlice(new Slice(2, 2), [$storeId]);
        $searchResponse = $this->search('potato');
        $this->assertCount(
            3,
            $searchResponse->documents(),
            'There are three products with "potato", all should be indexed after slices 1/2 and 2/2'
        );
    }

    private function createProductFixture()
    {
        $this->productFixtures = [
            new ProductFixture(
                ProductBuilder::aSimpleProduct()->withName('First potato')->build()
            ),
            new ProductFixture(
                ProductBuilder::aSimpleProduct()->withName('Second potato')->build()
            ),
            new ProductFixture(
                ProductBuilder::aSimpleProduct()->withName('Third potato')->build()
            ),
        ];
        SolrConfig::loadFromConfigFile();
    }

    private function deleteProductFixture()
    {
        ProductFixtureRollback::create()->execute(...$this->productFixtures);
        $this->productFixtures = [];
    }

    /**
     * @param $queryText
     * @return \IntegerNet\Solr\Response\Response
     */
    private function search($queryText)
    {
        /** @var QueryFactory $queryFactory */
        $queryFactory = $this->objectManager->get(QueryFactory::class);
        $queryFactory->get()->setQueryText($queryText);
        /** @var RequestFactory $searchRequestFactory */
        $searchRequestFactory = $this->objectManager->create(RequestFactory::class);
        return $searchRequestFactory->getSolrRequest(RequestFactory::REQUEST_MODE_SEARCH)->doRequest();
    }
}