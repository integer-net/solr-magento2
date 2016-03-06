<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
namespace IntegerNet\Solr\Block\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Layout;

class Description extends Field
{
    /**
     * @var Layout
     */
    protected $_viewLayout;

    /**
     * @param Layout $_viewLayout
     */
    public function __construct(Layout $_viewLayout)
    {
        $this->_viewLayout = $_viewLayout;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_viewLayout
            ->createBlock(Status::class, 'integernet_solr_config_status')
            ->setTemplate('IntegerNet_Solr::config/status.phtml')
            ->toHtml();
    }

    /**
     * Enter description here...
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $id = $element->getHtmlId();

        $html = '<tr id="row_' . $id . '">'
            . '<td colspan="3">' . $this->_getElementHtml($element) . '</td>';


        $html.= '<td></td>';
        $html.= '</tr>';
        return $html;
    }
}