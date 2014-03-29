<?php
/**
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
 *
 * @file
 */

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'TextExtracts',
	'author' => array( 'Max Semenik' ),
	'descriptionmsg' => 'textextracts-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:TextExtracts',
);

define( 'TEXT_EXTRACTS_INSTALLED', true );

$dir = __DIR__;
$wgMessagesDirs['TextExtracts'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['TextExtracts'] = "$dir/TextExtracts.i18n.php";


$wgAutoloadClasses['ExtractFormatter'] = "$dir/ExtractFormatter.php";
$wgAutoloadClasses['ApiQueryExtracts'] = "$dir/ApiQueryExtracts.php";
$wgAPIPropModules['extracts'] = 'ApiQueryExtracts';
$wgHooks['OpenSearchXml'][] = 'ApiQueryExtracts::onOpenSearchXml';
$wgHooks['UnitTestsList'][] = function( &$files ) {
	$files[] = __DIR__ . '/ExtractFormatterTest.php';
	return true;
};


// Configuration variables

/**
 * Selectors of content to be removed from HTML
 */
$wgExtractsRemoveClasses = array(
	'.toc', 'table', 'div', '.mw-editsection', 'sup.reference', 'span.coordinates',
	'span.geo-multi-punct', 'span.geo-nondefault', '.noexcerpt', '.error', '.nomobile',
);

/**
 * Whether this extension should provide its extracts for Extension:OpenSearchXml
 */
$wgExtractsExtendOpenSearchXml = false;
