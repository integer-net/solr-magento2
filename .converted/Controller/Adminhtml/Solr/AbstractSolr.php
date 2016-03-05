<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Controller\Adminhtml\Solr\AbstractSolr extends \Magento\Backend\App\Action
{
    public function flushAction()
    {
        $this->messageManager->addSuccess(
            __('The Solr Autosuggest Cache has been flushed and rebuilt successfully.')
        );
        $this->_helperAutosuggest->storeSolrConfig();
        $this->_redirectReferer();
    }

    protected function _isAllowed()
    {
        return true;
    }
}