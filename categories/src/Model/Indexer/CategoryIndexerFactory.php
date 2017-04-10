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
use IntegerNet\Solr\Implementor\EventDispatcher;
use IntegerNet\SolrCategories\Implementor\CategoryRenderer;
use IntegerNet\SolrCategories\Implementor\CategoryRepository;
use IntegerNet\Solr\Implementor\StoreEmulation;
use IntegerNet\Solr\Resource\ResourceFacade;
use IntegerNet\Solr\Model\Config\FrontendStoresConfig;

class CategoryIndexerFactory
{
    /**
     * @var FrontendStoresConfig
     */
    private $storesConfig;
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var CategoryRenderer
     */
    private $categoryRenderer;
    /**
     * @var StoreEmulation
     */
    private $storeEmulation;

    /**
     * @param FrontendStoresConfig $storesConfig
     * @param EventDispatcher $eventDispatcher
     * @param CategoryRepository $productRepository
     * @param CategoryRenderer $productRenderer
     * @param StoreEmulation $storeEmulation
     */
    public function __construct(FrontendStoresConfig $storesConfig, EventDispatcher $eventDispatcher,
                                CategoryRepository $productRepository, CategoryRenderer $productRenderer,
                                StoreEmulation $storeEmulation)
    {

        $this->storesConfig = $storesConfig;
        $this->eventDispatcher = $eventDispatcher;
        $this->categoryRepository = $productRepository;
        $this->categoryRenderer = $productRenderer;
        $this->storeEmulation = $storeEmulation;
    }

    /**
     * @return CategoryIndexer
     */
    public function create()
    {
        $storesConfig = $this->storesConfig->getArrayCopy();
        return new CategoryIndexer(
            0,
            $storesConfig,
            new ResourceFacade($storesConfig),
            $this->eventDispatcher,
            $this->categoryRepository,
            $this->categoryRenderer,
            $this->storeEmulation
        );
    }

}