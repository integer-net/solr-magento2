<?php
namespace IntegerNet\Solr\Model\Source;

use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

abstract class EavAttributes implements OptionSourceInterface
{
    /**
     * @var SearchCriteria
     */
    protected $searchCriteria;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(SearchCriteriaBuilder $searchCriteriaBuilder)
    {
        $this->buildSearchCriteria($searchCriteriaBuilder);
    }

    /**
     * Load attributes based on $searchCriteria
     *
     * @return \Magento\Catalog\Api\Data\EavAttributeInterface[]
     */
    abstract protected function loadAttributes();

    /**
     * Configure search criteria builder and build $searchCriteria
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    abstract protected function buildSearchCriteria(SearchCriteriaBuilder $searchCriteriaBuilder);

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributes = $this->loadAttributes();

        $options = [[
            'value' => '',
            'label' => '',
        ]];
        foreach ($attributes as $attribute) {
            /** @var \Magento\Catalog\Model\Entity\Attribute $attribute */
            $options[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => sprintf('%s [%s]', $attribute->getDefaultFrontendLabel(), $attribute->getAttributeCode()),
            ];
        }
        return $options;
    }

}