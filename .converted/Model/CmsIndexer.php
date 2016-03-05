<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

use IntegerNet\Solr\Exception;
use IntegerNet\SolrCms\Indexer\PageIndexer;

/**
 * Class Integer\Net\Solr\Model\CmsIndexer
 */
class Integer\Net\Solr\Model\CmsIndexer
{
    /**
     * @var PageIndexer
     */
    protected $_pageIndexer;

    /**
     * Internal constructor not depended on params. Can be used for object initialization
     */
    public function __construct(\Integer\Net\Solr\Helper\Factory $helperFactory)
    {
        $this->_helperFactory = $helperFactory;

        $autoloader = new Integer\Net\Solr\Helper\Autoloader();
        $autoloader->createAndRegister();

        $this->_pageIndexer = $this->_helperFactory->getPageIndexer();
    }

    /**
     * Rebuild all index data
     */
    public function reindexAll()
    {
        $this->_reindexPages(null, true);
    }
    
    public function cmsPageSaveAfter(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Cms\Model\Page $page */
        $page = $observer->getObject();
        $this->_reindexPages([$page->getId()]);
    }

    /**
     * @param array|null $pageIds
     * @param boolean $emptyIndex
     */
    protected function _reindexPages($pageIds = null, $emptyIndex = false)
    {
        try {
            $this->_pageIndexer->reindex($pageIds, $emptyIndex);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param string[] $pageIds
     */
    protected function _deletePagesIndex($pageIds)
    {
        $this->_pageIndexer->deleteIndex($pageIds);
    }
}