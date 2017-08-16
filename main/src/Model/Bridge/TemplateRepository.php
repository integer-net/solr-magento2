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
use Magento\Framework\App\AreaList;
use Magento\Framework\Locale\ResolverInterface as LocaleResolverInterface;
use Magento\Store\Model\App\Emulation;
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
    /**
     * @var Emulation
     */
    private $emulation;
    /**
     * @var LocaleResolverInterface
     */
    private $localeResolver;
    /**
     * @var AreaList
     */
    private $areaList;

    public function __construct(
        TemplateFileResolver $templateFileResolver,
        ScopeConfigInterface $scopeConfig,
        ThemeProviderInterface $themeProvider,
        PsrFileCacheStorageFactory $cacheStorageFactory,
        Emulation $emulation,
        LocaleResolverInterface $localeResolver,
        AreaList $areaList
    ) {
        $this->templateFileResolver = $templateFileResolver;
        $this->scopeConfig = $scopeConfig;
        $this->themeProvider = $themeProvider;
        $this->cacheStorageFactory = $cacheStorageFactory;
        $this->emulation = $emulation;
        $this->localeResolver = $localeResolver;
        $this->areaList = $areaList;
    }

    /**
     * @param int $storeId
     * @return Template
     */
    public function getTemplateByStoreId($storeId)
    {
        $this->emulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        // App\Emulation cannot use setLocale() if Backend\LocaleResolver is used, so we have to emulate locale explicitly
        $this->localeResolver->emulate($storeId);

        $area = $this->areaList->getArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        $area->load(\Magento\Framework\App\Area::PART_TRANSLATE);

        $template = new Template(
            $this->getTemplateFile($storeId)
        );

        $this->localeResolver->revert();
        $this->emulation->stopEnvironmentEmulation();

        return $template;
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

            $replace = (string)__($search);
            $templateContents = str_replace($this->quote($search), $this->quote($replace), $templateContents);
        }

        return $templateContents;
    }

    /**
     * @param string $search
     * @return string
     */
    private function quote($search)
    {
        return '\'' . $search . '\'';
    }
}