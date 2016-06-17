<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Bridge;

use Magento\Catalog\Api\CategoryRepositoryInterface as MagentoCategoryRepository;
use Magento\Catalog\Api\Data\CategoryInterface;
use IntegerNet\Solr\Implementor\Product as ProductInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class CategoryRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MagentoCategoryRepository
     */
    private $magentoCategoryRepository;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    protected function setUp()
    {
        $this->magentoCategoryRepository = $this->getMockBuilder(MagentoCategoryRepository::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->categoryRepository = new CategoryRepository($this->magentoCategoryRepository, $this->collectionFactory, $this->storeManager);
    }

    /**
     * @dataProvider dataCategoryNames
     * @param $storeId
     * @param array $categoryNamesById
     */
    public function testCategoryNames($storeId, array $categoryNamesById)
    {
        $this->magentoCategoryRepository->method('get')
            ->with($this->anything(), $storeId)
            ->willReturnCallback(function($id) use ($categoryNamesById) {
                $categoryStub = $this->getMockBuilder(CategoryInterface::class)
                    ->setMethods(['getName'])
                    ->getMockForAbstractClass();
                $categoryStub->method('getName')->willReturn($categoryNamesById[$id]);
                return $categoryStub;
            });
        $actualNames = $this->categoryRepository->getCategoryNames(\array_keys($categoryNamesById), $storeId);
        $this->assertEquals(\array_values($categoryNamesById), $actualNames);
    }
    public static function dataCategoryNames()
    {
        return [
            [
                'store_id' => 1,
                'category_names_by_id' => [
                    3 => 'Books',
                    4 => 'Science Fiction',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataCategoryIds
     * @param array $productCategoryIds
     * @param array $categoryData
     * @param array $expectedCategoryIds
     */
    public function testCategoryIds(array $productCategoryIds, array $categoryData, array $expectedCategoryIds)
    {
        $storeId = 1;
        $rootCategoryId = 2;

        $storeStub = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getRootCategoryId'])
            ->getMockForAbstractClass();
        $storeStub->method('getRootCategoryId')->willReturn($rootCategoryId);
        $this->storeManager->method('getStore')->willReturn($storeStub);
        $this->collectionFactory->expects($this->at(0))
            ->method('create')
            ->willReturn($this->mockIdLookupCollection($productCategoryIds, $categoryData));
        $this->collectionFactory->expects($this->at(1))
            ->method('create')
            ->willReturn($this->mockExcludeLookupCollection());
        $this->collectionFactory->expects($this->at(2))
            ->method('create')
            ->willReturn($this->mockExcludeParentsLookupCollection());
        $this->collectionFactory->expects($this->at(3))
            ->method('create')
            ->willReturn($this->mockExcludeChildrenLookupCollection());

        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductInterface $product */
        $product = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['getCategoryIds'])
            ->getMockForAbstractClass();
        $product->method('getCategoryIds')->willReturn($productCategoryIds);
        $product->method('getStoreId')->willReturn($storeId);

        $actualCategoryIds = $this->categoryRepository->getCategoryIds($product);
        $this->assertEquals($expectedCategoryIds, $actualCategoryIds, '', 0.0, 10, true);

        $this->markTestIncomplete('TODO test for solr_exclude');
    }
    public static function dataCategoryIds()
    {
        $categoryData = [
            '1' => [
                'is_active' => 1,
                'include_in_menu' => 0,
                'path' => '1',
            ],
            '2' => [
                'is_active' => 1,
                'include_in_menu' => 0,
                'path' => '1/2',
            ],
            '3' => [
                'is_active' => 1,
                'include_in_menu' => 1,
                'path' => '1/2/3',
            ],
            '4' => [
                'is_active' => 1,
                'include_in_menu' => 1,
                'path' => '1/2/3/4',
            ],
            '5' => [
                'is_active' => 0,
                'include_in_menu' => 1,
                'path' => '1/2/5',
            ],
            '6' => [
                'is_active' => 1,
                'include_in_menu' => 0,
                'path' => '1/2/6',
            ],
            '7' => [
                'is_active' => 1,
                'include_in_menu' => 1,
                'path' => '1/2/6/7',
            ],
            '100' => [
                'is_active' => 1,
                'include_in_menu' => 0,
                'path' => '1/100',
            ],
            '101' => [
                'is_active' => 1,
                'include_in_menu' => 1,
                'path' => '1/100/101',
            ],
        ];
        return [
            [
                'product_category_ids' => ['3', '4'],
                'category_data' => $categoryData,
                'expected_category_ids' => ['3', '4'],
            ],
            'exclude_inactive' => [
                'product_category_ids' => ['3', '4', '5'],
                'category_data' => $categoryData,
                'expected_category_ids' => ['3', '4'],
            ],
            'include_parent' => [
                'product_category_ids' => ['4'],
                'category_data' => $categoryData,
                'expected_category_ids' => ['3', '4'],
            ],
            'include_inactive_parent' => [
                'product_category_ids' => ['4', '7'],
                'category_data' => $categoryData,
                'expected_category_ids' => ['3', '4', '6', '7'],
            ],
            'not_include_outside_store_root' => [
                'product_category_ids' => ['4', '101'],
                'category_data' => $categoryData,
                'expected_category_ids' => ['3', '4'],
            ],
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CategoryCollection
     */
    private function mockCollection()
    {
        return $this->getMockBuilder(CategoryCollection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addAttributeToSelect', 'addFieldToFilter', 'getIterator'])
            ->getMock();
    }

    /**
     * @return CategoryCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockIdLookupCollection($categoryIds, $categoryData)
    {
        $categories = \array_map(function($id) use ($categoryData) {
            $categoryStub = $this->getMockBuilder(CategoryInterface::class)
                ->setMethods(['getName', 'getIncludeInMenu', 'getPath'])
                ->getMockForAbstractClass();
            $categoryStub->method('getId')->willReturn($id);
            $categoryStub->method('getIsActive')->willReturn($categoryData[$id]['is_active']);
            $categoryStub->method('getIncludeInMenu')->willReturn($categoryData[$id]['include_in_menu']);
            $categoryStub->method('getPath')->willReturn($categoryData[$id]['path']);
            return $categoryStub;
        }, $categoryIds);

        $collection = $this->mockCollection();
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('entity_id', ['in' => $categoryIds]);
        $collection->expects($this->once())
            ->method('addAttributeToSelect')
            ->with(['is_active', 'include_in_menu']);
        $collection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($categories));
        //TODO return stubs with is_active, include_in_menu, path
        return $collection;
    }

    /**
     * @return CategoryCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockExcludeLookupCollection()
    {
        return $this->mockCollection();
    }

    /**
     * @return CategoryCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockExcludeChildrenLookupCollection()
    {
        return $this->mockCollection();
    }

    /**
     * @return CategoryCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockExcludeParentsLookupCollection()
    {
        return $this->mockCollection();
    }
}
