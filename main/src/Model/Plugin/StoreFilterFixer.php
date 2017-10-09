<?php
namespace IntegerNet\Solr\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * This plugin fixes issue with filtration by store/website ID in \Magento\Catalog\Model\ProductRepository::getList()
 */
class StoreFilterFixer
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
        if (!is_array($condition)) {
            return $proceed($fields, $condition);
        }

        /*
         * This is to fix a bug in the original code, the third parameter "$strict = true"
         * is missing in 2.1.x. Thus, if we have numerical keys, they are interpreted as
         * belonging to ['from', 'to']. We convert the keys to string explicitly to avoid that.
         */
        if (!in_array(key($condition), ['from', 'to'], true)) {
            $newCondition = [];
            foreach($condition as $key => $value) {
                $newCondition['key' . $key] = $value;
            }
            return $proceed($fields, $newCondition);
        }
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
}