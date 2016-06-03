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

use IntegerNet\Solr\Implementor\ProductIterator as ProductIteratorInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Event\ManagerInterface;

class ProductIterator extends \IteratorIterator implements ProductIteratorInterface
{

    /**
     * @var AttributeRepository
     */
    private $attributeRepository;
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    /**
     * @var int|null
     */
    private $storeId;

    /**
     * @param ProductCollection $collection
     * @param AttributeRepository $attributeRepository
     * @param ManagerInterface $eventManager
     * @param int|null $storeId
     */
    public function __construct(ProductCollection $collection, AttributeRepository $attributeRepository,
                                ManagerInterface $eventManager, $storeId = null)
    {
        parent::__construct($collection->getIterator());
        $this->attributeRepository = $attributeRepository;
        $this->eventManager = $eventManager;
        $this->storeId = $storeId;
    }

    public function current()
    {
        return new Product(parent::current(), $this->attributeRepository, $this->eventManager, $this->storeId);
    }

}