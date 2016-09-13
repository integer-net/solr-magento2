<?php
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Implementor\PagedProductIteratorFactory;
use IntegerNet\Solr\Implementor\ProductFactory;
use IntegerNet\Solr\Implementor\ProductIteratorFactory;
use Magento\Catalog\Api\ProductRepositoryInterface as MagentoProductRepository;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatus;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory as StockStatusFactory;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\Framework\Event\ManagerInterface;

/**
 * @covers ProductRepository
 */
class ProductRepositoryTest extends \PHPUnit_Framework_TestCase
{
    private $productCollectionFactory;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StockStatus
     */
    private $stockStatusMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProductCollection\
     */
    private $collectionMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AttributeRepository
     */
    private $attributeRepositoryStub;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Config
     */
    private $catalogConfigStub;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LinkManagementInterface
     */
    private $linkManagementMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProductIteratorFactory
     */
    private $productIteratorFactoryMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PagedProductIteratorFactory
     */
    private $pagedProductIteratorFactoryMock;
    /**
     * @var ProductRepository
     */
    private $productRepository;

    protected function setUp()
    {
        $this->linkManagementMock = $this->getMockBuilder(LinkManagementInterface::class)
            ->setMethods(['getChildren'])
            ->getMockForAbstractClass();
        $this->productIteratorFactoryMock = $this->stubProductIteratorFactory();
        $this->pagedProductIteratorFactoryMock = $this->mockPagedProductIteratorFactory();
        $this->attributeRepositoryStub = $this->getMockBuilder(AttributeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogConfigStub = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productRepository = new ProductRepository(
            $this->linkManagementMock,
            $this->productIteratorFactoryMock,
            $this->pagedProductIteratorFactoryMock
        );
        $this->productCollectionFactory = new \IntegerNet\Solr\Model\Indexer\ProductCollectionFactory(
            $this->stubProductCollectionFactory(),
            $this->catalogConfigStub,
            $this->attributeRepositoryStub,
            $this->stubStockStatusFactory()
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
        $this->catalogConfigStub->method('getProductAttributes')->willReturn(['name', 'description']);
        $this->attributeRepositoryStub->method('getAttributeCodesToIndex')->willReturn(['meta_description']);
        $this->collectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with(['name', 'description', 'visibility', 'status', 'url_key', 'solr_boost', 'solr_exclude', 'meta_description']);
        $this->collectionMock->method('getItems')
            ->willReturn($productsInSearchResult);
        $this->collectionMock->method('getIterator')
            ->willReturn(new \ArrayIterator($productsInSearchResult));

        $actualResult = $this->productRepository->getProductsForIndex($storeId, $productIds);
        $actualResult->setPageCallback($this->mockCallback(1));

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
    protected function stubProductIteratorFactory()
    {
        $productIteratorFactoryStub = $this->getMockBuilder(ProductIteratorFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $productIteratorFactoryStub->method('create')->willReturnCallback(function($data) {
            return new ProductIterator(
                $this->mockProductFactory(),
                $data[ProductIterator::PARAM_MAGENTO_PRODUCTS],
                $data[ProductIterator::PARAM_STORE_ID]);
        });
        return $productIteratorFactoryStub;
    }

    protected function mockPagedProductIteratorFactory()
    {
        $productIteratorFactoryMock = $this->getMockBuilder(PagedProductIteratorFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $productIteratorFactoryMock->method('create')->willReturnCallback(function($data) {
            return new PagedProductIterator(
                $this->productCollectionFactory,
                $this->mockProductFactory(),
                $data[PagedProductIterator::PARAM_PRODUCT_ID_FILTER],
                $data[PagedProductIterator::PARAM_PAGE_SIZE],
                $data[PagedProductIterator::PARAM_STORE_ID]);
        });
        return $productIteratorFactoryMock;
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
        $productsFromIterator = \iterator_to_array($actualResult);
        $this->assertCount(\count($expectedProducts), $productsFromIterator);
        foreach ($productsFromIterator as $actualProduct) {
            /** @var Product $actualProduct */
            $this->assertInstanceOf(Product::class, $actualProduct);
            $this->assertEquals(\array_shift($expectedProducts)->getId(), $actualProduct->getId(), 'product id');
            $this->assertEquals($expectedStoreId, $actualProduct->getStoreId(), 'store id');
        }
    }

    private function stubProductCollectionFactory()
    {
        $this->collectionMock = $this->getMockBuilder(ProductCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollectionFactoryStub = $this->getMockBuilder(ProductCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $productCollectionFactoryStub->method('create')->willReturn($this->collectionMock);
        return $productCollectionFactoryStub;
    }

    /**
     * @param $callbackCount
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockCallback($callbackCount)
    {
        $callbackMock = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $callbackMock->expects($this->exactly($callbackCount))->method('__invoke');
        return $callbackMock;
    }

    private function stubStockStatusFactory()
    {
        $this->stockStatusMock = $this->getMockBuilder(StockStatus::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stockStatusFactoryStub = $this->getMockBuilder(StockStatusFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $stockStatusFactoryStub->method('create')->willReturn($this->stockStatusMock);
        return $stockStatusFactoryStub;
    }

}