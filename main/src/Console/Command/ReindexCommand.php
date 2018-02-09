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
 * solr:reindex:full command
 */
class ReindexCommand extends Command
{
    const INPUT_STORES       = 'stores';
    const INPUT_EMPTYINDEX   = 'emptyindex';
    const INPUT_NOEMPTYINDEX = 'noemptyindex';
    const INPUT_PROGRESS     = 'progress';
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
                self::INPUT_EMPTYINDEX,
                null,
                InputOption::VALUE_NONE,
                'Force emptying the solr index for the given store(s). If not set, configured value is used.'
            ),
            new InputOption(
                self::INPUT_NOEMPTYINDEX,
                null,
                InputOption::VALUE_NONE,
                'Force not emptying the solr index for the given store(s). If not set, configured value is used.'
            ),
            new InputOption(
                self::INPUT_PROGRESS,
                null,
                InputOption::VALUE_NONE,
                'Show progress bar.'
            )
        ];
        $this->setName('solr:reindex:full');
        $this->setHelp('Reindex Solr for given stores (see "stores" param).');
        $this->setDescription('Reindex Solr');
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
            if ($input->getOption(self::INPUT_EMPTYINDEX)) {
                $styledOutput->note('Forcing empty index.');
                $this->indexer->executeStoresForceEmpty($stores);
            } elseif ($input->getOption(self::INPUT_NOEMPTYINDEX)) {
                $styledOutput->note('Forcing non-empty index.');
                $this->indexer->executeStoresForceNotEmpty($stores);
            } else {
                $this->indexer->executeStores($stores);
            }
            $totalTime = number_format(microtime(true) - $startTime, 2);
            $styledOutput->success("Reindex finished in $totalTime seconds.");
        } catch (\Exception $e) {
            $styledOutput->error($e->getMessage());
        }
    }
}