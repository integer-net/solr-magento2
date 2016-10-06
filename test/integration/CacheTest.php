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
use Magento\TestFramework\Response;
use Magento\TestFramework\TestCase\AbstractBackendController;

class CacheTest extends  AbstractBackendController
{
    protected function setUp()
    {
        parent::setUp();
        $this->setUpRequest();
    }
    private function setUpRequest()
    {
        $this->uri = 'backend/integernet_solr/system/flushCache';
        $this->resource = 'IntegerNet_Solr::config_integernet_solr';
    }

    public function testFlushCacheButton()
    {
        $this->dispatch('backend/admin/cache/index');
        $xpath = new \DOMXPath($this->getResponseDom($this->getResponse()));
        $DOMNodeList = $xpath->query('//div[@class="additional-cache-management"]/*[@class="integernet_solr-cache"]');
        $this->assertEquals(1, $DOMNodeList->length);
        $this->assertRegExp(
            '{/backend/integernet_solr/system/flushCache}',
            $xpath->evaluate('string(button/@onclick)', $DOMNodeList->item(0))
        );
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


}