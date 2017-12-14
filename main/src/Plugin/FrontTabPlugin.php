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

class FrontTabPlugin
{
    /**
     * @param ProductAttributeFrontTabBlock $subject
     * @param \Closure $proceed
     * @param Form $form
     * @return ProductAttributeFrontTabBlock
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSetForm(ProductAttributeFrontTabBlock $subject, \Closure $proceed, Form $form)
    {
        $block = $proceed($form);
        $this->replaceSearchWeightInput($form->getElement('front_fieldset'));
        return $block;
    }

    private function replaceSearchWeightInput(Form\Element\AbstractElement $container)
    {
        $container->removeField('search_weight');
        $container->addField(
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
