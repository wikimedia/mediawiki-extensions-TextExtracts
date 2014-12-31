<?php

namespace TextExtracts;


use ApiMain;
use ConfigFactory;
use FauxRequest;

class Hooks {
	/**
	 * ApiOpenSearchSuggest hook handler
	 * @param array $results
	 * @return bool
	 */
	public static function onApiOpenSearchSuggest( &$results ) {
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'textextracts' );
		if ( !$config->get( 'ExtractsExtendOpenSearchXml' ) || !count( $results ) ) {
			return true;
		}
		$pageIds = array_keys( $results );
		$api = new ApiMain( new FauxRequest(
			array(
				'action' => 'query',
				'prop' => 'extracts',
				'explaintext' => true,
				'exintro' => true,
				'exlimit' => count( $results ),
				'pageids' => implode( '|', $pageIds ),
			) )
		);
		$api->execute();
		$data = $api->getResultData();
		foreach ( $pageIds as $id ) {
			if ( isset( $data['query']['pages'][$id]['extract']['*'] ) ) {
				$results[$id]['extract'] = $data['query']['pages'][$id]['extract']['*'];
				$results[$id]['extract trimmed'] = false;
			}
		}
		return true;
	}
} 