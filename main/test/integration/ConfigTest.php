<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr;

use DOMDocument;
use DOMXPath;
use IntegerNet\Solr\Model\SolrStatusMessages;
use IntegerNet\Solr\Model\StatusMessages;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Response;
use Magento\TestFramework\TestCase\AbstractBackendController;

class ConfigTest extends  AbstractBackendController
{
    /** @var  Response */
    private static $lastResponse;
    /** @var  ObjectManager */
    protected $objectManager;

    public static function tearDownAfterClass()
    {
        self::$lastResponse = null;
    }

    private function mockStatusMessages()
    {
        $messagesStub = $this->getMockForAbstractClass(StatusMessages::class);
        $messagesStub->method('getMessages')->willReturn([
            'error' => ['Error 1'],
            'success' => ['Success 1', 'Success 2'],
            'warning' => ['Warning 1'],
            'notice' => ['Notice 1']
        ]);
        $this->objectManager->addSharedInstance($messagesStub, SolrStatusMessages::class);
    }

    private function setUpRequest()
    {
        $this->uri = 'backend/admin/system_config/edit';
        $this->resource = 'IntegerNet_Solr::config_integernet_solr';
        $this->getRequest()->setParam('section', 'integernet_solr');
    }

    /**
     * @param Response $response
     * @return DOMDocument
     */
    private function getResponseDom(Response $response)
    {
        $dom = new DOMDocument();
        \libxml_use_internal_errors(true);
        $dom->loadHTML($response->getBody());
        \libxml_clear_errors();
        return $dom;
    }


    /**
     * @param DOMDocument $dom
     */
    private function assertStatusMessages(DOMDocument $dom)
    {
        $descriptionHtml = $dom->saveXml($dom->getElementById('row_integernet_solr_general_description'));
        $expectedDescriptionFragments = [
            'Status messages container' => '<div class="messages integernet_solr_messages">',
            'Error messages' => '<div class="message message-error error">',
            'Success messages' => '<div class="message message-success success">',
            'Warning messages' => '<div class="message message-warning warning">',
            'Notice messages' => '<div class="message message-notice notice">',
            'First success message' => '<li><span>Success 1</span></li>',
            'Second success message' => '<li><span>Success 2</span></li>',
            'Error message' => '<li><span>Error 1</span></li>',
            'Warning message' => '<li><span>Warning 1</span></li>',
            'Notice message' => '<li><span>Notice 1</span></li>',
        ];
        foreach ($expectedDescriptionFragments as $assertMessage => $expectedHtml) {
            $this->assertContains($expectedHtml, $descriptionHtml, $assertMessage);
        }
    }

    /**
     * @param DOMDocument $dom
     */
    private function assertDropdowns(DOMDocument $dom)
    {
        $this->markTestSkipped('Product and category redirect configuration is hidden as long as it is not implemented yet');
        $xpath = new DOMXPath($dom);
        $categoryRedirectDropdown = $xpath->query('//select[@id="integernet_solr_results_category_attributes_redirect"]');
        $this->assertEquals(1, $categoryRedirectDropdown->length, 'Category redirect');
        $this->assertContains('Name [name]', $dom->saveXML($categoryRedirectDropdown->item(0)));

        $productRedirectDropdown = $xpath->query('//select[@id="integernet_solr_results_product_attributes_redirect"]');
        $this->assertEquals(1, $productRedirectDropdown->length, 'Product redirect');
        $this->assertContains('Name [name]', $dom->saveXML($productRedirectDropdown->item(0)));
    }

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = ObjectManager::getInstance();
        $this->setUpRequest();
        $this->mockStatusMessages();
    }

    /**
     * Overridden to make depends annotation work
     */
    public function testAclHasAccess()
    {
        parent::testAclHasAccess();
        // we do not pass the response as parameter to dependent test because PHPUnit would take ages
        // to go through the object tree, looking for mock objects.
        self::$lastResponse = $this->getResponse();
    }

    /**
     * Overridden to check for redirect instead of "Forbidden" response
     */
    public function testAclNoAccess()
    {
        if ($this->resource === null) {
            $this->markTestIncomplete('Acl test is not complete');
        }
        $this->_objectManager->get('Magento\Framework\Acl\Builder')
            ->getAcl()
            ->deny(null, $this->resource);
        $this->dispatch($this->uri);
        $this->assertSame(302, $this->getResponse()->getHttpResponseCode());
        $this->assertContains('/index.php/backend/admin/system_config/index/', $this->getResponse()->getHeader('location')->toString());
    }

    /**
     * @depends testAclHasAccess
     */
    public function testConfigSectionLoads()
    {
        $response = self::$lastResponse;
        $this->assertEquals(200, $response->getStatusCode(), 'HTTP Status Code');
        $dom = $this->getResponseDom($response);
        $this->assertStatusMessages($dom);
        $this->assertDropdowns($dom);
    }

}
