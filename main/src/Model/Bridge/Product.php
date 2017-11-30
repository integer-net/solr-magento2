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
use Magento\Catalog\Api\Data\ProductInterface as MagentoProductInterface;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

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
     * @var EventManagerInterface
     */
    private $eventManager;

    /**#@+
     * Named constructor parameters
     */
    const PARAM_MAGENTO_PRODUCT = 'magentoProduct';
    const PARAM_STORE_ID = 'storeId';
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**#@-*/
    /**
     * Note: needs concrete Product model class for attribute frontend model, which expects a data object (Magento 2.0).
     *
     * @param MagentoProduct $magentoProduct
     * @param AttributeRepository $attributeRepository
     * @param EventManagerInterface $eventManager
     * @param StockRegistryInterface $stockRegistry
     * @param ScopeConfigInterface $scopeConfig
     * @param int|null $storeId store id for store specific values (null for default)
     */
    public function __construct(
        MagentoProduct $magentoProduct,
        AttributeRepository $attributeRepository,
        EventManagerInterface $eventManager,
        StockRegistryInterface $stockRegistry,
        ScopeConfigInterface $scopeConfig,
        $storeId = null
    ) {
        $this->magentoProduct = $magentoProduct;
        $this->attributeRepository = $attributeRepository;
        $this->eventManager = $eventManager;
        $this->storeId = $storeId;
        $this->stockRegistry = $stockRegistry;
        $this->scopeConfig = $scopeConfig;
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
        if ($solrExcludeValue = $this->magentoProduct->getData('solr_exclude')) {
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
        return \in_array($this->getMagentoProduct()->getVisibility(), [
            MagentoProduct\Visibility::VISIBILITY_IN_CATALOG,
            MagentoProduct\Visibility::VISIBILITY_BOTH,
        ]);
    }

    public function isVisibleInSearch()
    {
        return \in_array($this->getMagentoProduct()->getVisibility(), [
            MagentoProduct\Visibility::VISIBILITY_IN_SEARCH,
            MagentoProduct\Visibility::VISIBILITY_BOTH,
        ]);
    }

    public function getSolrBoost()
    {
        $boost = $this->getMagentoProduct()->getData('solr_boost');
        if ($boost === null) {
            $boost = 1;
        }
        if (!$this->isInStock()) {
            $boost *= floatval($this->scopeConfig->getValue('integernet_solr/results/priority_outofstock', 'stores', $this->storeId));
        }
        return $boost;
    }

    public function getPrice()
    {
        return $this->getMagentoProduct()->getPrice();
    }

    public function getAttributeValue(AttributeInterface $attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $attributeCode)));
        $product = $this->getMagentoProduct();
        if (\method_exists($product, $method)) {
            return $product->$method();
        }
        return $product->getData($attributeCode);
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

    public function hasSpecialPrice()
    {
        return (int) ($this->magentoProduct->getFinalPrice() < $this->getMagentoProduct()->getPrice());
    }

    /**
     * Returns Magento product. Use this method to type hint against the Service Contract interface.
     *
     * @return MagentoProductInterface
     */
    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->getMagentoProduct()->getSku();
    }

    /**
     * @return bool
     */
    public function isInStock()
    {
        /** @var StockItemInterface $stockItem */
        $stockItem = $this->stockRegistry->getStockItem($this->getMagentoProduct()->getId(), $this->storeId);
        return (bool)$stockItem->getIsInStock();
    }
}