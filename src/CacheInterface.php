<?php
declare( strict_types = 1 );

namespace Pangolia\Cache;

interface CacheInterface {

	/**
	 * Get the cached data.
	 *
	 * @param string       $file                The cache relative file path.
	 * @param string|false $key                 The cache key. If set as string, it will try to get
	 *                                          the value from the array key in the file or save it as array value.
	 *                                          If set false, it will get the entire value from the file.
	 * @param callable     $callback            Callback function to save data in case nothing is found.
	 *                                          This can also be the value itself.
	 * @return mixed
	 * @since 0.1.0
	 */
	public static function get_file_cache( string $file, $key, callable $callback );

	/**
	 * Remove cached files.
	 *
	 * @param string|false $file The cache relative file path, if set the false then it
	 *                           will try to  delete all the cached files inside the storage.
	 * @return array<string, bool> Array of all the deleted files as keys and a true/false bool as value.
	 * @since 0.1.0
	 */
	public static function remove_file_cache( $file = false ): array;

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
	public static function get_object_cache( string $key, callable $callback, string $group = '', int $expire = 0 );

	/**
	 * Retrieves multiple values from the cache in one call. If it doesn't exist,
	 * cache the value if it's set in the keys array.
	 *
	 * @param array  $keys                      Array of keys under which the cache contents are stored.
	 * @param string $group                     Optional. Where the cache contents are grouped. Default empty.
	 * @param int    $expire                    Optional. The number of seconds before the cache entry should expire.
	 *                                          Default is 0 (as long as possible).
	 * @return array
	 * @since 0.3.0
	 */
	public static function get_object_cache_multiple( array $keys, string $group = '', int $expire = 0 ): array;

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
	public static function remove_object_cache( string $key, string $group = '', $default = null );

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
	public static function get_transient( string $key, callable $callback, int $expire = 0 );

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
	public static function remove_transient( string $key, $default = null );

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
	public static function get_site_transient( string $key, callable $callback, int $expire = 0 );

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
	public static function forget_site_transient( string $key, $default = null );
}
