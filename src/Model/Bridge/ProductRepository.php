<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Implementor\ProductRepository;
use IntegerNet\Solr\Implementor\ProductIterator;

class Integer\Net\Solr\Model\Bridge\ProductRepository implements ProductRepository
{
    /**
     * @var int
     */
    protected $_pageSize;

    /**
     * @param int $pageSize
     * @return $this
     */
    public function setPageSizeForIndex($pageSize)
    {
        $this->_pageSize = $pageSize;
        return $this;
    }

    /**
     * Return product iterator, which may implement lazy loading
     *
     * @param int $storeId Products will be returned that are visible in this store and with store specific values
     * @param null|int[] $productIds filter by product ids
     * @return ProductIterator
     */
    public function getProductsForIndex($storeId, $productIds = null)
    {
        return new Integer\Net\Solr\Model\Bridge\LazyProductIterator($storeId, $productIds, $this->_pageSize);
    }

}