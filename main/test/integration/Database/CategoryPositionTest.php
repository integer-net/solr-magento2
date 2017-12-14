<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Database;

use IntegerNet\Solr\Model\ResourceModel\CategoryPosition;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class CategoryPositionTest extends TestCase
{
    /**
     * @var CategoryPosition
     */
    protected $categoryPosition;
    /** @var  ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->categoryPosition = $this->objectManager->get(CategoryPosition::class);
    }

    /**
     * @dataProvider dataCategoryPositions
     * @magentoDataFixture loadFixture
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @param $productId
     * @param $storeId
     * @param $expectedPositions
     */
    public function testGetCategoryPositions($productId, $storeId, $expectedPositions)
    {
        $positions = $this->categoryPosition->getCategoryPositions($productId, $storeId);
        $this->assertEquals($expectedPositions, $positions);
    }
    public static function dataCategoryPositions()
    {
        return [
            [
                'product_id' => 333,
                'store_id' => 1,
                'expected_positions' => [
                    ['category_id' => '2', 'position' => '1'],
                    ['category_id' => '333', 'position' => '10'],
                ]
            ]
        ];
    }
    public static function loadFixture()
    {
        include __DIR__ . '/../_files/products.php';
    }
}