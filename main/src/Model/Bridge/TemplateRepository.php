<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Bridge;


use IntegerNet\SolrSuggest\Implementor\TemplateRepository as TemplateRepositoryInterface;
use IntegerNet\SolrSuggest\Plain\Block\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Template\File\Resolver as TemplateFileResolver;
use Magento\Store\Model\ScopeInterface;

class TemplateRepository implements TemplateRepositoryInterface
{
    /**
     * @var TemplateFileResolver
     */
    private $templateFileResolver;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ThemeProviderInterface
     */
    private $themeProvider;

    public function __construct(TemplateFileResolver $templateFileResolver, ScopeConfigInterface $scopeConfig,
                                ThemeProviderInterface $themeProvider)
    {
        $this->templateFileResolver = $templateFileResolver;
        $this->scopeConfig = $scopeConfig;
        $this->themeProvider = $themeProvider;
    }

    /**
     * @param int $storeId
     * @return Template
     */
    public function getTemplateByStoreId($storeId)
    {
        return new Template(
            $this->templateFileResolver->getTemplateFileName(
                'IntegerNet_Solr::autosuggest/index.phtml',
                [
                    'area' => 'frontend',
                    'themeModel' => $this->themeProvider->getThemeById(
                        $this->scopeConfig->getValue(
                            DesignInterface::XML_PATH_THEME_ID,
                            ScopeInterface::SCOPE_STORE,
                            $storeId
                        )
                    )
                ]
            )
        );
    }

}