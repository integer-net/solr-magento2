<?php

namespace IntegerNet\Solr\Console\Command;

use IntegerNet\Solr\Indexer\ProductIndexer;
use IntegerNet\Solr\Indexer\Slice;
use IntegerNet\Solr\Model\Indexer;
use Magento\Framework\App;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ReindexSliceCommandTest extends TestCase
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
     * @var Indexer\Console|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexer;

    protected function setUp()
    {
        $this->indexer = $this->getMockBuilder(Indexer\Console::class)->disableOriginalConstructor()->getMock();
        $appState = $this->getMockBuilder(App\State::class)->disableOriginalConstructor()->getMock();
        $this->command = new ReindexSliceCommand($this->indexer, $appState);
        $this->output = new BufferedOutput();
    }

    public function testRunsProductReindexWithSlice()
    {
        $storeIds = [1];
        $sliceExpression = '1/2';

        $this->indexer->expects($this->once())->method('executeStoresSlice')->with(
            Slice::fromExpression($sliceExpression),
            $storeIds
        );
        $exitCode = $this->runCommandWithInput(
            [
                '--stores' => \implode(',', $storeIds),
                '--slice' => $sliceExpression
            ]
        );
        $this->assertEquals(0, $exitCode, 'Exit code should be 0 for successful indexing');
        $this->assertOutputMessages(
            'Reindex of Solr product index for stores 1.',
            'Processing slice 1/2.',
            'Finished'
        );
    }

    public function testRunsProductReindexWithSliceOnSwappedCore()
    {
        $storeIds = [1];
        $sliceExpression = '1/2';

        $this->indexer->expects($this->once())->method('executeStoresSliceOnSwappedCore')->with(
            Slice::fromExpression($sliceExpression),
            $storeIds
        );
        $exitCode = $this->runCommandWithInput(
            [
                '--stores' => \implode(',', $storeIds),
                '--slice' => $sliceExpression,
                '--useswapcore' => true,
            ]
        );
        $this->assertEquals(0, $exitCode, 'Exit code should be 0 for successful indexing');
        $this->assertOutputMessages(
            'Reindex of Solr product index for stores 1',
            'Processing slice 1/2',
            'Finished'
        );
    }

    private function runCommandWithInput($input)
    {
        return $this->command->run(new ArrayInput($input), $this->output);
    }

    /**
     * Assert that output contains all given strings.
     *
     * Note that this is only for output directly emitted from the command,
     * not via progress updates because the indexer is mocked.
     *
     * @param string[] $messages
     */
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
