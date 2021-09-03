# Cache helpers for WordPress

Cache helper classes, created for WordPress development.

## Installation

Use composer to install the package.

````bash
composer require pangolia/cache
````

## Implementation

````php
class Cache implements CacheInterface {
	use 
            FileCache, 
            ObjectCache, 
            TransientCache;

	/**
	 * The file cache storage path.
	 *
	 * @var string
	 */
	protected static $file_cache_storage;

	/**
	 * Set the cache properties.
	 */
	public function __construct() {
		static::$file_cache_storage = 'wp-content/your-file/cache/storage';
	}
}
````

## Examples

````php
$my_value = Cache::get_file_cache('my/file', 'my-key', function() {
return 'the cached value';
});


$my_value = Cache::get_object_cache( 'my-key', function() {
return 'the cached value';
}, 'my-cache-group' );


$my_value = Cache::get_transient( 'my-key', function() {
return 'the cached value';
} );
````