<?php

/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */
class Integer\Net\Solr\Model\ConnectionCheck
{
    protected $_flag = null;

    public function checkConnection()
    {
        $errors = [];
        $hasCheckedAStore = false;
        foreach ($this->_modelStoreManagerInterface->getStores() as $store) {
            if (!$this->_configScopeConfigInterface->isSetFlag('integernet_solr/general/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId())) {
                continue;
            }
            if (!$this->_configScopeConfigInterface->isSetFlag('integernet_solr/connection_check/is_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId())) {
                continue;
            }
            $hasCheckedAStore = true;
            $checkMessages = $this->_modelConfigurationFactory->create()->getMessages($store->getId());
            if (isset($checkMessages['error']) && sizeof($checkMessages['error'])) {
                $errors[$store->getId()] = $checkMessages['error'];
            }
        }

        if (!$hasCheckedAStore) {
            return;
        }
        
        $minErrorCount = intval($this->_configScopeConfigInterface->getValue('integernet_solr/connection_check/send_email_on_nth_failure', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $flagExists = !is_null($this->_getErrorFlag()->getFlagData());
        $currentErrorCount = intval($this->_getErrorFlag()->getFlagData());
        if (sizeof($errors)) {
            if (!$flagExists) {
                return; // don't do anything on errors if check wasn't successful once
            }
            $currentErrorCount++;
            if ($currentErrorCount == $minErrorCount) {
                $this->_sendErrorEmail($errors);
            }
            $this->_getErrorFlag()->setFlagData($currentErrorCount)->save();
        } else {
            if ($currentErrorCount >= $minErrorCount) {
                $this->_sendRestoredEmail();
            }
            if ($currentErrorCount > 0 || !$flagExists) {
                $this->_getErrorFlag()->setFlagData(0)->save();
            }
        }
    }

    /**
     * @return \Magento\Framework\Model\Flag
     */
    protected function _getErrorFlag()
    {
        if (is_null($this->_flag)) {
            $this->_flag = \Magento\Framework\App\ObjectManager::getInstance()->create('core/flag', ['flag_code' => 'solr_connection_error_count'])->loadSelf();
        }
        return $this->_flag;
    }

    /**
     * @param array $errors
     */
    protected function _sendErrorEmail($errors)
    {
        $templateId = $this->_configScopeConfigInterface->getValue('integernet_solr/connection_check/email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $sender = $this->_configScopeConfigInterface->getValue('integernet_solr/connection_check/identity', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $recipients = $this->_configScopeConfigInterface->getValue('integernet_solr/connection_check/recipient_emails', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        foreach(explode(',', $recipients) as $recipient) {

            $this->_modelTemplateFactory->create()
                ->sendTransactional(
                    $templateId,
                    $sender,
                    trim($recipient),
                    '',
                    [
                        'notification_text' => $this->_getErrorNotificationText($errors),
                        'base_url' => $this->_configScopeConfigInterface->getValue('web/unsecure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    ]
                );
        }
    }

    /**
     * @param array $errors
     * @return string
     */
    protected function _getErrorNotificationText($errors)
    {
        $text = '';
        foreach ($errors as $storeId => $storeErrorMessages) {
            $headline = __('Errors for Store "%1":', $this->_modelStoreManagerInterface->getStore($storeId)->getName());
            $text .= $headline . PHP_EOL;
            $text .= str_repeat('=', strlen($headline)) . PHP_EOL;
            foreach($storeErrorMessages as $message) {
                $text .= '- ' . $message . PHP_EOL;
            }
            $text .= PHP_EOL;
        }

        return $text;
    }

    /**
     */
    protected function _sendRestoredEmail()
    {
        $templateId = $this->_configScopeConfigInterface->getValue('integernet_solr/connection_check/email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $sender = $this->_configScopeConfigInterface->getValue('integernet_solr/connection_check/identity', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $recipients = $this->_configScopeConfigInterface->getValue('integernet_solr/connection_check/recipient_emails', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        foreach(explode(',', $recipients) as $recipient) {

            $this->_modelTemplateFactory->create()
                ->sendTransactional(
                    $templateId,
                    $sender,
                    trim($recipient),
                    '',
                    [
                        'notification_text' => $this->_getRestoredNotificationText(),
                        'base_url' => $this->_configScopeConfigInterface->getValue('web/unsecure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    ]
                );
        }
    }

    /**
     * @return string
     */
    protected function _getRestoredNotificationText()
    {
        return __('Connection to Solr Server has been restored.');
    }
}