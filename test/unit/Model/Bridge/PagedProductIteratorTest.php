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

use IntegerNet\Solr\Implementor\ProductFactory;
use IntegerNet\Solr\Indexer\Data\ProductIdChunks;
use IntegerNet\Solr\Model\Indexer\ProductCollectionFactory;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Event\ManagerInterface;

/**
 * @covers PagedProductIterator
 */
class PagedProductIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataIterator
     * @param $storeId
     * @param $productIds
     * @param int $pageSize
     */
    public function testIterator($storeId, $productIds, $pageSize)
    {
        $expectedPageCount = (int)\ceil(\count($productIds) / $pageSize);
        $productsById = $this->getProductStubs($productIds);
        $chunks = ProductIdChunks::withAssociationsTogether($productIds, [], $pageSize);
        $iterator = new PagedProductIterator(
            $this->mockCollectionFactory($storeId, $expectedPageCount, $chunks, $productsById),
            $this->mockProductFactory($productIds),
            $chunks,
            $storeId
        );
        $iterator->setPageCallback($this->mockCallback($expectedPageCount));
        /** @var Product[] $productsFromIterator */
        $productsFromIterator = \iterator_to_array($iterator);

        $this->assertEquals(\count($productsById), \count($productsFromIterator));
        foreach ($productsFromIterator as $actualProduct) {
            $this->assertProductBridgeFor(current($productsById), $storeId, $actualProduct);
            next($productsById);
        }
    }
    public static function dataIterator()
    {
        return [
            [
                'store_id' => null,
                'product_ids' => [11],
                'page_size' => 10,
            ],
            [
                'store_id' => 1,
                'product_ids' => [],
                'page_size' => 10,
            ],
            [
                'store_id' => 2,
                'product_ids' => [11, 12, 17],
                'page_size' => 2,
            ],
        ];
    }

    /**
     * @dataProvider dataSubset
     */
    public function testSubset($storeId, $allIds, $chunkIds, $chunkSize, $subsetIds, $currentChunkId, $expectedException = null)
    {
        $expectedCollectionCreateCalls = $currentChunkId + 1;
        $iterator = new PagedProductIterator(
            $this->mockCollectionFactory(
                $storeId,
                $expectedCollectionCreateCalls,
                ProductIdChunks::withAssociationsTogether($allIds, [], $chunkSize),
                $this->getProductStubs($allIds)
            ),
            $this->mockProductFactory($expectedException ? [] : $subsetIds),
            ProductIdChunks::withAssociationsTogether($allIds, [], $chunkSize),
            $storeId
        );
        $iterator->setPageCallback($this->mockCallback($currentChunkId));
        $iterator->rewind();
        for ($i = 0; $i < $currentChunkId * $chunkSize; ++$i) {
            $iterator->next();
            $iterator->valid();
        }

        if ($expectedException) {
            $this->setExpectedExceptionRegExp($expectedException);
        }
        $subset = $iterator->subset($subsetIds);
        $this->assertInstanceOf(ProductIterator::class, $subset);
        $productsById = $this->getProductStubs($subsetIds);
        $this->assertSameSize($productsById, $subset);
        foreach ($subset as $actualProduct) {
            $this->assertProductBridgeFor(current($productsById), $storeId, $actualProduct);
            next($productsById);
        }
    }
    public static function dataSubset()
    {
        return [
            [
                'store_id' => 1,
                'all_ids' => [1, 2, 3, 5, 8, 13],
                'chunk_ids' => [1, 2, 3],
                'chunk_size' => 3,
                'subset_ids' => [1, 2],
                'current_chunk_id' => 0,
            ],
            [
                'store_id' => 1,
                'all_ids' => [1, 2, 3, 5, 8, 13],
                'chunk_ids' => [1, 2, 3],
                'chunk_size' => 3,
                'subset_ids' => [1, 3],
                'current_chunk_id' => 0,
            ],
            [
                'store_id' => 1,
                'all_ids' => [1, 2, 3, 5, 8, 13],
                'chunk_ids' => [5, 8, 13],
                'chunk_size' => 3,
                'subset_ids' => [8, 13],
                'current_chunk_id' => 1,
            ],
            [
                'store_id' => 1,
                'all_ids' => [1, 2, 3, 5, 8, 13],
                'chunk_ids' => [1, 2, 3],
                'chunk_size' => 3,
                'subset_ids' => [1, 3, 21],
                'current_chunk_id' => 0,
                'expected_exception' => \OutOfBoundsException::class,
            ],
            [
                'store_id' => 1,
                'all_ids' => [1, 2, 3, 5, 8, 13],
                'chunk_ids' => [5, 8, 13],
                'chunk_size' => 3,
                'subset_ids' => [3, 13],
                'current_chunk_id' => 1,
                'expected_exception' => \OutOfBoundsException::class,
            ],
        ];
    }
    /**
     * @param $productId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getProductStub($productId)
    {
        $productMock = $this->getMockBuilder(MagentoProduct::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $productMock->method('getId')->willReturn($productId);
        return $productMock;
    }

    /**
     * @return AttributeRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAttributeRepositoryStub()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|AttributeRepository $attributeRepository */
        $attributeRepository = $this->getMockBuilder(AttributeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $attributeRepository;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ManagerInterface
     */
    private function getEventManagerStub()
    {
        return $this->getMock(ManagerInterface::class);
    }

    /**
     * @param $productIds
     * @return ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockProductFactory($productIds)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProductFactory $productFactory */
        $productFactory = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $productFactory->expects($this->exactly(count($productIds)))
            ->method('create')
            ->willReturnCallback(function ($arguments) {
                return new Product(
                    $arguments[Product::PARAM_MAGENTO_PRODUCT],
                    $this->getAttributeRepositoryStub(),
                    $this->getEventManagerStub(),
                    $arguments[Product::PARAM_STORE_ID]);
            });
        return $productFactory;
    }

    /**
     * @param $productIds
     * @return array
     */
    private function getProductStubs($productIds)
    {
        $products = \array_map(function ($productId) {
            return $this->getProductStub($productId);
        }, $productIds);
        return \array_combine($productIds, $products);
    }

    /**
     * @param $storeId
     * @param $expectedCalls
     * @param ProductIdChunks $chunks
     * @param $products
     * @return ProductCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockCollectionFactory($storeId, $expectedCalls, ProductIdChunks $chunks, $products)
    {
        $arguments = [];
        foreach ($chunks as $chunk) {
            $arguments[] = [$storeId, $chunk->getAllIds()];
        }
        $collectionFactory = $this->getMockBuilder(ProductCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionFactory->expects($this->exactly($expectedCalls))
            ->method('create')
            ->withConsecutive(...$arguments)
            ->willReturnCallback(function($storeId, $productIds) use ($products) {
                return $this->mockCollection($storeId, $productIds, \array_intersect_key($products, \array_flip($productIds)));
            });
        return $collectionFactory;
    }

    /**
     * @param $storeId
     * @param $productIds
     * @param $products
     * @return \PHPUnit_Framework_MockObject_MockObject|Collection
     */
    private function mockCollection($storeId, $productIds, $products)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Collection $collectionStub */
        $collectionStub = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIterator', 'load', 'getSize', 'getItemById'])
            ->getMock();
        $collectionStub->method('getSize')->willReturn(count($products));
        $collectionStub->method('getIterator')
            ->willReturnCallback(function() use ($collectionStub, $productIds, $products) {
                $offset = ($collectionStub->getCurPage() - 1) * $collectionStub->getPageSize();
                $limit = $collectionStub->getPageSize();
                return new \ArrayIterator(
                    \array_combine(
                        \array_slice($productIds, $offset, $limit),
                        \array_slice($products, $offset, $limit)
                    )
                );
            });
        $collectionStub->method('getItemById')
            ->willReturnCallback(function($id) use ($products) {
                return isset($products[$id]) ? $products[$id] : null;
            });
        return $collectionStub;
    }

    /**
     * @param $expectedCallCount
     * @return \PHPUnit_Framework_MockObject_MockObject|callable
     */
    private function mockCallback($expectedCallCount)
    {
        $callbackMock = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
        $callbackMock->expects($this->exactly($expectedCallCount))
            ->method('__invoke');
        return $callbackMock;
    }

    /**
     * @param $magentoProductStub
     * @param $storeId
     * @param $actualProduct
     */
    private function assertProductBridgeFor($magentoProductStub, $storeId, $actualProduct)
    {
        $this->assertInstanceOf(Product::class, $actualProduct, 'Should be instance of product bridge');
        $this->assertEquals($magentoProductStub->getId(), $actualProduct->getId(), 'Product ID');
        $this->assertEquals($storeId, $actualProduct->getStoreId(), 'Store ID');
    }
}