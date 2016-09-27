<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Model\Data\CategoryCollection;
use IntegerNet\SolrSuggest\Implementor\SerializableSuggestCategory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Session\SidResolverInterface;

class SerializableCategoryRepository implements \IntegerNet\SolrSuggest\Implementor\SerializableCategoryRepository
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;
    /**
     * @var SidResolverInterface
     */
    private $sidResolver;

    public function __construct(CategoryCollectionFactory $categoryCollectionFactory, SidResolverInterface $sidResolver)
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->sidResolver = $sidResolver;
    }

    /**
     * @param int $storeId
     * @return SerializableSuggestCategory[]
     */
    public function findActiveCategories($storeId)
    {
        $origUseSessionInUrl = $this->sidResolver->getUseSessionInUrl();
        $this->sidResolver->setUseSessionInUrl(false);
        try {
            return CategoryCollection::fromMagentoCollection(
                $this->categoryCollectionFactory->create()
                    ->setStoreId($storeId)
                    ->addAttributeToSelect(['name', 'url_key'])
                    ->addAttributeToFilter('is_active', 1)
                    ->addAttributeToFilter('include_in_menu', 1)
                    ->addAttributeToFilter('level', ['gt' => 1])
            )->map(
                function (Category $category) use ($storeId) {
                    $category->setStoreId($storeId);
                    return new \IntegerNet\SolrSuggest\Plain\Bridge\Category(
                        $category->getId(), $category->getName(), $category->getUrl()
                    );
                }
            )->getArrayCopy();
        } finally {
            $this->sidResolver->setUseSessionInUrl($origUseSessionInUrl);
        }
    }

}