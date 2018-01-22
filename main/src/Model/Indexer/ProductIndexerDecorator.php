<?php

namespace IntegerNet\Solr\Model\Indexer;

use IntegerNet\Solr\Indexer\Indexer;
use IntegerNet\Solr\Indexer\Progress\ProgressHandler;
use IntegerNet\Solr\Indexer\Slice;
use IntegerNet\Solr\Plugin\UrlFactoryPlugin;

/**
 * A decorator around the product indexer from the solr library. It is added in ProductIndexerFactory and can be used
 * to add plugins to the indexer methods
 *
 * @package IntegerNet\Solr\Model\Indexer
 */
class ProductIndexerDecorator implements Indexer
{
    /**
     * @var Indexer
     */
    private $productIndexer;

    const PARAM_INDEXER = 'productIndexer';
    /**
     * @var UrlFactoryPlugin
     */
    private $urlFactoryPlugin;

    /**
     * ProductIndexerDecorator constructor.
     * @param Indexer $productIndexer
     */
    public function __construct(Indexer $productIndexer, UrlFactoryPlugin $urlFactoryPlugin)
    {
        $this->productIndexer = $productIndexer;
        $this->urlFactoryPlugin = $urlFactoryPlugin;
    }

    public function addProgressHandler(ProgressHandler $handler)
    {
        $this->productIndexer->addProgressHandler($handler);
    }

    public function reindex(
        $entityIds = null,
        $emptyIndex = false,
        $restrictToStoreIds = null,
        $sliceId = null,
        $totalNumberSlices = null
    ) {
        $this->urlFactoryPlugin->setForceFrontend(true);
        $this->productIndexer->reindex($entityIds, $emptyIndex, $restrictToStoreIds, $sliceId, $totalNumberSlices);
        $this->urlFactoryPlugin->setForceFrontend(false);
    }

    public function reindexSlice(Slice $slice, $restrictToStoreIds = null)
    {
        $this->urlFactoryPlugin->setForceFrontend(true);
        $this->productIndexer->reindexSlice($slice, $restrictToStoreIds);
        $this->urlFactoryPlugin->setForceFrontend(false);
    }

    public function deleteIndex($entityIds)
    {
        $this->productIndexer->deleteIndex($entityIds);
    }

    public function clearIndex($storeId)
    {
        $this->productIndexer->clearIndex($storeId);
    }

    public function checkSwapCoresConfiguration($restrictToStoreIds)
    {
        $this->productIndexer->checkSwapCoresConfiguration($restrictToStoreIds);
    }

    public function swapCores($restrictToStoreIds)
    {
        $this->productIndexer->swapCores($restrictToStoreIds);
    }

    public function activateSwapCore()
    {
        $this->productIndexer->activateSwapCore();
    }

    public function deactivateSwapCore()
    {
        $this->productIndexer->deactivateSwapCore();
    }

    /**
     * Pass all non-interface method calls to the indexer as well
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->productIndexer->{$name}(...$arguments);
    }
}