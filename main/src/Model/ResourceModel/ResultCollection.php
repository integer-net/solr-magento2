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
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Collection;
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
        $this->setItemObjectClass(Product::class);
    }

    /**
     * @param bool|false $printQuery
     * @param bool|false $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        return Collection::load();
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
        $this->_items = [
            $this->getNewEmptyItem()->setData([
                'id' => 1337,
                'name' => 'i do not exist',
                'url_key' => 'well_that_will_be_404',
            ]),
        ];
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

    public function getSize()
    {
        return Collection::getSize();
    }
}