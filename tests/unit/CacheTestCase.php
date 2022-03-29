<?php

namespace Pangolia\CacheTests\Unit;

use Brain\Monkey;
use Pangolia\Cache\FileCache;
use PHPUnit\Framework\TestCase;

class CacheTestCase extends TestCase {
	protected FileCache $fileCache;
	protected string $file_cache_storage;
	protected string $cached_file_path;
	protected $cached_file;

	/**
	 * Setup which calls \WP_Mock setup
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Monkey\Functions\when( '__' )->returnArg( 1 );
		Monkey\Functions\when( '_e' )->returnArg( 1 );
		Monkey\Functions\when( '_n' )->returnArg( 1 );
	}

	public function setUpFilePath() {
		$this->file_cache_storage = PANGOLIA_DIR . '/cache';
	}

	public function setUpFileCache() {
		$this->setUpFilePath();
		$this->fileCache = new FileCache( $this->file_cache_storage );
	}

	/**
	 * Teardown which calls \WP_Mock tearDown
	 *
	 * @return void
	 */
	public function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}
}