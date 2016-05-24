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
     * @var AttributeSearchCriteriaBuilder
     */
    private $varcharAttributesSearchCriteriaBuilder;

    /**
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param AttributeSearchCriteriaBuilder $varcharAttributesSearchCriteriaBuilder
     */
     public function __construct(ProductAttributeRepositoryInterface $attributeRepository,
                                AttributeSearchCriteriaBuilder $varcharAttributesSearchCriteriaBuilder)
    {
        $this->attributeRepository = $attributeRepository;
        $this->varcharAttributesSearchCriteriaBuilder = $varcharAttributesSearchCriteriaBuilder;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface[]
     */
    protected function loadAttributes()
    {
        $searchCriteria = $this->varcharAttributesSearchCriteriaBuilder->varchar()->sortedByLabel()->except([
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
        $attributes = $this->attributeRepository->getList($searchCriteria)->getItems();
        return $attributes;
    }
}