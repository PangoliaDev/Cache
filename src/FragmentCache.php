<?php
declare( strict_types = 1 );

namespace Pangolia\Cache;

trait FragmentCache {

	/**
	 * Fragment caching
	 *
	 * @param string   $key                     The cache key.
	 * @param callable $callback                Callback function to save data in case nothing is found.
	 * @param string   $group                   Optional. Where the cache contents are grouped. Default empty.
	 * @param int      $expire                  Optional. The number of seconds before the cache entry should expire.
	 *                                          Default is 0 (as long as possible).
	 * @return void
	 * @since 0.4.0
	 */
	public static function get_fragment_cache( string $key, callable $callback, string $group = '', int $expire = 0 ) {
		$found = false;
		$output = \wp_cache_get( $key, $group, false, $found );

		if ( $found === false ) {
			$output = static::get_fragment_output( $callback );
			\wp_cache_set( $key, $output, $group, $expire );
		}

		echo $output;
	}

	/**
	 * Remove fragment cache
	 *
	 * wp_cache_delete wrapper
	 *
	 * @param string $key   The cache key.
	 * @param string $group Optional. The cache group. Default is empty.
	 *
	 * @return bool
	 * @since 0.4.0
	 */
	public static function remove_fragment_cache( string $key, string $group = '' ): bool {
		return \wp_cache_delete( $key, $group );
	}

	/**
	 * Gets the fragment buffer
	 *
	 * @param callable $callback
	 * @return false|string
	 * @since 0.4.0
	 */
	protected static function get_fragment_output( callable $callback ) {
		\ob_start();
		\call_user_func( $callback );
		return \ob_get_clean();
	}
}
