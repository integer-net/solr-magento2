<?php

namespace IntegerNet\Solr\Console\Command;

use IntegerNet\Solr\Indexer\Slice;
use IntegerNet\Solr\Model\Indexer;
use Magento\Framework\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * solr:clear command
 */
class ClearCommand extends Command
{
    const INPUT_STORES = 'stores';
    /**
     * @var Indexer\Console
     */
    private $indexer;
    /**
     * @var App\State
     */
    private $appState;

    public function __construct(Indexer\Console $indexer, App\State $appState, $name = null)
    {
        parent::__construct($name);
        $this->indexer = $indexer;
        $this->appState = $appState;
    }

    protected function configure()
    {
        $options = [
            new InputOption(
                'stores',
                null,
                InputOption::VALUE_OPTIONAL,
                'Clear solr product index for given stores (can be store id, store code, comma seperated. Or "all".) '
                . 'If not set, clear all stores.'
            ),
            new InputOption(
                'useswapcore',
                null,
                InputOption::VALUE_NONE,
                'Use swap core for clearing instead of live solr core (only if configured correctly).'
            ),
            ];
        $this->setName('solr:clear');
        $this->setHelp('Clear Solr index for given stores (see "stores" param).');
        $this->setDescription('Clear Solr index');
        $this->setDefinition($options);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $styledOutput = new StyledOutput(
            $output,
            class_exists(SymfonyStyle::class) ? new SymfonyStyle($input, $output) : null
        );
        $startTime = microtime(true);
        $this->appState->setAreaCode(App\Area::AREA_GLOBAL);
        if (!$input->getOption(self::INPUT_STORES) || $input->getOption(self::INPUT_STORES) === 'all') {
            $stores = null;
            $styledOutput->title('Clearing full Solr product index...');
        } else {
            $stores = \explode(',', $input->getOption(self::INPUT_STORES));
            $styledOutput->title('Clearing Solr product index for stores ' . \implode(', ', $stores) . '...');
        }
        try {
            $this->indexer->addProgressHandler(
                new ProgressInConsole($output)
            );
            if ($input->getOption('useswapcore')) {
                $this->indexer->clearStoresOnSwappedCore($stores);
            } else {
                $this->indexer->clearStores($stores);
            }
            $totalTime = number_format(microtime(true) - $startTime, 2);
            $styledOutput->success("Clearing finished in $totalTime seconds.");
        } catch (\Exception $e) {
            $styledOutput->error($e->getMessage());
        }
    }
}