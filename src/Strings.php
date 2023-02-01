<?php

namespace Wpify\Helpers;

class Strings {
	/**
	 * Get the case of the given name.
	 *
	 * @param string $name
	 * @param string $case
	 *
	 * @return string
	 */
	public static function get_case( string $name, string $case ) {
		// remove accents
		$name = str_replace( '\'', '', iconv( 'UTF-8', 'ASCII//TRANSLIT', $name ) );

		// add space before capital letters
		$name = preg_replace( '/(?<!\ )[A-Z]/', ' $0', $name );

		// remove all non-alphanumeric characters
		$name = trim( preg_replace( '/[^a-zA-Z0-9]/', ' ', $name ) );

		// make all lowercase
		$name = strtolower( $name );

		// split into words
		$name = preg_split( '/\s+/', $name );

		switch ( $case ) {
			case 'camel':
			{
				return lcfirst( join( '', array_map( fn( $word ) => ucfirst( $word ), $name ) ) );
			}
			case 'pascal':
			{
				return join( '', array_map( fn( $word ) => ucfirst( $word ), $name ) );
			}
			case 'snake':
			{
				return join( '_', $name );
			}
			case 'kebab':
			{
				return join( '-', $name );
			}
			case 'constant':
			{
				return join( '_', array_map( 'strtoupper', $name ) );
			}
			case 'sentence':
			{
				return join( ' ', array_map( 'ucfirst', $name ) );
			}
			default:
			{
				return $name;
			}
		}
	}

	/**
	 * Replace all cases of the given name.
	 *
	 * @param string $search
	 * @param string $replace
	 * @param string $subject
	 *
	 * @return string
	 */
	public static function replace_cases( string $search, string $replace, string $subject ) {
		$cases = array( 'camel', 'pascal', 'snake', 'kebab', 'constant', 'sentence' );

		foreach ( $cases as $case ) {
			$subject = str_replace( self::get_case( $search, $case ), self::get_case( $replace, $case ), $subject );
		}

		return $subject;
	}

	/**
	 * Generate random password.
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	public static function generate_password( int $length = 64 ): string {
		$chars    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
		$password = '';

		for ( $i = 0; $i < $length; $i ++ ) {
			$password .= substr( $chars, rand( 0, strlen( $chars ) - 1 ), 1 );
		}

		return $password;
	}

	/**
	 * Modify .env file for the new project.
	 *
	 * @param string $content .env file content.
	 * @param callable $callback Callback that accepts variable name and value and returns the new value.
	 *
	 * @return string
	 */
	public static function modify_dotenv( string $content, callable $callback, ?callable $preprocess = null ): string {
		$lines = preg_split( '/\n/', $content );

		foreach ( $lines as $index => $line ) {
			$line = trim( $line );

			if ( $preprocess ) {
				$line = $preprocess( $line );
			}

			if ( str_starts_with( $line, '#' ) || empty( $line ) || ! str_contains( $line, '=' ) ) {
				continue;
			}

			[ $variable, $value ] = preg_split( '/\s*=\s*/', $line, 2 );
			$variable = trim( $variable );
			$value    = trim( $value );
			$quote    = '';

			if ( str_starts_with( $value, '\'' ) && str_ends_with( $value, '\'' ) ) {
				$quote = '\'';
			} elseif ( str_starts_with( $value, '"' ) && str_ends_with( $value, '"' ) ) {
				$quote = '"';
			} elseif ( str_starts_with( $value, '`' ) && str_ends_with( $value, '`' ) ) {
				$quote = '"';
			}

			$value = trim( $value, $quote );

			$lines[ $index ] = $variable . '=' . $quote . $callback( $variable, $value ) . $quote;
		}

		return implode( "\n", $lines );
	}
}