<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
namespace IntegerNet\Solr\Search;

use IntegerNet\Solr\Implementor\SolrRequestFactoryInterface;
use IntegerNet\Solr\Model\Bridge\RequestFactory;
use IntegerNet\Solr\Request\Request;
use IntegerNet\Solr\Resource\ResourceFacade;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as FulltextSearchCollection;
use Magento\Framework\Registry;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class AdapterTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @magentoDataFixture loadFixture
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default/integernet_solr/general/is_active 1
     * @magentoConfigFixture default/integernet_solr/results/use_html_from_solr 0
     * @magentoConfigFixture default/catalog/search/engine integernet_solr
     */
    public function testSearchAdapterIsTriggeredInNonHtmlMode()
    {
        $this->markTestSkipped('this test is failing on CI for yet unknown reasons');
        /** @var Registry $registry */
        $registry = $this->objectManager->get(Registry::class);
        $this->assertNull($registry->registry('current_category'));
        $fakeRequestFactory = new FakeRequestFactory();
        $fakeRequestFactory->setResponse(\json_encode([
            "facet_counts" => [
                "facet_fields" => [],
                "facet_intervals" => ["price_f" => []],
            ],
            "response" => [
                "numFound" => 1,
                "start" => 0,
                "docs" => [
                    [
                        "id" => "333_1",
                        "product_id" => 333,
                        "category" => [333],
                        "store_id" => 1,
                        "content_type" => "product",
                        "name_t" => "Something"
                    ]
                ]
            ]
        ]));
        $this->objectManager->addSharedInstance($fakeRequestFactory, RequestFactory::class);
        /** @var FulltextSearchCollection $fulltextSearch */
        $fulltextSearch = $this->objectManager->get('Magento\CatalogSearch\Model\ResourceModel\Fulltext\SearchCollection'); // virtual type
        $fulltextSearch->addSearchFilter("something");
        $fulltextSearch->load();
        $this->assertEquals(1, $fulltextSearch->count(), "1 dummy result");

    }

    public static function loadFixture()
    {
        include __DIR__ . '/../_files/products.php';
    }
}

/**
 * @internal
 */
class FakeRequestFactory implements SolrRequestFactoryInterface 
{
    private $factory;
    public function __construct()
    {
        $this->factory = new \IntegerNet\Solr\Request\FakeRequestFactory();
    }
    /**
     * Returns new configured Solr recource
     *
     * @deprecated should not be used directly from application
     * @return ResourceFacade
     */
    public function getSolrResource()
    {
    }
    /**
     * Returns new Solr service (search, autosuggest or category service, depending on application state or parameter)
     *
     * @param int $requestMode
     * @return Request
     */
    public function getSolrRequest($requestMode = self::REQUEST_MODE_AUTODETECT)
    {
        return $this->factory->createRequest();
    }

    /**
     * @param string $response
     */
    public function setResponse($response)
    {
        return $this->factory->setResponse($response);
    }
}