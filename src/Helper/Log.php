<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2015 integer_net GmbH (http://www.integer-net.de/)
 * @author     Fabian Schmengler <fs@integer-net.de>
 */
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

final class Integer\Net\Solr\Helper\Log extends AbstractLogger
{
    /**
     * @var array
     */
    protected static $_levelMapping = [
        LogLevel::ALERT     => \Zend\Log\Logger::ALERT,
        LogLevel::CRITICAL  => \Zend\Log\Logger::CRIT,
        LogLevel::DEBUG     => \Zend\Log\Logger::DEBUG,
        LogLevel::EMERGENCY => \Zend\Log\Logger::EMERG,
        LogLevel::ERROR     => \Zend\Log\Logger::ERR,
        LogLevel::INFO      => \Zend\Log\Logger::INFO,
        LogLevel::NOTICE    => \Zend\Log\Logger::NOTICE,
        LogLevel::WARNING   => \Zend\Log\Logger::WARN,
    ];
    /**
     * @var string
     */
    protected $_file = 'solr.log';

    /**
     * @param string $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->_file = $file;
        return $this;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return null
     */
    public function log($level, $message, array $context = [])
    {
        $this->_logLoggerInterface->debug($message, self::$_levelMapping[$level], $this->_file);
    }

}