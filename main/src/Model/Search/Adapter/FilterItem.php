<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2017 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Search\Adapter;

class FilterItem extends \Magento\Catalog\Model\Layer\Filter\Item
{
    /**
     * @var \Magento\Framework\Search\RequestInterface
     */
    private $request;

    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        parent::__construct($url, $htmlPagerBlock, $data);
        $this->request = $request;
    }

    public function isActive()
    {
        $currentValues = $this->request->getParam($this->getFilter()->getRequestVar());
        if (!is_array($currentValues)) {
            $currentValues = [$currentValues];
        }

        return in_array($this->getValue(), $currentValues);
    }
}
