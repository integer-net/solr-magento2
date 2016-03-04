<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Block\Config\Description extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_viewLayoutFactory->create()
            ->createBlock('integernet_solr/config_status', 'integernet_solr_config_status')
            ->setTemplate('IntegerNet_Solr::integernet/solr/config/status.phtml')
            ->toHtml();
    }

    /**
     * Enter description here...
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $id = $element->getHtmlId();

        $html = '<tr id="row_' . $id . '">'
            . '<td colspan="3">' . $this->_getElementHtml($element) . '</td>';


        $html.= '<td></td>';
        $html.= '</tr>';
        return $html;
    }
}