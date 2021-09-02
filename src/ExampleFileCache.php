<?php
declare( strict_types = 1 );

namespace Pangolia\Cache;

class ExampleFileCache extends AbstractFileCache implements FileCacheInterface {

	/**
	 * Get the cached data.
	 *
	 * @param string               $cache_file  The cache relative file path.
	 * @param string|false         $cache_key   The cache key. If set as string, it will try to get
	 *                                          the value from the array key in the file or save it as array value.
	 *                                          If set false, it will get the entire value from the file.
	 * @param callable|mixed|false $cache_value Callback function to save data in case nothing is found.
	 *                                          This can also be the value itself.
	 * @return mixed
	 * @since 0.1.0
	 */
	public static function get_cache( string $cache_file, $cache_key, $cache_value ) {
		static::log( "---- File cache started for {$cache_key} in {$cache_file} ----" );
		$cache_file_path = \trailingslashit( 'your/folder' ) . $cache_file . '.php';

		if ( $cache_key === false ) {
			return \is_file( $cache_file_path )
				// Cache key is not set, if value is not cached then we're going to create a cached file and save the
				// value into it else we're going to get the returned value from the cached file
				? static::get_cache_file_data( $cache_file_path )
				: static::set_cache( $cache_value, false, $cache_file_path );
		}

		return \is_file( $cache_file_path )
			// Cache key is set, if value is not cached then we're going to create a cached file and save the value
			// within an empty array with the cache key being the array key else we're going to get the returned value
			// from the cached file and get the definite value based on the cache key being the array key
			? static::get_cache_key_data( $cache_file_path, $cache_key, $cache_value )
			: static::set_cache( $cache_value, $cache_key, $cache_file_path );
	}

	/**
	 * Remove cached files.
	 *
	 * @param string|false $path
	 * @return array<string, bool>
	 * @since 0.1.0
	 */
	public static function remove_cache( $path = false ): array {
		if ( $path === false ) {
			return [];
		}

		$path = trailingslashit( 'your/folder' ) . $path;
		$deleted = [];

		// If path is a directory, then delete all the files inside the
		// dir and then the dir itself.
		if ( \is_dir( $path ) ) {
			foreach ( static::get_path_names( $path . '/*' ) as $cache_file_path ) {

				if ( \is_file( $cache_file_path ) ) {
					$deleted[ $cache_file_path ] = \unlink( $cache_file_path );
				} elseif ( \is_dir( $cache_file_path ) ) {
					$deleted[ $cache_file_path ] = \rmdir( $cache_file_path );
				}
			}

			$deleted[ $path ] = \rmdir( $path );

		} elseif ( is_file( $path . '.php' ) ) {
			// Path is a file, so we're just going to delete the file.
			$deleted[ $path . '.php' ] = \unlink( $path . '.php' );
		}

		return $deleted;
	}
}
