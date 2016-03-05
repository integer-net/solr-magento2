<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Block\Result\Layer extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @return Integer\Net\Solr\Block\Result\Layer\State
     */
    public function getState()
    {
        if ($this->_helperData->isCategoryPage()) {
            if ($block = $this->_viewLayoutFactory->create()->getBlock('catalog.solr.layer.state')) {
                return $block;
            }

            $block = $this->_viewLayoutFactory->create()->createBlock('IntegerNet\Solr\Block\Result\Layer\State')
                ->setTemplate('IntegerNet_Solr::integernet/solr/layer/top/state.phtml')
                ->setLayer($this);

            return $block;
        }

        if ($block = $this->_viewLayoutFactory->create()->getBlock('catalogsearch.solr.layer.state')) {
            return $block;
        }

        $block = $this->_viewLayoutFactory->create()->createBlock('IntegerNet\Solr\Block\Result\Layer\State')
            ->setTemplate('IntegerNet_Solr::integernet/solr/layer/top/state.phtml')
            ->setLayer($this);

        return $block;
    }
}