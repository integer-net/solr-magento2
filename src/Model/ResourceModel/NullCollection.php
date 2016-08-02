<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as ProductCollection;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinDataInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Psr\Log\LoggerInterface;

/**
 * We have to extend the original product collection even if we don't need any of the database
 * related methods because the layer model and its plugins type hint against it (there is no
 * interface).
 *
 * We use this class to replace all public methods with stubs.
 */
abstract class NullCollection extends ProductCollection
{
    /**
     * @param EntityFactoryInterface $entityFactory
     */
    public function __construct(EntityFactoryInterface $entityFactory, LoggerInterface $logger, NullSelect $select)
    {
        $this->_entityFactory = $entityFactory;
        $this->_logger = $logger;
        $this->_select = $select;
    }
    /**
     * @deprecated
     * @param \Magento\Search\Api\SearchInterface $object
     * @return void
     */
    public function setSearch(\Magento\Search\Api\SearchInterface $object)
    {
        return;
    }

    /**
     * @deprecated
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $object
     * @return void
     */
    public function setSearchCriteriaBuilder(\Magento\Framework\Api\Search\SearchCriteriaBuilder $object)
    {
        return;
    }

    /**
     * @deprecated
     * @param \Magento\Framework\Api\FilterBuilder $object
     * @return void
     */
    public function setFilterBuilder(\Magento\Framework\Api\FilterBuilder $object)
    {
        return;
    }

    /**
     * Stub method for compatibility with other search engines
     *
     * @return $this
     */
    public function setGeneralDefaultQuery()
    {
        return $this;
    }

    /**
     * Return field faceted data from faceted search result
     *
     * @param string $field
     * @return array
     * @throws StateException
     */
    public function getFacetedData($field)
    {
        return [];
    }

    /**
     * Get cloned Select after dispatching 'catalog_prepare_price_select' event
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getCatalogPreparedSelect()
    {
        throw new \BadMethodCallException('Unsupported method ' . __METHOD__);
    }

    /**
     * Get price expression sql part
     *
     * @param \Magento\Framework\DB\Select $select
     * @return string
     */
    public function getPriceExpression($select)
    {
        return '';
    }

    /**
     * Get additional price expression sql part
     *
     * @param \Magento\Framework\DB\Select $select
     * @return string
     */
    public function getAdditionalPriceExpression($select)
    {
        return '';
    }

    /**
     * Get currency rate
     *
     * @return float
     */
    public function getCurrencyRate()
    {
        return 1.0;
    }

    /**
     * Retrieve Catalog Product Flat Helper object
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\State
     */
    public function getFlatState()
    {
        throw new \BadMethodCallException('Unsupported method ' . __METHOD__);
    }

    /**
     * Retrieve is flat enabled flag
     * Return always false if magento run admin
     *
     * @return bool
     */
    public function isEnabledFlat()
    {
        return false;
    }

    /**
     * Retrieve collection empty item
     * Redeclared for specifying id field name without getting resource model inside model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getNewEmptyItem()
    {
        return Collection::getNewEmptyItem();
    }

    /**
     * Set entity to use for attributes
     *
     * @param \Magento\Eav\Model\Entity\AbstractEntity $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        return $this;
    }

    /**
     * Set Store scope for collection
     *
     * @param mixed $store
     * @return $this
     */
    public function setStore($store)
    {
        return $this;
    }

    /**
     * Add attribute to entities in collection
     * If $attribute=='*' select all attributes
     *
     * @param array|string|integer|\Magento\Framework\App\Config\Element $attribute
     * @param bool|string $joinType
     * @return $this
     */
    public function addAttributeToSelect($attribute, $joinType = false)
    {
        return $this;
    }

    /**
     * Add collection filters by identifiers
     *
     * @param mixed $productId
     * @param boolean $exclude
     * @return $this
     */
    public function addIdFilter($productId, $exclude = false)
    {
        return $this;
    }

    /**
     * Adding product website names to result collection
     * Add for each product websites information
     *
     * @return $this
     */
    public function addWebsiteNamesToResult()
    {
        return $this;
    }

    /**
     * @param bool|false $printQuery
     * @param bool|false $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        return $this;
    }

    /**
     * Add store availability filter. Include availability product
     * for store website
     *
     * @param null|string|bool|int|Store $store
     * @return $this
     */
    public function addStoreFilter($store = null)
    {
        return $this;
    }

    /**
     * Add website filter to collection
     *
     * @param null|bool|int|string|array $websites
     * @return $this
     */
    public function addWebsiteFilter($websites = null)
    {
        return $this;
    }

    /**
     * Get filters applied to collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation
     */
    public function getLimitationFilters()
    {
        return null;
    }

    /**
     * Specify category filter for product collection
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return $this
     */
    public function addCategoryFilter(\Magento\Catalog\Model\Category $category)
    {
        return $this;
    }

    /**
     * Filter Product by Categories
     *
     * @param array $categoriesFilter
     */
    public function addCategoriesFilter(array $categoriesFilter)
    {
        return;
    }

    /**
     * Join minimal price attribute to result
     *
     * @return $this
     */
    public function joinMinimalPrice()
    {
        return $this;
    }

    /**
     * Retrieve max value by attribute
     *
     * @param string $attribute
     * @return array|null
     */
    public function getMaxAttributeValue($attribute)
    {
        return null;
    }

    /**
     * Retrieve ranging product count for arrtibute range
     *
     * @param string $attribute
     * @param int $range
     * @return array
     */
    public function getAttributeValueCountByRange($attribute, $range)
    {
        return [];
    }

    /**
     * Retrieve product count by some value of attribute
     *
     * @param string $attribute
     * @return array ($value => $count)
     */
    public function getAttributeValueCount($attribute)
    {
        return [];
    }

    /**
     * Return all attribute values as array in form:
     * array(
     *   [entity_id_1] => array(
     *          [store_id_1] => store_value_1,
     *          [store_id_2] => store_value_2,
     *          ...
     *          [store_id_n] => store_value_n
     *   ),
     *   ...
     * )
     *
     * @param string $attribute attribute code
     * @return array
     */
    public function getAllAttributeValues($attribute)
    {
        return [];
    }

    /**
     * Get SQL for get record count without left JOINs
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        throw new \BadMethodCallException('Unsupported method ' . __METHOD__);
    }

    /**
     * Retrieve all ids for collection
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return [];
    }

    /**
     * Retrieve product count select for categories
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getProductCountSelect()
    {
        throw new \BadMethodCallException('Unsupported method ' . __METHOD__);
    }

    /**
     * Destruct product count select
     *
     * @return $this
     */
    public function unsProductCountSelect()
    {
        return $this;
    }

    /**
     * Adding product count to categories collection
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $categoryCollection
     * @return $this
     */
    public function addCountToCategories($categoryCollection)
    {
        return $this;
    }

    /**
     * Retrieve unique attribute set ids in collection
     *
     * @return array
     */
    public function getSetIds()
    {
        return [];
    }

    /**
     * Return array of unique product type ids in collection
     *
     * @return array
     */
    public function getProductTypeIds()
    {
        return [];
    }

    /**
     * Joins url rewrite rules to collection
     *
     * @return $this
     */
    public function joinUrlRewrite()
    {
        return $this;
    }

    /**
     * Add URL rewrites data to product
     * If collection loadded - run processing else set flag
     *
     * @param int|string $categoryId
     * @return $this
     */
    public function addUrlRewrite($categoryId = '')
    {
        return $this;
    }

    /**
     * Add minimal price data to result
     *
     * @return $this
     */
    public function addMinimalPrice()
    {
        return $this;
    }

    /**
     * Add price data for calculate final price
     *
     * @return $this
     */
    public function addFinalPrice()
    {
        return $this;
    }

    /**
     * Retrieve all ids
     *
     * @param boolean $resetCache
     * @return array
     */
    public function getAllIdsCache($resetCache = false)
    {
        return [];
    }

    /**
     * Set all ids
     *
     * @param array $value
     * @return $this
     */
    public function setAllIdsCache($value)
    {
        return $this;
    }

    /**
     * Add Price Data to result
     *
     * @param int $customerGroupId
     * @param int $websiteId
     * @return $this
     */
    public function addPriceData($customerGroupId = null, $websiteId = null)
    {
        return $this;
    }

    /**
     * Add attribute to filter
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|string $attribute
     * @param array $condition
     * @param string $joinType
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        return $this;
    }

    /**
     * Add requere tax percent flag for product collection
     *
     * @return $this
     */
    public function addTaxPercents()
    {
        return $this;
    }

    /**
     * Get require tax percent flag value
     *
     * @return bool
     */
    public function requireTaxPercent()
    {
        return false;
    }

    /**
     * Adding product custom options to result collection
     *
     * @return $this
     */
    public function addOptionsToResult()
    {
        return $this;
    }

    /**
     * Filter products with required options
     *
     * @return $this
     */
    public function addFilterByRequiredOptions()
    {
        return $this;
    }

    /**
     * Set product visibility filter for enabled products
     *
     * @param array $visibility
     * @return $this
     */
    public function setVisibility($visibility)
    {
        return $this;
    }

    /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        return $this;
    }

    /**
     * Apply front-end price limitation filters to the collection
     *
     * @return $this
     */
    public function applyFrontendPriceLimitations()
    {
        return $this;
    }

    /**
     * Add category ids to loaded items
     *
     * @return $this
     */
    public function addCategoryIds()
    {
        return $this;
    }

    /**
     * Add tier price data to loaded items
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addTierPriceData()
    {
        return $this;
    }

    /**
     * Add field comparison expression
     *
     * @param string $comparisonFormat - expression for sprintf()
     * @param array $fields - list of fields
     * @return $this
     * @throws \Exception
     */
    public function addPriceDataFieldFilter($comparisonFormat, $fields)
    {
        return $this;
    }

    /**
     * Clear collection
     *
     * @return $this
     */
    public function clear()
    {
        return $this;
    }

    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = Select::SQL_DESC)
    {
        return $this;
    }

    /**
     * Get products max price
     *
     * @return float
     */
    public function getMaxPrice()
    {
        return 0.0;
    }

    /**
     * Get products min price
     *
     * @return float
     */
    public function getMinPrice()
    {
        return 0.0;
    }

    /**
     * Get standard deviation of products price
     *
     * @return float
     */
    public function getPriceStandardDeviation()
    {
        return 0.0;
    }

    /**
     * Get count of product prices
     *
     * @return int
     */
    public function getPricesCount()
    {
        return 0;
    }

    /**
     * Retrieve table name
     *
     * @param string $table
     * @return string
     * @codeCoverageIgnore
     */
    public function getTable($table)
    {
        return '';
    }

    /**
     * Get collection's entity object
     *
     * @return \Magento\Eav\Model\Entity\AbstractEntity
     * @throws LocalizedException
     */
    public function getEntity()
    {
        throw new \BadMethodCallException('Unsupported method ' . __METHOD__);
    }

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @codeCoverageIgnore
     */
    public function getResource()
    {
        throw new \BadMethodCallException('Unsupported method ' . __METHOD__);
    }

    /**
     * Set template object for the collection
     *
     * @param   \Magento\Framework\DataObject $object
     * @return $this
     */
    public function setObject($object = null)
    {
        return $this;
    }

    /**
     * Add an object to the collection
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @throws LocalizedException
     */
    public function addItem(\Magento\Framework\DataObject $object)
    {
        return $this;
    }

    /**
     * Retrieve entity attribute
     *
     * @param   string $attributeCode
     * @return  \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function getAttribute($attributeCode)
    {
        throw new \BadMethodCallException('Unsupported method ' . __METHOD__);
    }

    /**
     * Wrapper for compatibility with \Magento\Framework\Data\Collection\AbstractDb
     *
     * @param mixed $attribute
     * @param mixed $condition
     * @return $this|AbstractDb
     * @codeCoverageIgnore
     */
    public function addFieldToFilter($attribute, $condition = null)
    {
        return $this;
    }

    /**
     * Add entity type to select statement
     *
     * @param string $entityType
     * @param string $prefix
     * @return $this
     * @codeCoverageIgnore
     */
    public function addEntityTypeToSelect($entityType, $prefix)
    {
        return $this;
    }

    /**
     * Add field to static
     *
     * @param string $field
     * @return $this
     */
    public function addStaticField($field)
    {
        return $this;
    }

    /**
     * Add attribute expression (SUM, COUNT, etc)
     *
     * Example: ('sub_total', 'SUM({{attribute}})', 'revenue')
     * Example: ('sub_total', 'SUM({{revenue}})', 'revenue')
     *
     * For some functions like SUM use groupByAttribute.
     *
     * @param string $alias
     * @param string $expression
     * @param string $attribute
     * @return $this
     * @throws LocalizedException
     */
    public function addExpressionAttributeToSelect($alias, $expression, $attribute)
    {
        return $this;
    }

    /**
     * Groups results by specified attribute
     *
     * @param string|array $attribute
     * @return $this
     */
    public function groupByAttribute($attribute)
    {
        return $this;
    }

    /**
     * Add attribute from joined entity to select
     *
     * Examples:
     * ('billing_firstname', 'customer_address/firstname', 'default_billing')
     * ('billing_lastname', 'customer_address/lastname', 'default_billing')
     * ('shipping_lastname', 'customer_address/lastname', 'default_billing')
     * ('shipping_postalcode', 'customer_address/postalcode', 'default_shipping')
     * ('shipping_city', $cityAttribute, 'default_shipping')
     *
     * Developer is encouraged to use existing instances of attributes and entities
     * After first use of string entity name it will be cached in the collection
     *
     * @todo connect between joined attributes of same entity
     * @param string $alias alias for the joined attribute
     * @param string|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param string $bind attribute of the main entity to link with joined $filter
     * @param string $filter primary key for the joined entity (entity_id default)
     * @param string $joinType inner|left
     * @param null $storeId
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function joinAttribute($alias, $attribute, $bind, $filter = null, $joinType = 'inner', $storeId = null)
    {
        return $this;
    }

    /**
     * Join regular table field and use an attribute as fk
     *
     * Examples:
     * ('country_name', 'directory_country_name', 'name', 'country_id=shipping_country',
     *      "{{table}}.language_code='en'", 'left')
     *
     * @param string $alias 'country_name'
     * @param string $table 'directory_country_name'
     * @param string $field 'name'
     * @param string $bind 'PK(country_id)=FK(shipping_country_id)'
     * @param string|array $cond "{{table}}.language_code='en'" OR array('language_code'=>'en')
     * @param string $joinType 'left'
     * @return $this
     * @throws LocalizedException
     */
    public function joinField($alias, $table, $field, $bind, $cond = null, $joinType = 'inner')
    {
        return $this;
    }

    /**
     * Join a table
     *
     * @param string|array $table
     * @param string $bind
     * @param string|array $fields
     * @param null|array $cond
     * @param string $joinType
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function joinTable($table, $bind, $fields = null, $cond = null, $joinType = 'inner')
    {
        return $this;
    }

    /**
     * Remove an attribute from selection list
     *
     * @param string $attribute
     * @return $this
     */
    public function removeAttributeToSelect($attribute = null)
    {
        return $this;
    }

    /**
     * Set collection page start and records to show
     *
     * @param integer $pageNum
     * @param integer $pageSize
     * @return $this
     * @codeCoverageIgnore
     */
    public function setPage($pageNum, $pageSize)
    {
        return $this;
    }

    /**
     * Retrieve all ids sql
     *
     * @return Select
     */
    public function getAllIdsSql()
    {
        throw new \BadMethodCallException('Unsupported method ' . __METHOD__);
    }

    /**
     * Save all the entities in the collection
     *
     * @todo make batch save directly from collection
     *
     * @return $this
     */
    public function save()
    {
        return $this;
    }

    /**
     * Delete all the entities in the collection
     *
     * @todo make batch delete directly from collection
     *
     * @return $this
     */
    public function delete()
    {
        return $this;
    }

    /**
     * Import 2D array into collection as objects
     *
     * If the imported items already exist, update the data for existing objects
     *
     * @param array $arr
     * @return $this
     */
    public function importFromArray($arr)
    {
        return $this;
    }

    /**
     * Get collection data as a 2D array
     *
     * @return array
     */
    public function exportToArray()
    {
        return [];
    }

    /**
     * Retrieve row id field name
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getRowIdFieldName()
    {
        return '';
    }

    /**
     * Id field name getter
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return '';
    }

    /**
     * Set row id field name
     *
     * @param string $fieldName
     * @return $this
     */
    public function setRowIdFieldName($fieldName)
    {
        return $this;
    }

    /**
     * Retrieve array of attributes
     *
     * @param array $arrAttributes
     * @return array
     */
    public function toArray($arrAttributes = [])
    {
        return [];
    }

    /**
     * Returns already loaded element ids
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getLoadedIds()
    {
        return [];
    }

    /**
     * Remove all items from collection
     * @return $this
     */
    public function removeAllItems()
    {
        return $this;
    }

    /**
     * Remove item from collection by item key
     *
     * @param mixed $key
     * @return $this
     */
    public function removeItemByKey($key)
    {
        return $this;
    }

    /**
     * Returns main table name - extracted from "module/table" style and
     * validated by db adapter
     *
     * @return string
     */
    public function getMainTable()
    {
        return '';
    }

    /**
     * Wrapper for compatibility with \Magento\Framework\Data\Collection\AbstractDb
     *
     * @param string $field
     * @param string $alias
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return $this|\Magento\Framework\Data\Collection\AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function addFieldToSelect($field, $alias = null)
    {
        return $this;
    }

    /**
     * Wrapper for compatibility with \Magento\Framework\Data\Collection\AbstractDb
     *
     * @param string $field
     * @return $this|\Magento\Framework\Data\Collection\AbstractDb
     * @codeCoverageIgnore
     */
    public function removeFieldFromSelect($field)
    {
        return $this;
    }

    /**
     * Wrapper for compatibility with \Magento\Framework\Data\Collection\AbstractDb
     *
     * @return $this|\Magento\Framework\Data\Collection\AbstractDb
     * @codeCoverageIgnore
     */
    public function removeAllFieldsFromSelect()
    {
        return $this;
    }

    /**
     * Set store scope
     *
     * @param int|string|\Magento\Store\Api\Data\StoreInterface $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this;
    }

    /**
     * Return current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return 0;
    }

    /**
     * Retrieve default store id
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        return 0;
    }

    /**
     * Add variable to bind list
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addBindParam($name, $value)
    {
        return $this;
    }

    /**
     * Set database connection adapter
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $conn
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setConnection(\Magento\Framework\DB\Adapter\AdapterInterface $conn)
    {
        return $this;
    }

    /**
     * Get \Magento\Framework\DB\Select instance
     *
     * @return Select
     */
    public function getSelect()
    {
        return $this->_select;
    }

    /**
     * Retrieve connection object
     *
     * @return AdapterInterface
     */
    public function getConnection()
    {
        throw new \BadMethodCallException('Unsupported method ' . __METHOD__);
    }

    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
        return 0;
    }

    /**
     * Get sql select string or object
     *
     * @param   bool $stringMode
     * @return  string|\Magento\Framework\DB\Select
     */
    public function getSelectSql($stringMode = false)
    {
        throw new \BadMethodCallException('Unsupported method ' . __METHOD__);
    }

    /**
     * self::setOrder() alias
     *
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function addOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        return $this;
    }

    /**
     * Add select order to the beginning
     *
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function unshiftOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        return $this;
    }

    /**
     * Set select distinct
     *
     * @param   bool $flag
     * @return  $this
     */
    public function distinct($flag)
    {
        return $this;
    }

    /**
     * Load data with filter in place
     *
     * @param   bool $printQuery
     * @param   bool $logQuery
     * @return  $this
     */
    public function loadWithFilter($printQuery = false, $logQuery = false)
    {
        return $this;
    }

    /**
     * Returns a collection item that corresponds to the fetched row
     * and moves the internal data pointer ahead
     *
     * @return  \Magento\Framework\Model\AbstractModel|bool
     */
    public function fetchItem()
    {
        return false;
    }

    /**
     * Get all data array for collection
     *
     * @return array
     */
    public function getData()
    {
        return [];
    }

    /**
     * Reset loaded for collection data array
     *
     * @return $this
     */
    public function resetData()
    {
        return $this;
    }

    /**
     * Load the data.
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        return $this;
    }

    /**
     * Print and/or log query
     *
     * @param   bool $printQuery
     * @param   bool $logQuery
     * @param   string $sql
     * @return  $this
     */
    public function printLogQuery($printQuery = false, $logQuery = false, $sql = null)
    {
        return $this;
    }

    /**
     * Add filter to Map
     *
     * @param string $filter
     * @param string $alias
     * @param string $group default 'fields'
     * @return $this
     */
    public function addFilterToMap($filter, $alias, $group = 'fields')
    {
        return $this;
    }

    /**
     * Clone $this->_select during cloning collection, otherwise both collections will share the same $this->_select
     *
     * @return void
     */
    public function __clone()
    {
        return;
    }

    /**
     * Join extension attribute.
     *
     * @param JoinDataInterface $join
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @return $this
     */
    public function joinExtensionAttribute(
        JoinDataInterface $join,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    )
    {
        return $this;
    }

    /**
     * Get collection item object class name.
     *
     * @return string
     */
    public function getItemObjectClass()
    {
        return $this->_itemObjectClass;
    }

    /**
     * @inheritdoc
     */
    public function __sleep()
    {
        return Collection::__sleep();
    }

    /**
     * @inheritdoc
     */
    public function __wakeup()
    {
        Collection::__wakeup();
    }

    /**
     * Add collection filter
     *
     * @param string $field
     * @param string $value
     * @param string $type and|or|string
     * @return $this
     */
    public function addFilter($field, $value, $type = 'and')
    {
        return $this;
    }

    /**
     * Search for a filter by specified field
     *
     * Multiple filters can be matched if an array is specified:
     * - 'foo' -- get the first filter with field name 'foo'
     * - array('foo') -- get all filters with field name 'foo'
     * - array('foo', 'bar') -- get all filters with field name 'foo' or 'bar'
     * - array() -- get all filters
     *
     * @param string|string[] $field
     * @return \Magento\Framework\DataObject|\Magento\Framework\DataObject[]|void
     */
    public function getFilter($field)
    {
        return [];
    }

    /**
     * Retrieve collection loading status
     *
     * @return bool
     */
    public function isLoaded()
    {
        return false;
    }

    /**
     * Get current collection page
     *
     * @param  int $displacement
     * @return int
     */
    public function getCurPage($displacement = 0)
    {
        return parent::getCurPage($displacement);
    }

    /**
     * Retrieve collection last page number
     *
     * @return int
     */
    public function getLastPageNumber()
    {
        return parent::getLastPageNumber();
    }

    /**
     * Retrieve collection page size
     *
     * @return int
     */
    public function getPageSize()
    {
        return parent::getPageSize();
    }

    /**
     * Retrieve collection first item
     *
     * @return \Magento\Framework\DataObject
     */
    public function getFirstItem()
    {
        return parent::getFirstItem();
    }

    /**
     * Retrieve collection last item
     *
     * @return \Magento\Framework\DataObject
     */
    public function getLastItem()
    {
        return parent::getLastItem();
    }

    /**
     * Retrieve collection items
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getItems()
    {
        return parent::getItems();
    }

    /**
     * Retrieve field values from all items
     *
     * @param   string $colName
     * @return  array
     */
    public function getColumnValues($colName)
    {
        return parent::getColumnValues($colName);
    }

    /**
     * Search all items by field value
     *
     * @param   string $column
     * @param   mixed $value
     * @return  array
     */
    public function getItemsByColumnValue($column, $value)
    {
        return parent::getItemsByColumnValue($column, $value);
    }

    /**
     * Search first item by field value
     *
     * @param   string $column
     * @param   mixed $value
     * @return  \Magento\Framework\DataObject || null
     */
    public function getItemByColumnValue($column, $value)
    {
        return parent::getItemByColumnValue($column, $value);
    }

    /**
     * Walk through the collection and run model method or external callback
     * with optional arguments
     *
     * Returns array with results of callback for each item
     *
     * @param string $callback
     * @param array $args
     * @return array
     */
    public function walk($callback, array $args = [])
    {
        return parent::walk($callback, $args);
    }

    /**
     * @param string|array $objMethod
     * @param array $args
     * @return void
     */
    public function each($objMethod, $args = [])
    {
        parent::each($objMethod, $args);
    }

    /**
     * Setting data for all collection items
     *
     * @param   mixed $key
     * @param   mixed $value
     * @return $this
     */
    public function setDataToAll($key, $value = null)
    {
        return parent::setDataToAll($key, $value);
    }

    /**
     * Set current page
     *
     * @param   int $page
     * @return $this
     */
    public function setCurPage($page)
    {
        return parent::setCurPage($page);
    }

    /**
     * Set collection page size
     *
     * @param   int $size
     * @return $this
     */
    public function setPageSize($size)
    {
        return parent::setPageSize($size);
    }

    /**
     * Set collection item class name
     *
     * @param  string $className
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setItemObjectClass($className)
    {
        return parent::setItemObjectClass($className);
    }

    /**
     * Convert collection to XML
     *
     * @return string
     */
    public function toXml()
    {
        return parent::toXml();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return parent::toOptionArray();
    }

    /**
     * @return array
     */
    public function toOptionHash()
    {
        return parent::toOptionHash();
    }

    /**
     * Retrieve item by id
     *
     * @param   mixed $idValue
     * @return  \Magento\Framework\DataObject
     */
    public function getItemById($idValue)
    {
        return parent::getItemById($idValue);
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return parent::getIterator();
    }

    /**
     * Retrieve count of collection loaded items
     *
     * @return int
     */
    public function count()
    {
        return parent::count();
    }

    /**
     * Retrieve Flag
     *
     * @param string $flag
     * @return bool|null
     */
    public function getFlag($flag)
    {
        return parent::getFlag($flag);
    }

    /**
     * Set Flag
     *
     * @param string $flag
     * @param bool|null $value
     * @return $this
     */
    public function setFlag($flag, $value = null)
    {
        return parent::setFlag($flag, $value);
    }

    /**
     * Has Flag
     *
     * @param string $flag
     * @return bool
     */
    public function hasFlag($flag)
    {
        return parent::hasFlag($flag);
    }

    /**
     * Add search query filter
     *
     * @param string $query
     * @return $this
     */
    public function addSearchFilter($query)
    {
        return $this;
    }

}