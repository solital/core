<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Resource\Temporal\Temporal;

class TemporalTest extends TestCase
{
    public function testSetMicroseconds()
    {
        $res = Temporal::now()->setMicrosecond(46853)->getMicrosecond();
        $this->assertSame(46853, $res);

        $this->expectException(\DateRangeError::class);
        $res = Temporal::now()->setMicrosecond(1000000)->getMicrosecond();
    }

    public function testCreateFromTimestamp()
    {
        $res = Temporal::createFromTimeStamp(1703155440.628)->toFormat('Y-m-d h:i:s.u');
        $this->assertSame("2023-12-21 10:44:00.628000", $res);
        
        $res = Temporal::createFromTimeStamp(1703155440)->toFormat('Y-m-d h:i:s.u');
        $this->assertSame("2023-12-21 10:44:00.000000", $res);
    }

    public function testLeapYear() {
        $res = Temporal::createDatetime("2024-01-01")->isLeapYear();
        $this->assertTrue($res);

        $res = Temporal::createDatetime("2025-01-01")->isLeapYear();
        $this->assertFalse($res);
    }

    public function testICUFormat()
    {
        $res = Temporal::createDatetime("2024-01-01")->toi18nFormat("dd/MM/YYYY");
        $this->assertSame("01/01/2024", $res);

        $res = Temporal::createDatetime("2024-09-26")->getTextualDay();
        $this->assertSame("quinta-feira", $res);

        $res = Temporal::createDatetime("2024-09-26")->getTextualShortDay();
        $this->assertSame("qui.", $res);

        $res = Temporal::createDatetime("2024-09-26")->getTextualMonth();
        $this->assertSame("setembro", $res);

        $res = Temporal::createDatetime("2024-09-26")->getTextualShortMonth();
        $this->assertSame("set.", $res);

        Locale::setDefault('en_US');

        $res = Temporal::createDatetime("2024-09-26")->getTextualDay();
        $this->assertSame("Thursday", $res);

        $res = Temporal::createDatetime("2024-09-26")->getTextualShortDay();
        $this->assertSame("Thu", $res);

        $res = Temporal::createDatetime("2024-09-26")->getTextualMonth();
        $this->assertSame("September", $res);

        $res = Temporal::createDatetime("2024-09-26")->getTextualShortMonth();
        $this->assertSame("Sep", $res);
    }

    public function testLastDayOfMonth()
    {
        $res = Temporal::createDatetime("2024-09-26")->getLastDayOfMonth();
        $this->assertSame(30, $res);
    }

    public function testEasterDate()
    {
        $res = Temporal::createDatetime("2024-09-26")->getEasterDate();
        $this->assertSame("2024-03-31", $res);

        $res = Temporal::createDatetime("2024-09-26")->getEasterDateOrthodox();
        $this->assertSame("2024-05-05", $res);
    }
}