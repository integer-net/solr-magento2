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


use IntegerNet\Solr\Model\Cache\PsrFileCacheStorageFactory;
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
    /**
     * @var PsrFileCacheStorageFactory
     */
    private $cacheStorageFactory;

    public function __construct(
        TemplateFileResolver $templateFileResolver,
        ScopeConfigInterface $scopeConfig,
        ThemeProviderInterface $themeProvider,
        PsrFileCacheStorageFactory $cacheStorageFactory
    ) {
        $this->templateFileResolver = $templateFileResolver;
        $this->scopeConfig = $scopeConfig;
        $this->themeProvider = $themeProvider;
        $this->cacheStorageFactory = $cacheStorageFactory;
    }

    /**
     * @param int $storeId
     * @return Template
     */
    public function getTemplateByStoreId($storeId)
    {
        return new Template(
            $this->getTemplateFile($storeId)
        );
    }


    /**
     * Get absolute path to template
     *
     * @param int $storeId
     * @return string
     */
    private function getTemplateFile($storeId)
    {
        $templateName = $this->templateFileResolver->getTemplateFileName(
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
        );

        $templateContents = file_get_contents($templateName);

        $templateContents = $this->getTranslatedTemplate($templateContents);

        $targetDirname = $this->cacheStorageFactory->rootDir();
        if (!is_dir($targetDirname)) {
            mkdir($targetDirname, 0777, true);
        }
        $targetFilename = $targetDirname . DIRECTORY_SEPARATOR . 'store__' . $storeId . '.autosuggest.phtml';
        file_put_contents($targetFilename, $templateContents);

        return $targetFilename;
    }


    /**
     * Translate all occurences of __('...') with translated text
     *
     * @param string $templateContents
     * @return string
     */
    private function getTranslatedTemplate($templateContents)
    {
        preg_match_all('$__\(\'(.*)\'$', $templateContents, $results);

        foreach($results[1] as $key => $search) {

            $replace = __($search);
            $templateContents = str_replace($search, $replace, $templateContents);
        }

        return $templateContents;
    }
}