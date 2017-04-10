<?php
namespace IntegerNet\Solr\TestUtil\Traits;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SearchResultsInterface;

trait SearchResultsMock
{
    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * @param  string                                   $className
     * @return \PHPUnit_Framework_MockObject_MockBuilder
     * @since  Method available since Release 3.5.0
     */
    abstract public function getMockBuilder($className);

    /**
     * @param $items
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockSearchResults($items)
    {
        $attributeSearchResultStub = $this->getMockBuilder(SearchResultsInterface::class)->getMockForAbstractClass();
        $attributeSearchResultStub->method('getItems')
            ->willReturn($items);
        return $attributeSearchResultStub;
    }

}