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

use IntegerNet\SolrCategories\Implementor\Category as CategoryInterface;
use IntegerNet\SolrCategories\Implementor\CategoryRenderer as CategoryRendererInterface;
use IntegerNet\Solr\Indexer\IndexDocument;
use Magento\Framework\App\State as AppState;

class CategoryRenderer implements CategoryRendererInterface
{
    /**
     * @param CategoryInterface $category
     * @param IndexDocument $categoryData
     * @param bool $useHtmlInResults
     * @throws \InvalidArgumentException
     */
    public function addResultHtmlToCategoryData(CategoryInterface $category, IndexDocument $categoryData, $useHtmlInResults)
    {
        if (!$category instanceof Category) {
            // We need direct access to the Magento category
            throw new \InvalidArgumentException('Magento 1 category bridge expected, '. get_class($category) .' received.');
        }
    }
}