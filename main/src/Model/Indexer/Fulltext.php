<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
namespace IntegerNet\Solr\Model\Indexer;

use IntegerNet\Solr\Indexer\ProductIndexer;
use IntegerNet\Solr\Plugin\UrlFactoryPlugin;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

class Fulltext implements ActionInterface, MviewActionInterface
{
    /**
     * @var ProductIndexer
     */
    private $solrIndexer;
    /**
     * @var UrlFactoryPlugin
     */
    private $urlFactoryPlugin;

    /**
     * @param ProductIndexerFactory $solrIndexerFactory
     * @param UrlFactoryPlugin $urlFactoryPlugin
     */
    public function __construct(ProductIndexerFactory $solrIndexerFactory, UrlFactoryPlugin $urlFactoryPlugin)
    {
        $this->solrIndexer = $solrIndexerFactory->create();
        $this->urlFactoryPlugin = $urlFactoryPlugin;
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->reindex(null, true);
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->reindex($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->reindex([$id]);
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     * @api
     */
    public function execute($ids)
    {
        $this->reindex($ids);
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
    protected function reindex(
        $productIds = null,
        $emptyIndex = false,
        $restrictToStoreIds = null,
        $sliceId = null,
        $totalNumberSlices = null
    ) {
        $this->urlFactoryPlugin->setForceFrontend(true);
        $this->solrIndexer->reindex(
            $productIds,
            $emptyIndex,
            $restrictToStoreIds,
            $sliceId,
            $totalNumberSlices
        );
        $this->urlFactoryPlugin->setForceFrontend(false);
    }
}