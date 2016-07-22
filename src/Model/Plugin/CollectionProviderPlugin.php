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
    protected $_collectionFactory;

    /**
     * CollectionProviderPlugin constructor.
     * @param ResultCollectionFactory $_collectionFactory
     */
    public function __construct(ResultCollectionFactory $_collectionFactory)
    {
        $this->_collectionFactory = $_collectionFactory;
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
        return $this->_collectionFactory->create();
    }
}