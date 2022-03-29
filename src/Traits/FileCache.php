<?php
declare( strict_types = 1 );

namespace Pangolia\Cache\Traits;

trait FileCache{

	/**
	 * Object cache.
	 *
	 * @since 0.1.0
	 * @var array<string, mixed>
	 */
	protected static array $file_object_cache = [];

	/**
	 * The file cache storage path.
	 *
	 * @since 0.3.0
	 * @var string
	 */
	protected static string $file_cache_storage;

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
	public static function get_file_cache( string $file, $key, callable $callback ) {
		$file_path = static::get_file_path( $file );

		if ( $key === false ) {
			return \is_file( $file_path )
				// Cache key is not set, if value is not cached then we're going to create a cached file and save the
				// value into it else we're going to get the returned value from the cached file
				? static::get_file_cache_data( $file_path )
				: static::set_file_cache( $callback, false, $file_path );
		}

		return \is_file( $file_path )
			// Cache key is set, if value is not cached then we're going to create a cached file and save the value
			// within an empty array with the cache key being the array key else we're going to get the returned value
			// from the cached file and get the definite value based on the cache key being the array key
			? static::get_file_cache_key_data( $file_path, $key, $callback )
			: static::set_file_cache( $callback, $key, $file_path );
	}

	/**
	 * Remove cached files.
	 *
	 * @param string|false $file The cache relative file path, if set the false then it
	 *                           will try to  delete all the cached files inside the storage.
	 * @return array<string, bool> Array of all the deleted files as keys and a true/false bool as value.
	 * @since 0.1.0
	 */
	public static function remove_file_cache( $file = false ): array {
		if ( $file === false ) {
			return [];
		}

		$file = trailingslashit( static::$file_cache_storage ) . $file;
		$deleted = [];

		// If path is a directory, then delete all the files inside the
		// dir and then the dir itself.
		if ( \is_dir( $file ) ) {
			foreach ( static::get_file_path_names( $file . '/*' ) as $file_path ) {

				if ( \is_file( $file_path ) ) {
					$deleted[ $file_path ] = \unlink( $file_path );
				} elseif ( \is_dir( $file_path ) ) {
					$deleted[ $file_path ] = \rmdir( $file_path );
				}
			}

			$deleted[ $file ] = \rmdir( $file );

		} elseif ( is_file( $file . '.php' ) ) {
			// Path is a file, so we're just going to delete the file.
			$deleted[ $file . '.php' ] = \unlink( $file . '.php' );
		}

		return $deleted;
	}

	/**
	 * Returns the included data from the cached file.
	 *
	 * @param string $file_path The path to the cached file.
	 * @return mixed
	 * @since 0.1.0
	 */
	public static function get_file_cache_data( string $file_path ) {
		// We're going to save the file data inside the object in case we need it again.
		if ( isset( static::$file_object_cache[ $file_path ] ) ) {
			return static::$file_object_cache[ $file_path ];
		}

		static::$file_object_cache[ $file_path ] = include $file_path;

		return static::$file_object_cache[ $file_path ];
	}

	/**
	 * Returns the file path with our file cache storage path
	 *
	 * @param string $file The cache relative file path.
	 * @return string
	 */
	public static function get_file_path( string $file ): string {
		return \trailingslashit( static::$file_cache_storage ) . $file . '.php';
	}

	/**
	 * Returns the array value from cached file based on the cache key being the array key.
	 *
	 * @param string   $file_path The path to the cached file.
	 * @param string   $key       The cache key.
	 * @param callable $callback
	 * @return mixed
	 * @since 0.1.0
	 */
	protected static function get_file_cache_key_data( string $file_path, string $key, callable $callback ) {
		$file_data = static::get_file_cache_data( $file_path );

		// If cache key exists, then get the value
		if ( isset( $file_data[ $key ] ) ) {
			return $file_data[ $key ];
		}

		// Cache key doesn't exist, set te array value
		$file_data[ $key ] = \call_user_func( $callback );

		// Unset the object cache since it doesn't have our specific value
		unset( static::$file_object_cache[ $file_path ] );

		// Renew our cached file.
		return static::set_file_cache( $file_data, false, $file_path )[ $key ];
	}

	/**
	 * Create the cached file.
	 *
	 * @param callable|array $value     Callback function which returns the data to cache or array value,
	 * @param string         $file_path The full file path.
	 * @param string|false   $key
	 * @return mixed
	 * @since 0.1.0
	 */
	protected static function set_file_cache( $value, $key, string $file_path ) {

		// Create all the necessary folders.
		static::create_file_path( dirname( $file_path ) );

		// Get the definite value.
		$file_data = $key === false
			? static::get_file_cache_value( $value )
			: [ $key => static::get_file_cache_value( $value ) ];

		\file_put_contents( $file_path, static::render_file_cache_php( $file_data ) );
		\chmod( $file_path, 0777 );

		return $key === false
			? $file_data
			: $file_data[ $key ];
	}

	/**
	 * Get the definite cached value.
	 *
	 * @param callable|mixed|false $cache_value
	 * @return false|mixed
	 */
	protected static function get_file_cache_value( $cache_value ) {
		return \is_callable( $cache_value )
			? \call_user_func( $cache_value )
			: $cache_value;
	}

	/**
	 * Find path names matching a pattern
	 *
	 * @param string $pattern
	 * @return array<int,string>
	 * @since 0.1.0
	 */
	protected static function get_file_path_names( string $pattern ): array {
		$path_names = \glob( $pattern );

		return $path_names !== false
			? $path_names
			: [];
	}

	/**
	 * This will take a path, possibly with a long chain of uncreated directories, and keep going up one directory until
	 * it gets to an existing directory. Then it will attempt to create the next directory in that directory,
	 * and continue till it's created all the directories. It returns true if successful.
	 *
	 * @param string $path
	 * @return bool
	 * @since 0.1.0
	 */
	protected static function create_file_path( string $path ): bool {
		if ( \is_dir( $path ) ) {
			return true;
		}

		$prev_path = \substr( $path, 0, \strrpos( $path, '/', -2 ) + 1 );
		$return = static::create_file_path( $prev_path );

		return $return && \is_writable( $prev_path ) && \mkdir( $path ) && \chmod( $path, 0775 );
	}

	/**
	 *
	 * Render the php code for the cached files.
	 *
	 * @param mixed $data
	 * @return string
	 * @since 0.1.0
	 */
	protected static function render_file_cache_php( $data ): string {
		$php = '<?php ' . PHP_EOL;
		$php .= static::render_file_cache_php_doc() . PHP_EOL;
		$php .= 'declare( strict_types = 1 ); ' . PHP_EOL . PHP_EOL;
		$php .= 'return ' . \var_export( $data, true ) . ';';
		return $php;
	}

	/**
	 * Render php docs for the cached files.
	 *
	 * @return string
	 * @since 0.1.0
	 */
	protected static function render_file_cache_php_doc(): string {
		$php_doc = '/**' . PHP_EOL;
		$php_doc .= ' * This file has been auto-generated by the project in production-mode' . PHP_EOL;
		$php_doc .= ' * and is intended to store data permanently without expiration.';
		$php_doc .= '*/' . PHP_EOL;
		return $php_doc;
	}
}
