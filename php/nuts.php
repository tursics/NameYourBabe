<?php

//--------------------------------------------------------------------------------------------------
// http://de.wikipedia.org/wiki/NUTS
// http://de.wikipedia.org/wiki/Local_administrative_unit
// http://de.wikipedia.org/wiki/ISO-3166
// http://de.wikipedia.org/wiki/ISO-3166-1-Kodierliste
//    NUTS 0    Nationalstaaten
//              Standard ISO-3166, außer EL statt GR
//    NUTS 1    Größere Regionen/Landesteile
//    NUTS 2    Mittlere Regionen/Landschaften
//    NUTS 3    Kleinere Regionen/Großstädte
//    LAU 1     Gemeindeverbände
//    LAU 2     Gemeinden 
//
// http://simap.europa.eu/codes-and-nomenclatures/codes-nuts/codes-nuts-table_en.htm
//
//--------------------------------------------------------------------------------------------------
// $gNuts
//--------------------------------------------------------------------------------------------------

include_once( "data/nuts.php");

function gNutsToFile()
{
	global $gNuts;

	$contents = '<?php'."\n".'$gNuts=';
	$contents .= var_export( $gNuts, true);
	$contents .= ';'."\n".'?>'."\n";

	file_put_contents( 'data/nuts.php', $contents);
	file_put_contents( 'backup/nuts-' . date( 'Y-W') . '.php', $contents);
}

//--------------------------------------------------------------------------------------------------

function nutsShowPageBrowseSubTree( $data, $depth)
{
	global $gSource;

	$txt = '';
	for( $i = 0; $i < count( $data); ++$i) {
		$ret = nutsShowPageBrowseSubTree( $data[$i]['manRegions'], $depth + 1);

		$exists = false;
		for( $h = 0; !$exists && $h < count( $gSource); ++$h) {
			for( $j = 0; $j < count( $gSource[$h]['manNUTS']); ++$j) {
				if( $data[$i]['manNuts'] == $gSource[$h]['manNUTS'][$j]) {
					$exists = true;
				}
			}
		}

		$color = '';
		if( $exists) {
			$color = 'background-color:lightgreen;';
		}

		if(( strlen( $ret) > 0) || $exists) {
			$txt .= '<div style="margin:0 0 0 ' . $depth . 'em;' . $color . '">';
			$txt .= $data[$i]['manNuts'];
			$txt .= ' ';
			$txt .= $data[$i]['manName']['en-US'];
			$txt .= '</div>';
			$txt .= $ret;
		}
	}

	return $txt;
}

function nutsShowPageBrowse()
{
	global $gNuts;

	$txt = '';
	$txt .= '<h1>Show the world</h1>';
	$txt .= '<a href="/">Back to main</a><br>';
	$txt .= '<br>';
	$txt .= '<br>';
	echo( $txt);

	echo( nutsShowPageBrowseSubTree( $gNuts, 0));
}

//--------------------------------------------------------------------------------------------------

function nutsExistsSubTree( $data, $str)
{
	for( $i = 0; $i < count( $data); ++$i) {
		if( $str == $data[$i]['manNuts']) {
			return true;
		}

		if( nutsExistsSubTree( $data[$i]['manRegions'], $str)) {
			return true;
		}
	}

	return false;
}

function nutsExists( $str)
{
	global $gNuts;

	return nutsExistsSubTree( $gNuts, $str);
}

//--------------------------------------------------------------------------------------------------

function nutsGetNameSubTree( $data, $str)
{
	for( $i = 0; $i < count( $data); ++$i) {
		if( $str == $data[$i]['manNuts']) {
			return $data[$i]['manName'];
		}

		$ret = nutsGetNameSubTree( $data[$i]['manRegions'], $str);
		if( 0 < count( $ret)) {
			return $ret;
		}
	}

	return array();
}

function nutsGetName( $str)
{
	global $gNuts;

	return nutsGetNameSubTree( $gNuts, $str);
}

//--------------------------------------------------------------------------------------------------

?>
