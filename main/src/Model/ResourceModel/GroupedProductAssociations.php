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
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context as ResourceContext;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link as ProductLinkResource;

class GroupedProductAssociations extends AbstractDb implements ProductAssociations
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
        $this->_init('catalog_product_link', 'link_id');
    }

    /**
     * Returns product associations for given parent ids
     *
     * @param int[]|null $parentIds
     * @return ProductAssociation[] An array with parent id as keys and association data as values
     */
    public function getAssociations($parentIds)
    {
        $connection = $this->getConnection();
        $bind = [':link_type_id' => ProductLinkResource::LINK_TYPE_GROUPED];
        $select = $connection->select()->from(
            ['l' => $this->getMainTable()],
            ['product_id', 'linked_product_id']
        )->join(
            ['cpe' => $this->getTable('catalog_product_entity')],
            sprintf(
                'cpe.%s = l.product_id',
                $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()
            )
        )->where(
            'link_type_id = :link_type_id'
        );
        if ($parentIds !== null) {
            $select->where('cpe.entity_id IN (?)', $parentIds);
        }

        $select->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.entity_id = l.linked_product_id AND e.required_options = 0',
            []
        );

        $childrenIdsByParentId = [];
        $result = $connection->fetchAll($select, $bind);
        foreach ($result as $row) {
            $childrenIdsByParentId[$row['product_id']][] = $row['linked_product_id'];
        }

        return ArrayCollection::fromArray($childrenIdsByParentId)
            ->map(
                function($childrenIds, $parentId) {
                    return new ProductAssociation($parentId, $childrenIds);
                },
                ArrayCollection::FLAG_MAINTAIN_NUMERIC_KEYS
            )->getArrayCopy();
    }

}