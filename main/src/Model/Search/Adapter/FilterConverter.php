<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Search\Adapter;

use IntegerNet\Solr\Implementor\AttributeRepository;
use IntegerNet\Solr\Indexer\IndexField;
use IntegerNet\Solr\Model\Bridge\EventDispatcher;
use IntegerNet\Solr\Query\Params\FilterQueryBuilder;
use Magento\Framework\Search\Request as MagentoRequest;
use Psr\Log\LoggerInterface;

/**
 * Configures Solr FilterQueryBuilder based on Magento filter
 *
 * @package IntegerNet\Solr\Model\Search\Adapter
 */
class FilterConverter
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(
        AttributeRepository $attributeRepository,
        LoggerInterface $logger,
        EventDispatcher $eventDispatcher
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param FilterQueryBuilder $fqBuilder
     * @param MagentoRequest\Query\Filter $filter
     * @param int $storeId
     * @throws \IntegerNet\Solr\Exception
     */
    public function configure(FilterQueryBuilder $fqBuilder, MagentoRequest\Query\Filter $filter, $storeId)
    {
        $reference = $filter->getReference();
        if ($reference instanceof MagentoRequest\Filter\Term) {
            $this->configureTermFilter($fqBuilder, $reference, $storeId);
        } elseif ($reference instanceof MagentoRequest\Filter\Range) {
            $this->configureRangeFilter($fqBuilder, $reference, $storeId);
        } else {
            $this->log(sprintf('Unknown filter reference %s (type: %s)', get_class($reference), $filter->getReferenceType()));
        }
    }
    private function configureTermFilter(FilterQueryBuilder $fqBuilder, MagentoRequest\Filter\Term $term, $storeId)
    {
        if ($term->getField() === 'visibility') {
            return;
        }
        if ($term->getField() === 'category_ids') {
            $fqBuilder->addCategoryFilter($term->getValue());
        } else {
            $fqBuilder->addAttributeFilter(
                $this->attributeRepository->getAttributeByCode(
                    $term->getField(),
                    $storeId
                ),
                $term->getValue()
            );
        }
    }
    private function configureRangeFilter(FilterQueryBuilder $fqBuilder, MagentoRequest\Filter\Range $range, $storeId)
    {
        if ($range->getField() === 'price') {
            $fqBuilder->addPriceRangeFilterByMinMax(
                $range->getFrom(),
                $range->getTo()
            );
        } else {
            $indexField = new IndexField(
                $this->attributeRepository->getAttributeByCode($range->getField(), $storeId),
                $this->eventDispatcher
            );
            $fqBuilder->addRangeFilterByMinMax(
                $indexField->getFieldName(),
                $range->getFrom(),
                $range->getTo()
            );
        }
    }

    private function log($message)
    {
        $this->logger->notice(sprintf('[SOLR] %s (see %s)', $message, __CLASS__));
    }
}