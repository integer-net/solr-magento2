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
use IntegerNet\Solr\Indexer\Slice;

class Console
{
    /**
     * @var ProductIndexer
     */
    private $solrIndexer;

    /**
     * @param ProductIndexerFactory $solrIndexerFactory
     */
    public function __construct(ProductIndexerFactory $solrIndexerFactory)
    {
        $this->solrIndexer = $solrIndexerFactory->create();
    }

    public function executeStores(array $storeIds = null)
    {
        $this->reindex(null, true, $storeIds);
    }

    public function executeStoresSlice(Slice $slice, array $storeIds = null)
    {
        $this->solrIndexer->reindexSlice($slice, $storeIds);
    }

    public function executeStoresForceEmpty($storeIds)
    {
        $this->reindex(null, 'force', $storeIds);
    }

    public function executeStoresForceNotEmpty($storeIds)
    {
        $this->reindex(null, false, $storeIds);
    }

    public function clearStores(array $storeIds = null)
    {
        //TODO fetch all store ids if NULL
        if (empty($storeIds)) {
            throw new \BadMethodCallException("Command for 'clear all stores' not implemented yet");
        }
        foreach ($storeIds as $storeId) {
            $this->solrIndexer->clearIndex($storeId);
        }
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
}