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
use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;

class VarcharCategoryAttribute extends EavAttributes
{
    /**
     * @var CategoryAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @param CategoryAttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(CategoryAttributeRepositoryInterface $attributeRepository,
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
                'children_count',
                'level',
                'path',
                'position',
            ]
        )->create();
    }

    /**
     * @return \Magento\Catalog\Api\Data\CategoryAttributeInterface[]
     */
    protected function loadAttributes()
    {
        $attributes = $this->attributeRepository->getList($this->searchCriteria)->getItems();
        return $attributes;
    }
}