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

class FulltextTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|ProductIndexer  */
    private $solrIndexerMock;
    /**
     * @var Fulltext
     */
    private $fulltext;

    protected function setUp()
    {
        $this->solrIndexerMock = $this->getMockBuilder(ProductIndexer::class)
            ->disableOriginalConstructor()
            ->setMethods(['reindex', 'deleteIndex'])
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductIndexerFactory $indexerFactoryStub */
        $indexerFactoryStub = $this->getMockBuilder(ProductIndexerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $indexerFactoryStub->method('create')->willReturn($this->solrIndexerMock);
        $this->fulltext = new Fulltext($indexerFactoryStub);
    }
    public function testExecuteFull()
    {
        $this->solrIndexerMock->expects($this->once())
            ->method('reindex')
            ->with(null, true, null);
        $this->fulltext->executeFull();
    }
    public function testExecuteList()
    {
        $productIds = [1, 2, 3, 5];
        $this->solrIndexerMock->expects($this->once())
            ->method('reindex')
            ->with($productIds, false, null);
        $this->fulltext->executeList($productIds);
    }
    public function testExecute()
    {
        $productIds = [1, 2, 3, 5];
        $this->solrIndexerMock->expects($this->once())
            ->method('reindex')
            ->with($productIds, false, null);
        $this->fulltext->execute($productIds);
    }
    public function testExecuteRow()
    {
        $productId = 42;
        $this->solrIndexerMock->expects($this->once())
            ->method('reindex')
            ->with([$productId], false, null);
        $this->fulltext->executeRow($productId);
    }
}
