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

use IntegerNet\Solr\Model\Bridge\SerializableCategoryRepository;
use IntegerNet\SolrSuggest\Implementor\SerializableCategoryRepository as SerializableCategoryRepositoryInterface;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\ObjectManager;

class SerializableCategoryRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;
    /**
     * @var SerializableCategoryRepository
     */
    private $serializableCategoryRepository;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->serializableCategoryRepository = $this->objectManager->create(SerializableCategoryRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture loadFixture
     * @magentoAppArea adminhtml
     * @magentoConfigFixture admin_store web/unsecure/base_link_url http://admin.example.com/
     * @magentoConfigFixture current_store web/unsecure/base_link_url http://frontend.example.com/
     * @magentoConfigFixture fixture_second_store_store web/unsecure/base_link_url http://frontend2.example.com/
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @dataProvider dataFindActiveCategories
     */
    public function testFindActiveCategories($storeId)
    {
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->create(StoreManagerInterface::class);
        $baseUrl = $storeManager->getStore($storeId)->getBaseUrl();
        $expectedCategories = [
            new \IntegerNet\SolrSuggest\Plain\Bridge\Category(
                '333',
                'Category 1',
                $baseUrl .'category-1.html'
            ),
            new \IntegerNet\SolrSuggest\Plain\Bridge\Category(
                '444',
                'Category 2',
                $baseUrl .'category-2.html'
            ),
        ];

        $actualCategories = $this->serializableCategoryRepository->findActiveCategories($storeId);
        $this->assertCount(count($expectedCategories), $actualCategories);
        foreach($actualCategories as $actualCategory) {
            $this->assertEquals(current($expectedCategories), $actualCategory);
            next($expectedCategories);
        }
    }
    public static function dataFindActiveCategories()
    {
        return [
            [
                'store_id' => 1,
            ],
            /*
             * URLs for non-default store views only work with two bugfixes in the core:
             *
             * 1. Magento\Catalog\Model\Category
             *
             * @617
             * --- $this->setData('url', $this->getUrlInstance()->getDirectUrl($rewrite->getRequestPath()));
             * +++ $this->setData('url', $this->getUrlInstance()->getDirectUrl($rewrite->getRequestPath(), ['_scope' => $this->getStoreId()]));
             *
             * 2. Magento\Framework\Url
             *
             * @718
             * --- return $this->getBaseUrl() . $routeParams['_direct'];
             * +++ return $this->getBaseUrl($routeParams) . $routeParams['_direct'];
             */
//            [
//                'store_id' => 2,
//            ]
        ];
    }

    public static function setUpBeforeClass()
    {
        // store has to be created first, otherwise @magentoConfigFixture does not work
        // http://magento.stackexchange.com/questions/93902/magento-2-integration-tests-load-data-fixtures-before-config-fixtures/93961
        include __DIR__ . '/../_files/second_store.php';
    }

    public static function loadFixture()
    {
        include __DIR__ . '/../_files/categories.php';
    }
}
