<?php
declare( strict_types = 1 );

namespace Pangolia\Cache;

abstract class AbstractFileCache {

	/**
	 * Enable debug mode.
	 *
	 * @since 0.2.0
	 * @var bool
	 */
	protected static $debug = false;

	/**
	 * Logs for debugging.
	 *
	 * @since 0.2.0
	 * @var array<int, mixed>
	 */
	protected static $logs = [];

	/**
	 * Object cache.
	 *
	 * @since 0.1.0
	 * @var array<string, mixed>
	 */
	protected static $object_cache = [];

	/**
	 * Returns the included data from the cached file.
	 *
	 * @param string $cache_file_path The path to the cached file.
	 * @return mixed
	 * @since 0.1.0
	 */
	protected static function get_cache_file_data( string $cache_file_path ) {
		// We're going to save the file data inside the object in case we need it again.
		if ( isset( static::$object_cache[ $cache_file_path ] ) ) {
			static::log( 'Object cache used' );
			return static::$object_cache[ $cache_file_path ];
		}

		static::log( "File included: {$cache_file_path}" );
		static::$object_cache[ $cache_file_path ] = include $cache_file_path;

		return static::$object_cache[ $cache_file_path ];
	}

	/**
	 * Returns the array value from cached file based on the cache key being the array key.
	 *
	 * @param string               $cache_file_path The path to the cached file.
	 * @param string               $cache_key       The cache key.
	 * @param callable|mixed|false $cache_value
	 * @return mixed
	 * @since 0.1.0
	 */
	protected static function get_cache_key_data( string $cache_file_path, string $cache_key, $cache_value ) {
		$cache_file_data = static::get_cache_file_data( $cache_file_path );

		// If cache key exists, then get the value
		if ( isset( $cache_file_data[ $cache_key ] ) ) {
			return $cache_file_data[ $cache_key ];
		}

		// Cache key doesn't exist, set te array value
		$cache_file_data[ $cache_key ] = static::get_cache_value( $cache_value );

		// Unset the object cache since it doesn't have our specific value
		unset( static::$object_cache[ $cache_file_path ] );

		// Renew our cached file.
		return static::set_cache( $cache_file_data, false, $cache_file_path )[ $cache_key ];
	}

	/**
	 * Get the definite cached value.
	 *
	 * @param callable|mixed|false $cache_value
	 * @return false|mixed
	 * @since 0.1.0
	 */
	protected static function get_cache_value( $cache_value ) {
		return \is_callable( $cache_value )
			? \call_user_func( $cache_value )
			: $cache_value;
	}

	/**
	 * Create the cached file.
	 *
	 * @param callable|mixed|false $cache_value     Callback function which returns the data to cache,
	 *                                              or the data itself.
	 * @param string               $cache_file_path The full file path.
	 * @param string|false         $cache_key
	 * @return mixed
	 * @since 0.1.0
	 */
	protected static function set_cache( $cache_value, $cache_key, string $cache_file_path ) {
		static::log( 'Set cache triggered' );

		// Create all the necessary folders.
		static::create_path( dirname( $cache_file_path ) );

		// Get the definite value.
		$cache_file_data = $cache_key === false
			? static::get_cache_value( $cache_value )
			: [ $cache_key => static::get_cache_value( $cache_value ) ];

		\file_put_contents( $cache_file_path, static::render_php( $cache_file_data ) );
		\chmod( $cache_file_path, 0777 );

		static::log( "File created or updated: {$cache_file_path}" );

		return $cache_key === false
			? $cache_file_data
			: $cache_file_data[ $cache_key ];
	}

	/**
	 * Find path names matching a pattern
	 *
	 * @param string $pattern
	 * @return array<int,string>
	 * @since 0.1.0
	 */
	protected static function get_path_names( string $pattern ): array {
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
	protected static function create_path( string $path ): bool {
		if ( \is_dir( $path ) ) {
			return true;
		}

		$prev_path = \substr( $path, 0, \strrpos( $path, '/', -2 ) + 1 );
		$return = static::create_path( $prev_path );

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
	protected static function render_php( $data ): string {
		$php = '<?php ' . PHP_EOL;
		$php .= static::render_php_doc() . PHP_EOL;
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
	protected static function render_php_doc(): string {
		$php_doc = '/**' . PHP_EOL;
		$php_doc .= ' * This file has been auto-generated by the project in production-mode' . PHP_EOL;
		$php_doc .= ' * and is intended to store data permanently without expiration.';
		$php_doc .= '*/' . PHP_EOL;
		return $php_doc;
	}

	/**
	 * Log the debug messages
	 *
	 * @param string $message
	 * @return void
	 * @since 0.2.0
	 */
	protected static function log( string $message ) {
		if ( static::$debug === true ) {
			static::$logs[] = $message;
		}
	}
}
