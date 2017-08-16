<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Plugin;

use \Magento\Catalog\Model\Layer\Filter\Item as Subject;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Html\Pager;

/**
 * Plugin to add "[]" to filter URL parameters in order to allow multivalue
 */
class FilterItemPlugin
{
    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var Pager
     */
    private $htmlPagerBlock;
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(UrlInterface $url, Pager $htmlPagerBlock, RequestInterface $request)
    {
        $this->htmlPagerBlock = $htmlPagerBlock;
        $this->url = $url;
        $this->request = $request;
    }

    public function aroundGetUrl(Subject $subject, \Closure $proceed)
    {
        $currentValues = $this->request->getParam($subject->getFilter()->getRequestVar());
        if (!is_array($currentValues)) {
            $currentValues = [$currentValues];
        }

        if (in_array($subject->getValue(), $currentValues)) {
            return $subject->getRemoveUrl($subject->getValue());
        }

        $query = [
            $subject->getFilter()->getRequestVar() . '[]' => $subject->getValue(),
            // exclude current page from urls
            $this->htmlPagerBlock->getPageVarName() => null,
        ];
        return $this->url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }

    public function aroundGetRemoveUrl(Subject $subject, \Closure $proceed, $attributeValue = null)
    {
        if (is_null($attributeValue)) {
            return $proceed();
        }
        if (is_array($attributeValue)) {
            $attributeValue = implode('-', $attributeValue);
        }
        $currentValues = $this->request->getParam($subject->getFilter()->getRequestVar());
        if (!is_array($currentValues)) {
            $currentValues = [$currentValues];
        }

        $params['_query'] = [$subject->getFilter()->getRequestVar() => array_diff($currentValues, [$attributeValue])];
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_escape'] = true;
        return $this->url->getUrl('*/*/*', $params);
    }
}