<?php

namespace IntegerNet\Solr\Model\Indexer;

use IntegerNet\Solr\Indexer\Slice;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConsoleTest extends AbstractIndexerTest
{
    const ARBITRARY_STORE_IDS = [1, 3];
    const STORE_ID_FRENCH     = 7;
    const STORE_ID_GERMAN     = 8;
    const STORE_CODE_FRENCH   = 'french';
    const STORE_CODE_GERMAN   = 'german';
    /**
     * @var Console
     */
    private $indexer;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StoreManagerInterface\
     */
    private $storeManagerStub;

    protected function setUp()
    {
        parent::setUp();
        $frenchStore = $this->getMockBuilder(StoreInterface::class)->getMockForAbstractClass();
        $frenchStore->method('getId')->willReturn(self::STORE_ID_FRENCH);
        $germanStore = $this->getMockBuilder(StoreInterface::class)->getMockForAbstractClass();
        $germanStore->method('getId')->willReturn(self::STORE_ID_GERMAN);
        $this->storeManagerStub = $this->getMockBuilder(StoreManagerInterface::class)->getMockForAbstractClass();
        $this->storeManagerStub->method('getStores')->with(false, true)->willReturn(
            [
                self::STORE_CODE_FRENCH => $frenchStore,
                self::STORE_CODE_GERMAN => $germanStore,
            ]
        );
        $this->indexer = new Console($this->indexerFactoryStub, $this->storeManagerStub);
    }

    public function testExecuteStores()
    {
        $storeIds = self::ARBITRARY_STORE_IDS;
        $this->expectForcedFrontendUrls();
        $this->expectReindexWithArguments(null, true, $storeIds);
        $this->indexer->executeStores($storeIds);
    }

    public function testExecuteStoresByStoreCode()
    {
        $storeCodesById = [
            self::STORE_ID_FRENCH => self::STORE_CODE_FRENCH,
            self::STORE_ID_GERMAN => self::STORE_CODE_GERMAN,
        ];
        $this->expectForcedFrontendUrls();
        $this->expectReindexWithArguments(null, true, array_keys($storeCodesById));
        $this->indexer->executeStores(array_values($storeCodesById));
    }

    public function testExecuteStoresSlice()
    {
        $storeIds = self::ARBITRARY_STORE_IDS;
        $slice = new Slice(1, 2);
        $this->expectForcedFrontendUrls();
        $this->solrIndexerMock->expects($this->once())->method('reindexSlice')->with($slice, $storeIds);
        $this->indexer->executeStoresSlice($slice, $storeIds);
    }

    public function testExecuteStoresWithForceEmptyIndex()
    {
        $storeIds = self::ARBITRARY_STORE_IDS;
        $this->expectForcedFrontendUrls();
        $this->expectReindexWithArguments(null, 'force', $storeIds);
        $this->indexer->executeStoresForceEmpty($storeIds);
    }

    public function testExecuteStoresWithForceNotEmptyIndex()
    {
        $storeIds = self::ARBITRARY_STORE_IDS;
        $this->expectForcedFrontendUrls();
        $this->expectReindexWithArguments(null, false, $storeIds);
        $this->indexer->executeStoresForceNotEmpty($storeIds);
    }
}
