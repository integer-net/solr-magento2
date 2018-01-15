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
 * solr:swap command
 */
class SwapCommand extends Command
{
    const INPUT_STORES      = 'stores';

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
                self::INPUT_STORES,
                null,
                InputOption::VALUE_OPTIONAL,
                'Swap cores for given stores (can be store id, store code, comma seperated. Or "all".) '
                . 'If not set, swap cores for all stores.'
            ),
        ];
        $this->setName('solr:swap');
        $this->setHelp(
            'Swap cores. This is useful if using slices (see solr:reindex:slice) '
            . 'after indexing with the "--use_swap_core" param; it\'s not needed otherwise.'
        );
        $this->setDescription('Swap cores');
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
            $styledOutput->title('Swap all cores...');
        } else {
            $stores = \explode(',', $input->getOption(self::INPUT_STORES));
            $styledOutput->title('Swap cores for stores ' . \implode(', ', $stores) . '...');
        }
        try {
            $this->indexer->addProgressHandler(
                new ProgressInConsole($output)
            );
            $this->indexer->swapCores($stores);
            $totalTime = number_format(microtime(true) - $startTime, 2);
            $styledOutput->success("Core swap finished in $totalTime seconds.");
        } catch (\Exception $e) {
            $styledOutput->error($e->getMessage());
        }
    }
}