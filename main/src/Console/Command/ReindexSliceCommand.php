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
    const INPUT_STORES      = 'stores';
    const INPUT_SLICE       = 'slice';
    const INPUT_USESWAPCORE = 'useswapcore';
    const INPUT_PROGRESS    = 'progress';
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
                'Reindex given stores (can be store id, store code, comma seperated. Or "all".) '
                . 'If not set, reindex all stores.'
            ),
            new InputOption(
                self::INPUT_SLICE,
                null,
                InputOption::VALUE_REQUIRED,
                '<number>/<total_number>, i.e. "1/5" or "2/5". '
            ),
            new InputOption(
                self::INPUT_USESWAPCORE,
                null,
                InputOption::VALUE_NONE,
                'Use swap core for indexing instead of live solr core (only if configured correctly).'
            ),
            new InputOption(
                self::INPUT_PROGRESS,
                null,
                InputOption::VALUE_NONE,
                'Show progress bar.'
            )
        ];
        $this->setName('solr:reindex:slice');
        $this->setHelp(
            'Partially reindex Solr for given stores (see "stores" param). '
            . 'Can be used for letting indexing run in parallel.'
        );
        $this->setDescription('Partially reindex Solr');
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
            $styledOutput->title('Full reindex of Solr product index...');
        } else {
            $stores = \explode(',', $input->getOption(self::INPUT_STORES));
            $styledOutput->title('Reindex of Solr product index for stores ' . \implode(', ', $stores) . '...');
        }
        try {
            $this->indexer->addProgressHandler(
                new ProgressInConsole(
                    $output,
                    $input->getOption(self::INPUT_PROGRESS) ? ProgressInConsole::USE_PROGRESS_BAR : false
                )
            );
            $styledOutput->note('Processing slice ' . $input->getOption(self::INPUT_SLICE) . '...');
            if ($input->getOption(self::INPUT_USESWAPCORE)) {
                $this->indexer->executeStoresSliceOnSwappedCore(
                    Slice::fromExpression($input->getOption(self::INPUT_SLICE)),
                    $stores
                );
            } else {
                $this->indexer->executeStoresSlice(
                    Slice::fromExpression($input->getOption(self::INPUT_SLICE)),
                    $stores
                );
            }
            $totalTime = number_format(microtime(true) - $startTime, 2);
            $styledOutput->success("Reindex finished in $totalTime seconds.");
        } catch (\Exception $e) {
            $styledOutput->error($e->getMessage());
        }
    }
}