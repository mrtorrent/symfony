<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\HttpKernel\Debug;

use Symfony\Component\HttpKernel\Debug\StopwatchEvent;

/**
 * StopwatchEventTest
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class StopwatchEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetOrigin()
    {
        $event = new StopwatchEvent(12);
        $this->assertEquals(12, $event->getOrigin());
    }

    public function testGetCategory()
    {
        $event = new StopwatchEvent(microtime(true) * 1000);
        $this->assertEquals('default', $event->getCategory());

        $event = new StopwatchEvent(time(), 'cat');
        $this->assertEquals('cat', $event->getCategory());
    }

    public function testGetPeriods()
    {
        $event = new StopwatchEvent(microtime(true) * 1000);
        $this->assertEquals(array(), $event->getPeriods());

        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        $event->stop();
        $this->assertEquals(1, count($event->getPeriods()));

        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        $event->stop();
        $event->start();
        $event->stop();
        $this->assertEquals(2, count($event->getPeriods()));
    }

    public function testLap()
    {
        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        $event->lap();
        $event->stop();
        $this->assertEquals(2, count($event->getPeriods()));
    }

    public function testTotalTime()
    {
        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        usleep(10000);
        $event->stop();
        $total = $event->getTotalTime();
        $this->assertTrue($total >= 10 && $total <= 20);

        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        usleep(10000);
        $event->stop();
        $event->start();
        usleep(10000);
        $event->stop();
        $total = $event->getTotalTime();
        $this->assertTrue($total >= 20 && $total <= 30);
    }

    /**
     * @expectedException \LogicException
     */
    public function testStopWithoutStart()
    {
        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->stop();
    }

    public function testEnsureStopped()
    {
        // this also test overlap between two periods
        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        usleep(10000);
        $event->start();
        usleep(10000);
        $event->ensureStopped();
        $total = $event->getTotalTime();
        $this->assertTrue($total >= 30 && $total <= 40);
    }

    public function testStartTime()
    {
        $event = new StopwatchEvent(microtime(true) * 1000);
        $this->assertEquals(0, $event->getStartTime());

        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        $event->stop();
        $this->assertEquals(0, $event->getStartTime());

        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        usleep(10000);
        $event->stop();
        $start = $event->getStartTime();
        $this->assertTrue($start >= 0 && $start <= 20);
    }

    public function testEndTime()
    {
        $event = new StopwatchEvent(microtime(true) * 1000);
        $this->assertEquals(0, $event->getEndTime());

        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        $this->assertEquals(0, $event->getEndTime());

        $event = new StopwatchEvent(microtime(true) * 1000);
        $event->start();
        usleep(10000);
        $event->stop();
        $event->start();
        usleep(10000);
        $event->stop();
        $end = $event->getEndTime();
        $this->assertTrue($end >= 20 && $end <= 30);
    }
}
