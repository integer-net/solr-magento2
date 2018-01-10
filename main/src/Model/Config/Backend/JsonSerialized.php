<?php

namespace IntegerNet\Solr\Model\Config\Backend;

class JsonSerialized extends \Magento\Framework\App\Config\Value
{
    /**
     * @return void
     */
    protected function _afterLoad()
    {
        if (!is_array($this->getValue())) {
            $value = $this->getValue();
            $this->setValue(empty($value) ? false : $this->unserialize($value));
        }
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);
        }
        $this->setValue($value);
        if (is_array($this->getValue())) {
            $this->setValue(\json_encode($this->getValue()));
        }
        return parent::beforeSave();
    }

    private function unserialize($value)
    {
        $jsonDecoded = \json_decode($value, true);
        if ($jsonDecoded === null) {
            /*
             * Fall back to PHP unserialization for backwards compatibility with old configuration values
             */
            $phpUnserialized = @\unserialize($value, []);
            if ($phpUnserialized !== false) {
                return $phpUnserialized;
            }
        }
        return $jsonDecoded;
    }
}
