<?php
/**
 * integer_net Magento Module
 *
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\Solr\Console\Command;

use IntegerNet\Solr\Indexer\ProductIndexer;
use IntegerNet\Solr\Model\Indexer\ProductIndexerFactory;
use IntegerNet\Solr\Plugin\UrlFactoryPlugin;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Index extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ProductIndexer
     */
    private $solrIndexer;
    /**
     * @var UrlFactoryPlugin
     */
    private $urlFactoryPlugin;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ProductIndexerFactory $solrIndexerFactory,
        UrlFactoryPlugin $urlFactoryPlugin,
        State $state
    ) {
        parent::__construct();
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->solrIndexer = $solrIndexerFactory->create();
        $this->urlFactoryPlugin = $urlFactoryPlugin;
        $state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                'stores',
                null,
                InputOption::VALUE_OPTIONAL,
                'Reindex given stores (can be store id, store code, comma seperated. Or "all".) If not set, reindex all stores.'
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
        $this->setName('solr:index')
            ->setDescription('Reindex the IntegerNet_Solr index for given stores (see "stores" param)')
            ->setDefinition($options);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        try {
            $storeIds = $this->getStoreIds($input->getOption('stores'));

            $emptyIndex = true;
            if ($input->getOption('emptyindex')) {
                $emptyIndex = 'force';
            } else if ($input->getOption('noemptyindex')) {
                $emptyIndex = false;
            }

            $this->urlFactoryPlugin->setForceFrontend(true);
            $this->solrIndexer->reindex(null, $emptyIndex, $storeIds);
            $this->urlFactoryPlugin->setForceFrontend(false);

            $storeIdsString = implode(', ', $storeIds);
            echo "Solr product index rebuilt for Stores {$storeIdsString}.\n";


        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }

    /**
     * @param string $storeIdentifiers A comma seperated string with store IDs or codes; or "all"
     * @return int[]
     * @throws \Exception
     */
    private function getStoreIds($storeIdentifiers)
    {
        if (!$storeIdentifiers) {
            $storeIdentifiers = 'all';
        }
        $storeIds = array();
        foreach (explode(',', $storeIdentifiers) as $storeIdentifier) {
            $storeIdentifier = trim($storeIdentifier);
            if ($storeIdentifier == 'all') {
                $storeIds = array();
                foreach ($this->storeManager->getStores(false) as $store) {
                    if ($this->isStoreActive($store)) {
                        $storeIds[] = $store->getId();
                    }
                }
                break;
            }
            $store = $this->storeManager->getStore($storeIdentifier);
            if ($this->isStoreActive($store)) {
                $storeIds[] = $store->getId();
            }
        }
        if (empty($storeIds)) {
            throw new \Exception('No active store given.');
        }

        return $storeIds;
    }

    /**
     * @param Store $store
     * @return bool
     */
    private function isStoreActive($store)
    {
        return $store->isActive()
            && $this->scopeConfig->isSetFlag('integernet_solr/general/is_active', 'stores', $store->getId());
    }
}