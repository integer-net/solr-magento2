<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
namespace IntegerNet\SolrCategories\Model\Indexer;

use IntegerNet\SolrCategories\Indexer\CategoryIndexer;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

class Fulltext implements ActionInterface, MviewActionInterface
{
    /** @var CategoryIndexer */
    private $solrIndexer;

    /**
     * @param CategoryIndexerFactory $solrIndexerFactory
     */
    public function __construct(CategoryIndexerFactory $solrIndexerFactory)
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