<?php
declare( strict_types = 1 );

namespace Pangolia\Cache;

abstract class FileCache implements FileCacheInterface {

	/**
	 * Debug the file cache.
	 *
	 * @var bool
	 */
	protected $debug = false;

	/**
	 * Object cache.
	 *
	 * @var array<string, mixed>
	 */
	protected $object_cache = [];

	/**
	 * Returns the path where our cached files will be saved.
	 *
	 * @return string
	 * @since 0.1.0
	 */
	abstract public function get_cache_path(): string;

	/**
	 * Do something with the logs
	 *
	 * @param string $message
	 * @return void;
	 */
	abstract public function log( string $message );

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
	public function get_cache( string $cache_file, $cache_key, $cache_value ) {
		$this->log( "---- File cache started for {$cache_key} in {$cache_file} ----" );
		$cache_file_path = \trailingslashit( $this->get_cache_path() ) . $cache_file . '.php';

		if ( $cache_key === false ) {
			return \is_file( $cache_file_path )
				// Cache key is not set, if value is not cached then we're going to create a cached file and save the
				// value into it else we're going to get the returned value from the cached file
				? $this->get_cache_file_data( $cache_file_path )
				: $this->set_cache( $cache_value, false, $cache_file_path );
		}

		return \is_file( $cache_file_path )
			// Cache key is set, if value is not cached then we're going to create a cached file and save the value
			// within an empty array with the cache key being the array key else we're going to get the returned value
			// from the cached file and get the definite value based on the cache key being the array key
			? $this->get_cache_key_data( $cache_file_path, $cache_key, $cache_value )
			: $this->set_cache( $cache_value, $cache_key, $cache_file_path );
	}

	/**
	 * Returns the included data from the cached file.
	 *
	 * @param string $cache_file_path The path to the cached file.
	 * @return mixed
	 */
	protected function get_cache_file_data( string $cache_file_path ) {
		// We're going to save the file data inside the object in case we need it again.
		if ( isset( $this->object_cache[ $cache_file_path ] ) ) {
			$this->log( 'Object cache used' );
			return $this->object_cache[ $cache_file_path ];
		}

		$this->log( "File included: {$cache_file_path}" );
		$this->object_cache[ $cache_file_path ] = include $cache_file_path;

		return $this->object_cache[ $cache_file_path ];
	}

	/**
	 * Returns the array value from cached file based on the cache key being the array key.
	 *
	 * @param string               $cache_file_path The path to the cached file.
	 * @param string               $cache_key       The cache key.
	 * @param callable|mixed|false $cache_value
	 * @return mixed
	 */
	protected function get_cache_key_data( string $cache_file_path, string $cache_key, $cache_value ) {
		$cache_file_data = $this->get_cache_file_data( $cache_file_path );

		// If cache key exists, then get the value
		if ( isset( $cache_file_data[ $cache_key ] ) ) {
			return $cache_file_data[ $cache_key ];
		}

		// Cache key doesn't exist, set te array value
		$cache_file_data[ $cache_key ] = $this->get_cache_value( $cache_value );

		// Unset the object cache since it doesn't have our specific value
		unset( $this->object_cache[ $cache_file_path ] );

		// Renew our cached file.
		return $this->set_cache( $cache_file_data, false, $cache_file_path )[ $cache_key ];
	}

	/**
	 * Get the definite cached value.
	 *
	 * @param callable|mixed|false $cache_value
	 * @return false|mixed
	 */
	protected function get_cache_value( $cache_value ) {
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
	protected function set_cache( $cache_value, $cache_key, string $cache_file_path ) {
		$this->log( 'Set cache triggered' );

		// Create all the necessary folders.
		$this->create_path( dirname( $cache_file_path ) );

		// Get the definite value.
		$cache_file_data = $cache_key === false
			? $this->get_cache_value( $cache_value )
			: [ $cache_key => $this->get_cache_value( $cache_value ) ];

		\file_put_contents( $cache_file_path, $this->render_php( $cache_file_data ) );
		\chmod( $cache_file_path, 0777 );

		$this->log( "File created or updated: {$cache_file_path}" );

		return $cache_key === false
			? $cache_file_data
			: $cache_file_data[ $cache_key ];
	}

	/**
	 * Remove cached files.
	 *
	 * @param string|false $path
	 * @return array<string, bool>
	 * @since 0.1.0
	 */
	public function remove_cache( $path = false ): array {
		if ( $path === false ) {
			return [];
		}

		$path = trailingslashit( $this->get_cache_path() ) . $path;
		$deleted = [];

		// If path is a directory, then delete all the files inside the
		// dir and then the dir itself.
		if ( \is_dir( $path ) ) {
			foreach ( $this->get_path_names( $path . '/*' ) as $cache_file_path ) {

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

	/**
	 * Find path names matching a pattern
	 *
	 * @param string $pattern
	 * @return array<int,string>
	 */
	protected function get_path_names( string $pattern ): array {
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
	protected function create_path( string $path ): bool {
		if ( \is_dir( $path ) ) {
			return true;
		}

		$prev_path = \substr( $path, 0, \strrpos( $path, '/', -2 ) + 1 );
		$return = $this->create_path( $prev_path );

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
	protected function render_php( $data ): string {
		$php = '<?php ' . PHP_EOL;
		$php .= $this->render_php_doc() . PHP_EOL;
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
	protected function render_php_doc(): string {
		$php_doc = '/**' . PHP_EOL;
		$php_doc .= ' * This file has been auto-generated by the project in production-mode' . PHP_EOL;
		$php_doc .= ' * and is intended to store data permanently without expiration.';
		$php_doc .= '*/' . PHP_EOL;
		return $php_doc;
	}
}
