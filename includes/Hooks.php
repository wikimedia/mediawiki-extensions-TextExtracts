<?php

namespace TextExtracts;

use ApiMain;
use ApiResult;
use FauxRequest;
use MediaWiki\MediaWikiServices;

class Hooks {

	/**
	 * ApiOpenSearchSuggest hook handler
	 * @param array $results
	 * @return bool
	 */
	public static function onApiOpenSearchSuggest( &$results ) {
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'textextracts' );
		if ( !$config->get( 'ExtractsExtendOpenSearchXml' ) || !count( $results ) ) {
			return true;
		}
		$pageIds = array_keys( $results );
		$api = new ApiMain( new FauxRequest(
			[
				'action' => 'query',
				'prop' => 'extracts',
				'explaintext' => true,
				'exintro' => true,
				'exlimit' => count( $results ),
				'pageids' => implode( '|', $pageIds ),
			] )
		);
		$api->execute();
		$data = $api->getResult()->getResultData( [ 'query', 'pages' ] );
		foreach ( $pageIds as $id ) {
			$contentKey = isset( $data[$id]['extract'][ApiResult::META_CONTENT] )
				? $data[$id]['extract'][ApiResult::META_CONTENT]
				: '*';
			if ( isset( $data[$id]['extract'][$contentKey] ) ) {
				$results[$id]['extract'] = $data[$id]['extract'][$contentKey];
				$results[$id]['extract trimmed'] = false;
			}
		}
		return true;
	}
}
