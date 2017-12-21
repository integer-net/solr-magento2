<?php

namespace IntegerNet\Solr\Console\Command;

use IntegerNet\Solr\Indexer\ProductIndexer;
use IntegerNet\Solr\Model\Indexer;
use IntegerNet\Solr\Model\Indexer\ProductIndexerFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ReindexCommandTest extends TestCase
{
    /**
     * @var ReindexCommand
     */
    private $command;

    /**
     * @var BufferedOutput
     */
    private $output;

    /**
     * @var ProductIndexer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexer;

    public function testRunsFullProductReindexWithoutArguments()
    {
        $this->indexer->expects($this->once())->method('executeFull');
        $exitCode = $this->runCommandWithInput([]);
        $this->assertEquals(0, $exitCode, 'Exit code should be 0 for successful indexing');
        $this->assertOutputMessages('Starting full reindex of Solr product index.', 'Finished');
    }

    public function testRunsProductReindexWithStoreFilter()
    {
        $storeIds = [1, 3];
        $this->indexer->expects($this->once())->method('executeStores')->with($storeIds);
        $exitCode = $this->runCommandWithInput(
            [
                '--stores' => \implode(',', $storeIds)
            ]
        );
        $this->assertEquals(0, $exitCode, 'Exit code should be 0 for successful indexing');
        $this->assertOutputMessages('Starting reindex of Solr product index for stores 1, 3.', 'Finished');
    }

    public function testRunsProductReindexWithStoreFilterByCodes()
    {
        $this->markTestIncomplete('--stores parameter with store codes instead of ids not implemented yet');
    }

    public function testRunsFullProductReindexWithForcedEmptyIndex()
    {
        $this->markTestIncomplete('Not implemented yet');
    }

    public function testRunsFullProductReindexWithForcedNonEmptyIndex()
    {
        $this->markTestIncomplete('Not implemented yet');
    }

    public function testRunsProductReindexWithStoreFilterAndForcedEmptyIndex()
    {
        $this->markTestIncomplete('Not implemented yet');
    }

    public function testRunsProductReindexWithStoreFilterAndForcedNonEmptyIndex()
    {
        $this->markTestIncomplete('Not implemented yet');
    }

    protected function setUp()
    {
        $this->indexer = $this->getMockBuilder(Indexer\Console::class)->disableOriginalConstructor()->getMock();
        $this->command = new ReindexCommand($this->indexer);
        $this->output = new BufferedOutput();
    }

    private function runCommandWithInput($input)
    {
        return $this->command->run(new ArrayInput($input), $this->output);
    }

    private function assertOutputMessages($startMessage, $endMessage)
    {
        $this->assertThat(
            $this->output->fetch(),
            $this->logicalAnd(
                $this->stringContains($startMessage),
                $this->stringContains($endMessage)
            )
        );
    }
}
