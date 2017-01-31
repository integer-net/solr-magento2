<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */

namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Implementor\StoreEmulation as StoreEmulationInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

class StoreEmulation implements StoreEmulationInterface
{
    /**
     * @var Emulation
     */
    private $appEmulation;
    /**
     * @var LocaleResolver
     */
    private $localeResolver;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var bool
     */
    private $isEmulated = false;
    /**
     * @var string
     */
    private $originalStoreCode;

    /**
     * StoreEmulation constructor.
     * @param Emulation $appEmulation
     * @param LocaleResolver $localeResolver
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(Emulation $appEmulation, LocaleResolver $localeResolver, StoreManagerInterface $storeManager)
    {
        $this->appEmulation = $appEmulation;
        $this->localeResolver = $localeResolver;
        $this->storeManager = $storeManager;
    }

    /**
     * Starts environment emulation for given store. Previously emulated environments are stopped before new emulation starts.
     *
     * @internal use runInStore() instead
     * @param int $storeId
     * @return void
     */
    public function start($storeId)
    {
        if ($this->isEmulated) {
            $this->stop();
        }
        // Saving the original store code to ensure that store is reset correctly
        // Magento uses the store id, but setCurrentStore(0) does not work, while setCurrentStore('admin') works
        $this->originalStoreCode = $this->storeManager->getStore()->getCode();
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        // App\Emulation cannot use setLocale() if Backend\LocaleResolver is used, so we have to emulate locale explicitly
        $this->localeResolver->emulate($storeId);
        $this->isEmulated = true;
    }

    /**
     * Stops any active store emulation
     *
     * @internal use runInStore() instead
     * @return void
     */
    public function stop()
    {
        $this->localeResolver->revert();
        $this->appEmulation->stopEnvironmentEmulation();
        $this->storeManager->setCurrentStore($this->originalStoreCode);
        $this->isEmulated = false;
    }

    /**
     * Executes callback with environment emulation for given store. Emulation is stopped in any case (Exception or successful execution).
     *
     * @param $storeId
     * @param callable $callback
     * @return void
     */
    public function runInStore($storeId, $callback)
    {
        // Note: do not use App\State::emulateAreaCode() together with App\Emulation
        // it is not necessary and conflicts with startEnvironmentEmulation()
        try {
            $this->start($storeId);
            $callback();
        } finally {
            $this->stop();
        }
    }
}