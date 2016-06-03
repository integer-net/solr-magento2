<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Implementor\Attribute as AttributeInterface;
use IntegerNet\Solr\Implementor\Product as ProductInterface;
use IntegerNet\Solr\Implementor\ProductIterator as ProductIteratorInterface;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Api\Data\ProductInterface as MagentoProductInterface;
use Magento\Framework\Event\ManagerInterface;

class Product implements ProductInterface
{
    const EVENT_CAN_INDEX_PRODUCT = 'integernet_solr_can_index_product';
    /**
     * Magento product. Only access this directly if methods are needed that are not available in the
     * Service Contract. Prefer {@see getMagentoProduct()} if possible
     *
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
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * Note: needs concrete Product model class for attribute frontend model, which expects a data object (Magento 2.0).
     *
     * @param MagentoProduct $magentoProduct
     * @param AttributeRepository $attributeRepository
     * @param ManagerInterface $eventManager
     * @param int|null $storeId store id for store specific values (null for default)
     */
    public function __construct(MagentoProduct $magentoProduct, AttributeRepository $attributeRepository,
                                ManagerInterface $eventManager, $storeId = null)
    {
        $this->magentoProduct = $magentoProduct;
        $this->attributeRepository = $attributeRepository;
        $this->eventManager = $eventManager;
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
        $this->eventManager->dispatch(self::EVENT_CAN_INDEX_PRODUCT, ['product' => $this->getMagentoProduct()]);
        if ($this->getMagentoProduct()->getStatus() == MagentoProduct\Attribute\Source\Status::STATUS_DISABLED) {
            return false;
        }
        if (! $this->isVisibleInCatalog() && ! $this->isVisibleInSearch()) {
            return false;
        }
        if (! \in_array($this->magentoProduct->getStore()->getWebsiteId(), $this->magentoProduct->getWebsiteIds())) {
            return false;
        }
        if (! $this->getMagentoProduct()->getExtensionAttributes()->getStockItem()->getIsInStock()) {
            return false;
        }
        if ($this->getMagentoProduct()->getExtensionAttributes()->getSolrExclude()) {
            return false;
        }
        return true;
    }

    public function getId()
    {
        return $this->getMagentoProduct()->getId();
    }

    public function getStoreId()
    {
        return $this->storeId;
    }

    public function isVisibleInCatalog()
    {
        return \in_array($this->magentoProduct->getVisibility(), [
            MagentoProduct\Visibility::VISIBILITY_IN_CATALOG,
            MagentoProduct\Visibility::VISIBILITY_BOTH,
        ]);
    }

    public function isVisibleInSearch()
    {
        return \in_array($this->magentoProduct->getVisibility(), [
            MagentoProduct\Visibility::VISIBILITY_IN_SEARCH,
            MagentoProduct\Visibility::VISIBILITY_BOTH,
        ]);
    }

    public function getSolrBoost()
    {
        return $this->getMagentoProduct()->getExtensionAttributes()->getSolrBoost();
    }

    public function getPrice()
    {
        return $this->getMagentoProduct()->getPrice();
    }

    public function getAttributeValue(AttributeInterface $attribute)
    {
        return $this->getMagentoProduct()->getCustomAttribute($attribute->getAttributeCode())->getValue();
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
        return $this->magentoProduct->getCategoryIds();
    }

    /**
     * @return ProductIteratorInterface
     */
    public function getChildren()
    {
        //TODO implement
    }

    /**
     * Returns Magento product. Use this method to type hint against the Service Contract interface.
     *
     * @return MagentoProductInterface
     */
    private function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

}