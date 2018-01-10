<?php

namespace IntegerNet\Solr\Controller;

use IntegerNet\Solr\Fixtures\SolrConfig;
use Magento\TestFramework\TestCase\AbstractController as BaseAbstractController;
use Magento\TestFramework\ObjectManager;

abstract class AbstractController extends BaseAbstractController
{
    /** @var  ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        parent::setUp();
        SolrConfig::loadFromConfigFile();
        $this->objectManager = ObjectManager::getInstance();
    }

    protected function assertDomElementContains(string $xpath, string $expectedString, string $message = '')
    {
        $dom = $this->getResponseDom();
        $this->assertContains($expectedString, $dom->saveHTML((new \DOMXPath($dom))->query($xpath)->item(0)), $message);
    }

    protected function assertDomElementNotContains(string $xpath, string $expectedString, string $message = '')
    {
        $dom = $this->getResponseDom();
        $this->assertNotContains(
            $expectedString,
            $dom->saveHTML((new \DOMXPath($dom))->query($xpath)->item(0)),
            $message
        );
    }

    protected function assertDomElementPresent(string $xpath, string $message = '')
    {
        $this->assertDomElementCount($xpath, 1, $message);
    }

    protected function assertDomElementCount(string $xpath, int $expectedCount, string $message = '')
    {
        $dom = $this->getResponseDom();
        $this->assertEquals($expectedCount, (new \DOMXPath($dom))->query($xpath)->length, $message);
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