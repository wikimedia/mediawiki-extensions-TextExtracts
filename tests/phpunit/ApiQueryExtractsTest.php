<?php

namespace TextExtracts\Test;

use MediaWikiCoversValidator;
use TextExtracts\ApiQueryExtracts;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \TextExtracts\ApiQueryExtracts
 * @group TextExtracts
 */
class ApiQueryExtractsTest extends \PHPUnit\Framework\TestCase {
	use MediaWikiCoversValidator;

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

	public function testSelfDocumentation() {
		/** @var ApiQueryExtracts $instance */
		$instance = TestingAccessWrapper::newFromObject( $this->newInstance() );

		$this->assertInternalType( 'string', $instance->getCacheMode( [] ) );
		$this->assertNotEmpty( $instance->getExamplesMessages() );
		$this->assertInternalType( 'string', $instance->getHelpUrls() );

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

	/**
	 * @dataProvider provideFirstSectionsToExtract
	 */
	public function testGetFirstSection( $text, $isPlainText, $expected ) {
		/** @var ApiQueryExtracts $instance */
		$instance = TestingAccessWrapper::newFromObject( $this->newInstance() );

		$this->assertSame( $expected, $instance->getFirstSection( $text, $isPlainText ) );
	}

	public function provideFirstSectionsToExtract() {
		return [
			'Plain text match' => [
				"First\nsection \1\2... \1\2...",
				true,
				"First\nsection ",
			],
			'Plain text without a match' => [
				'Example\1\2...',
				true,
				'Example\1\2...',
			],

			'HTML match' => [
				"First\nsection <h1>...<h2>...",
				false,
				"First\nsection ",
			],
			'HTML without a match' => [
				'Example <h11>...',
				false,
				'Example <h11>...',
			],
		];
	}

	/**
	 * @dataProvider provideSectionsToFormat
	 */
	public function testDoSections( $text, $format, $expected ) {
		/** @var ApiQueryExtracts $instance */
		$instance = TestingAccessWrapper::newFromObject( $this->newInstance() );
		$instance->params = [ 'sectionformat' => $format ];

		$this->assertSame( $expected, $instance->doSections( $text ) );
	}

	public function provideSectionsToFormat() {
		$level = 3;
		$marker = "\1\2$level\2\1";

		return [
			'Raw' => [
				"$marker Headline\t\nNext line",
				'raw',
				"$marker Headline\t\nNext line",
			],
			'Wiki text' => [
				"$marker Headline\t\nNext line",
				'wiki',
				"\n=== Headline ===\nNext line",
			],
			'Plain text' => [
				"$marker Headline\t\nNext line",
				'plain',
				"\nHeadline\nNext line",
			],

			'Multiple matches' => [
				"${marker}First\n${marker}Second",
				'wiki',
				"\n=== First ===\n\n=== Second ===",
			],
		];
	}
}
