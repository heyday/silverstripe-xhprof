<?php

class HeydayXhprofTest extends SapphireTest
{

	public function testAppName()
	{


		HeydayXhprof::set_app_name('Something');

		$this->assertEquals( 'Something', HeydayXhprof::get_app_name() );


	}

	public function testStartEnd()
	{

		HeydayXhprof::start( 'Start' );

		$this->assertEquals( 'Start', HeydayXhprof::get_app_name() );

		$this->assertTrue( HeydayXhprof::is_started() );

		HeydayXhprof::end();

		$this->assertFalse( HeydayXhprof::is_started() );

		$app = DataObject::get_one( 'HeydayXhprofApp', "Name = 'Start'" );

		$this->assertTrue( $app instanceof HeydayXhprofApp );
		$this->assertEquals( $app->SafeName(), 'start' );

		$runs = $app->Runs();

		$this->assertTrue( $runs instanceof DataObjectSet );
		$this->assertTrue( $runs->First() instanceof HeydayXhprofRun );

		HeydayXhprof::start( 'New Start Something' );
		HeydayXhprof::end();

		$app = DataObject::get_one( 'HeydayXhprofApp', "Name = 'New Start Something'" );
		$this->assertTrue( $app instanceof HeydayXhprofApp );
		$this->assertEquals( $app->SafeName(), 'new-start-something' );

		$apps = DataObject::get( 'HeydayXhprofApp' );

		$this->assertEquals( count($apps), 2 );

	}

	public function testProbability()
	{

		HeydayXhprof::set_probability(1/10);

		$this->assertEquals(1/10, HeydayXhprof::get_probability());


		HeydayXhprof::set_probability(10);

		$this->assertEquals(1, HeydayXhprof::get_probability());


		HeydayXhprof::set_probability(-10);

		$this->assertEquals(0, HeydayXhprof::get_probability());


		HeydayXhprof::set_probability(1);

		$this->assertTrue(HeydayXhprof::test_probability());


		HeydayXhprof::set_probability(0);

		$this->assertFalse(HeydayXhprof::test_probability());


		function run() {

			$sum = 0;
			$tot = 100000;

			for ( $i = 0; $i < $tot; $i++ ) {

				if ( HeydayXhprof::test_probability() ) {

					$sum++;

				}

			}

			return round( $sum / $tot, 1 );

		}

		HeydayXhprof::set_probability( 1 / 10 );

		$this->assertEquals( run(), round( HeydayXhprof::get_probability(), 1 ) );

		HeydayXhprof::set_probability( 1 / 100 );

		$this->assertEquals( run(), round( HeydayXhprof::get_probability(), 1 ) );

		HeydayXhprof::set_probability( 1 / 1000 );

		$this->assertEquals( run(), round( HeydayXhprof::get_probability(), 1 ) );

		HeydayXhprof::set_probability( 1 / 2 );

		$this->assertEquals( run(), round( HeydayXhprof::get_probability(), 1 ) );

		HeydayXhprof::set_probability( 1 / 3 );

		$this->assertEquals( run(), round( HeydayXhprof::get_probability(), 1 ) );

		HeydayXhprof::set_probability( 2 / 3 );

		$this->assertEquals( run(), round( HeydayXhprof::get_probability(), 1 ) );

	}

	public function testExclusions()
	{

		$exclusions = array(
			'hello'
		);

		HeydayXhprof::set_exclusions( $exclusions );

		$this->assertEquals( $exclusions, HeydayXhprof::get_exclusions() );

		$exclusions = array(
			'hello',
			'something'
		);

		HeydayXhprof::add_exclusion( 'something' );

		$this->assertEquals( $exclusions, HeydayXhprof::get_exclusions() );


		$exclusions = array(
			'hello',
			'something',
			'bob'
		);

		$newExclusions = array(
			'bob'
		);

		HeydayXhprof::add_exclusions( $newExclusions );

		$this->assertEquals( $exclusions, HeydayXhprof::get_exclusions() );

		$this->assertTrue( HeydayXhprof::is_excluded('bob') );

		$this->assertTrue( HeydayXhprof::is_excluded('hello') );

		$this->assertFalse( HeydayXhprof::is_excluded('barbie') );


		HeydayXhprof::set_probability(1);

		$this->assertTrue( HeydayXhprof::is_allowed('barbie') );
		$this->assertFalse( HeydayXhprof::is_allowed('/bob/') );

	}
	
}