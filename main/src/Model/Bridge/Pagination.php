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

use IntegerNet\Solr\Implementor\Pagination as PaginationInterface;

class Pagination implements PaginationInterface
{
    /**
     * Returns page size
     *
     * @return int
     */
    public function getPageSize()
    {
        // TODO: Implement getPageSize() method.
    }

    /**
     * Returns current page
     *
     * @return int
     */
    public function getCurrentPage()
    {
        // TODO: Implement getCurrentPage() method.
    }

    /**
     * Returns sort order
     *
     * @return string {'asc', 'desc'}
     */
    public function getCurrentDirection()
    {
        // TODO: Implement getCurrentDirection() method.
    }

    /**
     * Returns sort criterion (attribute)
     *
     * @return string
     */
    public function getCurrentOrder()
    {
        // TODO: Implement getCurrentOrder() method.
    }

}