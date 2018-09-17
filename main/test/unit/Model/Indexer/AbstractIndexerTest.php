<?php

namespace IntegerNet\Solr\Model\Indexer;

use IntegerNet\Solr\Indexer\ProductIndexer;
use IntegerNet\Solr\Plugin\UrlFactoryPlugin;
use PHPUnit\Framework\TestCase;

abstract class AbstractIndexerTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|ProductIndexerFactory */
    protected $indexerFactoryStub;

    /** @var \PHPUnit_Framework_MockObject_MockObject|UrlFactoryPlugin */
    protected $urlFactoryPluginMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ProductIndexer */
    protected $solrIndexerMock;

    protected function setUp()
    {
        $this->solrIndexerMock = $this->getMockBuilder(ProductIndexer::class)
            ->disableOriginalConstructor()->setMethods(
                [
                    'reindex',
                    'reindexSlice',
                    'deleteIndex',
                    'checkSwapCoresConfiguration',
                    'activateSwapCore',
                    'deactivateSwapCore',
                    'swapCores'
                ]
            )->getMock();
        $this->indexerFactoryStub = $this->getMockBuilder(ProductIndexerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->urlFactoryPluginMock = $this->getMockBuilder(UrlFactoryPlugin::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['setForceFrontend']
            )->getMock();
        $decoratedSolrIndexer = new ProductIndexerDecorator($this->solrIndexerMock, $this->urlFactoryPluginMock);
        $this->indexerFactoryStub->method('create')->willReturn($decoratedSolrIndexer);
    }

    protected function expectForcedFrontendUrls()
    {
        $this->urlFactoryPluginMock->expects($this->exactly(2))
            ->method('setForceFrontend')
            ->withConsecutive([true], [false]);
    }

    /**
     * @param array|null $productIds Restrict to given Products if this is set
     * @param boolean|string $emptyIndex Whether to truncate the index before refilling it
     * @param null|int[] $restrictToStoreIds
     * @param null|int $sliceId
     * @param null|int $totalNumberSlices
     */
    protected function expectReindexWithArguments(
        $productIds = null,
        $emptyIndex = false,
        $restrictToStoreIds = null,
        $sliceId = null,
        $totalNumberSlices = null
    ) {
        $this->solrIndexerMock->expects($this->once())->method('reindex')->with(
            $productIds,
            $emptyIndex,
            $restrictToStoreIds,
            $sliceId,
            $totalNumberSlices
        );
    }
}