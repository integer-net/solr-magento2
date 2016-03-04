<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2014 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

use IntegerNet\SolrSuggest\Implementor\AutosuggestBlock;
use IntegerNet\SolrSuggest\Implementor\Factory\AutosuggestResultFactory;
use IntegerNet\SolrSuggest\Result\AutosuggestResult;
use IntegerNet\SolrSuggest\Util\HtmlStringHighlighter;
use IntegerNet\SolrSuggest\Util\StringHighlighter;

class Integer\Net\Solr\Block\Autosuggest extends \Magento\Framework\View\Element\Template implements AutosuggestBlock
{
    protected $_attributes = [];
    protected $_result = null;
    protected $_customHelper;
    protected $_suggestData;
    /**
     * @var AutosuggestResultFactory
     */
    private $resultFactory;
    /**
     * @var StringHighlighter
     */
    private $highlighter;

    protected function _construct()
    {
        $this->setTemplate('IntegerNet_Solr::integernet/solr/autosuggest.phtml');
        $this->highlighter = new HtmlStringHighlighter();
        $this->resultFactory = $this->_helperFactory;
    }

    /**
     * Lazy loading the Solr result
     *
     * @return AutosuggestResult
     */
    public function getResult()
    {
        if (is_null($this->_result)) {
            $this->_result = $this->resultFactory->getAutosuggestResult();
        }

        return $this->_result;
    }

    /**
     * @param string $resultText
     * @param string $query
     * @return string
     */
    public function highlight($resultText, $query)
    {
        return $this->highlighter->highlight($resultText, $query);
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->getResult()->getQuery();
    }

    /**
     * Fallback if solr is deactivated
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_helperData->isActive()) {

            return $this->_getFallbackHtml();
        }

        return parent::_toHtml();
    }

    public function getSuggestData()
    {
        if (!$this->_suggestData) {
            $collection = $this->_catalogSearchHelperData->getSuggestCollection();
            $query = $this->_catalogSearchHelperData->getQueryText();
            $counter = 0;
            $data = [];
            foreach ($collection as $item) {
                $_data = [
                    'title' => $item->getQueryText(),
                    'row_class' => (++$counter)%2?'odd':'even',
                    'num_of_results' => $item->getNumResults()
                ];

                if ($item->getQueryText() == $query) {
                    array_unshift($data, $_data);
                }
                else {
                    $data[] = $_data;
                }
            }
            $this->_suggestData = $data;
        }
        return $this->_suggestData;
    }

    /**
     * @return string
     */
    protected function _getFallbackHtml()
    {
        $html = '';

        if (!$this->_beforeToHtml()) {
            return $html;
        }

        $suggestData = $this->getSuggestData();
        if (!($count = count($suggestData))) {
            return $html;
        }

        $count--;

        $html = '<ul><li style="display:none"></li>';
        foreach ($suggestData as $index => $item) {
            if ($index == 0) {
                $item['row_class'] .= ' first';
            }

            if ($index == $count) {
                $item['row_class'] .= ' last';
            }

            $html .= '<li title="' . $this->escapeHtml($item['title']) . '" class="' . $item['row_class'] . '">'
                . '<span class="amount">' . $item['num_of_results'] . '</span>' . $this->escapeHtml($item['title']) . '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * @return Integer\Net\Solr\Helper\Custom|Integer\Net\Solr\Autosuggest\Custom
     */
    public function getCustomHelper()
    {
        if (class_exists('Integer\Net\Solr\Autosuggest\Custom')) {
            /**
             * @deprecated only for backwards compatibility in Magento mode
             */
            return new Integer\Net\Solr\Autosuggest\Custom();
        }
        $cacheReader = $this->_helperFactory->getCacheReader();
        $storeId = $this->_modelStoreManagerInterface->getStore()->getId();
        try {
            $customHelperFactory = $cacheReader->getCustomHelperFactory($storeId);
        } catch (\IntegerNet\SolrSuggest\Plain\Cache\CacheItemNotFoundException $e) {
            $this->_helperAutosuggest->storeSolrConfig();
            $customHelperFactory = $cacheReader->getCustomHelperFactory($storeId);
        }
        return $customHelperFactory->getCustomHelper($this, $cacheReader);
    }
}