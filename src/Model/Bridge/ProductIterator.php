<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Implementor\Product;
use IntegerNet\Solr\Implementor\ProductIterator;

class Integer\Net\Solr\Model\Bridge\ProductIterator extends IteratorIterator implements ProductIterator
{

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $_collection
     */
    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\Collection $_collection)
    {
        parent::__construct($_collection->getIterator());
    }

    /**
     * @return Product
     */
    public function current()
    {
        return new Integer\Net\Solr\Model\Bridge\Product($this->getInnerIterator()->current());
    }

}