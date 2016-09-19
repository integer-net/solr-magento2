<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\ResourceModel;

use IntegerNet\Solr\Indexer\Data\ProductAssociation;
use IntegerNet\Solr\Model\Data\ArrayCollection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context as ResourceContext;

class ConfigurableProductAssociations extends AbstractDb implements ProductAssociations
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    private $productEntityLinkField;

    public function __construct(ResourceContext $context, MetadataPool $metadataPool, $connectionName = null
    ) {
        $this->metadataPool = $metadataPool;
        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_super_link', 'link_id');
    }

    public function getAssociations($parentIds)
    {
        $select = $this->getConnection()->select()->from(
            ['l' => $this->getMainTable()],
            ['product_id', 'parent_id']
        )->join(
            ['p' => $this->getTable('catalog_product_entity')],
            'p.' . $this->getProductEntityLinkField() . ' = l.parent_id',
            []
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.entity_id = l.product_id AND e.required_options = 0',
            []
        );
        if ($parentIds !== null) {
            $select->where('p.entity_id IN (?)', $parentIds);
        }

        $childrenByParent = [];
        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $childrenByParent[$row['parent_id']][] = $row['product_id'];
        }

        return ArrayCollection::fromArray($childrenByParent)
                ->map(
                    function($childrenIds, $parentId) {
                        return new ProductAssociation($parentId, $childrenIds);
                    },
                    ArrayCollection::FLAG_MAINTAIN_NUMERIC_KEYS
                )->getArrayCopy();
    }

    /**
     * Get product entity link field
     *
     * @return string
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->metadataPool
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }
}