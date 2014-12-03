<?php

class HeydayXhprofTest extends SapphireTest
{

    public function setUpOnce() {
        HeydayXhprof::setLinkMode(HeydayXhprof::LINK_MODE_NONE);
        parent::setUpOnce();
    }

    public function testAppName()
    {

        HeydayXhprof::setAppName('Something');

        $this->assertEquals('Something', HeydayXhprof::getAppName());

    }

    public function testProbability()
    {

        HeydayXhprof::setProbability(1 / 10);

        $this->assertEquals(1 / 10, HeydayXhprof::getProbability());

        HeydayXhprof::setProbability(10);

        $this->assertEquals(1, HeydayXhprof::getProbability());

        HeydayXhprof::setProbability(-10);

        $this->assertEquals(0, HeydayXhprof::getProbability());

        HeydayXhprof::setProbability(1);

        $this->assertTrue(HeydayXhprof::testProbability());

        HeydayXhprof::setProbability(0);

        $this->assertFalse(HeydayXhprof::testProbability());

        function run()
        {

            $sum = 0;
            $tot = 100000;

            for ($i = 0; $i < $tot; $i++) {

                if (HeydayXhprof::testProbability()) {

                    $sum++;

                }

            }

            return round($sum / $tot, 1);

        }

        HeydayXhprof::setProbability(1 / 10);

        $this->assertEquals(run(), round(HeydayXhprof::getProbability(), 1));

        HeydayXhprof::setProbability(1 / 100);

        $this->assertEquals(run(), round(HeydayXhprof::getProbability(), 1));

        HeydayXhprof::setProbability(1 / 1000);

        $this->assertEquals(run(), round(HeydayXhprof::getProbability(), 1));

        HeydayXhprof::setProbability(1 / 2);

        $this->assertEquals(run(), round(HeydayXhprof::getProbability(), 1));

        HeydayXhprof::setProbability(1 / 3);

        $this->assertEquals(run(), round(HeydayXhprof::getProbability(), 1));

        HeydayXhprof::setProbability(2 / 3);

        $this->assertEquals(run(), round(HeydayXhprof::getProbability(), 1));

    }

    public function testExclusions()
    {

        $exclusions = array(
            'hello'
        );

        HeydayXhprof::setExclusions($exclusions);

        $this->assertEquals($exclusions, HeydayXhprof::getExclusions());

        $exclusions = array(
            'hello',
            'something'
        );

        HeydayXhprof::addExclusion('something');

        $this->assertEquals($exclusions, HeydayXhprof::getExclusions());

        $exclusions = array(
            'hello',
            'something',
            'bob'
        );

        $newExclusions = array(
            'bob'
        );

        HeydayXhprof::addExclusions($newExclusions);

        $this->assertEquals($exclusions, HeydayXhprof::getExclusions());

        $this->assertTrue(HeydayXhprof::isExcluded('bob'));

        $this->assertTrue(HeydayXhprof::isExcluded('hello'));

        $this->assertTrue(HeydayXhprof::isExcluded('Hello'));

        $this->assertFalse(HeydayXhprof::isExcluded('barbie'));

        HeydayXhprof::setProbability(1);

        $this->assertTrue(HeydayXhprof::isAllowed('barbie'));
        $this->assertFalse(HeydayXhprof::isAllowed('/bob/'));

    }

}
