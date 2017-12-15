<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
namespace IntegerNet\Solr\Block\Config;

use IntegerNet\Solr\Block\Config\Adminhtml\Form\Field\Attribute;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\View\Layout;

class AttributeFilterSuggestions extends AbstractFieldArray
{
    private $attributeCodeRenderer = null;

    protected function _prepareToRender()
    {
        $this->addColumn('attribute_code', [
            'label' => __('Attribute'),
            'style' => 'width:120px',
            'renderer' => $this->getAttributeCodeRenderer(),
        ]);
        $this->addColumn('max_number_suggestions', [
            'label' => __('Maximum number of suggestions'),
            'style' => 'width:60px',
            'class' => 'validate-number validate-zero-or-greater',
        ]);
        $this->addColumn('sorting', [
            'label' => __('Sorting'),
            'style' => 'width:60px',
            'class' => 'validate-number validate-zero-or-greater',
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @return \IntegerNet\Solr\Block\Config\Adminhtml\Form\Field\Attribute
     */
    private function getAttributeCodeRenderer()
    {
        if (!$this->attributeCodeRenderer) {
            $this->attributeCodeRenderer = $this->getLayout()->createBlock(
                Attribute::class,
                '',
                ['is_render_to_js_template' => true]
            );
        }
        return $this->attributeCodeRenderer;
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];
        $value = $row->getData('attribute_code');
        if ($value !== null) {
            $options['option_' . $this->getAttributeCodeRenderer()->calcOptionHash($value)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

}
