<?php

namespace IntegerNet\Solr\Console\Command;

use IntegerNet\Solr\Indexer\ProductIndexer;
use IntegerNet\Solr\Model\Indexer;
use IntegerNet\Solr\Model\Indexer\ProductIndexerFactory;
use PHPUnit\Framework\Assert;
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

    protected function setUp()
    {
        $this->indexer = $this->getMockBuilder(Indexer\Console::class)->disableOriginalConstructor()->getMock();
        $this->command = new ReindexCommand($this->indexer);
        $this->output = new BufferedOutput();
    }


    public function testRunsFullProductReindexWithoutArguments()
    {
        $this->indexer->expects($this->once())->method('executeStores')->with(null);
        $exitCode = $this->runCommandWithInput([]);
        $this->assertEquals(0, $exitCode, 'Exit code should be 0 for successful indexing');
        $this->assertOutputMessages('Starting full reindex of Solr product index.', 'Finished.');
    }

    public function testRunsProductReindexWithStoreFilter()
    {
        $storeIds = [1, 3];
        $this->indexer->expects($this->once())->method('executeStores')->with($storeIds);
        $exitCode = $this->runCommandWithInput(
            [
                '--stores' => \implode(',', $storeIds),
            ]
        );
        $this->assertEquals(0, $exitCode, 'Exit code should be 0 for successful indexing');
        $this->assertOutputMessages('Starting reindex of Solr product index for stores 1, 3.', 'Finished.');
    }

    public function testRunsProductReindexWithStoreFilterByCodes()
    {
        $this->markTestIncomplete('--stores parameter with store codes instead of ids not implemented yet');
    }

    public function testRunsFullProductReindexWithForcedEmptyIndex()
    {
        $this->indexer->expects($this->once())->method('executeStoresForceEmpty')->with(null);
        $exitCode = $this->runCommandWithInput(
            [
                '--emptyindex' => true,
            ]
        );
        $this->assertEquals(0, $exitCode, 'Exit code should be 0 for successful indexing');
        $this->assertOutputMessages(
            'Starting full reindex of Solr product index.',
            'Forcing empty index.',
            'Finished.'
        );
    }

    public function testRunsFullProductReindexWithForcedNonEmptyIndex()
    {
        $this->indexer->expects($this->once())->method('executeStoresForceNotEmpty')->with(null);
        $exitCode = $this->runCommandWithInput(
            [
                '--noemptyindex' => true,
            ]
        );
        $this->assertEquals(0, $exitCode, 'Exit code should be 0 for successful indexing');
        $this->assertOutputMessages(
            'Starting full reindex of Solr product index.',
            'Forcing non-empty index.',
            'Finished.'
        );
    }

    public function testRunsProductReindexWithStoreFilterAndForcedEmptyIndex()
    {
        $storeIds = [1, 3];
        $this->indexer->expects($this->once())->method('executeStoresForceEmpty')->with($storeIds);
        $exitCode = $this->runCommandWithInput(
            [
                '--stores' => \implode(',', $storeIds),
                '--emptyindex' => true,
            ]
        );
        $this->assertEquals(0, $exitCode, 'Exit code should be 0 for successful indexing');
        $this->assertOutputMessages(
            'Starting reindex of Solr product index for stores 1, 3.',
            'Forcing empty index.',
            'Finished.'
        );
    }

    public function testRunsProductReindexWithStoreFilterAndForcedNonEmptyIndex()
    {
        $storeIds = [1, 3];
        $this->indexer->expects($this->once())->method('executeStoresForceNotEmpty')->with($storeIds);
        $exitCode = $this->runCommandWithInput(
            [
                '--stores' => \implode(',', $storeIds),
                '--noemptyindex' => true,
            ]
        );
        $this->assertEquals(0, $exitCode, 'Exit code should be 0 for successful indexing');
        $this->assertOutputMessages(
            'Starting reindex of Solr product index for stores 1, 3.',
            'Forcing non-empty index.',
            'Finished.'
        );
    }
    private function runCommandWithInput($input)
    {
        return $this->command->run(new ArrayInput($input), $this->output);
    }

    private function assertOutputMessages(...$messages)
    {
        $this->assertThat(
            $this->output->fetch(),
            $this->logicalAnd(
                ...array_map([Assert::class, 'stringContains'], $messages)
            )
        );
    }
}
