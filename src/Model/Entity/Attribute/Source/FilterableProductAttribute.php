<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Entity\Attribute\Source;

use IntegerNet\Solr\Implementor\Attribute;
use IntegerNet\Solr\Implementor\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Store\Model\Store;

class FilterableProductAttribute extends AbstractSource
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @param AttributeRepository $attributeRepository
     */
     public function __construct(AttributeRepository $attributeRepository)
     {
         $this->attributeRepository = $attributeRepository;
     }
    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $options = [
            ['value' => '', 'label' => '']
        ];
        foreach ($this->attributeRepository->getFilterableInCatalogAttributes(Store::DEFAULT_STORE_ID) as $attribute) {
            /** @var Attribute $attribute */
            $options[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => sprintf('%s [%s]', $attribute->getStoreLabel(), $attribute->getAttributeCode()),
            ];
        }
        return $options;
    }
}