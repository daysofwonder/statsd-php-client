<?php

namespace Liuggio\StatsdClient\Tests\Monolog\Formatter;

use Monolog\Logger;
use Liuggio\StatsdClient\Monolog\Formatter\StatsDFormatter;
use Monolog\Level;
use Monolog\LogRecord;

/**
 * @covers Liuggio\StatsdClient\Monolog\Formatter\StatsDFormatter
 */
class StatsDFormatterTest extends \PHPUnit\Framework\TestCase
{
    public function testBatchFormat()
    {
        $formatter = new StatsDFormatter(null, 2);
        $message = $formatter->formatBatch([
            new LogRecord(new \DateTimeImmutable(), 'test', Level::Critical, 'bar'),
            new LogRecord(new \DateTimeImmutable(), 'log', Level::Warning, 'foo')
        ]);

        $this->assertEquals("test.CRITICAL.bar\nlog.WARNING.foo\n", $message);
    }

    public function testDefFormatWithString()
    {
        $formatter = new StatsDFormatter(StatsDFormatter::SIMPLE_FORMAT);
        $message = $formatter->format(new LogRecord(new \DateTimeImmutable(), 'log', Level::Warning, 'foo'));
        $this->assertEquals("log.WARNING.foo\n", $message);
    }

    public function testDefFormatWithArrayContext()
    {
        $formatter = new StatsDFormatter();
        $message = $formatter->format(new LogRecord(new \DateTimeImmutable(), 'meh', Level::Error, 'foo', ['foo' => 'bar', 'baz' => 'qux']));

        $assert = "meh.ERROR.foo\n".
            "meh.ERROR.foo.context.foo.bar\n".
            "meh.ERROR.foo.context.baz.qux\n";

        $this->assertEquals($assert, $message);
    }

    public function testDefFormatWithArrayContextAndExtra()
    {
        $formatter = new StatsDFormatter();
        $message = $formatter->format(new LogRecord(new \DateTimeImmutable(), 'meh', Level::Error, 'foo', ['foo' => 'bar', 'baz' => 'qux'], ['extra' => 'woow']));

        $assert = "meh.ERROR.foo\n".
            "meh.ERROR.foo.context.foo.bar\n".
            "meh.ERROR.foo.context.baz.qux\n".
            "meh.ERROR.foo.extra.extra.woow\n";

        $this->assertEquals($assert, $message);

    }

    public function testDefLongFormat()
    {
        $formatter = new StatsDFormatter();
        $message = $formatter->format(new LogRecord(new \DateTimeImmutable(), 'doctrine', Level::Debug, 'INSERT INTO viaggio_calendar (enable, viaggio_id, calendar_id) VALUES (?, ?, ?)', ['foo' => 'bar', 'baz' => 'qux']));
        $this->assertEquals("doctrine.DEBUG.INSERT-INTO\n".
            "doctrine.DEBUG.INSERT-INTO.context.foo.bar\n".
            "doctrine.DEBUG.INSERT-INTO.context.baz.qux\n", $message);
    }

    public function testDefLongFormatWith3WordsNoContextAndNoExtra()
    {
        $formatter = new StatsDFormatter(null, false, false, 3);
        $message = $formatter->format(new LogRecord(new \DateTimeImmutable(), 'doctrine', Level::Debug, 'INSERT INTO viaggio_calendar (enable, viaggio_id, calendar_id) VALUES (?, ?, ?)', ['foo' => 'bar', 'baz' => 'qux']));
        $this->assertEquals("doctrine.DEBUG.INSERT-INTO-viaggio-calendar\n", $message);
    }
    public function testDefRouteException()
    {
        $formatter = new StatsDFormatter();
        $message = $formatter->format(new LogRecord(new \DateTimeImmutable(), 'doctrine', Level::Debug, 'Symfony\Component\HttpKernel\Exception\NotFoundHttpException: No route found for "GET /ddd" (uncaught exception) at /xxxx/classes.php line 5062'));
        $this->assertEquals("doctrine.DEBUG.Symfony-Component-HttpKernel-Exception-NotFoundHttpException--No\n", $message);
    }

    public function testDefKernelException()
    {
        $formatter = new StatsDFormatter();
        $message = $formatter->format(new LogRecord(new \DateTimeImmutable(), 'doctrine', Level::Debug, 'Notified event "kernel.exception" to listener "Symfony\Component\HttpKernel\EventListener\ProfilerListener::onKernelException"', ['foo' => 'bar', 'baz' => 'qux']));

        $assert = "doctrine.DEBUG.Notified-event\n".
            "doctrine.DEBUG.Notified-event.context.foo.bar\n".
            "doctrine.DEBUG.Notified-event.context.baz.qux\n";

        $this->assertEquals($assert, $message);


    }
}
