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
use IntegerNet\Solr\Implementor\ProductFactory;
use Magento\Catalog\Model\Product as MagentoProduct;

class ProductIterator extends \IteratorIterator implements ProductIteratorInterface
{
    /**
     * @var int|null
     */
    private $storeId;
    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**#@+
     * Named constructor parameters
     */
    const PARAM_MAGENTO_PRODUCTS = 'magentoProducts';
    const PARAM_STORE_ID = 'storeId';
    /**#@-*/
    /**
     * @param ProductFactory $productFactory
     * @param MagentoProduct[] $magentoProducts
     * @param int|null $storeId
     */
    public function __construct(ProductFactory $productFactory, array $magentoProducts, $storeId = null)
    {
        parent::__construct(new \ArrayIterator($magentoProducts));
        $this->storeId = $storeId;
        $this->productFactory = $productFactory;
    }

    public function current()
    {
        return $this->productFactory->create([
            Product::PARAM_MAGENTO_PRODUCT => parent::current(),
            Product::PARAM_STORE_ID => $this->storeId
        ]);
    }

}