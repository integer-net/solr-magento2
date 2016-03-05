<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Implementor\Attribute;
use IntegerNet\Solr\Implementor\AttributeRepository;

class Integer\Net\Solr\Model\Bridge\AttributeRepository implements AttributeRepository
{
    const DEFAULT_STORE_ID = 1;
    /**
     * Holds attribute instances with their Magento attributes as attached data
     *
     * @var SplObjectStorage
     */
    protected $_attributeStorage;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection[] */
    protected $_searchableAttributes = [];

    /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection[] */
    protected $_filterableInCatalogOrSearchAttributes = [];

    /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection[] */
    protected $_filterableInSearchAttributes = [];

    /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection[] */
    protected $_filterableInCatalogAttributes = [];

    /** @var \Magento\Eav\Model\Entity\Attribute[] */
    protected $_varcharProductAttributes = null;

    /** @var \Magento\Eav\Model\Entity\Attribute[] */
    protected $_varcharCategoryAttributes = null;

    /** @var \Magento\Eav\Model\Entity\Attribute[] */
    protected $_sortableAttributes = null;

    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributeCollection, 
        \Integer\Net\Solr\Helper\Data $helperData, 
        \Magento\Catalog\Model\ProductFactory $modelProductFactory)
    {
        $this->_attributeCollection = $attributeCollection;
        $this->_helperData = $helperData;
        $this->_modelProductFactory = $modelProductFactory;

        $this->_attributeStorage = new SplObjectStorage();
    }

    /**
     * Creates and registers bridge object for given Magento attribute
     *
     * @internal
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $magentoAttribute
     * @return Integer\Net\Solr\Model\Bridge\Attribute
     */
    public function _registerAttribute(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $magentoAttribute)
    {
        $attribute = new Integer\Net\Solr\Model\Bridge\Attribute($magentoAttribute);
        $this->_attributeStorage->attach($attribute, $magentoAttribute);
        return $attribute;
    }

    /**
     * Returns Magento attribute for a given registered attribute instance
     * @param Attribute $attribute
     * @return null|\Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function getMagentoAttribute(Attribute $attribute)
    {
        if ($this->_attributeStorage->contains($attribute)) {
            return $this->_attributeStorage[$attribute];
        }
        return null;
    }

    /**
     * @param int $storeId
     * @return Attribute[]
     */
    public function getSearchableAttributes($storeId)
    {
        $this->_prepareSearchableAttributeCollection($storeId);

        return $this->_getAttributeArrayFromCollection($this->_searchableAttributes[$storeId], $storeId);
    }

    /**
     * @return Attribute[]
     */
    public function getSortableAttributes()
    {
        if (is_null($this->_sortableAttributes)) {

            /** @var $attributes \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection */
            $this->_sortableAttributes = $this->_attributeCollection
                ->addFieldToFilter('used_for_sort_by', self::DEFAULT_STORE_ID)
                ->addFieldToFilter('attribute_code', ['nin' => ['status']])
            ;
        }

        return $this->_getAttributeArrayFromCollection($this->_sortableAttributes, self::DEFAULT_STORE_ID);
    }

    /**
     * @param int $storeId
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableAttributes($storeId, $useAlphabeticalSearch = true)
    {
        if ($this->_helperData->isCategoryPage()) {
            return $this->getFilterableInCatalogAttributes($storeId, $useAlphabeticalSearch);
        } else {
            return $this->getFilterableInSearchAttributes($storeId, $useAlphabeticalSearch);
        }
    }

    /**
     * @param int $storeId
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableInSearchAttributes($storeId, $useAlphabeticalSearch = true)
    {
        if (! isset($this->_filterableInSearchAttributes[$storeId])) {

            $this->_filterableInSearchAttributes[$storeId] = $this->_attributeCollection
                ->addStoreLabel($storeId)
                ->addIsFilterableInSearchFilter()
                ->addFieldToFilter('attribute_code', ['nin' => ['status']])
            ;

            if ($useAlphabeticalSearch) {
                $this->_filterableInSearchAttributes[$storeId]
                    ->setOrder('frontend_label', \Magento\Eav\Model\Entity\Collection\AbstractCollection::SORT_ORDER_ASC);
            } else {
                $this->_filterableInSearchAttributes[$storeId]
                    ->setOrder('position', \Magento\Eav\Model\Entity\Collection\AbstractCollection::SORT_ORDER_ASC);
            }
        }

        return $this->_getAttributeArrayFromCollection($this->_filterableInSearchAttributes[$storeId], $storeId);
    }


    /**
     * @param int $storeId
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableInCatalogAttributes($storeId, $useAlphabeticalSearch = true)
    {
        if (! isset($this->_filterableInCatalogAttributes[$storeId])) {

            $this->_filterableInCatalogAttributes[$storeId] = $this->_attributeCollection
                ->addStoreLabel($storeId)
                ->addIsFilterableFilter()
                ->addFieldToFilter('attribute_code', ['nin' => ['status']])
            ;

            if ($useAlphabeticalSearch) {
                $this->_filterableInCatalogAttributes[$storeId]
                    ->setOrder('frontend_label', \Magento\Eav\Model\Entity\Collection\AbstractCollection::SORT_ORDER_ASC);
            } else {
                $this->_filterableInCatalogAttributes[$storeId]
                    ->setOrder('position', \Magento\Eav\Model\Entity\Collection\AbstractCollection::SORT_ORDER_ASC);
            }
        }

        return $this->_getAttributeArrayFromCollection($this->_filterableInCatalogAttributes[$storeId], $storeId);
    }

    /**
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getVarcharProductAttributes($useAlphabeticalSearch = true)
    {
        if (is_null($this->_varcharProductAttributes)) {

            /** @var $attributes \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection */
            $this->_varcharProductAttributes = $this->_attributeCollection
                ->addFieldToFilter('backend_type', ['in' => ['static', 'varchar']])
                ->addFieldToFilter('frontend_input', 'text')
                ->addFieldToFilter('attribute_code', ['nin' => [
                    'url_path',
                    'image_label',
                    'small_image_label',
                    'thumbnail_label',
                    'category_ids',
                    'required_options',
                    'has_options',
                    'created_at',
                    'updated_at',
                ]])
            ;

            if ($useAlphabeticalSearch) {
                $this->_varcharProductAttributes
                    ->setOrder('frontend_label', \Magento\Eav\Model\Entity\Collection\AbstractCollection::SORT_ORDER_ASC);
            } else {
                $this->_varcharProductAttributes
                    ->setOrder('position', \Magento\Eav\Model\Entity\Collection\AbstractCollection::SORT_ORDER_ASC);
            }
        }

        return $this->_getAttributeArrayFromCollection($this->_varcharProductAttributes, self::DEFAULT_STORE_ID);
    }

    /**
     * @param int $storeId
     * @param bool $useAlphabeticalSearch
     * @return Attribute[]
     */
    public function getFilterableInCatalogOrSearchAttributes($storeId, $useAlphabeticalSearch = true)
    {
        $this->_prepareFilterableInCatalogOrSearchAttributeCollection($useAlphabeticalSearch, $storeId);

        return $this->_getAttributeArrayFromCollection($this->_filterableInCatalogOrSearchAttributes[$storeId], $storeId);
    }

    /**
     * @return string[]
     */
    public function getAttributeCodesToIndex()
    {
        $this->_prepareFilterableInCatalogOrSearchAttributeCollection(true, self::DEFAULT_STORE_ID);
        $this->_prepareSearchableAttributeCollection(self::DEFAULT_STORE_ID);
        return array_merge(
            $this->_filterableInCatalogOrSearchAttributes[self::DEFAULT_STORE_ID]->getColumnValues('attribute_code'),
            $this->_searchableAttributes[self::DEFAULT_STORE_ID]->getColumnValues('attribute_code')
        );
    }

    /**
     * @param int $storeId
     * @param string $attributeCode
     * @return Attribute
     * @deprecated not part of AttributeRepository interface anymore, should not be needed
     */
    public function getAttributeByCode($storeId, $attributeCode)
    {
        $attribute = $this->_modelProductFactory->create()->getResource()->getAttribute($attributeCode);
        $attribute->setStoreId($storeId);
        return $this->_registerAttribute($attribute);
    }

    /**
     * @param bool $useAlphabeticalSearch
     * @param int $storeId
     */
    protected function _prepareFilterableInCatalogOrSearchAttributeCollection($useAlphabeticalSearch, $storeId)
    {
        if (! isset($this->_filterableInCatalogOrSearchAttributes[$storeId])) {

            /** @var $attributes \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection */
            $this->_filterableInCatalogOrSearchAttributes[$storeId] = $this->_attributeCollection
                ->addStoreLabel($storeId)
                ->addFieldToFilter(
                    [
                        'additional_table.is_filterable',
                        'additional_table.is_filterable_in_search'
                    ],
                    [
                        ['gt' => 0],
                        ['gt' => 0],
                    ]
                )
                ->addFieldToFilter('attribute_code', ['nin' => ['status']]);

            if ($useAlphabeticalSearch) {
                $this->_filterableInCatalogOrSearchAttributes[$storeId]
                    ->setOrder('frontend_label', \Magento\Eav\Model\Entity\Collection\AbstractCollection::SORT_ORDER_ASC);
            } else {
                $this->_filterableInCatalogOrSearchAttributes[$storeId]
                    ->setOrder('position', \Magento\Eav\Model\Entity\Collection\AbstractCollection::SORT_ORDER_ASC);
            }
        }
    }

    protected function _prepareSearchableAttributeCollection($storeId)
    {
        if (! isset($this->_searchableAttributes[$storeId])) {

            $this->_searchableAttributes[$storeId] = $this->_attributeCollection
                ->addStoreLabel($storeId)
                ->addIsSearchableFilter()
                ->addFieldToFilter('attribute_code', ['nin' => ['status']])
                ->addFieldToFilter('source_model', [
                    ['neq' => 'eav/entity_attribute_source_boolean'],
                    ['null' => true]
                ]);
        }
    }
    protected function _getAttributeArrayFromCollection(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection, $storeId)
    {
        $self = $this;
        return array_map(
            function($item) use ($self, $storeId) {
                $item->setStoreId($storeId);
                return $self->_registerAttribute($item);
            },
            $collection->getItems()
        );
    }

}