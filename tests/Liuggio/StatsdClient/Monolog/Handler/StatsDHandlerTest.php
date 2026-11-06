<?php

namespace Liuggio\StatsdClient\Tests\Monolog\Handler;

use Monolog\Logger;
use Liuggio\StatsdClient\Monolog\Handler\StatsDHandler;
use Monolog\Level;
use Monolog\LogRecord;

class StatsDHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return LogRecord Record
     */
    protected function getRecord(Level $level = Level::Warning, $message = 'test', $context = array()) : LogRecord
    {
        return new LogRecord(\DateTimeImmutable::createFromFormat('U.u', sprintf('%.6F', microtime(true))), 'test', $level, $message, $context);
    }

    /**
     * @return array
     */
    protected function getMultipleRecords()
    {
        return array(
            $this->getRecord(Level::Debug, 'debug message 1'),
            $this->getRecord(Level::Debug, 'debug message 2'),
            $this->getRecord(Level::Info, 'information'),
            $this->getRecord(Level::Warning, 'warning'),
            $this->getRecord(Level::Error, 'error')
        );
    }

    /**
     * @return \Monolog\Formatter\FormatterInterface
     */
    protected function getIdentityFormatter()
    {
        $formatter = $this->getMockBuilder('Monolog\\Formatter\\FormatterInterface')->getMock();
        $formatter->expects($this->any())
            ->method('format')
            ->willReturnCallback(function($record) { return $record['message']; });

        return $formatter;
    }


    protected function setUp() : void
    {
        if (!interface_exists('Liuggio\StatsdClient\StatsdClientInterface')) {
            $this->markTestSkipped('The "liuggio/statsd-php-client" package is not installed');
        }
    }

    public function testHandle()
    {
        $client = $this->getMockBuilder('Liuggio\StatsdClient\StatsdClientInterface')->getMock();
        $factory = $this->getMockBuilder('Liuggio\StatsdClient\Factory\StatsdDataFactoryInterface')->getMock();

        $factory->expects($this->any())
            ->method('increment')
            ->willReturnCallback(function ($input){
                return sprintf('%s|c|1', $input);
            });

        $prefixToAssert = 'prefix';
        $messageToAssert = 'test-msg';

        $record = $this->getRecord(Level::Warning, $messageToAssert, array('data' => new \stdClass, 'foo' => 34));

        $assert = array(sprintf('%s.test.WARNING.%s|c|1',$prefixToAssert, $messageToAssert),
            sprintf('%s.test.WARNING.%s.context.data.{"stdClass":{}}|c|1',$prefixToAssert, $messageToAssert),
            sprintf('%s.test.WARNING.%s.context.foo.34|c|1',$prefixToAssert, $messageToAssert));

        $client->expects($this->once())
            ->method('send')
            ->with($assert);

        $handler = new StatsDHandler($client, $factory, $prefixToAssert);
        $handler->handle($record);
    }
}
