<?php
namespace IntegerNet\Solr\TestUtil\Traits;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

trait SearchCriteriaBuilderMock
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
     * Returns a matcher that matches when the method is executed exactly once.
     *
     * @return \PHPUnit_Framework_MockObject_Matcher_InvokedCount
     *
     * @since  Method available since Release 3.0.0
     */
    abstract public function once();

    /**
     * Returns a matcher that matches when the method is executed
     * exactly $count times.
     *
     * @param int $count
     *
     * @return \PHPUnit_Framework_MockObject_Matcher_InvokedCount
     *
     * @since  Method available since Release 3.0.0
     */
    abstract public function exactly($count);


    /**
     * Returns a matcher that matches when the method is never executed.
     *
     * @return \PHPUnit_Framework_MockObject_Matcher_InvokedCount
     *
     * @since  Method available since Release 3.0.0
     */
    abstract public function never();

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SearchCriteriaBuilder
     */
    protected function getSearchCriteriaBuilderMock()
    {
        $searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'addFilters', 'addSortOrder', 'create'])
            ->disableOriginalConstructor()
            ->getMock();
        return $searchCriteriaBuilderMock;
    }

    /**
     * @param $searchCriteriaBuilderMock SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     * @param $expectedFilters
     * @param $expectedSortOrder
     * @param SearchCriteria $searchCriteria
     * @return SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function searchCriteriaBuilderExpects(\PHPUnit_Framework_MockObject_MockObject $searchCriteriaBuilderMock, $expectedFilters, $expectedSortOrder, SearchCriteria $searchCriteria)
    {
        $searchCriteriaBuilderMock->method('create')
            ->willReturn($searchCriteria);
        if ($expectedSortOrder) {
            $searchCriteriaBuilderMock->expects($this->once())
                ->method('addSortOrder')
                ->with($expectedSortOrder);
        } else {
            $searchCriteriaBuilderMock->expects($this->never())
                ->method('addSortOrder');
        }
        $expectedAddFilter = [];
        $expectedAddFilters = [];
        foreach ($expectedFilters as $expectedFilter) {
            if ($expectedFilter instanceof FilterGroup) {
                $expectedAddFilters[] = [ $expectedFilter->getFilters() ];
            } else {
                $expectedAddFilter[] = $expectedFilter;
            }
        }
        $searchCriteriaBuilderMock->expects($this->exactly(count($expectedAddFilter)))
            ->method('addFilter')
            ->withConsecutive(...$expectedAddFilter)
            ->willReturnSelf();
        $searchCriteriaBuilderMock->expects($this->exactly(count($expectedAddFilters)))
            ->method('addFilters')
            ->withConsecutive(...$expectedAddFilters)
            ->willReturnSelf();
        return $searchCriteriaBuilderMock;
    }

    /**
     * @param $returnSearchCriteriaBuilder SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     * @return \PHPUnit_Framework_MockObject_MockObject|SearchCriteriaBuilderFactory
     */
    protected function mockSearchCriteriaBuilderFactory($returnSearchCriteriaBuilder)
    {
        $searchCriteriaBuilderFactoryMock = $this->getMockBuilder(SearchCriteriaBuilderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilderFactoryMock->method('create')->willReturn($returnSearchCriteriaBuilder);
        return $searchCriteriaBuilderFactoryMock;
    }

}