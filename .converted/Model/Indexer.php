<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
use IntegerNet\Solr\Exception;
use IntegerNet\Solr\Indexer\ProductIndexer;

/**
 * Class Integer\Net\Solr\Model\Indexer
 * 
 * @todo fix URLs for comparison to not include referrer URL
 */
class Integer\Net\Solr\Model\Indexer extends \Magento\Indexer\Model\Indexer\AbstractIndexer
{
    /**
     * @var ProductIndexer
     */
    protected $_productIndexer;
    /**
     * @var string[]
     */
    protected $_matchedEntities = [
        \Magento\Catalog\Model\Product::ENTITY => [
            \Magento\Indexer\Model\Event::TYPE_SAVE,
            \Magento\Indexer\Model\Event::TYPE_DELETE,
            \Magento\Indexer\Model\Event::TYPE_MASS_ACTION,
        ],
    ];

    /**
     * Internal constructor not depended on params. Can be used for object initialization
     */
    protected function _construct()
    {
        $autoloader = new Integer\Net\Solr\Helper\Autoloader();
        $autoloader->createAndRegister();

        $this->_productIndexer = $this->_helperFactory->getProductIndexer();
    }


    public function getName()
    {
        return __('Solr Search Index');
    }

    public function getDescription()
    {
        return __('Indexing of Product Data for Solr');
    }

    /**
     * Rebuild all index data
     */
    public function reindexAll()
    {
        $this->_reindexProducts(null, true);
    }

    /**
     * @param \Magento\Indexer\Model\Event $event
     * @return $this
     */
    protected function _registerEvent(\Magento\Indexer\Model\Event $event)
    {
        if ($event->getEntity() == \Magento\Catalog\Model\Product::ENTITY) {

            $productIds = [];

            /* @var $object \Magento\Framework\DataObject */
            $object = $event->getDataObject();

            switch ($event->getType()) {
                case \Magento\Indexer\Model\Event::TYPE_SAVE:
                    $productIds[] = $object->getId();
                    break;

                case \Magento\Indexer\Model\Event::TYPE_DELETE:
                    $event->addNewData('solr_delete_product_skus', [$object->getId()]);
                    break;

                case \Magento\Indexer\Model\Event::TYPE_MASS_ACTION:
                    $productIds = $object->getProductIds();
                    break;
            }

            if (sizeof($productIds)) {
                $event->addNewData('solr_update_product_ids', $productIds);
            }

        }
        return $this;
    }

    /**
     * @param \Magento\Indexer\Model\Event $event
     */
    protected function _processEvent(\Magento\Indexer\Model\Event $event)
    {
        $data = $event->getNewData();

        if (isset($data['solr_delete_product_skus'])) {
            $productSkus = $data['solr_delete_product_skus'];
            if (is_array($productSkus) && !empty($productSkus)) {

                $this->_deleteProductsIndex($productSkus);
            }
        }

        if (isset($data['solr_update_product_ids'])) {
            $productIds = $data['solr_update_product_ids'];
            if (is_array($productIds) && !empty($productIds)) {

                $this->_reindexProducts($productIds);
            }
        }
    }

    /**
     * @param array|null $productIds
     * @param boolean $emptyIndex
     */
    protected function _reindexProducts($productIds = null, $emptyIndex = false)
    {
        try {
            $this->_productIndexer->reindex($productIds, $emptyIndex);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param string[] $productIds
     */
    protected function _deleteProductsIndex($productIds)
    {
        $this->_productIndexer->deleteIndex($productIds);
    }
}