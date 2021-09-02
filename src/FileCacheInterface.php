<?php
declare( strict_types = 1 );

namespace Pangolia\Cache;

interface FileCacheInterface {

	/**
	 * Get the cached data.
	 *
	 * @param string               $cache_file  The cache relative file path.
	 * @param string|false         $cache_key   The cache key. If set as string, it will get and/or save
	 *                                          the value from the array key in the file. If set false,
	 *                                          it will get and/or save the value as an individual whole.
	 * @param callable|mixed|false $cache_value Callback function to save data in case nothing is found.
	 *                                          This can also be the value itself.
	 * @return mixed
	 * @since 0.1.0
	 */
	public static function get_cache( string $cache_file, $cache_key, $cache_value );

	/**
	 * Remove cached files.
	 *
	 * @param string|false $path The path to the file or directory. When a directory is chosen,
	 *                           then all the files within the directory will be removed as well.
	 * @return false|array<string, bool>
	 * @since 0.1.0
	 */
	public static function remove_cache( $path = false );
}
