<?php

namespace IntegerNet\Solr\Model\Indexer;

class ConsoleTest extends AbstractIndexerTest
{
    /**
     * @var Console
     */
    private $indexer;

    public function testExecuteStores()
    {
        $storeIds = [1, 2];
        $this->expectReindexWithArguments(null, true, $storeIds);
        $this->indexer->executeStores($storeIds);
    }

    public function testExecuteStoresWithForceEmptyIndex()
    {
        $storeIds = [1, 2];
        $this->expectReindexWithArguments(null, 'force', $storeIds);
        $this->indexer->executeStoresForceEmpty($storeIds);
    }

    public function testExecuteStoresWithForceNotEmptyIndex()
    {
        $storeIds = [1, 2];
        $this->expectReindexWithArguments(null, false, $storeIds);
        $this->indexer->executeStoresForceNotEmpty($storeIds);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->indexer = new Console($this->indexerFactoryStub, $this->urlFactoryPluginMock);
    }
}
