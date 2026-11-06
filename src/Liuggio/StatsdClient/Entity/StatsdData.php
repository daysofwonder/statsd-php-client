<?php

namespace Liuggio\StatsdClient\Entity;

use Liuggio\StatsdClient\Entity\StatsdDataInterface;

class StatsdData implements StatsdDataInterface
{

    private mixed $key;
    private mixed $value;
    private string $metric;
    private float $sampleRate = 1;

    /**
     * @param mixed $key
     */
    public function setKey(mixed $key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getKey() : mixed
    {
        return $this->key;
    }

    /**
     * @param mixed $value
     */
    public function setValue(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue() : mixed
    {
        return $this->value;
    }


    public function setMetric(string $metric)
    {
        $this->metric = $metric;
    }

    public function getMetric() : string
    {
        return $this->metric;
    }

    /**
     * @param float $sampleRate
     */
    public function setSampleRate(float $sampleRate)
    {
        $this->sampleRate = $sampleRate;
    }

    /**
     * @return float
     */
    public function getSampleRate() : float
    {
        return $this->sampleRate;
    }

    /**
     * @param bool $withMetric
     *
     * @return string
     */
    public function getMessage(bool $withMetric = true) : string
    {
        if (!$withMetric) {
            $result = sprintf('%s:%s', $this->getKey(), $this->getValue());
        } else {
            $result = sprintf('%s:%s|%s', $this->getKey(), $this->getValue(), $this->getMetric());
        }

        $sampleRate = $this->getSampleRate();
        if($sampleRate < 1){
            $result.= "|@$sampleRate";
        }

        return $result;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->getMessage();
    }
}
