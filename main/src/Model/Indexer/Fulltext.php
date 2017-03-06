<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Model\Indexer;

use IntegerNet\Solr\Indexer\ProductIndexer;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

class Fulltext implements ActionInterface, MviewActionInterface
{
    /** @var ProductIndexer */
    private $solrIndexer;

    /**
     * @param ProductIndexerFactory $solrIndexerFactory
     */
    public function __construct(ProductIndexerFactory $solrIndexerFactory)
    {
        $this->solrIndexer = $solrIndexerFactory->create();
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->solrIndexer->reindex(null, true);
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->solrIndexer->reindex($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->executeList([$id]);
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
        $this->executeList($ids);
    }
}