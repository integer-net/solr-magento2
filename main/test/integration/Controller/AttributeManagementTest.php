<?php

namespace IntegerNet\Solr\Controller;

use Magento\TestFramework\TestCase\AbstractBackendController;

class AttributeManagementTest extends AbstractBackendController
{
    protected $uri = 'backend/catalog/product_attribute/edit/attribute_id/81';

    public function testCanBeAccessed()
    {
        $this->dispatch($this->uri);
        $this->assertDomElementContains(
            '//*[@id="search_weight"]',
            'type="text"',
            'Search Weight input should be text'
        );
    }

    protected function assertDomElementContains(string $xpath, string $expectedString, string $message = '')
    {
        $dom = $this->getResponseDom();
        $this->assertContains($expectedString, $dom->saveHTML((new \DOMXPath($dom))->query($xpath)->item(0)), $message);
    }

    private function getResponseDom(): \DOMDocument
    {
        $dom = new \DOMDocument();
        \libxml_use_internal_errors(true);
        $dom->loadHTML($this->getResponse()->getBody());
        \libxml_use_internal_errors(false);
        return $dom;
    }
}