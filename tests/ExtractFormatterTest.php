<?php
use TextExtracts\ExtractFormatter;

/**
 * @group TextExtracts
 */
class ExtractFormatterTest extends MediaWikiTestCase {
	/**
	 * Disabled for now due to Jenkins weirdness
	 * @dataProvider provideExtracts
	 */
	private function notReallyTestExtracts( $expected, $wikiText, $plainText ) {
		$title = Title::newFromText( 'Test' );
		$po = new ParserOptions();
		$po->setEditSection( true );
		$parser = new Parser();
		$text = $parser->parse( $wikiText, $title, $po )->getText();
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'textextracts' );
		$fmt = new ExtractFormatter( $text, $plainText, $config );
		$fmt->remove( '.metadata' ); // Will be added via $wgExtractsRemoveClasses on WMF
		$text = trim( $fmt->getText() );
		$this->assertEquals( $expected, $text );
	}

	public function provideExtracts() {
		$dutch = "'''Dutch''' (<span class=\"unicode haudio\" style=\"white-space:nowrap;\"><span class=\"fn\">"
			. "[[File:Loudspeaker.svg|11px|link=File:nl-Nederlands.ogg|About this sound]]&nbsp;[[:Media:nl-Nederlands.ogg|''Nederlands'']]"
			. "</span>&nbsp;<small class=\"metadata audiolinkinfo\" style=\"cursor:help;\">([[Wikipedia:Media help|<span style=\"cursor:help;\">"
			. "help</span>]]·[[:File:nl-Nederlands.ogg|<span style=\"cursor:help;\">info</span>]])</small></span>) is a"
			. " [[West Germanic languages|West Germanic language]] and the native language of most of the population of the [[Netherlands]]";

		return array(
			array(
				"Dutch ( Nederlands ) is a West Germanic language and the native language of most of the population of the Netherlands",
				$dutch,
				true,
			),

			array(
				"<p><span><span lang=\"baz\">qux</span></span>\n</p>",
				'<span class="foo"><span lang="baz">qux</span></span>',
				false,
			),
			array(
				"<p><span><span lang=\"baz\">qux</span></span>\n</p>",
				'<span style="foo: bar;"><span lang="baz">qux</span></span>',
				false,
			),
			array(
				"<p><span><span lang=\"qux\">quux</span></span>\n</p>",
				'<span class="foo"><span style="bar: baz;" lang="qux">quux</span></span>',
				false,
			),
		);
	}

	/**
	 * @dataProvider provideGetFirstSentences
	 * @param $text
	 * @param $sentences
	 * @param $expected
	 */
	public function testGetFirstSentences( $text, $sentences, $expected ) {
		$this->assertEquals( $expected, ExtractFormatter::getFirstSentences( $text, $sentences ) );
	}

	private function sentences( $n = 1000000 ) {
		$sentences = array(
			'Foo is a bar.',
			'Such a smart boy.',
			'But completely useless.',
		);
		return implode( ' ', array_slice( $sentences, 0, $n ) );
	}

	public function provideGetFirstSentences() {
		return array(
			array(
				$this->sentences(),
				2,
				$this->sentences( 2 ),
			),
			array(
				$this->sentences(),
				1,
				$this->sentences( 1 ),
			),
			array(
				$this->sentences( 1 ),
				1,
				$this->sentences( 1 ),
			),
			array(
				$this->sentences( 1 ),
				2,
				$this->sentences( 1 ),
			),
			array(
				'',
				1,
				'',
			),
			/* @fixme
			array(
				'P.J. Harvey is a singer. She is awesome!',
				1,
				'P.J. Harvey is a singer.',
			),*/
		);
	}

	/**
	 * @dataProvider provideGetFirstChars
	 * @param $text
	 * @param $chars
	 * @param $expected
	 */
	public function testGetFirstChars( $text, $chars, $expected ) {
		$this->assertEquals( $expected, ExtractFormatter::getFirstChars( $text, $chars ) );
	}

	public function provideGetFirstChars() {
		$text = 'Lullzy lulz are lullzy!';
		return array(
			//array( $text, 0, '' ),
			array( $text, 100, $text ),
			array( $text, 1, 'Lullzy' ),
			array( $text, 6, 'Lullzy' ),
			//array( $text, 7, 'Lullzy' ),
			array( $text, 8, 'Lullzy lulz' ),
		);
	}
}
