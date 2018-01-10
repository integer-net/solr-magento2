<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Plugin;

use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\InputException;

/**
 * This plugin fixes issue with filter groups in Magento\Eav\Model\AttributeRepository::getList()
 *
 * @see https://github.com/magento/magento2/issues/4287
 */
class AttributeFilterFixer
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    private $attributeCollectionFactory;
    /**
     * @var \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    private $joinProcessor;

    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
    ) {
        $this->eavConfig = $eavConfig;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->joinProcessor = $joinProcessor;
    }

    public function aroundGetList(AttributeRepository $subject, \Closure $proceed, $entityTypeCode, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        if (!$entityTypeCode) {
            throw InputException::requiredField('entity_type_code');
        }

        /** @var Collection $attributeCollection */
        $attributeCollection = $this->attributeCollectionFactory->create();
        $this->joinProcessor->process($attributeCollection);
        $attributeCollection->addFieldToFilter('entity_type_code', ['eq' => $entityTypeCode]);
        $attributeCollection->join(
            ['entity_type' => $attributeCollection->getTable('eav_entity_type')],
            'main_table.entity_type_id = entity_type.entity_type_id',
            []
        );
        $attributeCollection->joinLeft(
            ['eav_entity_attribute' => $attributeCollection->getTable('eav_entity_attribute')],
            'main_table.attribute_id = eav_entity_attribute.attribute_id',
            []
        );
        $entityType = $this->eavConfig->getEntityType($entityTypeCode);

        $additionalTable = $entityType->getAdditionalAttributeTable();
        if ($additionalTable) {
            $attributeCollection->join(
                ['additional_table' => $attributeCollection->getTable($additionalTable)],
                'main_table.attribute_id = additional_table.attribute_id',
                []
            );
        }
        //Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $attributeCollection);
        }
        /** @var SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $attributeCollection->addOrder(
                $sortOrder->getField(),
                ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }

        $totalCount = $attributeCollection->getSize();

        // Group attributes by id to prevent duplicates with different attribute sets
        $attributeCollection->addAttributeGrouping();
        $attributeCollection->setCurPage($searchCriteria->getCurrentPage());
        $attributeCollection->setPageSize($searchCriteria->getPageSize());

        $attributes = [];
        /** @var \Magento\Eav\Api\Data\AttributeInterface $attribute */
        foreach ($attributeCollection as $attribute) {
            $attributes[] = $subject->get($entityTypeCode, $attribute->getAttributeCode());
        }
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($attributes);
        $searchResults->setTotalCount($totalCount);
        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * This is how AttributeRepository::addFilterGroupToCollection() should look like
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    private function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        Collection $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $conditionType = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $field = $filter->getField();
            // Prevent ambiguity during filtration
            if ($field == \Magento\Eav\Api\Data\AttributeInterface::ATTRIBUTE_ID) {
                $field = 'main_table.' . $field;
            }
            $fields[] = $field;
            $conditions[] = [$conditionType => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }
}