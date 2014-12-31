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


$wgConfigRegistry['textextracts'] = 'GlobalVarConfig::newInstance';
$wgAutoloadClasses['TextExtracts\ApiQueryExtracts'] = "$dir/includes/ApiQueryExtracts.php";
$wgAutoloadClasses['TextExtracts\ExtractFormatter'] = "$dir/includes/ExtractFormatter.php";
$wgAutoloadClasses['TextExtracts\Hooks'] = "$dir/includes/Hooks.php";
$wgAPIPropModules['extracts'] = array(
	'class' => 'TextExtracts\ApiQueryExtracts',
	'factory' => 'wfNewApiQueryExtracts'
);

/**
 * @param ApiQuery $query
 * @param string $action
 * @return TextExtracts\ApiQueryExtracts
 */
function wfNewApiQueryExtracts( $query, $action ) {
	$config = ConfigFactory::getDefaultInstance()->makeConfig( 'textextracts' );
	return new TextExtracts\ApiQueryExtracts( $query, $action, $config );
}

$wgHooks['OpenSearchXml'][] = 'TextExtracts\Hooks::onApiOpenSearchSuggest';
$wgHooks['ApiOpenSearchSuggest'][] = 'TextExtracts\Hooks::onApiOpenSearchSuggest';
$wgHooks['UnitTestsList'][] = function( &$files ) {
	$files[] = __DIR__ . '/tests/ExtractFormatterTest.php';
	return true;
};


// Configuration variables

/**
 * Selectors of content to be removed from HTML
 */
$wgExtractsRemoveClasses = array(
	// These usually represent content that is not part of usual text flow
	'table', 'div', 'ul.gallery',
	// Section edit links
	'.mw-editsection',
	// Extension:Cite references
	'sup.reference',
	// Used by parser for various wikitext errors, no point having them in extracts
	'.error',
	// Ignored in MobileFrontend. @todo: decide if it's really needed
	'.nomobile',
	// Elements marked not to show up in the print version
	'.noprint',
	// Class specifically for this extension
	'.noexcerpt',
);

/**
 * Whether this extension should provide its extracts for OpenSearch
 */
$wgExtractsExtendOpenSearchXml = false;
