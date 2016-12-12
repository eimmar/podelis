<?php
/**
 * Created by PhpStorm.
 * User: eimantas
 * Date: 16.12.12
 * Time: 22.28
 */

namespace Tests\AppBundle\Service;


use AppBundle\Service\TestStarter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class TestStarterTest extends TestCase
{
    public function testSetTimeLimit()
    {
        $starter = new TestStarter(new Session(new MockArraySessionStorage()));

        $result = $starter->setTimeLimit('1 minute', 10);
        $this->assertEquals('10 minute', $result);
    }
}