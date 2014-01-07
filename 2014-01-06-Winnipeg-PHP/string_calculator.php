<?php

$examples = array(
	'' => 0,
	'1' => 1,
	'1,2' => 3,
	'1,2,3,4' => 10,
	"1\n2,3" => 6,
	"//;\n1;2" => 3,
);

foreach ( $examples as $parameter => $expected_output ) {
	if_I_run(
		$function = 'add',
		$with_parameter = $parameter,
		$the_output_should_be = $expected_output
	);
}

$examples_that_should_throw_an_exception = array(
	'-1' => 'negative numbers not allowed: -1',
	'-1,-2' => 'negative numbers not allowed: -1, -2',
	"-1\n2,-3" => 'negative numbers not allowed: -1, -3',
	"//;\n1;-2" => 'negative numbers not allowed: -2',
);

foreach ( $examples_that_should_throw_an_exception as $input => $expected_exception_message ) {
	if_I_run(
		$function = 'add',
		$with_parameter = $input,
		$there_should_be_no_output,
		$and_the_exception_thrown_should_contain_message = $expected_exception_message
	);
}

function add( $numbers ) {
	$a_custom_delimiter_was_provided = preg_match( "%^//(.)\n(.+)%", $numbers, $matches );
	if ( $a_custom_delimiter_was_provided ) {
		$delimiter = $matches[1];
		$numbers = $matches[2];
	} else {
		$delimiter = "(,|\n)";
	}

	$numbers_as_an_array = preg_split( "/$delimiter/", $numbers );

	$negative_numbers_that_were_provided = find_any_negative_numbers( $numbers_as_an_array );

	if ( ! empty( $negative_numbers_that_were_provided ) ) {
		throw new Exception( 'negative numbers not allowed: ' . join( ', ', $negative_numbers_that_were_provided ) );
	}

	return array_sum( $numbers_as_an_array );
}

function find_any_negative_numbers( $numbers ) {
	$negative_numbers = array();

	foreach ( $numbers as $number ) {
		if ( $number < 0 ) {
			$negative_numbers[] = $number;
		}
	}

	return $negative_numbers;
}

function if_I_run( $function, $with_parameter, $the_output_should_be, $and_the_exception_thrown_should_contain_message=null ) {
	try {
		$actual_output = call_user_func( $function, $with_parameter );

		if ( $we_were_expecting_an_exception = ! is_null( $and_the_exception_thrown_should_contain_message ) ) {
			echo 'Expected an exception to be thrown, but that aint what happened';
		} elseif ( $actual_output !== $the_output_should_be ) {
			echo 'Expected: ' . $the_output_should_be . "\n" . 'Actual: ' . $actual_output;
		}
	} catch ( Exception $e ) {
		if ( $and_the_exception_thrown_should_contain_message !== $e->getMessage() ) {
			echo
				'Expected exception message to be: ' . $and_the_exception_thrown_should_contain_message . "\n"
				. 'Actual exception message: ' . $e->getMessage()
			;
		}
	}
}
