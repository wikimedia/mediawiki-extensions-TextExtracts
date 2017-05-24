<?php
namespace TextExtracts\Test;

use PHPUnit_Framework_TestCase;
use TextExtracts\ApiQueryExtracts;

/**
 * @covers ApiQueryExtracts
 * @group TextExtracts
 */
class ApiQueryExtractsTest extends PHPUnit_Framework_TestCase {
	private function newInstance() {
		$context = $this->getMockBuilder( 'IContextSource' )
			->disableOriginalConstructor()
			->getMock();

		$main = $this->getMockBuilder( 'ApiMain' )
			->disableOriginalConstructor()
			->getMock();
		$main->expects( $this->once() )
			->method( 'getContext' )
			->will( $this->returnValue( $context ) );

		$query = $this->getMockBuilder( 'ApiQuery' )
			->disableOriginalConstructor()
			->getMock();
		$query->expects( $this->once() )
			->method( 'getMain' )
			->will( $this->returnValue( $main ) );

		$config = $this->getMockBuilder( 'Config' )
			->disableOriginalConstructor()
			->getMock();

		return new ApiQueryExtracts( $query, '', $config );
	}

	public function testGetAllowedParams() {
		$instance = $this->newInstance();
		$params = $instance->getAllowedParams();
		$this->assertInternalType( 'array', $params );
		$this->assertArrayHasKey( 'chars', $params );
		$this->assertEquals( $params['chars'][\ApiBase::PARAM_MIN], 1 );
		$this->assertEquals( $params['chars'][\ApiBase::PARAM_MAX], 1200 );
		$this->assertArrayHasKey( 'limit', $params );
		$this->assertEquals( $params['limit'][\ApiBase::PARAM_DFLT], 20 );
		$this->assertEquals( $params['limit'][\ApiBase::PARAM_TYPE], 'limit' );
		$this->assertEquals( $params['limit'][\ApiBase::PARAM_MIN], 1 );
		$this->assertEquals( $params['limit'][\ApiBase::PARAM_MAX], 20 );
		$this->assertEquals( $params['limit'][\ApiBase::PARAM_MAX2], 20 );
	}
}
