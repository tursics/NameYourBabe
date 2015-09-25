<?php

//------------------------------------------------------------------------------

$BASE64ENCODE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-';
$BASE64DECODE = array_flip( str_split( $BASE64ENCODE));

//------------------------------------------------------------------------------

function intEncode( $int)
{
	global $BASE64ENCODE;
	$str = '';

	do {
		$remainder = $int % 64;
		$int = (int)( $int / 64);
		$str = $BASE64ENCODE[$remainder] . $str;
	} while( $int > 0);

	return $str;
}

//------------------------------------------------------------------------------

function intEncodeBytes( $int, $len)
{
	global $BASE64ENCODE;
	$str = intEncode( $int);

	while( strlen( $str) < $len) {
		$str = $BASE64ENCODE[0] . $str;
	}

	return $str;
}

//------------------------------------------------------------------------------

function intDecode( $str)
{
	global $BASE64DECODE;
	$int = 0;

	for( $i = 0; $i < strlen( $str); ++$i) {
		$int = $int * 64 + $BASE64DECODE[ $str[ $i]];
	}

	return $int;
}

//------------------------------------------------------------------------------

?>
