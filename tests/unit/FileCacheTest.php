<?php

namespace Pangolia\CacheTests\Unit;

class FileCacheTest extends CacheTestCase {
	public function testFileStorage() {
		$this->setUpFileCache();
		$file_paths = [
			'first_folder/my_data',
			'first_folder/second_folder/my_data',
			'first_folder/second_folder/third_folder/my_data',
		];

		foreach ( $file_paths as $path ) {
			$this->fileCache->get( $path, function () {
				return 'myValue';
			} );
		}
		foreach ( $file_paths as $path ) {
			$this->cached_file_path = "{$this->file_cache_storage}/{$path}.php";
			$this->assertFileExists( $this->cached_file_path );
			$this->assertFileIsReadable( $this->cached_file_path );
			$this->assertFileIsWritable( $this->cached_file_path );
		}
		foreach ( $file_paths as $path ) {
			unlink( "{$this->file_cache_storage}/{$path}.php" );
		}
		rmdir( "{$this->file_cache_storage}/first_folder/second_folder/third_folder" );
		rmdir( "{$this->file_cache_storage}/first_folder/second_folder" );
		rmdir( "{$this->file_cache_storage}/first_folder" );
		rmdir( "{$this->file_cache_storage}" );
	}

	public function testSingleFileCacheReturn() {
		$this->setUpFileCache();
		$data = [];
		foreach (
			[
				[
					'file'  => 'my_data_1',
					'value' => 'MyDataString',
				],
				[
					'file'  => 'my_data_2',
					'value' => [ 'MyDataArrayKey' => 'MyDataArrayValue' ],
				],
				[
					'file'  => 'my_data_3',
					'value' => true,
				],
				[
					'file'  => 'my_data_4',
					'value' => 123,
				],
			] as $test ) {
			$data[ $test['file'] ] = $this->fileCache->get( $test['file'], function () use ( $test ) {
				return $test['value'];
			} );
			$this->cached_file_path = "{$this->file_cache_storage}/{$test['file']}.php";
			$this->cached_file = include $this->cached_file_path;
			$this->assertSame( $test['value'], $this->cached_file );
			$this->assertSame( $test['value'], $data[ $test['file'] ] );
		}
		foreach ( $data as $file => $value ) {
			unlink( "{$this->file_cache_storage}/{$file}.php" );
		}
		rmdir( "{$this->file_cache_storage}" );
	}

	public function testFileCacheReturnByKey() {
		$this->setUpFileCache();
		$string_data = $this->fileCache->get( 'my_data_by_keys', function () {
			return 'MyStringValue';
		}, 'MyStringKey' );
		$bool_data = $this->fileCache->get( 'my_data_by_keys', function () {
			return true;
		}, 'MyBoolKey' );
		$array_data = $this->fileCache->get( 'my_data_by_keys', function () {
			return [ 'MyArrayKey' => 'MyArrayValue' ];
		}, 'MyArrayKey' );

		$this->cached_file_path = "{$this->file_cache_storage}/my_data_by_keys.php";
		$this->cached_file = include $this->cached_file_path;

		$this->assertSame( 'MyStringValue', $string_data );
		$this->assertSame( 'MyStringValue', $this->cached_file['MyStringKey'] );

		$this->assertSame( true, $bool_data );
		$this->assertSame( true, $this->cached_file['MyBoolKey'] );

		$this->assertSame( 'MyArrayValue', $array_data['MyArrayKey'] );
		$this->assertSame( 'MyArrayValue', $this->cached_file['MyArrayKey']['MyArrayKey'] );

		unlink( "{$this->file_cache_storage}/my_data_by_keys.php" );
		rmdir( "{$this->file_cache_storage}" );
	}
}