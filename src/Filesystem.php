<?php

namespace Wpify\Helpers;

/**
 * Filesystem helpers.
 */
class Filesystem {
	/**
	 * List all files in given directory.
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	private static function recursive_files( string $path ): array {
		$files = array();

		if ( is_dir( $path ) ) {
			if ( $handle = opendir( $path ) ) {
				while ( ( $name = readdir( $handle ) ) !== false ) {
					if ( ! in_array( $name, array( '..', '.' ) ) ) {
						if ( ! is_dir( $path . "/" . $name ) ) {
							$files[] = $path . '/' . $name;
						} else {
							array_push( $files, ...self::recursive_files( $path . "/" . $name ) );
						}
					}
				}

				closedir( $handle );
			}
		}

		sort( $files );

		return $files;
	}

	/**
	 * List all files in given directory.
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	public static function list_files( string $path ): array {
		$files = self::recursive_files( $path );

		return array_map( function ( $file_path ) use ( $path ) {
			return substr( $file_path, strlen( $path ) + 1 );
		}, $files );
	}

	/**
	 * Delete recursively folder.
	 *
	 * @param string $path
	 */
	public static function delete( string $path ): void {
		if ( is_dir( $path ) ) {
			$files = array_diff( scandir( $path ), array( '.', '..' ) );

			foreach ( $files as $file ) {
				( is_dir( "$path/$file" ) ) ? self::delete( "$path/$file" ) : unlink( "$path/$file" );
			}

			rmdir( $path );
		} elseif ( is_file( $path ) ) {
			unlink( $path );
		}
	}

	/**
	 * Create directory.
	 */
	public static function mkdir( string $path ): void {
		if ( ! is_dir( $path ) ) {
			mkdir( $path, 0755, true );
		}
	}

	/**
	 * Move file or folder.
	 */
	public static function move( string $source, string $destination ): void {
		if ( is_dir( $source ) ) {
			self::mkdir( $destination );

			$files = array_diff( scandir( $source ), array( '.', '..' ) );

			foreach ( $files as $file ) {
				self::move( "$source/$file", "$destination/$file" );
			}

			rmdir( $source );
		} elseif ( is_file( $source ) ) {
			self::mkdir( dirname( $destination ) );
		}

		rename( $source, $destination );
	}
}
