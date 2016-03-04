<?php
/**
 * integer_net Magento Module
 *
 * @category   IntegerNet
 * @package    IntegerNet_Solr
 * @copyright  Copyright (c) 2016 integer_net GmbH (http://www.integer-net.de/)
 * @author     Andreas von Studnitz <avs@integer-net.de>
 */

/**
 * Class Integer\Net\Solr\Block\Result\Layer\Checkbox
 *
 * @method boolean getIsChecked()
 * @method Integer\Net\Solr\Block\Result\Layer\Checkbox setIsChecked(boolean $value)
 * @method boolean getIsTopNav()
 * @method Integer\Net\Solr\Block\Result\Layer\Checkbox setIsTopNav(boolean $value)
 * @method int getOptionId()
 * @method Integer\Net\Solr\Block\Result\Layer\Checkbox setOptionId(int $value)
 * @method string getAttributeCode()
 * @method Integer\Net\Solr\Block\Result\Layer\Checkbox setAttributeCode(string $value)
 */
class Integer\Net\Solr\Block\Result\Layer\Checkbox extends \Magento\Framework\View\Element\Template
{
    protected function _construct()
    {
        $this->setTemplate('IntegerNet_Solr::integernet/solr/filter/checkbox.phtml');
    }
}