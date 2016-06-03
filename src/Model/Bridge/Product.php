<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Implementor\Attribute as AttributeInterface;
use IntegerNet\Solr\Implementor\Product as ProductInterface;
use IntegerNet\Solr\Implementor\ProductIterator;
use Magento\Catalog\Api\Data\ProductInterface as MagentoProduct;

class Product implements ProductInterface
{
    /**
     * @var MagentoProduct
     */
    private $magentoProduct;
    /**
     * @var int|null
     */
    private $storeId;
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @param MagentoProduct $magentoProduct
     * @param AttributeRepository $attributeRepository
     * @param int|null $storeId
     */
    public function __construct(MagentoProduct $magentoProduct, AttributeRepository $attributeRepository, $storeId = null)
    {
        $this->magentoProduct = $magentoProduct;
        $this->attributeRepository = $attributeRepository;
        $this->storeId = $storeId;
    }

    /**
     * @return string
     */
    public function getSolrId()
    {
        return sprintf('%d_%d', $this->getId(), $this->getStoreId());
    }

    /**
     * @return bool
     */
    public function isIndexable()
    {
        // TODO: Implement isIndexable() method.
    }

    public function getId()
    {
        return $this->magentoProduct->getId();
    }

    public function getStoreId()
    {
        return $this->storeId;
    }

    public function isVisibleInCatalog()
    {
        // TODO: Implement isVisibleInCatalog() method.
    }

    public function isVisibleInSearch()
    {
        // TODO: Implement isVisibleInSearch() method.
    }

    public function getSolrBoost()
    {
        return $this->magentoProduct->getExtensionAttributes()->getSolrBoost();
    }

    public function getPrice()
    {
        return $this->magentoProduct->getPrice();
    }

    public function getAttributeValue(AttributeInterface $attribute)
    {
        return $this->magentoProduct->getCustomAttribute($attribute->getAttributeCode())->getValue();
    }

    /**
     * Return searchable attribute value (localized values instead of ids, comma separated strings instead of arrays)
     *
     * @param AttributeInterface $attribute
     * @return string|null
     */
    public function getSearchableAttributeValue(AttributeInterface $attribute)
    {
        return $this->attributeRepository->getMagentoAttribute($attribute)
            ->getFrontend()
            ->getValue($this->magentoProduct);
    }

    public function getCategoryIds()
    {
        //TODO: find a way to get these with service contracts
        //       or require actual product resource instance
    }

    /**
     * @return ProductIterator
     */
    public function getChildren()
    {
        // TODO: find a way to get product type or children with service contracts
        //       or require actual product resource instance
    }

}