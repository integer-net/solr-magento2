<?php
namespace IntegerNet\Solr\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * Among other problems, this plugin fixes issue with filtration by store/website ID in
 * \Magento\Catalog\Model\ProductRepository::getList()
 */
class ProductCollectionPlugin
{
    /**
     * Enable filtration by store_id or website_id. The only supported condition is 'eq'
     *
     * @see \Magento\Catalog\Model\ResourceModel\Product\Collection::addFieldToFilter()
     * @param ProductCollection $subject
     * @param \Closure $proceed
     * @param array $fields
     * @param string|null $condition
     * @return ProductCollection
     */
    public function aroundAddFieldToFilter(ProductCollection $subject, \Closure $proceed, $fields, $condition = null)
    {
        if (is_array($fields)) {
            foreach ($fields as $key => $filter) {
                if ($filter['attribute'] == 'website_id' && isset($filter['eq'])) {
                    $subject->addWebsiteFilter([$filter['eq']]);
                    unset($fields[$key]);
                } else if ($filter['attribute'] == 'store_id' && isset($filter['eq'])) {
                    $subject->addStoreFilter($filter['eq']);
                    unset($fields[$key]);
                }
            }
        }
        /** Do not try to pass empty $fields to addFieldToFilter, it will cause exception */
        return $fields? $proceed($fields, $condition) : $subject;
    }

    /**
     * Fix filter in collection by changing "eq" to "in" if the value is an array.
     * This was an issue if there were multiple values for a swatches attribute filter.
     *
     * @param ProductCollection $subject
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|string $attribute
     * @param array $condition
     * @param string $joinType
     * @return array
     */
    public function beforeAddAttributeToFilter(
        ProductCollection $subject,
        $attribute,
        $condition = null,
        $joinType = 'inner'
    ) {
        if (is_array($condition) && isset($condition['eq']) && is_array($condition['eq'])) {
            $condition = ['in' => $condition['eq']];
        }
        return [$attribute, $condition, $joinType];
    }
}