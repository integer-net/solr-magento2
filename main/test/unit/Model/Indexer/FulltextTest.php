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

use Magento\Framework\App\State;

class FulltextTest extends AbstractIndexerTest
{
    /** @var Fulltext */
    private $indexer;

    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appStateMock;

    protected function setUp()
    {
        parent::setUp();
        $this->appStateMock = $this->createMock(State::class);
        $this->indexer = new Fulltext($this->indexerFactoryStub, $this->appStateMock);
    }

    public function testExecuteFull()
    {
        $this->expectForcedFrontendUrls();
        $this->expectEmulateFrontend();
        $this->expectReindexWithArguments(null, true, null);
        $this->indexer->executeFull();
    }

    public function testExecuteList()
    {
        $productIds = [1, 2, 3, 5];
        $this->expectForcedFrontendUrls();
        $this->expectEmulateFrontend();
        $this->expectReindexWithArguments($productIds, false, null);
        $this->indexer->executeList($productIds);
    }

    public function testExecute()
    {
        $productIds = [1, 2, 3, 5];
        $this->expectForcedFrontendUrls();
        $this->expectEmulateFrontend();
        $this->expectReindexWithArguments($productIds, false, null);
        $this->indexer->execute($productIds);
    }
    public function testExecuteRow()
    {
        $productId = 42;
        $this->expectForcedFrontendUrls();
        $this->expectEmulateFrontend();
        $this->expectReindexWithArguments([$productId], false, null);
        $this->indexer->executeRow($productId);
    }
    private function expectEmulateFrontend()
    {
        $this->appStateMock->expects($this->once())->method('emulateAreaCode')->with(
            'frontend',
            $this->isType('callable'),
            $this->isType('array')
        )->willReturnCallback(
            function ($code, $callback, $args) {
                return $callback(...$args);
            }
        );
    }
}