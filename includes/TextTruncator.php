<?php

namespace TextExtracts;

/**
 * @license GPL-2.0-or-later
 */
class TextTruncator {

	/**
	 * Returns no more than the given number of sentences
	 *
	 * @param string $text Source text to extract from
	 * @param int $requestedSentenceCount Maximum number of sentences to extract
	 * @return string
	 */
	public static function getFirstSentences( $text, $requestedSentenceCount ) {
		if ( $requestedSentenceCount <= 0 ) {
			return '';
		}

		// Based on code from OpenSearchXml by Brion Vibber
		$endchars = [
			// regular ASCII
			'[^\p{Lu}]\.(?:[ \n]|$)', '[\!\?](?:[ \n]|$)',
			// full-width ideographic full-stop
			'。',
			// double-width roman forms
			'．', '！', '？',
			// half-width ideographic full stop
			'｡',
		];

		$endgroup = implode( '|', $endchars );
		$regexp = "/($endgroup)+/u";

		$matches = [];
		$res = preg_match_all( $regexp, $text, $matches, PREG_OFFSET_CAPTURE );

		if ( $res ) {
			$index = min( $requestedSentenceCount, $res ) - 1;
			list( $tail, $length ) = $matches[0][ $index ];
			// PCRE returns raw offsets, so using substr() instead of mb_substr()
			$text = substr( $text, 0, $length ) . trim( $tail );
		} else {
			// Just return the first line
			$lines = explode( "\n", $text, 2 );
			$text = trim( $lines[0] );
		}
		return $text;
	}

	/**
	 * Returns no more than a requested number of characters, preserving words
	 *
	 * @param string $text Source text to extract from
	 * @param int $requestedLength Maximum number of characters to return
	 * @return string
	 */
	public static function getFirstChars( $text, $requestedLength ) {
		if ( $requestedLength <= 0 ) {
			return '';
		}
		$length = mb_strlen( $text );
		if ( $length <= $requestedLength ) {
			return $text;
		}
		$pattern = "#^[\\w/]*>?#su";
		preg_match( $pattern, mb_substr( $text, $requestedLength ), $m );
		return mb_substr( $text, 0, $requestedLength ) . $m[0];
	}

}
