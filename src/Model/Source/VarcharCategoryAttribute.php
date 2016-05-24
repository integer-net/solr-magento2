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

use IntegerNet\Solr\Model\SearchCriteria\AttributeSearchCriteriaBuilder;
use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;

class VarcharCategoryAttribute extends EavAttributes
{
    /**
     * @var CategoryAttributeRepositoryInterface
     */
    protected $attributeRepository;
    /**
     * @var AttributeSearchCriteriaBuilder
     */
    private $varcharAttributesSearchCriteriaBuilder;

    /**
     * @param CategoryAttributeRepositoryInterface $attributeRepository
     * @param AttributeSearchCriteriaBuilder $varcharAttributesSearchCriteriaBuilder
     */
    public function __construct(CategoryAttributeRepositoryInterface $attributeRepository,
                                AttributeSearchCriteriaBuilder $varcharAttributesSearchCriteriaBuilder)
    {
        $this->attributeRepository = $attributeRepository;
        $this->varcharAttributesSearchCriteriaBuilder = $varcharAttributesSearchCriteriaBuilder;
    }

    /**
     * @return \Magento\Catalog\Api\Data\CategoryAttributeInterface[]
     */
    protected function loadAttributes()
    {
        $searchCriteria = $this->varcharAttributesSearchCriteriaBuilder->varchar()->sortedByLabel()->except([
                'url_path',
                'children_count',
                'level',
                'path',
                'position',
            ]
        )->create();
        $attributes = $this->attributeRepository->getList($searchCriteria)->getItems();
        return $attributes;
    }
}