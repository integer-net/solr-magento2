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

class VarcharCategoryAttribute
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [[
            'value' => '',
            'label' => '',
        ]];
        return $options; //TODO implement

        /** @var $attributes \Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection */
        $attributes = $this->_attributeCollection
            ->addFieldToFilter('backend_type', ['in' => ['static', 'varchar']])
            ->addFieldToFilter('frontend_input', 'text')
            ->addFieldToFilter('attribute_code', ['nin' => [
                'url_path',
                'children_count',
                'level',
                'path',
                'position',
            ]])
            ->setOrder('frontend_label', \Magento\Eav\Model\Entity\Collection\AbstractCollection::SORT_ORDER_ASC);
        
        foreach ($attributes as $attribute) {
            /** @var \Magento\Catalog\Model\Entity\Attribute $attribute */
            $options[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => sprintf('%s [%s]', $attribute->getFrontendLabel(), $attribute->getAttributeCode()),
            ];
        }
        return $options;
    }
}