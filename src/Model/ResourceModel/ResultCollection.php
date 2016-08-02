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
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Select;
use Psr\Log\LoggerInterface;

/**
 *
 */
class ResultCollection extends NullCollection
{
    /**
     * @param EntityFactoryInterface $entityFactory
     */
    public function __construct(EntityFactoryInterface $entityFactory, LoggerInterface $logger, NullSelect $select)
    {
        parent::__construct($entityFactory, $logger, $select);
        $this->_select;
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
        //TODO implement
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
        //TODO implement
        return [];
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


}