<?php
/**
 * integer_net Magento Module
 *
 * @copyright  Copyright (c) 2018 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\Solr\Plugin;

use Magento\Eav\Model\Config as EavConfig;

class CategoryDataProviderPlugin
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    public function __construct(
        EavConfig $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    public function afterPrepareMeta(\Magento\Catalog\Model\Category\DataProvider $subject, $result)
    {
        $meta = $result;
        $meta = array_replace_recursive($meta, $this->prepareFieldsMeta(
            $this->getFieldsMap(),
            $subject->getAttributesMeta($this->eavConfig->getEntityType('catalog_category'))
        ));
        return $meta;
    }

    private function prepareFieldsMeta($fieldsMap, $fieldsMeta)
    {
        $result = [];
        foreach ($fieldsMap as $fieldSet => $fields) {
            foreach ($fields as $field) {
                if (isset($fieldsMeta[$field])) {
                    $result[$fieldSet]['children'][$field]['arguments']['data']['config'] = $fieldsMeta[$field];
                }
            }
        }
        return $result;
    }

    private function getFieldsMap()
    {
        return [
            'solr' => [
                'solr_exclude',
                'solr_exclude_children',
                'solr_remove_filters',
                'solr_boost',
            ]
        ];
    }
}
