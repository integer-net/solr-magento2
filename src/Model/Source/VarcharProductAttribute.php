<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Source;

use IntegerNet\Solr\Model\SearchCriteria\VarcharAttributes;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;

class VarcharProductAttribute extends EavAttributes
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(ProductAttributeRepositoryInterface $attributeRepository,
                                SearchCriteriaBuilder $searchCriteriaBuilder)
    {
        $this->attributeRepository = $attributeRepository;
        parent::__construct($searchCriteriaBuilder);
    }

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    protected function buildSearchCriteria(SearchCriteriaBuilder $searchCriteriaBuilder)
    {
        $this->searchCriteria = (new VarcharAttributes($searchCriteriaBuilder))->except([
                'url_path',
                'image_label',
                'small_image_label',
                'thumbnail_label',
                'category_ids',
                'required_options',
                'has_options',
                'created_at',
                'updated_at',
            ]
        )->create();
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    protected function loadAttributes()
    {
        $attributes = $this->attributeRepository->getList($this->searchCriteria)->getItems();
        return $attributes;
    }
}