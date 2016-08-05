<?php
use TextExtracts\ExtractFormatter;

/**
 * @group TextExtracts
 */
class ExtractFormatterTest extends MediaWikiTestCase {
	/**
	 * @dataProvider provideExtracts
	 */
	public function testExtracts( $expected, $text, $plainText ) {
		$title = Title::newFromText( 'Test' );
		$po = new ParserOptions();
		$po->setEditSection( true );
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'textextracts' );
		$fmt = new ExtractFormatter( $text, $plainText, $config );
		$fmt->remove( '.metadata' ); // Will be added via $wgExtractsRemoveClasses on WMF
		$text = trim( $fmt->getText() );
		$this->assertEquals( $expected, $text );
	}

	public function provideExtracts() {
		$dutch = '<b>Dutch</b> (<span class="unicode haudio" style="white-space:nowrap;"><span class="fn"><a href="/wiki/File:Nl-Nederlands.ogg" title="About this sound"><img alt="About this sound" src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Loudspeaker.svg/11px-Loudspeaker.svg.png" width="11" height="11" srcset="https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Loudspeaker.svg/17px-Loudspeaker.svg.png 1.5x, https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Loudspeaker.svg/22px-Loudspeaker.svg.png 2x" /></a>&#160;<a href="https://upload.wikimedia.org/wikipedia/commons/d/db/Nl-Nederlands.ogg" class="internal" title="Nl-Nederlands.ogg"><i>Nederlands</i></a></span>&#160;<small class="metadata audiolinkinfo" style="cursor:help;">(<a href="/w/index.php?title=Wikipedia:Media_help&amp;action=edit&amp;redlink=1" class="new" title="Wikipedia:Media help (page does not exist)"><span style="cursor:help;">help</span></a>·<a href="/wiki/File:Nl-Nederlands.ogg" title="File:Nl-Nederlands.ogg"><span style="cursor:help;">info</span></a>)</small></span>) is a <a href="/w/index.php?title=West_Germanic_languages&amp;action=edit&amp;redlink=1" class="new" title="West Germanic languages (page does not exist)">West Germanic language</a> and the native language of most of the population of the <a href="/w/index.php?title=Netherlands&amp;action=edit&amp;redlink=1" class="new" title="Netherlands (page does not exist)">Netherlands</a>';

		return array(
			array(
				"Dutch ( Nederlands ) is a West Germanic language and the native language of most of the population of the Netherlands",
				$dutch,
				true,
			),

			array(
				"<span><span lang=\"baz\">qux</span></span>",
				'<span class="foo"><span lang="baz">qux</span></span>',
				false,
			),
			array(
				"<span><span lang=\"baz\">qux</span></span>",
				'<span style="foo: bar;"><span lang="baz">qux</span></span>',
				false,
			),
			array(
				"<span><span lang=\"qux\">quux</span></span>",
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

	public function provideGetFirstSentences() {
		return array(
			array(
				'Foo is a bar. Such a smart boy. But completely useless.',
				2,
				'Foo is a bar. Such a smart boy.',
			),
			array(
				'Foo is a bar. Such a smart boy. But completely useless.',
				1,
				'Foo is a bar.',
			),
			array(
				'Foo is a bar. Such a smart boy.',
				2,
				'Foo is a bar. Such a smart boy.',
			),
			array(
				'Foo is a bar.',
				1,
				'Foo is a bar.',
			),
			array(
				'Foo is a bar.',
				2,
				'Foo is a bar.',
			),
			array(
				'',
				1,
				'',
			),
			// Exclamation points too!!!
			array(
				'Foo is a bar! Such a smart boy! But completely useless!',
				1,
				'Foo is a bar!',
			),
			// A tricky one
			array(
				"Acid phosphatase (EC 3.1.3.2) is a chemical you don't want to mess with. Polyvinyl acetate, however, is another story.",
				1,
				"Acid phosphatase (EC 3.1.3.2) is a chemical you don't want to mess with.",
			),
			// Bug T118621
			array(
				'Foo was born in 1977. He enjoys listening to Siouxsie and the Banshees.',
				1,
				'Foo was born in 1977.',
			),
			// Bug T115795 - Test no cropping after initials
			array(
				'P.J. Harvey is a singer. She is awesome!',
				1,
				'P.J. Harvey is a singer.',
			),
			// Bug T115817 - Non-breaking space is not a delimiter
			array(
				html_entity_decode( 'Pigeons (lat.&nbsp;Columbidae) are birds. They primarily feed on seeds.' ),
				1,
				html_entity_decode( 'Pigeons (lat.&nbsp;Columbidae) are birds.' ),
			),
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
