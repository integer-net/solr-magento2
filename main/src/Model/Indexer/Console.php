<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Indexer;

use IntegerNet\Solr\Indexer\ProductIndexer;
use IntegerNet\Solr\Indexer\Progress\ProgressHandler;
use IntegerNet\Solr\Indexer\Slice;
use Magento\Store\Model\StoreManagerInterface;

class Console
{
    /**
     * @var ProductIndexer
     */
    private $solrIndexer;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ProductIndexerFactory $solrIndexerFactory
     */
    public function __construct(ProductIndexerFactory $solrIndexerFactory, StoreManagerInterface $storeManager)
    {
        $this->solrIndexer = $solrIndexerFactory->create();
        $this->storeManager = $storeManager;
    }

    public function executeStores(array $storeIds = null)
    {
        $this->reindex(null, true, $this->getStoreIds($storeIds));
    }

    public function executeStoresSlice(Slice $slice, array $storeIds = null)
    {
        $this->solrIndexer->reindexSlice($slice, $this->getStoreIds($storeIds));
    }

    public function executeStoresForceEmpty($storeIds)
    {
        $this->reindex(null, 'force', $this->getStoreIds($storeIds));
    }

    public function executeStoresForceNotEmpty($storeIds)
    {
        $this->reindex(null, false, $this->getStoreIds($storeIds));
    }

    public function executeStoresSliceOnSwappedCore($slice, $storeIds)
    {
        $this->solrIndexer->activateSwapCore($storeIds);
        $this->solrIndexer->reindexSlice($slice, $this->getStoreIds($storeIds));
        $this->solrIndexer->deactivateSwapCore($storeIds);
    }

    public function clearStores(array $storeIds = null)
    {
        //TODO fetch all store ids if NULL
        if (empty($storeIds)) {
            throw new \BadMethodCallException("Command for 'clear all stores' not implemented yet");
        }
        foreach ($this->getStoreIds($storeIds) as $storeId) {
            $this->solrIndexer->clearIndex($storeId);
        }
    }

    public function addProgressHandler(ProgressHandler $handler)
    {
        $this->solrIndexer->addProgressHandler($handler);
    }

    /**
     * Call product indexer
     *
     * @param array|null $productIds Restrict to given Products if this is set
     * @param boolean|string $emptyIndex Whether to truncate the index before refilling it
     * @param null|int[] $restrictToStoreIds
     * @param null|int $sliceId
     * @param null|int $totalNumberSlices
     * @throws \Exception
     */
    private function reindex(
        $productIds = null,
        $emptyIndex = false,
        $restrictToStoreIds = null,
        $sliceId = null,
        $totalNumberSlices = null
    ) {
        $this->solrIndexer->reindex(
            $productIds,
            $emptyIndex,
            $restrictToStoreIds,
            $sliceId,
            $totalNumberSlices
        );
    }

    /**
     * @param array|null $storeIds An array of store codes or store ids
     * @return array|null An array of store ids
     */
    private function getStoreIds(array $storeIds = null)
    {
        if ($storeIds === null) {
            return null;
        }
        $storesByCode = $this->storeManager->getStores(false, true);
        return array_map(
            function ($storeId) use ($storesByCode) {
                if (is_numeric($storeId)) {
                    return $storeId;
                }
                if (isset($storesByCode[$storeId])) {
                    return $storesByCode[$storeId]->getId();
                }
                throw new \InvalidArgumentException("'$storeId' is neither a numric ID nor an existing store code");
            },
            $storeIds
        );
    }
}