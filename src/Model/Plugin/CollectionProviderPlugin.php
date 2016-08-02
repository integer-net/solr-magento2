<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Plugin;
use Closure;
use IntegerNet\Solr\Model\Config\CurrentStoreConfig;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use IntegerNet\Solr\Model\ResourceModel\ResultCollection;
use IntegerNet\Solr\Model\ResourceModel\ResultCollectionFactory;

/**
 * Plugin to replace collections in layered navigation
 *
 * @see \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface
 */
class CollectionProviderPlugin
{
    /**
     * @var ResultCollectionFactory
     */
    private $collectionFactory;
    /**
     * @var CurrentStoreConfig
     */
    private $currentStoreConfig;

    /**
     * CollectionProviderPlugin constructor.
     * @param ResultCollectionFactory $_collectionFactory
     */
    public function __construct(ResultCollectionFactory $_collectionFactory, CurrentStoreConfig $currentStoreConfig)
    {
        $this->collectionFactory = $_collectionFactory;
        $this->currentStoreConfig = $currentStoreConfig;
    }

    /**
     * @see \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface::getCollection()
     * @param ItemCollectionProviderInterface $subject
     * @param Closure $proceed
     * @param Category $category
     * @return ResultCollection
     */
    public function aroundGetCollection(ItemCollectionProviderInterface $subject, Closure $proceed, Category $category)
    {
        if (! $this->isModuleActive()) {
            //TODO configure AdapterFactory to use MySQL adapter
            return $proceed($category);
        }
        if ($this->currentStoreConfig->getResultsConfig()->isUseHtmlFromSolr()) {
            return $this->collectionFactory->create();
        }
        return $proceed($category);
    }

    /**
     * @return bool
     */
    private function isModuleActive()
    {
        //TODO also check if licence is valid and engine is integernet_solr
        //TODO if active, ping solr service, if unsuccessful return false
        return $this->currentStoreConfig->getGeneralConfig()->isActive();
    }
}