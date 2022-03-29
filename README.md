# Cache helpers for WordPress

Small cache wrappers/helpers for WordPress development.

## Installation

Use composer to install the package.

````bash
composer require pangolia/cache
````

## Examples

````php
$file_cache = new FileCache( 'wp-content/your/file-cache/storage' );
$object_cache = new ObjectCache();
$transient_cache = new Transient();

$my_value = $file_cache->get('my/file', function() {
  return 'the cached value';
}, 'my-key');

$my_value = $transient_cache->get( 'my-key', function() {
  return 'the cached value';
}, 34000 );

$my_value = $object_cache->get( 'my-key', function() {
  return 'the cached value';
}, 'my-cache-group', 34000 );
````