<?php
/**
 * integer_net Magento Module
 *
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\Solr\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Swatches\Block\LayeredNavigation\RenderLayered as Subject;
use Magento\Theme\Block\Html\Pager;

class SwatchesRenderLayeredPlugin
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
        $this->url = $url;
        $this->htmlPagerBlock = $htmlPagerBlock;
        $this->request = $request;
    }

    public function aroundBuildUrl(Subject $subject, \Closure $proceed, $attributeCode, $optionId)
    {
        $currentValues = $this->request->getParam($attributeCode);
        if (!is_array($currentValues)) {
            $currentValues = [$currentValues];
        }

        if (in_array($optionId, $currentValues)) {
            return $subject->getRemoveUrl($optionId);
        }

        $query = [
            $attributeCode . '[]' => $optionId,
            // exclude current page from urls
            $this->htmlPagerBlock->getPageVarName() => null,
        ];
        return $this->url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }
}