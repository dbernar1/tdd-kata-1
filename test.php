<?php
require_once('simpletest/autorun.php');

function add( $calculation_string ){
		$sum = 0;

		list( $delimiters, $numbers_string ) = extract_delimiters_and_numbers_string( $calculation_string );

		$numbers = get_numbers_out_of_string( $numbers_string, $delimiters );

		$negatives = array();

		foreach( $numbers as $number ) {

			if ( 0 === strpos( $number, '-' ) ) $negatives[] = $number;

			if ( $number > 1000 ) continue;

			$sum += $number;
		}

		if ( ! empty( $negatives ) ) throw new Exception( 'Negatives not allowed: ' . join( ', ', $negatives ) );

		return $sum;
}

function extract_delimiters_and_numbers_string( $calculation_string ) {
	if ( 0 === strpos( $calculation_string, '//' ) ) {
		list( $delimiter_string, $number_string ) = explode( "\n", $calculation_string, 2 );
		$delimiter_string = str_replace( '//', '', $delimiter_string );
		$delimiter_string = trim( $delimiter_string, '[]' );
		$delimiters = explode( '][', $delimiter_string );
		return array( $delimiters, $number_string );
	} else {
		return array( array( ',' ), $calculation_string );
	}
}

function get_numbers_out_of_string( $numbers_string, $delimiters ) {
	$delimiters[] = "\n";

	$numbers = array( $numbers_string );

	foreach( $delimiters as $possible_delimiter ) {
		$numbers = get_numbers_out_of_strings( $numbers, $possible_delimiter );
	}

	return $numbers;
}

function get_numbers_out_of_strings( $numbers_strings, $delimiter ) {

		$numbers = array();
		foreach( $numbers_strings as $number_string ) {
			$numbers = array_merge( $numbers, explode( $delimiter, $number_string ) );
		}

		return $numbers;
}

class TestStringThingy extends UnitTestCase {
	public function test_add_returns_0_for_empty_string() {
		$this->assertEqual( 0, add( '' ) );
	}

	public function test_add_returns_number_if_only_one_is_provided() {
		$this->assertEqual( 1, add( '1' ) );
	}

	public function test_add_returns_sum_of_two_numbers() {
		$this->assertEqual( 3, add( '1,2' ) );
	}

	public function test_add_returns_sum_of_any_number_of_comma_separated_numbers() {
		$this->assertEqual( 127, add( '1,2,4,56,56,8' ) );
	}

	public function test_add_accepts_newline_separated_strings_as_well_as_comma_separated() {
		$this->assertEqual( 6, add( "1\n2,3" ) );
	}

	public function test_add_accepts_delimiter_parameter() {
		$this->assertEqual( 3, add( "//;\n1;2" ) );
	}

	public function test_add_numbers_above_1000_are_ignored() {
		$this->assertEqual( 2, add( '2,1001' ) );
	}

	public function test_add_throws_exception_for_negative_numbers() {
		$this->expectException( new Exception( 'Negatives not allowed: -2, -4' ) );
		add( "2,-2,5,-4" );
	}

	public function test_add_accepts_multichar_delimiters() {
		$this->assertEqual( 6, add( "//***\n1***2***3" ) );
	}

	public function test_add_accepts_multiple_delimiters() {
		$this->assertEqual( 6, add( "//[*][%]\n1*2%3" ) );
	}

	public function test_add_accepts_multiple_multichar_delimiters() {
		$this->assertEqual( 6, add( "//[*%*][%*%]\n1*%*2%*%3" ) );
	}

}
