<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_SolrCategories
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\Solr\Implementor\Category;

class Integer\Net\Solr\Model\Bridge\Category implements Category
{
    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;
    /**
     * @var string[]
     */
    protected $_categoryPathNames = [];

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param string[] $categoryPathNames
     */
    public function __construct(\Magento\Catalog\Model\Category $category, array $categoryPathNames)
    {
        $this->_category = $category;
        $this->_categoryPathNames = $categoryPathNames;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_category->getId();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->_category->getUrl();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_category->getName();
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getPath($separator)
    {
        return implode($separator, $this->_categoryPathNames);
    }

}
