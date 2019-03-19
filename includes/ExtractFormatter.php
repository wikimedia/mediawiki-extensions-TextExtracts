<?php

namespace TextExtracts;

use DOMElement;
use HtmlFormatter\HtmlFormatter;

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
	 * @param string $text Text to convert
	 * @param bool $plainText Whether extract should be plaintext
	 */
	public function __construct( $text, $plainText ) {
		parent::__construct( HtmlFormatter::wrapHTML( $text ) );
		$this->plainText = $plainText;

		$this->setRemoveMedia( true );

		if ( $plainText ) {
			$this->flattenAllTags();
		} else {
			$this->flatten( [ 'a' ] );
		}
	}

	/**
	 * Performs final transformations (such as newline replacement for plaintext
	 * option) and returns resulting HTML.
	 *
	 * @param DOMElement|string|null $element ID of element to get HTML from.
	 * Ignored
	 * @return string Processed HTML
	 */
	public function getText( $element = null ) {
		$this->filterContent();
		$text = parent::getText();
		if ( $this->plainText ) {
			$text = html_entity_decode( $text );
			// replace nbsp with space
			$text = str_replace( "\u{00A0}", ' ', $text );
			// for Windows
			$text = str_replace( "\r", "\n", $text );
			// normalise newlines
			$text = preg_replace( "/\n{3,}/", "\n\n", $text );
		}
		return $text;
	}

	/**
	 * @param string $html HTML string to process
	 * @return string Processed HTML
	 */
	public function onHtmlReady( $html ) {
		if ( $this->plainText ) {
			$html = preg_replace( '/\s*(<h([1-6])\b)/i',
				"\n\n" . self::SECTION_MARKER_START . '$2' . self::SECTION_MARKER_END . '$1',
				$html
			);
		}
		return $html;
	}

	/**
	 * Removes content we've chosen to remove then removes class and style
	 * attributes from the remaining span elements.
	 *
	 * @return array Array of removed DOMElements
	 */
	public function filterContent() {
		$removed = parent::filterContent();

		$doc = $this->getDoc();
		$spans = $doc->getElementsByTagName( 'span' );

		/** @var DOMElement $span */
		foreach ( $spans as $span ) {
			$span->removeAttribute( 'class' );
			$span->removeAttribute( 'style' );
		}

		return $removed;
	}
}
