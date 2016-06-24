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

use IntegerNet\Solr\Implementor\PagedProductIterator as PagedProductIteratorInterface;
use IntegerNet\Solr\Implementor\ProductIterator as ProductIteratorInterface;
use IntegerNet\Solr\Implementor\ProductFactory;
use Magento\Catalog\Model\Product as MagentoProduct;

/**
 * Implements PagedProductIteratorInterface so that it can be used as replacement of real PagedProductIterator
 *
 * i.e. it uses the callback but loads all products at once in one page
 */
class ProductIterator extends \IteratorIterator implements ProductIteratorInterface, PagedProductIteratorInterface
{
    /**
     * @var int|null
     */
    private $storeId;
    /**
     * @var ProductFactory
     */
    private $productFactory;
    /**
     * @var callable
     */
    private $callback;

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

    /**
     * @return bool true if the iterator is valid, otherwise false
     */
    public function valid()
    {
        $valid = parent::valid();
        if (! $valid && \is_callable($this->callback)) {
            \call_user_func($this->callback, $this);
        }
        return $valid;
    }


    /**
     * Define a callback that is called after each "page" iteration (i.e. finished inner iterator)
     *
     * @param callable $callback
     */
    public function setPageCallback($callback)
    {
        $this->callback = $callback;
    }

}