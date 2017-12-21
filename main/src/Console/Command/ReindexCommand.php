<?php

namespace IntegerNet\Solr\Console\Command;

use IntegerNet\Solr\Indexer\ProductIndexer;
use IntegerNet\Solr\Model\Indexer;
use IntegerNet\Solr\Model\Indexer\ProductIndexerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
        $this->setName('solr:reindex:products');
        $this->setDescription('Reindex solr for given stores (see "stores" param)');
        $this->addOption(
            self::INPUT_STORES,
            null,
            InputOption::VALUE_OPTIONAL,
            'Reindex given stores (can be store id, store code, comma seperated. Or "all".) If not set, reindex all stores.',
            'all'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (! $input->hasOption(self::INPUT_STORES) || $input->getOption(self::INPUT_STORES) === 'all') {
            $this->executeFull($output);
            return;
        }
        $this->executeStores(\explode(',', $input->getOption(self::INPUT_STORES)), $output);
    }

    private function executeFull(OutputInterface $output)
    {
        $output->writeln('Starting full reindex of Solr product index...');
        $this->indexer->executeFull();
        $output->writeln('Finished');
    }

    private function executeStores(array $stores, OutputInterface $output)
    {
        $output->writeln('Starting reindex of Solr product index for stores ' . \implode(', ', $stores) . '...');
        $this->indexer->executeStores(array_map('intval', $stores));
        $output->writeln('Finished');
    }
}