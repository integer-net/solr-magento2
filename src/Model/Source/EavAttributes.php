<?php
namespace IntegerNet\Solr\Model\Source;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Data\OptionSourceInterface;

abstract class EavAttributes implements OptionSourceInterface
{
    /**
     * @var SearchCriteria
     */
    protected $searchCriteria;

    /**
     * Load attributes based on $searchCriteria
     *
     * @return \Magento\Catalog\Api\Data\EavAttributeInterface[]
     */
    abstract protected function loadAttributes();

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