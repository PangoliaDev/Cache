<?php
declare( strict_types = 1 );

namespace Pangolia\Cache;

class ExampleCache implements CacheInterface {
	use
		FileCache,
		ObjectCache,
		TransientCache;

	/**
	 * The file cache storage path.
	 *
	 * @var string
	 */
	protected static string $file_cache_storage;

	/**
	 * Set the cache properties.
	 */
	public function __construct() {
		static::$file_cache_storage = 'wp-content/your-file/cache/storage';
	}
}
