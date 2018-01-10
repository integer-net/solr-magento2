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

use IntegerNet\Solr\Implementor\AttributeRepository;
use IntegerNet\Solr\Implementor\EventDispatcher;
use IntegerNet\Solr\Implementor\IndexCategoryRepository;
use IntegerNet\Solr\Implementor\ProductRenderer;
use IntegerNet\Solr\Implementor\ProductRepository;
use IntegerNet\Solr\Implementor\StoreEmulation;
use IntegerNet\Solr\Indexer\ProductIndexer;
use IntegerNet\Solr\Resource\ResourceFacade;
use IntegerNet\Solr\Model\Config\FrontendStoresConfig;

class ProductIndexerFactory
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
     * @var AttributeRepository
     */
    private $attributeRepository;
    /**
     * @var IndexCategoryRepository
     */
    private $indexCategoryRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var ProductRenderer
     */
    private $productRenderer;
    /**
     * @var StoreEmulation
     */
    private $storeEmulation;
    /**
     * @var ProductIndexerDecoratorFactory
     */
    private $productIndexerDecoratorFactory;

    /**
     * @param FrontendStoresConfig $storesConfig
     * @param EventDispatcher $eventDispatcher
     * @param AttributeRepository $attributeRepository
     * @param IndexCategoryRepository $indexCategoryRepository
     * @param ProductRepository $productRepository
     * @param ProductRenderer $productRenderer
     * @param StoreEmulation $storeEmulation
     */
    public function __construct(
        FrontendStoresConfig $storesConfig,
        EventDispatcher $eventDispatcher,
        AttributeRepository $attributeRepository,
        IndexCategoryRepository $indexCategoryRepository,
        ProductRepository $productRepository,
        ProductRenderer $productRenderer,
        StoreEmulation $storeEmulation,
        ProductIndexerDecoratorFactory $productIndexerDecoratorFactory
    ) {

        $this->storesConfig = $storesConfig;
        $this->eventDispatcher = $eventDispatcher;
        $this->attributeRepository = $attributeRepository;
        $this->indexCategoryRepository = $indexCategoryRepository;
        $this->productRepository = $productRepository;
        $this->productRenderer = $productRenderer;
        $this->storeEmulation = $storeEmulation;
        $this->productIndexerDecoratorFactory = $productIndexerDecoratorFactory;
    }

    /**
     * @return ProductIndexer
     */
    public function create()
    {
        $storesConfig = $this->storesConfig->getArrayCopy();
        $productIndexer = new ProductIndexer(
            0,
            $storesConfig,
            new ResourceFacade($storesConfig),
            $this->eventDispatcher,
            $this->attributeRepository,
            $this->indexCategoryRepository,
            $this->productRepository,
            $this->productRenderer,
            $this->storeEmulation
        );
        return $this->productIndexerDecoratorFactory->create(
            [
                ProductIndexerDecorator::PARAM_INDEXER => $productIndexer
            ]
        );
    }

}