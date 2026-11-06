<?php

namespace Liuggio\StatsdClient\Entity;

interface StatsdDataInterface
{
    CONST STATSD_METRIC_TIMING = 'ms';
    CONST STATSD_METRIC_GAUGE  = 'g';
    CONST STATSD_METRIC_SET    = 's';
    CONST STATSD_METRIC_COUNT  = 'c';

    /**
     * @abstract
     * @return mixed
     */
    function getKey() : mixed;

    /**
     * @abstract
     * @return mixed
     */
    function getValue() : mixed;

    /**
     * @abstract
     * @return string
     */
    function getMetric() : string;

    /**
     * @abstract
     * @return string
     */
    function getMessage() : string;

    /**
     * @abstract
     * @return float
     */
    function getSampleRate() : float;

    /**
     * @abstract
     * @return string
     */
    function __toString() : string;
}
