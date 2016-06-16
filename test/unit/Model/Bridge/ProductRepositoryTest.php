<?php
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Implementor\ProductFactory;
use IntegerNet\Solr\Implementor\ProductIteratorFactory;
use IntegerNet\Solr\Model\SearchCriteria\ProductSearchCriteriaBuilder;
use IntegerNet\Solr\TestUtil\Traits\SearchCriteriaBuilderMock;
use IntegerNet\Solr\TestUtil\Traits\SearchResultsMock;
use Magento\Catalog\Api\ProductRepositoryInterface as MagentoProductRepository;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ManagerInterface;

/**
 * @covers ProductRepository
 */
class ProductRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use SearchCriteriaBuilderMock;
    use SearchResultsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MagentoProductRepository
     */
    private $magentoProductRepositoryMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LinkManagementInterface
     */
    private $linkManagementMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SearchCriteriaBuilder
     */
    private $searchCriteriaBuilderMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProductIteratorFactory
     */
    private $productIteratorFactoryMock;
    /**
     * @var ProductSearchCriteriaBuilder
     */
    private $productSearchCriteriaBuilder;
    /**
     * @var ProductRepository
     */
    private $productRepository;

    protected function setUp()
    {
        $this->magentoProductRepositoryMock = $this->getMockBuilder(MagentoProductRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMockForAbstractClass();
        $this->linkManagementMock = $this->getMockBuilder(LinkManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getChildren'])
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock = $this->getSearchCriteriaBuilderMock();
        $this->productSearchCriteriaBuilder = new ProductSearchCriteriaBuilder($this->mockSearchCriteriaBuilderFactory($this->searchCriteriaBuilderMock));
        $this->productIteratorFactoryMock = $this->mockProductIteratorFactory();

        $this->productRepository = new ProductRepository(
            $this->magentoProductRepositoryMock,
            $this->linkManagementMock,
            $this->productIteratorFactoryMock,
            $this->productSearchCriteriaBuilder
        );

    }
    protected function tearDown()
    {
    }

    /**
     * @dataProvider dataProductsForIndex
     * @param $storeId
     * @param $productIds
     */
    public function testProductsForIndex($storeId, $productIds)
    {
        $productsInSearchResult = $this->mockProducts($productIds);
        $searchCriteriaDummy = new SearchCriteria();
        $this->magentoProductRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->identicalTo($searchCriteriaDummy))
            ->willReturn($this->mockSearchResults($productsInSearchResult));
        $this->searchCriteriaBuilderExpects(
            $this->searchCriteriaBuilderMock,
            [['store_id', $storeId], ['entity_id', $productIds, 'in']], null, $searchCriteriaDummy);

        $actualResult = $this->productRepository->getProductsForIndex($storeId, $productIds);

        $this->assertIteratorContainsProducts($actualResult, $productsInSearchResult, $storeId);
    }

    public static function dataProductsForIndex()
    {
        return [
            ['store_id' => 1, 'product_id' => [11, 12]]
        ];
    }

    /**
     * @dataProvider dataChildProducts
     * @param $storeId
     * @param $parentSku
     */
    public function testChildProducts($parentId, $storeId, $parentSku)
    {
        $magentoProductStub = $this->mockProduct($parentId);
        $magentoProductStub->method('getSku')->willReturn($parentSku);
        $magentoProductStub->method('getStoreId')->willReturn($storeId);
        $product = $this->mockProductFactory()->create([
            Product::PARAM_STORE_ID => $storeId,
            Product::PARAM_MAGENTO_PRODUCT => $magentoProductStub,
        ]);

        $productsInSearchResult = $this->mockProducts([100, 101, 103]);
        $this->linkManagementMock->expects($this->once())
            ->method('getChildren')
            ->with($parentSku)
            ->willReturn($productsInSearchResult);
        $actualResult = $this->productRepository->getChildProducts($product);
        $this->assertIteratorContainsProducts($actualResult, $productsInSearchResult, $storeId);
    }

    public static function dataChildProducts()
    {
        return [
            ['parent_id' => 1, 'store_id' => 1, 'parent_sku' => 'the_parent']
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ProductIteratorFactory
     */
    protected function mockProductIteratorFactory()
    {
        $searchCriteriaBuilderFactoryMock = $this->getMockBuilder(ProductIteratorFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilderFactoryMock->method('create')->willReturnCallback(function($data) {
            return new ProductIterator(
                $this->mockProductFactory(),
                $data[ProductIterator::PARAM_MAGENTO_PRODUCTS],
                $data[ProductIterator::PARAM_STORE_ID]);
        });
        return $searchCriteriaBuilderFactoryMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ProductFactory
     */
    protected function mockProductFactory()
    {
        $searchCriteriaBuilderFactoryMock = $this->getMockBuilder(ProductFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilderFactoryMock->method('create')->willReturnCallback(function($data) {
            /** @var \PHPUnit_Framework_MockObject_MockObject|AttributeRepository $attributeRepositoryMock */
            $attributeRepositoryMock = $this->getMockBuilder(AttributeRepository::class)->disableOriginalConstructor()->getMock();
            /** @var \PHPUnit_Framework_MockObject_MockObject|ManagerInterface $linkManagerMock */
            $linkManagerMock = $this->getMockBuilder(ManagerInterface::class)->getMockForAbstractClass();
            return new Product(
                $data[Product::PARAM_MAGENTO_PRODUCT],
                $attributeRepositoryMock,
                $linkManagerMock,
                $data[Product::PARAM_STORE_ID]
            );
        });
        return $searchCriteriaBuilderFactoryMock;
    }

    /**
     * @param $productId
     * @return \PHPUnit_Framework_MockObject_MockObject|MagentoProduct
     */
    protected function mockProduct($productId)
    {
        $productMock = $this->getMockBuilder(MagentoProduct::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getSku', 'getStoreId'])
            ->getMock();
        $productMock->method('getId')->willReturn($productId);
        return $productMock;
    }

    /**
     * @param $productIds
     * @return \PHPUnit_Framework_MockObject_MockObject[]|MagentoProduct[]
     */
    protected function mockProducts($productIds)
    {
        return \array_map(function ($productId) {
            return $this->mockProduct($productId);
        }, $productIds);
    }

    /**
     * @param \Iterator $actualResult
     * @param \PHPUnit_Framework_MockObject_MockObject[]|MagentoProduct[] $expectedProducts
     * @param $expectedStoreId
     */
    private function assertIteratorContainsProducts($actualResult, $expectedProducts, $expectedStoreId)
    {
        $this->assertInstanceOf(ProductIterator::class, $actualResult);
        $productsFromIterator = \iterator_to_array($actualResult);
        $this->assertCount(\count($expectedProducts), $productsFromIterator);
        foreach ($productsFromIterator as $actualProduct) {
            /** @var Product $actualProduct */
            $this->assertInstanceOf(Product::class, $actualProduct);
            $this->assertEquals(\array_shift($expectedProducts)->getId(), $actualProduct->getId(), 'product id');
            $this->assertEquals($expectedStoreId, $actualProduct->getStoreId(), 'store id');
        }
    }
}