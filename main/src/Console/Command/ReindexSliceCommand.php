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
 * solr:reindex:slice command
 */
class ReindexSliceCommand extends Command
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
                'Reindex given stores (can be store id, store code, comma seperated. Or "all".) '
                . 'If not set, reindex all stores.'
            ),
            new InputOption(
                'slice',
                null,
                InputOption::VALUE_REQUIRED,
                '<number>/<total_number>, i.e. "1/5" or "2/5". '
            ),
            new InputOption(
                'useswapcore',
                null,
                InputOption::VALUE_NONE,
                'Use swap core for indexing instead of live solr core (only if configured correctly).'
            ),
            new InputOption(
                'progress',
                null,
                InputOption::VALUE_NONE,
                'Show progress bar.'
            )
        ];
        $this->setName('solr:reindex:slice');
        $this->setHelp('Partially reindex Solr for given stores (see "stores" param). Can be used for letting indexing run in parallel.');
        $this->setDescription('Partially reindex Solr');
        $this->setDefinition($options);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $styledOutput = new SymfonyStyle($input, $output);
        $startTime = microtime(true);
        $this->appState->setAreaCode(App\Area::AREA_GLOBAL);
        if (!$input->getOption(self::INPUT_STORES) || $input->getOption(self::INPUT_STORES) === 'all') {
            $stores = null;
            $styledOutput->title('Full reindex of Solr product index...');
        } else {
            $stores = \explode(',', $input->getOption(self::INPUT_STORES));
            $styledOutput->title('Reindex of Solr product index for stores ' . \implode(', ', $stores) . '...');
        }
        try {
            $this->indexer->addProgressHandler(
                new ProgressInConsole(
                    $output,
                    $input->getOption('progress') ? ProgressInConsole::USE_PROGRESS_BAR : false
                )
            );
            $output->writeln('Processing slice ' . $input->getOption('slice') . '...');
            if ($input->getOption('useswapcore')) {
                $this->indexer->executeStoresSliceOnSwappedCore(
                    Slice::fromExpression($input->getOption('slice')),
                    $stores
                );
            } else {
                $this->indexer->executeStoresSlice(Slice::fromExpression($input->getOption('slice')), $stores);
            }
            $totalTime = number_format(microtime(true) - $startTime, 2);
            $styledOutput->success("Reindex finished in $totalTime seconds.");
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}