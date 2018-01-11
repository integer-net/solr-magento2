<?php

namespace IntegerNet\Solr\Model\Indexer;

use IntegerNet\Solr\Indexer\Slice;

class ConsoleTest extends AbstractIndexerTest
{
    /**
     * @var Console
     */
    private $indexer;

    protected function setUp()
    {
        parent::setUp();
        $this->indexer = new Console($this->indexerFactoryStub);
    }

    public function testExecuteStores()
    {
        $storeIds = [1, 2];
        $this->expectForcedFrontendUrls();
        $this->expectReindexWithArguments(null, true, $storeIds);
        $this->indexer->executeStores($storeIds);
    }

    public function testExecuteStoresSlice()
    {
        $storeIds = [1, 2];
        $slice = new Slice(1, 2);
        $this->expectForcedFrontendUrls();
        $this->solrIndexerMock->expects($this->once())->method('reindexSlice')->with($slice, $storeIds);
        $this->indexer->executeStoresSlice($slice, $storeIds);
    }

    public function testExecuteStoresWithForceEmptyIndex()
    {
        $storeIds = [1, 2];
        $this->expectForcedFrontendUrls();
        $this->expectReindexWithArguments(null, 'force', $storeIds);
        $this->indexer->executeStoresForceEmpty($storeIds);
    }

    public function testExecuteStoresWithForceNotEmptyIndex()
    {
        $storeIds = [1, 2];
        $this->expectForcedFrontendUrls();
        $this->expectReindexWithArguments(null, false, $storeIds);
        $this->indexer->executeStoresForceNotEmpty($storeIds);
    }
}
