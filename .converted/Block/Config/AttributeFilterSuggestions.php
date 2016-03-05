<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Block\Config\AttributeFilterSuggestions extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    protected $_itemRenderer = null;

    public function __construct(\Magento\Framework\View\Layout $viewLayout)
    {
        $this->_viewLayout = $viewLayout;

        $this->addColumn('attribute_code', [
            'label' => __('Attribute'),
            'style' => 'width:120px',
            'renderer' => $this->_getRenderer(),
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
        parent::__construct();
    }

    /**
     * @return Integer\Net\Solr\Block\Config\Adminhtml\Form\Field\Attribute
     */
    protected function  _getRenderer() {
        if (!$this->_itemRenderer) {
            $this->_itemRenderer = $this->_viewLayout->createBlock(
                'integernet_solr/config_adminhtml_form_field_attribute', '',
                ['is_render_to_js_template' => true]
            );
        }
        return $this->_itemRenderer;
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getRenderer()->calcOptionHash($row->getData('attribute_code')),
            'selected="selected"'
        );
    }

}
