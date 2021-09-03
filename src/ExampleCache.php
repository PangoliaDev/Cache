<?php
declare( strict_types = 1 );

namespace Pangolia\Cache;

class ExampleCache implements CacheInterface {
	use
		FileCache,
		ObjectCache,
		FragmentCache,
		TransientCache;

	/**
	 * Set the cache properties.
	 */
	public function __construct() {
		static::$file_cache_storage = 'wp-content/your-file/cache/storage';
	}
}
