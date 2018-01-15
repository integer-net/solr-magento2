<?php

namespace IntegerNet\Solr\Console\Command;

use IntegerNet\Solr\Indexer\ProductIndexer;
use IntegerNet\Solr\Model\Indexer;
use Magento\Framework\App;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SwapCommandTest extends TestCase
{
    /**
     * @var SwapCommand
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
        $this->command = new SwapCommand($this->indexer, $appState);
        $this->output = new BufferedOutput();
    }


    public function testSwapAllCoresWithoutArguments()
    {
        $this->indexer->expects($this->once())->method('swapCores')->with(null);
        $exitCode = $this->runCommandWithInput([]);
        $this->assertEquals(0, $exitCode, 'Exit code should be 0 for successful clear');
        $this->assertOutputMessages('Swap all cores', 'Finished');
    }

    public function testSwapCoresWithStoreFilter()
    {
        $storeIds = [1, 3, 'french'];
        $this->indexer->expects($this->once())->method('swapCores')->with($storeIds);
        $exitCode = $this->runCommandWithInput(
            [
                '--stores' => \implode(',', $storeIds),
            ]
        );
        $this->assertEquals(0, $exitCode, 'Exit code should be 0 for successful clearing');
        $this->assertOutputMessages('Swap cores for stores 1, 3, french', 'Finished');
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
