# Cache helpers for WordPress

Cache helpers for WordPress development.

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
            FragmentCache,
            TransientCache;

	/**
	 * Set the cache properties.
	 */
	public function __construct( $file_storage ) {
		static::$file_cache_storage = $file_storage;
	}
}
````

## Examples

````php
new Cache( 'wp-content/your/file-cache/storage' );

$my_value = Cache::get_file_cache('my/file', 'my-key', function() {
  return 'the cached value';
});

$my_value = Cache::get_transient( 'my-key', function() {
  return 'the cached value';
} );

$my_value = Cache::get_object_cache( 'my-key', function() {
  return 'the cached value';
}, 'my-cache-group' );


Cache::get_fragment_cache( 'my-key', function() {
  echo 'the cached value'
}, 'my-cache-group' );
````