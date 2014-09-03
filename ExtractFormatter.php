<?php

/**
 * Provides text-only or limited-HTML extracts of page HTML
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */
class ExtractFormatter extends HtmlFormatter {
	const SECTION_MARKER_START = "\1\2";
	const SECTION_MARKER_END = "\2\1";

	private $plainText;

	/**
	 * @param string $text: Text to convert
	 * @param bool $plainText: Whether extract should be plaintext
	 * @param Config $config
	 */
	public function __construct( $text, $plainText, Config $config ) {
		wfProfileIn( __METHOD__ );
		parent::__construct( HtmlFormatter::wrapHTML( $text ) );
		$this->plainText = $plainText;

		$this->setRemoveMedia( true );
		$this->remove( $config->get( 'ExtractsRemoveClasses' ) );

		if ( $plainText ) {
			$this->flattenAllTags();
		} else {
			$this->flatten( array( 'span', 'a' ) );
		}
		wfProfileOut( __METHOD__ );
	}

	public function getText( $dummy = null ) {
		wfProfileIn( __METHOD__ );
		$this->filterContent();
		$text = parent::getText();
		if ( $this->plainText ) {
			$text = html_entity_decode( $text );
			$text = str_replace( "\xC2\xA0", ' ', $text ); // replace nbsp with space
			$text = str_replace( "\r", "\n", $text ); // for Windows
			$text = preg_replace( "/\n{3,}/", "\n\n", $text ); // normalise newlines
		}
		wfProfileOut( __METHOD__ );
		return $text;
	}

	public function onHtmlReady( $html ) {
		wfProfileIn( __METHOD__ );
		if ( $this->plainText ) {
			$html = preg_replace( '/\s*(<h([1-6])\b)/i',
				"\n\n" . self::SECTION_MARKER_START . '$2' . self::SECTION_MARKER_END . '$1' ,
				$html
			);
		}
		wfProfileOut( __METHOD__ );
		return $html;
	}

	/**
	 * Returns no more than the given number of sentences
	 *
	 * @param string $text
	 * @param int $requestedSentenceCount
	 * @return string
	 */
	public static function getFirstSentences( $text, $requestedSentenceCount ) {
		wfProfileIn( __METHOD__ );
		// Based on code from OpenSearchXml by Brion Vibber
		$endchars = array(
			'([^\d])\.\s', '\!\s', '\?\s', // regular ASCII
			'。', // full-width ideographic full-stop
			'．', '！', '？', // double-width roman forms
			'｡', // half-width ideographic full stop
			);

		$endgroup = implode( '|', $endchars );
		$end = "(?:$endgroup)";
		$sentence = ".+?$end+";
		$regexp = "/^($sentence){1,{$requestedSentenceCount}}/u";
		$matches = array();
		$res = preg_match( $regexp, $text, $matches );
		if( $res ) {
			$text = trim( $matches[0] );
		} else {
			if ( $res === false ) {
				throw new MWException( __METHOD__ . "() error compiling regular expression $regexp" );
			}
			// Just return the first line
			$lines = explode( "\n", $text );
			$text = trim( $lines[0] );
		}
		wfProfileOut( __METHOD__ );
		return $text;
	}

	/**
	 * Returns no more than a requested number of characters, preserving words
	 *
	 * @param string $text
	 * @param int $requestedLength
	 * @return string
	 */
	public static function getFirstChars( $text, $requestedLength ) {
		wfProfileIn( __METHOD__ );
		$length = mb_strlen( $text );
		if ( $length <= $requestedLength ) {
			wfProfileOut( __METHOD__ );
			return $text;
		}
		$pattern = "#^.{{$requestedLength}}[\\w/]*>?#su";
		preg_match( $pattern, $text, $m );
		wfProfileOut( __METHOD__ );
		return $m[0];
	}
}
