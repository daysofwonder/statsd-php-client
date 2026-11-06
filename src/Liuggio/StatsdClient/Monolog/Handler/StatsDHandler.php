<?php

namespace Liuggio\StatsdClient\Monolog\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Formatter\FormatterInterface;

use Liuggio\StatsdClient\Monolog\Formatter\StatsDFormatter;
use Liuggio\StatsdClient\Factory\StatsdDataFactoryInterface;
use Liuggio\StatsdClient\Factory\StatsdDataFactory;
use Liuggio\StatsdClient\StatsdClientInterface;
use Monolog\Level;
use Monolog\LogRecord;

/**
 * A processing handler for StatsD.
 *
 * @author Giulio De Donato <liuggio@gmail.com>
 */
class StatsDHandler extends AbstractProcessingHandler
{
    /**
     * @var array
     */
    protected $buffer = array();

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var statsDService
     */
    protected $statsDService;

    /**
     * @var statsDFactory
     */
    protected $statsDFactory;

    /**
     * @param StatsdClientInterface $statsDService The Service sends the packet
     * @param StatsdDataFactoryInterface $statsDFactory The Factory creates the StatsDPacket
     * @param string $prefix Statsd key prefix
     * @param integer $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(StatsdClientInterface $statsDService, ?StatsdDataFactoryInterface $statsDFactory, $prefix, int|string|Level $level = \Monolog\Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->statsDService = $statsDService;
        $this->statsDFactory = $statsDFactory ? $statsDFactory : new StatsdDataFactory();
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function close() : void
    {
        $this->statsDService->send($this->buffer);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(LogRecord $record) : void
    {
        $records = is_array($record['formatted']) ? $record['formatted'] : array($record['formatted']);

        foreach ($records as $rec) {
            if (!empty($rec)) {
                $recs = explode("\n", $rec);
                foreach ($recs as $r) {
                    if (!empty($r)) {
                        $this->buffer[] = $this->statsDFactory->increment(sprintf('%s.%s', $this->getPrefix(), $r));
                    }
                }
            }
        }
    }

    /**
     * Gets the default formatter.
     *
     * @return FormatterInterface
     */
    protected function getDefaultFormatter() : FormatterInterface
    {
        return new StatsDFormatter();
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param StatsdClientInterface $statsDService
     */
    public function setStatsDService(StatsdClientInterface $statsDService)
    {
        $this->statsDService = $statsDService;
    }

    /**
     * @param StatsdDataFactoryInterface $statsDFactory
     */
    public function setStatsDFactory(StatsdDataFactoryInterface $statsDFactory)
    {
        $this->statsDFactory = $statsDFactory;
    }
}
