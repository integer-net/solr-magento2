<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use IntegerNet\SolrCms\Implementor\PageIterator;
use IntegerNet\SolrCms\Implementor\Page;

/**
 * Page iterator implementation with lazy loading of multiple collections (chunking).
 * Collections are prepared to be used by the indexer.
 */
class Integer\Net\Solr\Model\Bridge\LazyPageIterator implements PageIterator, OuterIterator
{
    /**
     * @var int
     */
    protected $_storeId;
    /**
     * @var null|int[]
     */
    protected $_pageIdFilter;
    /**
     * @var int
     */
    protected $_pageSize;
    /**
     * @var int
     */
    protected $_currentPage;
    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\Collection
     */
    protected $_collection;
    /**
     * @var ArrayIterator
     */
    protected $_collectionIterator;

    /**
     * @link http://php.net/manual/en/outeriterator.getinneriterator.php
     * @return Iterator The inner iterator for the current entry.
     */
    public function getInnerIterator()
    {
        return $this->_collectionIterator;
    }


    /**
     * @param int $_storeId store id for the collections
     * @param int[]|null $_pageIdFilter array of page ids to be loaded, or null for all page ids
     * @param int $_pageSize Number of pages per loaded collection (chunk)
     */
    public function __construct($_storeId, $_pageIdFilter, $_pageSize, \Magento\Cms\Model\ResourceModel\Page\Collection $pageCollection, 
        \Magento\Framework\Event\ManagerInterface $eventManagerInterface)
    {
        $this->_pageCollection = $pageCollection;
        $this->_eventManagerInterface = $eventManagerInterface;

        $this->_storeId = $_storeId;
        $this->_pageIdFilter = $_pageIdFilter;
        $this->_pageSize = $_pageSize;
    }

    /**
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->getInnerIterator()->next();
    }

    /**
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->getInnerIterator()->key();
    }

    /**
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        if ($this->getInnerIterator()->valid()) {
            return true;
        } elseif ($this->_currentPage < $this->_collection->getLastPageNumber()) {
            $this->_currentPage++;
            $this->_collection = self::getPageCollection($this->_storeId, $this->_pageIdFilter, $this->_pageSize, $this->_currentPage);
            $this->_collectionIterator = $this->_collection->getIterator();
            $this->getInnerIterator()->rewind();
            return $this->getInnerIterator()->valid();
        }
        return false;
    }

    /**
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->_currentPage = 1;
        $this->_collection = self::getPageCollection($this->_storeId, $this->_pageIdFilter, $this->_pageSize, $this->_currentPage);
        $this->_collectionIterator = $this->_collection->getIterator();
        $this->_collectionIterator->rewind();
    }

    /**
     * @return Page
     */
    public function current()
    {
        $page = $this->getInnerIterator()->current();
        $page->setStoreId($this->_storeId);
        return new Integer\Net\Solr\Model\Bridge\Page($page);
    }

    /**
     * @param int $storeId
     * @param int[]|null $pageIds
     * @param int $pageSize
     * @param int $pageNumber
     * @return \Magento\Cms\Model\ResourceModel\Page\Collection
     */
    private static function getPageCollection($storeId, $pageIds = null, $pageSize = null, $pageNumber = 0)
    {
        /** @var $pageCollection \Magento\Cms\Model\ResourceModel\Page\Collection */
        $pageCollection = $this->_pageCollection
            ->addStoreFilter($storeId);

        if (is_array($pageIds)) {
            $pageCollection->addFieldToFilter('page_id', ['in' => $pageIds]);
        }

        if (!is_null($pageSize)) {
            $pageCollection->setPageSize($pageSize);
            $pageCollection->setCurPage($pageNumber);
        }

        $this->_eventManagerInterface->dispatch('integernet_solr_page_collection_load_before', [
            'collection' => $pageCollection
        ]);

        $event = new \Magento\Framework\Event();
        $event->setCollection($pageCollection);
        $observer = new \Magento\Framework\Event\Observer();
        $observer->setEvent($event);

        $pageCollection->load();

        $this->_eventManagerInterface->dispatch('integernet_solr_page_collection_load_after', [
            'collection' => $pageCollection
        ]);

        return $pageCollection;
    }
}
