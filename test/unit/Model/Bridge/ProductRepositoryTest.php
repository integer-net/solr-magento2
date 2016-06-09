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
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
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

    protected function setUp()
    {
        $this->magentoProductRepositoryMock = $this->getMockBuilder(MagentoProductRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMockForAbstractClass();
    }
    protected function tearDown()
    {
        $this->magentoProductRepositoryMock = null;
    }

    public function testProductsForIndex()
    {
        $storeId = 1;
        $productIds = [11, 12];
        $products = [
            $this->mockProduct(11),
            $this->mockProduct(12),
        ];
        $searchCriteriaDummy = new SearchCriteria();
        $this->magentoProductRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->identicalTo($searchCriteriaDummy))
            ->willReturn($this->mockSearchResults($products));
        $productRepository = new ProductRepository(
            $this->magentoProductRepositoryMock,
            $this->mockProductIteratorFactory(),
            new ProductSearchCriteriaBuilder($this->mockSearchCriteriaBuilderFactory($this->mockSearchCriteriaBuilder(
                [['store_id', $storeId], ['entity_id', $productIds, 'in']], null, $searchCriteriaDummy)))
        );
        $actualResult = $productRepository->getProductsForIndex($storeId, $productIds);
        $this->assertInstanceOf(ProductIterator::class, $actualResult);
        $productsFromIterator = \iterator_to_array($actualResult);
        $this->assertCount(\count($products), $productsFromIterator);
        foreach ($productsFromIterator as $actualProduct) {
            $this->assertInstanceOf(Product::class, $actualProduct);
            $this->assertEquals(\array_shift($products)->getId(), $actualProduct->getId());
        }
    }

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
    protected function mockProductFactory()
    {
        $searchCriteriaBuilderFactoryMock = $this->getMockBuilder(ProductFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilderFactoryMock->method('create')->willReturnCallback(function($data) {
            return new Product(
                $data[Product::PARAM_MAGENTO_PRODUCT],
                $this->getMockBuilder(AttributeRepository::class)->disableOriginalConstructor()->getMock(),
                $this->getMockBuilder(ManagerInterface::class)->getMockForAbstractClass(),
                $data[Product::PARAM_STORE_ID]
            );
        });
        return $searchCriteriaBuilderFactoryMock;
    }

    /**
     * @param $productId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockProduct($productId)
    {
        $productMock = $this->getMockBuilder(MagentoProduct::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $productMock->method('getId')->willReturn($productId);
        return $productMock;
    }
}