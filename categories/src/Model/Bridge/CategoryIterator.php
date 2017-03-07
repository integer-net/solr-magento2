<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\SolrCategories\Model\Bridge;

use IntegerNet\SolrCategories\Implementor\CategoryIterator as CategoryIteratorInterface;
use IntegerNet\SolrCategories\Implementor\CategoryFactory as CategoryFactoryInterface;
use Magento\Catalog\Model\Category as MagentoCategory;

/**
 * Implements PagedCategoryIteratorInterface so that it can be used as replacement of real PagedCategoryIterator
 *
 * i.e. it uses the callback but loads all Categorys at once in one page
 */
class CategoryIterator extends \IteratorIterator implements CategoryIteratorInterface
{
    /**
     * @var string[]|null
     */
    private $categoryPathNames;
    /**
     * @var CategoryFactoryInterface
     */
    private $categoryFactory;
    /**
     * @var callable
     */
    private $callback;

    /**
     * Named constructor parameters
     */
    const PARAM_MAGENTO_CATEGORIES = 'magentoCategories';
    const PARAM_STORE_ID = 'storeId';

    /**
     * @param CategoryFactoryInterface $CategoryFactory
     * @param MagentoCategory[] $magentoCategories
     * @param int|null $categoryPathNames
     */
    public function __construct(CategoryFactoryInterface $CategoryFactory, array $magentoCategories, $categoryPathNames = null)
    {
        parent::__construct(new \ArrayIterator($magentoCategories));
        $this->categoryPathNames = $categoryPathNames;
        $this->categoryFactory = $CategoryFactory;
    }

    public function current()
    {
        return $this->categoryFactory->create([
            Category::PARAM_MAGENTO_CATEGORY => parent::current(),
            Category::PARAM_CATEGORY_PATH_NAMES => $this->categoryPathNames
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