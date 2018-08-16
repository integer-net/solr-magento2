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
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;

class CategoryPosition extends AbstractDb
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetaData;

    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadata,
        ?string $connectionName = null
    ) {
        $this->productMetaData = $productMetadata;
        parent::__construct($context, $connectionName);
    }

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
        $table = $this->getMainTable();
        if (version_compare($this->productMetaData->getVersion(), '2.2', '>=')) {
            $table = $this->getTable('catalog_category_product_index_store' . $storeId);
        }
        $select = $this->getConnection()
            ->select()
            ->from($table, ['category_id', 'position'])
            ->where('product_id = ?', $productId)
            ->where('store_id = ?', $storeId);
        return $this->getConnection()->fetchAll($select);
    }
}