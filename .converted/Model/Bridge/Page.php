<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\SolrCms\Implementor\Page;

class Integer\Net\Solr\Model\Bridge\Page implements Page
{
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_page;

    /**
     * @param \Magento\Cms\Model\Page $_page
     */
    public function __construct(\Magento\Cms\Model\Page $_page, \Magento\Framework\Event\ManagerInterface $eventManagerInterface)
    {
        $this->_eventManagerInterface = $eventManagerInterface;

        $this->_page = $_page;
    }

    /**
     * @return \Magento\Cms\Model\Page
     */
    public function getMagentoPage()
    {
        return $this->_page;
    }


    public function getId()
    {
        return $this->_page->getId();
    }

    public function getStoreId()
    {
        return $this->_page->getStoreId();
    }

    public function getSolrBoost()
    {
        $this->_page->getData('solr_boost');
    }
    
    public function getTitle()
    {
        return $this->_page->getData('title');
    }
    
    public function getContent()
    {
        return $this->_page->getData('content');
    }

    /**
     * @return int
     */
    public function getSolrId()
    {
        return 'page_' . $this->getId() . '_' . $this->getStoreId();
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isIndexable($storeId)
    {
        $this->_eventManagerInterface->dispatch('integernet_solr_can_index_page', ['page' => $this->_page]);

        if ($this->_page->getSolrExclude()) {
            return false;
        }
        
        if (!$this->_page->getIsActive()) {
            return false;
        }
        
        return true;
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     * @deprecated only use interface methods!
     */
    public function __call($method, $args)
    {
        return call_user_func_arrayfunc([$this->_page, $method], $args);
    }
}