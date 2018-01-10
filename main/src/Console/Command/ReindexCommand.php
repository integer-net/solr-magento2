<?php

namespace IntegerNet\Solr\Console\Command;

use IntegerNet\Solr\Indexer\ProductIndexer;
use IntegerNet\Solr\Model\Indexer;
use IntegerNet\Solr\Model\Indexer\ProductIndexerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @todo Add slice arguments
 * @todo Add --useswapcore argument
 * @todo Allow store codes instead of store ids
 * @todo Add callback to indexer to allow progress output and info about indexed stores
 */
class ReindexCommand extends Command
{
    const INPUT_STORES = 'stores';
    /**
     * @var Indexer\Console
     */
    private $indexer;

    public function __construct(Indexer\Console $indexer, $name = null)
    {
        parent::__construct($name);
        $this->indexer = $indexer;
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
                'emptyindex',
                null,
                InputOption::VALUE_OPTIONAL,
                'Force emptying the solr index for the given store(s). If not set, configured value is used.'
            ),
            new InputOption(
                'noemptyindex',
                null,
                InputOption::VALUE_OPTIONAL,
                'Force not emptying the solr index for the given store(s). If not set, configured value is used.'
            ),
        ];
        $this->setName('solr:reindex:products');
        $this->setDescription('Reindex solr for given stores (see "stores" param)');
        $this->setDefinition($options);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption(self::INPUT_STORES) || $input->getOption(self::INPUT_STORES) === 'all') {
            $stores = null;
            $output->writeln('Starting full reindex of Solr product index...');
        } else {
            $stores = \array_map('intval', \explode(',', $input->getOption(self::INPUT_STORES)));
            $output->writeln('Starting reindex of Solr product index for stores ' . \implode(', ', $stores) . '...');
        }
        try {
            if ($input->getOption('emptyindex')) {
                $output->writeln('Forcing empty index.');
                $this->indexer->executeStoresForceEmpty($stores);
            } elseif ($input->getOption('noemptyindex')) {
                $output->writeln('Forcing non-empty index.');
                $this->indexer->executeStoresForceNotEmpty($stores);
            } else {
                $this->indexer->executeStores($stores);
            }
            $output->writeln('Finished.');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}