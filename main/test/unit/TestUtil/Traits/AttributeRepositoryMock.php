<?php
namespace IntegerNet\Solr\TestUtil\Traits;

use Magento\Framework\Api\SearchCriteria;

trait AttributeRepositoryMock
{
    use SearchCriteriaBuilderMock;
    use SearchResultsMock;

    /**
     * @param array $dataAttributes
     * @param SearchCriteria $expectedSearchCriteria
     * @param $repositoryInterface
     * @param $attributeInterface
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockAttributeRepository(array $dataAttributes, SearchCriteria $expectedSearchCriteria, $repositoryInterface, $attributeInterface)
    {
        $attributeRepositoryStub = $this->getMockBuilder($repositoryInterface)->getMockForAbstractClass();
        $attributeStubs = [];
        foreach ($dataAttributes as $dataAttribute) {
            $attributeStubs[] = $this->mockAttribute($attributeInterface, $dataAttribute);
        }
        $attributeRepositoryStub->method('getList')
            ->with(\PHPUnit_Framework_Assert::identicalTo($expectedSearchCriteria))
            ->willReturn($this->mockSearchResults($attributeStubs));
        return $attributeRepositoryStub;
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