<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Database;

use Magento\TestFramework\ObjectManager;
use IntegerNet\Solr\Model\Bridge\SerializableCategoryRepository;
use IntegerNet\SolrSuggest\Implementor\SerializableCategoryRepository as SerializableCategoryRepositoryInterface;

class SerializableCategoryRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SerializableCategoryRepository
     */
    private $serializableCategoryRepository;

    protected function setUp()
    {
        $objectManager = ObjectManager::getInstance();
        $this->serializableCategoryRepository = $objectManager->create(SerializableCategoryRepositoryInterface::class);
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf(SerializableCategoryRepository::class, $this->serializableCategoryRepository);
    }

    /**
     * @magentoDataFixture loadFixture
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testFindActiveCategories()
    {
        $storeId = 1;
        $expectedCategories = [
            new \IntegerNet\SolrSuggest\Plain\Bridge\Category(
                '333',
                'Category 1',
                'http://localhost/index.php/category-1.html'
            ),
            new \IntegerNet\SolrSuggest\Plain\Bridge\Category(
                '444',
                'Category 2',
                'http://localhost/index.php/category-2.html'
            ),
        ];

        $actualCategories = $this->serializableCategoryRepository->findActiveCategories($storeId);
        $this->assertCount(count($expectedCategories), $actualCategories);
        foreach($actualCategories as $actualCategory) {
            $this->assertEquals(current($expectedCategories), $actualCategory);
            next($expectedCategories);
        }
    }

    public static function loadFixture()
    {
        include __DIR__ . '/../_files/categories.php';
    }
}
