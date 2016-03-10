<?php
namespace IntegerNet\Solr\Model\Bridge;

use IntegerNet\Solr\Implementor\Source as SourceInterface;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Source implements SourceInterface
{
    /**
     * @var AbstractSource
     */
    protected $magentoSource;

    /**
     * @param AbstractSource $magentoSource
     */
    public function __construct(AbstractSource $magentoSource)
    {
        $this->magentoSource = $magentoSource;
    }

    /**
     * @param int $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        return $this->magentoSource->getOptionText($optionId);
    }

    /**
     * Returns [optionId => optionText] map
     *
     * @return string[]
     */
    public function getOptionMap()
    {
        $_options = [];
        foreach ($this->magentoSource->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;

    }

}