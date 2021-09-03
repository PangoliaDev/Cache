<?php
declare( strict_types = 1 );

namespace Pangolia\Cache;

trait ObjectCache {

	/**
	 * Retrieve a value from the object cache. If it doesn't exist, cache the value.
	 *
	 * @param string   $key                     The cache key.
	 * @param callable $callback                Callback function to save data in case nothing is found.
	 * @param string   $group                   Optional. The cache group. Default is empty.
	 * @param int      $expire                  Optional. The number of seconds before the cache entry should expire.
	 *                                          Default is 0 (as long as possible).
	 * @return mixed
	 * @since 0.3.0
	 */
	public static function get_object_cache( string $key, callable $callback, string $group = '', int $expire = 0 ) {
		$found = false;
		$cached = \wp_cache_get( $key, $group, false, $found );

		if ( $found === false ) {
			$cached = \call_user_func( $callback );
			\wp_cache_set( $key, $cached, $group, $expire );
		}

		return $cached;
	}

	/**
	 * Retrieves multiple values from the cache in one call. If it doesn't exist,
	 * cache the value if it's set in the keys array.
	 *
	 * @param array<string|int, string|mixed> $keys   Array of keys under which the cache contents are stored.
	 * @param string                          $group  Optional. Where the cache contents are grouped. Default empty.
	 * @param int                             $expire Optional. The number of seconds before the cache entry should expire.
	 *                                                Default is 0 (as long as possible).
	 * @return array
	 * @since 0.3.0
	 */
	public static function get_object_cache_multiple( array $keys, string $group = '', int $expire = 0 ): array {
		$cache_keys = [];

		foreach ( $keys as $cache_key => $cache_value ) {
			$cache_keys[] = \is_int( $cache_key )
				? $cache_value
				: $cache_key;
		}

		$cached = \wp_cache_get_multiple( $cache_keys, $group );

		foreach ( array_filter( $cached, fn( $value ) => $value == '' ) as $empty_key => $empty_value ) {
			if ( isset( $keys[ $empty_key ] ) && \is_callable( $keys[ $empty_key ] ) ) {
				$cache_value = \call_user_func( $keys[ $empty_key ] );

				\wp_cache_set( $empty_key, $cache_value, $group, $expire );

				$cached[ $empty_key ] = $cache_value;
			}
		}

		return $cached;
	}

	/**
	 * Retrieve and subsequently delete a value from the object cache.
	 *
	 * @param string $key     The cache key.
	 * @param string $group   Optional. The cache group. Default is empty.
	 * @param mixed  $default Optional. The default value to return if the given key doesn't
	 *                        exist in the object cache. Default is null.
	 *
	 * @return mixed The cached value, when available, or $default.
	 * @since 0.3.0
	 */
	public static function remove_object_cache( string $key, string $group = '', $default = null ) {
		$found = false;
		$cached = \wp_cache_get( $key, $group, false, $found );

		if ( $found !== false ) {
			\wp_cache_delete( $key, $group );

			return $cached;
		}

		return $default;
	}
}
