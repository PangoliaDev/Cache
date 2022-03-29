<?php
declare( strict_types = 1 );

namespace Pangolia\Cache\Traits;

trait TransientCache {

	/**
	 * Retrieve a value from transients. If it doesn't exist, run the $callback to generate and
	 * cache the value.
	 *
	 * @param string   $key      The transient key.
	 * @param callable $callback The callback used to generate and cache the value.
	 * @param int      $expire   Optional. The number of seconds before the cache entry should expire.
	 *                           Default is 0 (as long as possible).
	 *
	 * @return mixed The value returned from $callback, pulled from transients when available.
	 * @since 0.3.0
	 */
	public static function get_transient( string $key, callable $callback, int $expire = 0 ) {
		$cached = \get_transient( $key );

		if ( false !== $cached ) {
			return $cached;
		}

		$value = \call_user_func( $callback );

		if ( ! \is_wp_error( $value ) ) {
			\set_transient( $key, $value, $expire );
		}

		return $value;
	}

	/**
	 * Retrieve and subsequently delete a value from the transient cache.
	 *
	 * @param string $key     The transient key.
	 * @param mixed  $default Optional. The default value to return if the given key doesn't
	 *                        exist in transients. Default is null.
	 *
	 * @return mixed The cached value, when available, or $default.
	 * @since 0.3.0
	 */
	public static function remove_transient( string $key, $default = null ) {
		$cached = \get_transient( $key );

		if ( false !== $cached ) {
			\delete_transient( $key );

			return $cached;
		}

		return $default;
	}

	/**
	 * Retrieve a value from site transients. If it doesn't exist, run the $callback to generate
	 * and cache the value.
	 *
	 * @param string   $key      The site transient key.
	 * @param callable $callback The callback used to generate and cache the value.
	 * @param int      $expire   Optional. The number of seconds before the cache entry should expire.
	 *                           Default is 0 (as long as possible).
	 *
	 * @return mixed The value returned from $callback, pulled from transients when available.
	 * @since 0.3.0
	 */
	public static function get_site_transient( string $key, callable $callback, int $expire = 0 ) {
		$cached = \get_site_transient( $key );

		if ( false !== $cached ) {
			return $cached;
		}

		$value = \call_user_func( $callback );

		if ( ! \is_wp_error( $value ) ) {
			\set_site_transient( $key, $value, $expire );
		}

		return $value;
	}

	/**
	 * Retrieve and subsequently delete a value from the site transient cache.
	 *
	 * @param string $key     The site transient key.
	 * @param mixed  $default Optional. The default value to return if the given key doesn't
	 *                        exist in the site transients. Default is null.
	 *
	 * @return mixed The cached value, when available, or $default.
	 * @since 0.3.0
	 */
	public static function remove_site_transient( string $key, $default = null ) {
		$cached = \get_site_transient( $key );

		if ( false !== $cached ) {
			\delete_site_transient( $key );

			return $cached;
		}

		return $default;
	}

}
