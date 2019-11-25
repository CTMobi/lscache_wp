<?php
namespace LiteSpeed\CLI;
defined( 'WPINC' ) || exit;

use LiteSpeed\Log;
use LiteSpeed\Cloud;
use WP_CLI;

/**
 * QUIC.cloud API CLI
 */
class Online
{
	private $__cloud;

	public function __construct()
	{
		Log::debug( 'CLI_Cloud init' );

		$this->__cloud = Cloud::get_instance();
	}

	/**
	 * Gen key from cloud server
	 *
	 * ## OPTIONS
	 *
	 * ## EXAMPLES
	 *
	 *     # Generate domain API key from Cloud server
	 *     $ wp litespeed-online init
	 *
	 */
	public function init()
	{
		$key = $this->__cloud->gen_key();
		if ( $key ) {
			WP_CLI::success( 'key = ' . $key );
		}
	}

	/**
	 * Sync data from cloud server
	 *
	 * ## OPTIONS
	 *
	 * ## EXAMPLES
	 *
	 *     # Sync online service usage info
	 *     $ wp litespeed-online sync
	 *
	 */
	public function sync()
	{
		$json = $this->__cloud->sync_usage();

		WP_CLI::success( 'Sync successfully' );

		$list = array();
		foreach ( Cloud::$SERVICES as $v ) {
			$list[] = array(
				'key' => $v,
				'used' => ! empty( $json[ 'usage.' . $v ][ 'used' ] ) ? $json[ 'usage.' . $v ][ 'used' ] : 0,
				'quota' => ! empty( $json[ 'usage.' . $v ][ 'quota' ] ) ? $json[ 'usage.' . $v ][ 'quota' ] : 0,
				'PayAsYouGo_Used' => ! empty( $json[ 'usage.' . $v ][ 'pag_used' ] ) ? $json[ 'usage.' . $v ][ 'pag_used' ] : 0,
				'PayAsYouGo_Balance' => ! empty( $json[ 'usage.' . $v ][ 'pag_bal' ] ) ? $json[ 'usage.' . $v ][ 'pag_bal' ] : 0,
			);
		}

		WP_CLI\Utils\format_items( 'table', $list, array( 'key', 'used', 'quota', 'PayAsYouGo_Used', 'PayAsYouGo_Balance' ) );
	}

	/**
	 * List all services
	 *
	 * ## OPTIONS
	 *
	 * ## EXAMPLES
	 *
	 *     # List all services tag
	 *     $ wp litespeed-online services
	 *
	 */
	public function services()
	{
		$list = array();
		foreach ( Cloud::$SERVICES as $v ) {
			$list[] = array(
				'service' => $v,
			);
		}

		WP_CLI\Utils\format_items( 'table', $list, array( 'service' ) );
	}

	/**
	 * List all cloud servers in use
	 *
	 * ## OPTIONS
	 *
	 * ## EXAMPLES
	 *
	 *     # List all cloud servers in use
	 *     $ wp litespeed-online nodes
	 *
	 */
	public function nodes()
	{
		$json = Cloud::get_summary();

		$list = array();
		foreach ( Cloud::$SERVICES as $v ) {
			$list[] = array(
				'service' => $v,
				'server' => ! empty( $json[ 'server.' . $v ] ) ? $json[ 'server.' . $v ] : '',
			);
		}

		WP_CLI\Utils\format_items( 'table', $list, array( 'service', 'server' ) );
	}

	/**
	 * Detect closest Node server for current service
	 *
	 * ## OPTIONS
	 *
	 * ## EXAMPLES
	 *
	 *     # Detect closest Node for one service
	 *     $ wp litespeed-online ping img_optm
	 *
	 */
	public function ping( $param )
	{
		$svc = $param[ 0 ];
		$json = $this->__cloud->detect_cloud( $svc );
		WP_CLI::success( 'Updated closest server.' );
		WP_CLI::log( 'svc = ' . $svc );
		WP_CLI::log( 'node = ' . $json );
	}

}
