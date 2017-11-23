<?php
/**
 * integer_net Magento Module
 *
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Plugin;

use Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front as ProductAttributeFrontTabBlock;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Fieldset;

class FrontTabPlugin
{
    /**
     * Change Search Weight field to text input
     *
     * @param ProductAttributeFrontTabBlock $subject
     * @param Form $form
     * @return void
     */
    public function beforeSetForm(ProductAttributeFrontTabBlock $subject, Form $form)
    {
        /** @var Fieldset $fieldset */
        $fieldset = $form->getElement('front_fieldset');

        $fieldset->removeField('search_weight');
        $fieldset->addField(
            'search_weight',
            'text',
            [
                'name'  => 'search_weight',
                'label' => __('Search Weight'),
                'class' => 'validate-number',
            ],
            'is_searchable'
        );
    }
}
