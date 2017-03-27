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

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CategoryPosition extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        // id_field_name will not be used. the table has a compound primary key (product_id,category_id,store_id)
        $this->_init('catalog_category_product_index', 'product_id');
    }

    /**
     * @param $productId
     * @param $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryPositions($productId, $storeId)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), ['category_id', 'position'])
            ->where('product_id = ?', $productId)
            ->where('store_id = ?', $storeId);
        return $this->getConnection()->fetchAll($select);
    }
}