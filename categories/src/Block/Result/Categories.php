<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
namespace IntegerNet\SolrCategories\Block\Result;

use IntegerNet\SolrCategories\Model\ResourceModel\CategoriesCollection;
use Magento\Catalog\Block\Product\Context;

class Categories extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var CategoriesCollection
     */
    private $categoriesCollection;

    public function __construct(
        Context $context,
        CategoriesCollection $categoriesCollection,
        array $data
    )
    {
        parent::__construct($context, $data);
        $this->categoriesCollection = $categoriesCollection;
    }

    /**
     * @return CategoriesCollection
     */
    public function getResultsCollection()
    {
        return $this->categoriesCollection;
    }

    /**
     * @param \Apache_Solr_Document $document
     * @return string
     */
    public function getCategoryPath($document)
    {
        return $document->path_s_nonindex;
    }

    /**
     * @param \Apache_Solr_Document $document
     * @return string
     */
    public function getCategoryTitle($document)
    {
        return $document->name_t;
    }

    /**
     * @param \Apache_Solr_Document $document
     * @return string
     */
    public function getCategoryAbstract($document)
    {
        if (isset($document->abstract_t_nonindex)) {
            return $document->abstract_t_nonindex;
        }
        return '';
    }

    /**
     * @param \Apache_Solr_Document $document
     * @return string
     */
    public function getCategoryUrl($document)
    {
        return $document->url_s_nonindex;
    }

    /**
     * @param \Apache_Solr_Document $document
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getCategoryImageUrl($document, $width = null, $height = null)
    {
        if (isset($document->image_url_s_nonindex) && ($imageUrl = $document->image_url_s_nonindex)) {
            return $imageUrl; /** @TODO resize image */
        }
        return '';
    }
}