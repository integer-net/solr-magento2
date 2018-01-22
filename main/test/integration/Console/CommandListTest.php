<?php

namespace IntegerNet\Solr\Console;

use Magento\Framework\Console\CommandListInterface;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 */
class CommandListTest extends TestCase
{
    /**
     * @var CommandListInterface
     */
    private $commandList;

    public function testContainsReindexProductsCommand()
    {
        $commands = $this->commandList->getCommands();
        $this->assertArrayHasKey(
            'solr_reindex_full',
            $commands,
            'Command should be listed'
        );
        $reindexCommand = $commands['solr_reindex_full'];
        $this->assertEquals('solr:reindex:full', $reindexCommand->getName(), 'Command name');
        $this->assertInstanceof(
            Command\ReindexCommand::class,
            $reindexCommand,
            'Command should be instantiated.'
        );
    }

    public function testContainsReindexSliceCommand()
    {
        $commands = $this->commandList->getCommands();
        $this->assertArrayHasKey(
            'solr_reindex_slice',
            $commands,
            'Command should be listed'
        );
        $reindexCommand = $commands['solr_reindex_slice'];
        $this->assertEquals('solr:reindex:slice', $reindexCommand->getName(), 'Command name');
        $this->assertInstanceof(
            Command\ReindexSliceCommand::class,
            $reindexCommand,
            'Command should be instantiated.'
        );
    }

    protected function setUp()
    {
        $this->fixMagento2․2Di();
        $this->commandList = Bootstrap::getObjectManager()->create(CommandListInterface::class);
    }

    /**
     * In Magento 2.2 a missing DI configuration in the test environment prevents the command list from being
     * instantiated.
     *
     * Bug occurs on:
     *  - 2.2.0
     *  - 2.2.1
     *  - 2.2.2
     *
     * @link https://github.com/magento/magento2/pull/12845 Pull Request
     */
    private function fixMagento2․2Di()
    {
        Bootstrap::getObjectManager()->configure(
            [
                'preferences' => [
                    ltrim(ConfigInterface::class, '\\') => ltrim(ObjectManager\Config::class, '\\'),
                ],
            ]
        );
    }

}