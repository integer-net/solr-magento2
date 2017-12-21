<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Indexer;

use IntegerNet\Solr\Indexer\ProductIndexer;
use IntegerNet\Solr\Plugin\UrlFactoryPlugin;
use PHPUnit\Framework\TestCase;

class FulltextTest extends AbstractIndexerTest
{
    /** @var Fulltext */
    private $indexer;

    public function testExecuteFull()
    {
        $this->expectForcedFrontendUrls();
        $this->expectReindexWithArguments(null, true, null);
        $this->indexer->executeFull();
    }

    public function testExecuteList()
    {
        $productIds = [1, 2, 3, 5];
        $this->expectReindexWithArguments($productIds, false, null);
        $this->indexer->executeList($productIds);
    }

    public function testExecute()
    {
        $productIds = [1, 2, 3, 5];
        $this->expectReindexWithArguments($productIds, false, null);
        $this->indexer->execute($productIds);
    }

    public function testExecuteRow()
    {
        $productId = 42;
        $this->expectReindexWithArguments([$productId], false, null);
        $this->indexer->executeRow($productId);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->indexer = new Fulltext($this->indexerFactoryStub, $this->urlFactoryPluginMock);
    }

}
