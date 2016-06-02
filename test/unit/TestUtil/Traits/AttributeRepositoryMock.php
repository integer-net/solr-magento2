<?php
namespace IntegerNet\Solr\TestUtil\Traits;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;

trait AttributeRepositoryMock
{
    /**
     * Returns a mock object for the specified abstract class with all abstract
     * methods of the class mocked. Concrete methods to mock can be specified with
     * the last parameter
     *
     * @param  string                                  $originalClassName
     * @param  array                                   $arguments
     * @param  string                                  $mockClassName
     * @param  boolean                                 $callOriginalConstructor
     * @param  boolean                                 $callOriginalClone
     * @param  boolean                                 $callAutoload
     * @param  array                                   $mockedMethods
     * @param  boolean                                 $cloneArguments
     * @return \PHPUnit_Framework_MockObject_MockObject
     * @since  Method available since Release 3.4.0
     * @throws \PHPUnit_Framework_Exception
     */
    abstract public function getMockForAbstractClass($originalClassName, array $arguments = array(), $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = array(), $cloneArguments = false);

    /**
     * Returns a builder object to create mock objects using a fluent interface.
     *
     * @param  string                                   $className
     * @return \PHPUnit_Framework_MockObject_MockBuilder
     * @since  Method available since Release 3.5.0
     */
    abstract public function getMockBuilder($className);

    /**
     * @param array $dataAttributes
     * @param SearchCriteria $expectedSearchCriteria
     * @param $repositoryInterface
     * @param $attributeInterface
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockAttributeRepository(array $dataAttributes, SearchCriteria $expectedSearchCriteria, $repositoryInterface, $attributeInterface)
    {
        $attributeRepositoryStub = $this->getMockForAbstractClass($repositoryInterface);
        $attributeStubs = [];
        foreach ($dataAttributes as $dataAttribute) {
            $attributeStubs[] = $this->mockAttribute($attributeInterface, $dataAttribute, $attributeStubs);
        }
        $attributeSearchResultStub = $this->getMockForAbstractClass(SearchResultsInterface::class);
        $attributeSearchResultStub->method('getItems')
            ->willReturn($attributeStubs);
        $attributeRepositoryStub->method('getList')
            ->with(\PHPUnit_Framework_Assert::identicalTo($expectedSearchCriteria))
            ->willReturn($attributeSearchResultStub);
        return $attributeRepositoryStub;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
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
     * @param $attributeInterface
     * @param $dataAttribute
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockAttribute($attributeInterface, $dataAttribute)
    {
        $attributeStub = $this->getMockBuilder($attributeInterface)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultFrontendLabel', 'getAttributeCode'])
            ->getMockForAbstractClass();
        $attributeStub->method('getDefaultFrontendLabel')
            ->willReturn($dataAttribute['frontend_label']);
        $attributeStub->method('getAttributeCode')
            ->willReturn($dataAttribute['attribute_code']);
        return $attributeStub;
    }
}