<?php
declare( strict_types = 1 );

namespace Pangolia\Cache;

abstract class AbstractCache {

	/**
	 * Debug the cache utilities.
	 *
	 * @since 0.1.0
	 * @var bool
	 */
	protected $debug = true;

	/**
	 * Object cache.
	 *
	 * @since 0.1.0
	 * @var array<string, mixed>
	 */
	protected $object_cache = [];
}
