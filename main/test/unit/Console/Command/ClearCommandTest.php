<?php

namespace IntegerNet\Solr\Console\Command;

use IntegerNet\Solr\Indexer\ProductIndexer;
use IntegerNet\Solr\Model\Indexer;
use Magento\Framework\App;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ClearCommandTest extends TestCase
{
    /**
     * @var ClearCommand
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
        $appState = $this->getMockBuilder(App\State::class)->disableOriginalConstructor()->getMock();
        $this->command = new ClearCommand($this->indexer, $appState);
        $this->output = new BufferedOutput();
    }


    public function testClearsFullProductIndexWithoutArguments()
    {
        $this->indexer->expects($this->once())->method('clearStores')->with(null);
        $exitCode = $this->runCommandWithInput([]);
        $this->assertEquals(0, $exitCode, 'Exit code should be 0 for successful clear');
        $this->assertOutputMessages('Clearing full Solr product index', 'Finished');
    }

    public function testClearProductIndexWithStoreFilter()
    {
        $storeIds = [1, 3, 'french'];
        $this->indexer->expects($this->once())->method('clearStores')->with($storeIds);
        $exitCode = $this->runCommandWithInput(
            [
                '--stores' => \implode(',', $storeIds),
            ]
        );
        $this->assertEquals(0, $exitCode, 'Exit code should be 0 for successful clearing');
        $this->assertOutputMessages('Clearing Solr product index for stores 1, 3, french', 'Finished');
    }

    public function testClearProductIndexOnSwappedCore()
    {
        $storeIds = [1];

        $this->indexer->expects($this->once())->method('clearStoresOnSwappedCore')->with($storeIds);
        $exitCode = $this->runCommandWithInput(
            [
                '--stores' => \implode(',', $storeIds),
                '--useswapcore' => true,
            ]
        );
        $this->assertEquals(0, $exitCode, 'Exit code should be 0 for successful clearing');
        $this->assertOutputMessages(
            'Clearing Solr product index for stores 1',
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
