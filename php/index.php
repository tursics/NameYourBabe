<?php
	$do = '';
	$what = '';
	if( isset( $_GET[ 'do'])) {
		$do = $_GET[ 'do'];
	}
	if( isset( $_GET[ 'what'])) {
		$what = $_GET[ 'what'];
	}

	include_once( "export.php");
	include_once( "metadata.php");
	include_once( "sources.php");
	include_once( "nuts.php");

//--------------------------------------------------------------------------------------------------
// $gMeanings
//--------------------------------------------------------------------------------------------------

include_once( "data/meanings.php");

function gMeaningsToFile()
{
	global $gMeanings;

	$contents = '<?php'."\n".'$gMeanings=';
	$contents .= var_export( $gMeanings, true);
	$contents .= ';'."\n".'?>'."\n";

	file_put_contents( dirname(__FILE__) . '/data/meanings.php', $contents);
	file_put_contents( dirname(__FILE__) . '/backup/meanings-' . date( 'Y-W') . '.php', $contents);
}

//--------------------------------------------------------------------------------------------------
// $gBoys
//--------------------------------------------------------------------------------------------------

$gBoysMem = memory_get_peak_usage( false);
include_once( "data/boys.php");
$gBoysMem = memory_get_peak_usage( false) - $gBoysMem;

function gBoysToFile()
{
	global $gBoys;

	$contents = '<?php'."\n".'$gBoys=';
	$contents .= var_export( $gBoys, true);
	$contents .= ';'."\n".'?>'."\n";

	file_put_contents( dirname(__FILE__) . '/data/boys.php', $contents);
	file_put_contents( dirname(__FILE__) . '/backup/boys-' . date( 'Y-W') . '.php', $contents);
}

//--------------------------------------------------------------------------------------------------
// $gGirls
//--------------------------------------------------------------------------------------------------

$gGirlsMem = memory_get_peak_usage( false);
include_once( "data/girls.php");
$gGirlsMem = memory_get_peak_usage( false) - $gGirlsMem;

function gGirlsToFile()
{
	global $gGirls;

	$contents = '<?php'."\n".'$gGirls=';
	$contents .= var_export( $gGirls, true);
	$contents .= ';'."\n".'?>'."\n";

	file_put_contents( dirname(__FILE__) . '/data/girls.php', $contents);
	file_put_contents( dirname(__FILE__) . '/backup/girls-' . date( 'Y-W') . '.php', $contents);
}

//--------------------------------------------------------------------------------------------------

function showPageHome()
{
	global $gGirls;
	global $gBoys;
	global $gSource;
	global $gGirlsMem;
	global $gBoysMem;

	$memory_limit = ini_get( 'memory_limit');
	if( preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
		if( $matches[2] == 'M') {
			$memory_limit = $matches[1] * 1024 * 1024;
		} else if( $matches[2] == 'K') {
			$memory_limit = $matches[1] * 1024;
		}
	}

	$txt = '';
	$txt .= '<h1>Admin area</h1>';
	$txt .= '<div style="display:inline;float:left;min-width:7em;">Memory max:</div> ' . intval( $memory_limit /1024/1024*10)/10 . ' MByte<br>';
	$txt .= '<div style="display:inline;float:left;min-width:7em;">Memory used:</div> ' . intval( memory_get_peak_usage() /1024/1024*10)/10 . ' MByte<br>';
	$txt .= '<div style="display:inline;float:left;min-width:7em;">Memory:</div> ' . intval( memory_get_peak_usage() * 100 / $memory_limit) . '%<br>';
	$txt .= '<br>';
	$txt .= '<div style="display:inline;float:left;min-width:7em;">Girl mem:</div> ' . intval( $gGirlsMem /1024/1024*10)/10 . ' MByte<br>';
	$txt .= '<div style="display:inline;float:left;min-width:7em;">Boy mem:</div> ' . intval( $gBoysMem /1024/1024*10)/10 . ' MByte<br>';
	$txt .= '<br>';
	$txt .= '<div style="display:inline;float:left;min-width:7em;">Girl count:</div> ' . count( $gGirls) . '<br>';
	$txt .= '<div style="display:inline;float:left;min-width:7em;">Boy count:</div> ' . count( $gBoys) . '<br>';
	$txt .= '<div style="display:inline;float:left;min-width:7em;">Source count:</div> ' . count( $gSource) . '<br>';
	$txt .= '<br>';
	$txt .= '<hr>';
	$txt .= '<br>';
	$txt .= '<a href="do=browse&what=sources">Browse sources</a><br>';
	$txt .= '<a href="do=browse&what=nuts">Browse the world</a><br>';
	$txt .= '<a href="do=browse&what=names">Browse names</a><br>';
	$txt .= '<br>';
	$txt .= '<a href="do=export&what=dataSource.js">Export files</a><br>';
	echo( $txt);
}

function parseSourcedataLinz( $vec, $sourceID, $urlID, $sourceName, $quite)
{
	// Beliebteste Vornamen 0-4 Jährige am 01.01.$year
	// Beliebteste Vornamen 5-9 Jährige am 01.01.$year
	// Beliebteste Vornamen des Jahres $year
	// Beliebteste Vornamen aller Linzer am 01.01.$year

	$theYear = intval( substr( $sourceName, strlen( $sourceName) - 4));
	$urlID = 4;
	if( 2010 == $theYear) {
		$urlID = 5;
	}

	if( false !== strpos( $sourceName, 'Beliebteste Vornamen 0-4')) {
		if( !$quite) {
			echo( 'Ignored data.<br>');
		}
		return;
	}
	if( false !== strpos( $sourceName, 'Beliebteste Vornamen 5-9')) {
		if( !$quite) {
			echo( 'Ignored data.<br>');
		}
		return;
	}
	if( false !== strpos( $sourceName, 'Beliebteste Vornamen aller Linzer')) {
		if( !$quite) {
			echo( 'Ignored data.<br>');
		}
		return;
	}
	if( count( $vec) < 2) {
		if( !$quite) {
			echo( '<span style="background-color:DarkOrange;padding:2px;">Unknown Linz format!</span><br>');
		}
		return;
	}

	$colPos = 0;
	$colSex = 1;
	$colName = 2;

	$data = Array();
	for( $row = 1; $row < count( $vec); ++$row) {
		if( count( $vec[ $row]) > 1) {
			$data[] = Array(
				name=> trim( $vec[ $row][ $colName]),
				sex=> $vec[ $row][ $colSex] == 'weiblich' ? 'girl' : 'boy',
				year=> $theYear,
				pos=> $vec[ $row][ $colPos],
			);
		}
	}

	parseSourcedataVec( $data, $sourceID, $urlID, $quite);
}

function parseSourcedataVorarlberg( $vec, $sourceID, $urlID, $quite)
{
	if( count( $vec) < 2) {
		if( !$quite) {
			echo( 'Unknown Vorarlberg format.<br>');
		}
		return;
	}

	$colYear = 0;
	$colSex = 1;
	$colName = 2;
	$yearPos = 0;
	$oldYear = 0;

	$data = Array();
	for( $row = 1; $row < count( $vec); ++$row, ++$yearPos) {
		if( count( $vec[ $row]) > 1) {
			if( $oldYear != intval( $vec[ $row][ $colYear])) {
				$oldYear = intval( $vec[ $row][ $colYear]);
				$yearPos = 1;
			}
			$data[] = Array(
				name=> trim( $vec[ $row][ $colName], '* '),
				sex=> $vec[ $row][ $colSex] == 'Knaben' ? 'boy' : 'girl',
				year=> $vec[ $row][ $colYear],
				pos=> $yearPos,
			);
		}
	}

	parseSourcedataVec( $data, $sourceID, $urlID, $quite);
}

function parseSourcedataEngerwitzdorf( $vec, $sourceID, $urlID, $quite)
{
	if( count( $vec) < 12) {
		if( !$quite) {
			echo( 'Unknown Engerwitzdorf format.<br>');
		}
		return;
	}

	$row = 3;
	$theYear = intval( $vec[ $row][0]);
	$row += 2;
	if( $theYear != intval( $vec[ $row][0])) {
		echo( 'Unknown Engerwitzdorf year format... ' . $theYear . ' != ' . $vec[ $row][0]);
		return;
	}

	$theSex = 'boy';
//	$sourceID = 3;
	$row += 3;
	if( 'weibl.' == trim( $vec[ $row][0])) {
		$theSex = 'girl';
//		$sourceID = 4;
	} else if( "m\xe4nnl." != trim( $vec[ $row][0])) {
		echo( 'Unknown Engerwitzdorf sex format... ' . $vec[ $row][0]);
		return;
	}

	if(( '' != trim( $vec[ $row + 1][0])) || ('' != trim( $vec[ $row + 2][0]))) {
		echo( 'Unknown Engerwitzdorf line feed format...');
		return;
	}
	$row += 3;

	$data = Array();
	$thePos = 1;
	for( ; ($row < count( $vec)) && (0 < strlen( trim( $vec[ $row][0]))); ++$row) {
		$theName = trim( $vec[ $row][0]);
		if( is_numeric( $theName)) {
			continue;
		}

		$nameExists = false;
		for( $i = 0; $i < count( $data); ++$i) {
			if( $data[ $i]['name'] == $theName) {
				$nameExists = true;
			}
		}

		if( $nameExists) {
			continue;
		}

		$data[] = Array(
			name=> $theName,
			sex=> $theSex,
			year=> $theYear,
			pos=> $thePos
		);
		++$thePos;
	}

	parseSourcedataVec( $data, $sourceID, $urlID, $quite);
}

function parseSourcedataBremen( $vec, $sourceID, $urlID, $quite)
{
	$vecCount = count( $vec);
	if( $vecCount < 20) {
		if( !$quite) {
			echo( 'Unknown Bremen format.<br>');
		}
		return;
	}

	$row = 0;
	$theYearStr = trim( $vec[ $row][0]);
	$theYearStr = substr( $theYearStr, strlen( $theYearStr) - 4);
	$theYear = intval( $theYearStr);
	if( $theYear < 2000) { echo( 'Unknown Bremen year format... ' . $theYear . ' != ' . $theYearStr); return; }

	for( ; $row < $vecCount; ++$row) {
		if( '' == $vec[ $row][0]) {
			break;
		} else if( false !== strpos( $vec[ $row][0], 'figkeit der vergebenen Vornamen')) {
			--$row;
			break;
		}
	}
	$row += 2;

	if( $row >= $vecCount) { echo( 'Unknown Bremen year format (less data)...'); return; }
	if( 'Anzahl' != trim( $vec[ $row][2])) {
		--$row;
		if( 'Anzahl' != trim( $vec[ $row-1][2])) {
			echo( 'Unknown Bremen format (Anzahl 1)...'); return;
		}
	}
	if( 'Knaben' != trim( $vec[ $row][3])) {
		if( 'Knaben' != trim( $vec[ $row-1][3])) {
			echo( 'Unknown Bremen format (Knaben)...'); return;
		}
	}
	if( 'Anzahl' != trim( $vec[ $row][4])) {
		if( 'Anzahl' != trim( $vec[ $row-1][4])) {
			echo( 'Unknown Bremen format (Anzahl 2)...'); return;
		}
	}

	++$row;

//	$colYear = 0;
	$colPos = 0;
	$colNameBoy = 3;
	$colNameGirl = 1;
	$data = Array();

	for( ; $row < $vecCount; ++$row) {
		if( intval( $vec[ $row][ $colPos]) < 1) {
			break;
		}
		if( count( $vec[ $row]) > 1) {
			$data[] = Array(
				name=> trim( $vec[ $row][ $colNameBoy]),
				sex=> 'boy',
				year=> $theYear,
				pos=> intval( $vec[ $row][ $colPos]),
			);
			$data[] = Array(
				name=> trim( $vec[ $row][ $colNameGirl]),
				sex=> 'girl',
				year=> $theYear,
				pos=> intval( $vec[ $row][ $colPos]),
			);
		}
	}

	parseSourcedataVec( $data, $sourceID, $urlID, $quite);
}

function parseSourcedataVec( $data, $sourceID, $urlID, $quite)
{
	$txt = '<br>';

	$dataCount = count( $data);
	for( $it = 0; $it < $dataCount; ++$it) {
		$item = $data[ $it];
		$isBoy = ($item['sex'] == 'boy');
		$txt .= parseSourcedataVecItem( $item, $isBoy, $sourceID, $urlID, $quite);
	}

	$txt .= $dataCount . ' items collected.<br>';

	if( !$quite) {
		echo( $txt);
	}
}

function parseSourcedataVecItem( $item, $isBoy, $sourceID, $urlID, $quite)
{
	global $gBoys;
	global $gGirls;

	$ret = '';
	$found = false;
	$good = false;
//	$name = ucwords( strtolower( $item['name']));
//	$nameUFT8 = mb_strtoupper( $item['name'], 'UTF-8');
	$name = $item['name'];
	$nameUFT8 = $name;

	if( $isBoy) {
		$found = ($gBoys[$nameUFT8] !== NULL);
	} else {
		$found = ($gGirls[$nameUFT8] !== NULL);
	}

/*	if( $name == 'ohne') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	if( $name == 'noch') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	if( $name == 'kein') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	if( $name == 'keinen') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	if( $name == 'Vorname') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	if( $name == 'Vornamen') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	if( $name == '(Eigenname)') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	if( $name == 'de') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	if( $name == 'del') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	if( $name == 'don') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	if( $name == 'oğlu') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	if( $name == '(Vorname') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	if( $name == '(Vornamen') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	if( $name == 'und') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	if( $name == 'Vatersname)') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	if( $name == 'A.') return '&nbsp;&nbsp;<span style="background-color:black;color:white;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
*/
/*	// Kleinbuchstaben am Anfang?
	if( $name != ucwords( strtolower( $item['name']))) {
		if( false !== strpos( $name, '-')) {
		} else {
			return '&nbsp;&nbsp;' . $name . ' | ' . $item['sex'] . ' | #' . $item['pos'] . ' | ' . $item['year'] . '<br>';
		}
	}
	// Platzhalter?
	if( false !== strpos( $name, 'name')) {
		return '&nbsp;&nbsp;' . $name . ' | ' . $item['sex'] . ' | #' . $item['pos'] . ' | ' . $item['year'] . '<br>';
	}
	// Abkürzung?
	if( false !== strpos( $name, '.')) {
		return '&nbsp;&nbsp;' . $name . ' | ' . $item['sex'] . ' | #' . $item['pos'] . ' | ' . $item['year'] . '<br>';
	}
	return '';*/

/*	if( !$found) {
		if( $nameUFT8 == 'YAGMUR') {
			$nameUFT8 = 'YAĞMUR';
//		} else if( $nameUFT8 == 'IREM') {
//			$nameUFT8 = 'İREM';
		} else if( $nameUFT8 == "\x3frem") { // fix Windows Latin 1-encoding
			$name = 'İrem';
			$nameUFT8 = $name;
		} else if( $nameUFT8 == 'ILAYDA') {
			$nameUFT8 = 'İLAYDA';
//		} else if( $nameUFT8 == 'RENE') {
//			$nameUFT8 = 'RENÉ';
		} else if( $nameUFT8 == "Ren\xe9") { // fix Windows Latin 1-encoding
			$name = 'René';
			$nameUFT8 = $name;
		} else if( $nameUFT8 == "Zo\xe9") { // fix Windows Latin 1-encoding
			$name = 'Zoé';
			$nameUFT8 = $name;
//		} else if( $nameUFT8 == 'NOEL') {
//			$nameUFT8 = 'NOËL';
		} else if( $nameUFT8 == "\x3fMER") { // fix Windows Latin 1-encoding
			$name = 'Ömer';
		} else if( $nameUFT8 == "Z\x3fMRA") { // fix Windows Latin 1-encoding
			$name = 'Zümra';
		} else if( $nameUFT8 == "H\x3fSEYIN") { // fix Windows Latin 1-encoding
			$name = 'Hüseyin';
		}

		if( $isBoy) {
			$found = ($gBoys[$nameUFT8] !== NULL);
		} else {
			$found = ($gGirls[$nameUFT8] !== NULL);
		}
	}*/

	$strRef = intval( $sourceID).'-'.intval( $urlID).'#'.intval( $item['pos']).','.intval( $item['year']);

	if( $found) {
		$refFound = false;

		if( $isBoy) {
			if( $gBoys[$nameUFT8]['ref'] === NULL) {
				$gBoys[$nameUFT8]['ref'] = Array();
			}
			for( $j = 0; $j < count( $gBoys[$nameUFT8]['ref']); ++$j) {
				$refStr = $gBoys[$nameUFT8]['ref'][$j];
				$refSourceP = strpos( $refStr, '-');
				$refSource = intVal( substr( $refStr, 0, $refSourceP));
				$refUrlP = strpos( $refStr, '#');
				$refUrl = intVal( substr( $refStr, $refSourceP + 1, $refUrlP - $refSourceP - 1));
				$refPosP = strpos( $refStr, ',');
				$refPos = intVal( substr( $refStr, $refUrlP + 1, $refPosP - $refUrlP - 1));
				$refYear = intVal( substr( $refStr, $refPosP + 1));

				if(( $refSource == $sourceID) && ( $refUrl == $urlID) && ($refYear == $item['year'])) {
					$refFound = true;
					if( $refPos == intval( $item['pos'])) {
						$good = true;
					} else {
						$ret .= '<span style="background-color:#ffd0d0;padding:2px;">Updated pos</span> Old pos: ' . $refPos . ' New pos: ' . intval( $item['pos']) . '<br>';
						$gBoys[$nameUFT8]['ref'][$j] = $strRef;
					}
				}
			}
			if( !$refFound) {
				$ret .= '<span style="background-color:#d0d0d0;padding:2px;">Added reference data</span><br>';
				$gBoys[$nameUFT8]['ref'][] = $strRef;
			}
		} else {
			if( $gGirls[$nameUFT8]['ref'] === NULL) {
				$gGirls[$nameUFT8]['ref'] = Array();
			}
			for( $j = 0; $j < count( $gGirls[$nameUFT8]['ref']); ++$j) {
				$refStr = $gGirls[$nameUFT8]['ref'][$j];
				$refSourceP = strpos( $refStr, '-');
				$refSource = intVal( substr( $refStr, 0, $refSourceP));
				$refUrlP = strpos( $refStr, '#');
				$refUrl = intVal( substr( $refStr, $refSourceP + 1, $refUrlP - $refSourceP - 1));
				$refPosP = strpos( $refStr, ',');
				$refPos = intVal( substr( $refStr, $refUrlP + 1, $refPosP - $refUrlP - 1));
				$refYear = intVal( substr( $refStr, $refPosP + 1));

				if(( $refSource == $sourceID) && ( $refUrl == $urlID) && ($refYear == $item['year'])) {
					$refFound = true;
					if( $refPos == intval( $item['pos'])) {
						$good = true;
					} else {
						$ret .= '<span style="background-color:#ffd0d0;padding:2px;">Updated pos</span> Old pos: ' . $refPos . ' New pos: ' . intval( $item['pos']) . '<br>';
						$gGirls[$nameUFT8]['ref'][$j] = $strRef;
					}
				}
			}
			if( !$refFound) {
				$ret .= '<span style="background-color:#d0d0d0;padding:2px;">Added reference data</span><br>';
				$gGirls[$nameUFT8]['ref'][] = $strRef;
			}
		}
	}

	if( $good) {
		return '';
	}

	if( !$found) {
		if( $isBoy) {
			$gBoys[$nameUFT8] = Array( name=> $name, id=> count($gBoys), ref=> Array($strRef) );
		} else {
			$gGirls[$nameUFT8] = Array( name=> $name, id=> count($gGirls), ref=> Array($strRef) );
		}

/*		$ret .= '$nameUFT8 '.$nameUFT8;
		for( $i = 0; $i < strlen( $nameUFT8); ++$i) {
			$ret .= ' \\x' . dechex( ord( $nameUFT8[ $i]));
		}
		$ret .= '<br>';*/
//		$ret .= '<br>Name in database: '.mb_strtoupper( gGirls[101]['name'], 'UTF-8');
//		for( $i = 0; $i < strlen( mb_strtoupper( gGirls[101]['name'], 'UTF-8')); ++$i) {
//			$ret .= ' \\x' . dechex( ord( mb_strtoupper( gGirls[101]['name'], 'UTF-8')[ $i]));
//		}

		$ret .= '&nbsp;&nbsp;<span style="background-color:';
		if( $isBoy) {
			$ret .= 'LightBlue';
		} else {
			$ret .= 'LightCoral';
		}
		return $ret . ';padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	}

	return '&nbsp;&nbsp;' . $name . ' | ' . $item['sex'] . ' | #' . $item['pos'] . ' | ' . $item['year'] . ' | ' . $ret;
}

function showPageUpdateSourcedata()
{
	global $gSource;

	$txt = '';
	$txt .= '<h1>Update dirty data</h1>';
	$txt .= '<br>';
	echo( $txt);

	$dirtyCount = 0;
	for( $i = 0; $i < count( $gSource); ++$i) {
//	for( $i = 48; $i < count( $gSource); ++$i) {
		if( $gSource[$i]['autoUpdate'] <= 0) {
			continue;
		}

		++$dirtyCount;

		$txt = '';
		$txt .= $gSource[$i]['name'] . '<br>';
		$txt .= '&nbsp;&nbsp;Update available since ' . $gSource[$i]['autoUpdate'] . ' days.<br>';
		echo( $txt);

		for( $j = 0; $j < count( $gSource[$i]['autoUrl']); ++$j) {
			if( 0 === strpos( $gSource[$i]['autoUrl'][$j], '/katalog/storage')) {
				$gSource[$i]['autoUrl'][$j] = 'http://data.gv.at' . $gSource[$i]['autoUrl'][$j];
			} else
			if( 0 === strpos( $gSource[$i]['autoUrl'][$j], '/at.gv.brz.ogd/storage')) {
				$gSource[$i]['autoUrl'][$j] = 'http://data.gv.at/katalog/' . substr( $gSource[$i]['autoUrl'][$j], 15);
			} else
			if( 0 === strpos( $gSource[$i]['autoUrl'][$j], '/private/')) {
				$gSource[$i]['autoUrl'][$j] = dirname(__FILE__) . '/data/' . $gSource[$i]['autoUrl'][$j];
			}

			$name = $gSource[$i]['autoName'][$j];
			if( strlen( $name) == 0) {
				$name = '[Data]';
			}
			if( $name == '') {
				$name = '[Data]';
			}
			$txt = '&nbsp;&nbsp;' . ($j+1) . '. <a href="' . $gSource[$i]['autoUrl'][$j]. '">' . $name . '</a><br>';
			echo( $txt);

			parseSourcedataURL( $i, $j, false);

			$txt = '<br>';
			echo( $txt);
		}

		break;
	}

	$txt = '';

	if( 0 == $dirtyCount) {
		$txt .= 'All data are clean.<br><br>';
		$txt .= '<a href="do=browse&what=sources">OK</a><br>';
	} else {
		$txt .= '<br><br>';
		$txt .= '<a href="do=save&what=sourcedata">Save</a> - ';
		$txt .= '<a href="do=browse&what=sources">Cancel</a><br>';
	}

	echo( $txt);
}

function showPageSaveSourcedata()
{
	global $gSource;

	$txt = '';
	$txt .= '<h1>Save dirty data</h1>';
	$txt .= '<br>';
	echo( $txt);

	for( $i = 0; $i < count( $gSource); ++$i) {
		if( $gSource[$i]['autoUpdate'] <= 0) {
			continue;
		}

		$txt = '';
		$txt .= 'Saving ' . $gSource[$i]['name'] . '<br>';
		echo( $txt);

		for( $j = 0; $j < count( $gSource[$i]['autoUrl']); ++$j) {
			if( 0 === strpos( $gSource[$i]['autoUrl'][$j], '/katalog/storage')) {
				$gSource[$i]['autoUrl'][$j] = 'http://data.gv.at' . $gSource[$i]['autoUrl'][$j];
			} else
			if( 0 === strpos( $gSource[$i]['autoUrl'][$j], '/at.gv.brz.ogd/storage')) {
				$gSource[$i]['autoUrl'][$j] = 'http://data.gv.at/katalog/' . substr( $gSource[$i]['autoUrl'][$j], 15);
			} else
			if( 0 === strpos( $gSource[$i]['autoUrl'][$j], '/private/')) {
				$gSource[$i]['autoUrl'][$j] = dirname(__FILE__) . '/data/' . $gSource[$i]['autoUrl'][$j];
			}

			$name = $gSource[$i]['autoName'][$j];
			if( strlen( $name) == 0) {
				$name = '[Data]';
			}
			if( $name == '') {
				$name = '[Data]';
			}

			parseSourcedataURL( $i, $j, true);
		}

		$gSource[$i]['autoModified'] = date( 'Y-m-d');

		break;
	}

	gBoysToFile();
	gGirlsToFile();
	gSourceToFile();

	$txt = '';
	$txt .= '<br>';
	$txt .= '<a href="do=update&what=sourcemetadata">Done</a><br>';
	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

function showPageBrowseNames()
{
	$txt = '';
	$txt .= '<h1>Names list</h1>';
	$txt .= '<a href="do=">Back to main</a><br>';
	$txt .= '<br>';
	$txt .= '<a href="do=update&what=namehitlist">Update hit lists</a><br>';
	$txt .= '<br>';

	echo( $txt);
}

function getPageUpdateNamehitlist( $value, $top, $yearFrom, $yearTo)
{
	global $gSource;
	global $gMeanings;

	$style = '';
	if( !isset( $gMeanings['name'])) {
		$style = ' style="text-decoration:none;border-bottom:1px solid red;"';
	}

	$txt = '<a href="do=name&what='.$value['name'].'" '.$style.'>'.$value['name'].'</a>';
	$bestSource = '';
	$bestNum = 100000;
	$bestYear = 0;

	foreach( $value['ref'] as $refvalue) {
		$posSource = strpos( $refvalue, '-');
		$posNum = strpos( $refvalue, '#', $posSource);
		$posYear = strpos( $refvalue, ',', $posNum);

		$strNum = substr( $refvalue, $posNum + 1, $posYear - $posNum - 1);
		$strYear = substr( $refvalue, $posYear + 1);

		if(( $strNum <= $top) && ($yearFrom <= $strYear) && ($strYear <= $yearTo)) {
			$line = '&nbsp;&nbsp;#'.$strNum;
			$line .= ' in '.$strYear;

			$strSource = substr( $refvalue, 0, $posSource);
			$strUrl = substr( $refvalue, $posSource + 1, $posNum - $posSource - 1);

			foreach( $gSource as $source) {
				if( $strSource == $source['id']) {
					if(( $bestNum >= $strNum) && ($bestYear < $strYear)) {
						$bestSource = $source['de-DE'][$strUrl];
						$bestNum = $strNum;
						$bestYear = $strYear;
					}
				}
			}
		}
	}

	if( $bestYear > 0) {
		return $txt.' (Platz '.$bestNum.', beliebteste Vornamen '.$bestYear.' in '.$bestSource.')<br>';
	}

	return '';
}

function showPageUpdateNamehitlist()
{
	global $gBoys;
	global $gGirls;

	$top = 1;
	$yearFrom = 2004;
	$yearTo = 2013;

	$txt = '';
	$txt .= '<h1>Update hit lists</h1>';
	$txt .= '<a href="do=">Back to main</a><br>';
	$txt .= '<br>';
	$txt .= 'Start parsing top '.$top.' given names in '.$yearFrom.'-'.$yearTo.'<br>';
	$txt .= '<br>';
	echo( $txt);

	foreach( $gBoys as $value) {
		echo( getPageUpdateNamehitlist( $value, $top, $yearFrom, $yearTo));
	}
	foreach( $gGirls as $value) {
		echo( getPageUpdateNamehitlist( $value, $top, $yearFrom, $yearTo));
	}

	$txt = '<br><br>' . count( $gBoys)+count( $gGirls). ' given names analysed';
	$txt .= '<br><br>';
	$txt .= '<a href="do=browse&what=names">Back to list</a><br>';
	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

function getPageNameRef( $value)
{
	global $gSource;

	$txt = '';

	foreach( $value['ref'] as $refvalue) {
		$posSource = strpos( $refvalue, '-');
		$posNum = strpos( $refvalue, '#', $posSource);
		$posYear = strpos( $refvalue, ',', $posNum);

		$strSource = substr( $refvalue, 0, $posSource);
		$strUrl = substr( $refvalue, $posSource + 1, $posNum - $posSource - 1);
		$strNum = substr( $refvalue, $posNum + 1, $posYear - $posNum - 1);
		$strYear = substr( $refvalue, $posYear + 1);

		$line = '&nbsp;&nbsp;#'.$strNum;
		$line .= ' in '.$strYear;

		foreach( $gSource as $source) {
			if( $strSource == $source['id']) {
//				$line = '&nbsp;&nbsp;Platz '.$strNum.', beliebteste Vornamen '.$strYear.' in '.$source['de-DE'][$strUrl];
//				$line = '&nbsp;&nbsp;Platz '.$strNum.', beliebteste Vornamen '.$strYear.' in '.nutsGetName($source['manNUTS'][$strUrl])['de-DE'];
				$line = '&nbsp;&nbsp;Platz '.$strNum.', beliebteste Vornamen '.$strYear.' in '.nutsGetName($source['manNUTS'][$strUrl]);
			}
		}

		$txt .= $line.'<br>';
	}

	return $txt;
}

function getPageNameAlt( $value)
{
	global $gBoys;
	global $gGirls;
	global $gMeanings;

	$style = ' style="text-decoration:none;border-bottom:1px solid red;"';
	$txt = '';

	$txt .= '&nbsp;&nbsp;Jungennamen:';
	if( isset( $value['altM']) && (0 < count( $value['altM']))) {
		foreach( $value['altM'] as $altvalue) {
			if( isset( $gMeanings[$altvalue])) {
				$txt .= ' <a href="do=name&what='.$altvalue.'">'.$altvalue.'</a>';
			} else if( isset( $gBoys[$altvalue])) {
				$txt .= ' <a href="do=name&what='.$altvalue.'" '.$style.'>'.$altvalue.'</a>';
			} else {
				$txt .= ' '.$altvalue;
			}
		}
	}
	$txt .= '<br>';
	$txt .= '&nbsp;&nbsp;Mädchennamen:';
	if( isset( $value['altF']) && (0 < count( $value['altF']))) {
		foreach( $value['altF'] as $altvalue) {
			if( isset( $gMeanings[$altvalue])) {
				$txt .= ' <a href="do=name&what='.$altvalue.'">'.$altvalue.'</a>';
			} else if( isset( $gGirls[$altvalue])) {
				$txt .= ' <a href="do=name&what='.$altvalue.'" '.$style.'>'.$altvalue.'</a>';
			} else {
				$txt .= ' '.$altvalue;
			}
		}
	}
	$txt .= '<br>';

	return $txt;
}

function getPageNameForm( $name)
{
	global $gBoys;
	global $gGirls;

	$oldText = '';
	if( isset( $gBoys[ $name]) && isset( $gBoys[ $name]['text'])) {
		$oldText = $gBoys[ $name]['text'];
	} else if( isset( $gGirls[ $name]) && isset( $gGirls[ $name]['text'])) {
		$oldText = $gGirls[ $name]['text'];
	}

	$oldWiki = '';
	if( isset( $gBoys[ $name]) && isset( $gBoys[ $name]['url'])) {
		$oldWiki = $gBoys[ $name]['url'];
	} else if( isset( $gGirls[ $name]) && isset( $gGirls[ $name]['url'])) {
		$oldWiki = $gGirls[ $name]['url'];
	}

	$oldBoy = '';
	if( isset( $gBoys[ $name]) && isset( $gBoys[ $name]['altM'])) {
		foreach( $gBoys[ $name]['altM'] as $altvalue) {
			$oldBoy .= $altvalue.',';
		}
	}
	if( isset( $gGirls[ $name]) && isset( $gGirls[ $name]['altM'])) {
		foreach( $gGirls[ $name]['altM'] as $altvalue) {
			$oldBoy .= $altvalue.',';
		}
	}
	if( 0 < strlen( $oldBoy)) {
		$oldBoy = substr( $oldBoy, 0, strlen( $oldBoy) - 1);
	}

	$oldGirl = '';
	if( isset( $gBoys[ $name]) && isset( $gBoys[ $name]['altF'])) {
		foreach( $gBoys[ $name]['altF'] as $altvalue) {
			$oldGirl .= $altvalue.',';
		}
	}
	if( isset( $gGirls[ $name]) && isset( $gGirls[ $name]['altF'])) {
		foreach( $gGirls[ $name]['altF'] as $altvalue) {
			$oldGirl .= $altvalue.',';
		}
	}
	if( 0 < strlen( $oldGirl)) {
		$oldGirl = substr( $oldGirl, 0, strlen( $oldGirl) - 1);
	}

	$txt = '<form action="do=save&what=name" method="post">';
	$txt .= '<div class="supportPage">';
	$txt .= '<input name="name" type="hidden" value="'.$name.'">';
	$txt .= '<label for="text">Set the explanation text (use „ and “):</label><br>';
	$txt .= '<textarea id="text" name="text" rows="10" autofocus>'.$oldText.'</textarea><br>';
	$txt .= '<label for="wiki">Set the wikipedia reference link:</label><br>';
	$txt .= '<input id="wiki" name="wiki" size="100" value="'.$oldWiki.'"><br>';
	$txt .= '<label for="copyright">Set copyright notes (comma separated):</label><br>';
	$txt .= '<textarea id="copyright" name="copyright" rows="10"></textarea><br>';
	$txt .= '<label for="boy">Set similar given boy names (comma separated):</label><br>';
	$txt .= '<textarea id="boy" name="boy" rows="3">'.$oldBoy.'</textarea><br>';
	$txt .= '<label for="girl">Set similar given girl names (comma separated):</label><br>';
	$txt .= '<textarea id="girl" name="girl" rows="3">'.$oldGirl.'</textarea><br>';
	$txt .= '<input type="submit" name="next" value="Create entry"><br>';
	$txt .= '</div>';
	$txt .= '</form>';
	$txt .= '<br><br>';

	return $txt;
}

function showPageName( $name)
{
	global $gBoys;
	global $gGirls;

	$txt = '';
	$txt .= '<h1>'.$name.'</h1>';
	$txt .= '<a href="do=">Back to main</a><br>';
	$txt .= '<br>';
	echo( $txt);

	if( !isset( $gMeanings[$name])) {
		echo( getPageNameForm( $name));
	}

	if( isset( $gBoys[ $name]) && isset( $gGirls[ $name])) {
		echo( $name.' ist ein männlicher und weiblicher Vorname.<br><br>');
		if( isset( $gBoys[ $name]['text'])) {
			echo( 'Old text boy:<br>'.$gBoys[ $name]['text'].'<br><br>');
		}
		if( isset( $gGirls[ $name]['text'])) {
			echo( 'Old text girl:<br>'.$gGirls[ $name]['text'].'<br><br>');
		}
		if( isset( $gBoys[ $name]['url'])) {
			echo( '<a href="'.$gBoys[ $name]['url'].'" target="_blank">Wikipedia boy</a><br><br>');
		}
		if( isset( $gGirls[ $name]['url'])) {
			echo( '<a href="'.$gGirls[ $name]['url'].'" target="_blank">Wikipedia girl</a><br><br>');
		}
		echo( 'Ähnliche Namen männlich:<br>');
		echo( getPageNameAlt( $gBoys[ $name]));
		echo( '<br>');
		echo( 'Ähnliche Namen weiblich:<br>');
		echo( getPageNameAlt( $gGirls[ $name]));
		echo( '<br>');
		echo( 'Hitliste männlich:<br>');
		echo( getPageNameRef( $gBoys[ $name]));
		echo( '<br>');
		echo( 'Hitliste: weiblich<br>');
		echo( getPageNameRef( $gGirls[ $name]));
	} else if( isset( $gBoys[ $name])) {
		echo( $name.' ist ein männlicher Vorname.<br><br>');
		if( isset( $gBoys[ $name]['text'])) {
			echo( 'Old text:<br>'.$gBoys[ $name]['text'].'<br><br>');
		}
		if( isset( $gBoys[ $name]['url'])) {
			echo( '<a href="'.$gBoys[ $name]['url'].'" target="_blank">Wikipedia</a><br><br>');
		}
		echo( 'Ähnliche Namen:<br>');
		echo( getPageNameAlt( $gBoys[ $name]));
		echo( '<br>');
		echo( 'Hitliste:<br>');
		echo( getPageNameRef( $gBoys[ $name]));
	} else if( isset( $gGirls[ $name])) {
		echo( $name.' ist ein weiblicher Vorname.<br><br>');
		if( isset( $gGirls[ $name]['text'])) {
			echo( 'Old text:<br>'.$gGirls[ $name]['text'].'<br><br>');
		}
		if( isset( $gGirls[ $name]['url'])) {
			echo( '<a href="'.$gGirls[ $name]['url'].'" target="_blank">Wikipedia</a><br><br>');
		}
		echo( 'Ähnliche Namen:<br>');
		echo( getPageNameAlt( $gGirls[ $name]));
		echo( '<br>');
		echo( 'Hitliste:<br>');
		echo( getPageNameRef( $gGirls[ $name]));
	}
}

function showPageSaveName()
{
	$txt = '';
	$txt .= '<h1>Save name</h1>';
	$txt .= '<a href="do=">Back to main</a><br>';
	$txt .= '<br>';
	echo( $txt);

	$name = '';
	if( isset( $_POST[ 'name'])) {
		$name = $_POST[ 'name'];
	}
	$txt = 'Name: '.$name.'<br>';

	$text = '';
	if( isset( $_POST[ 'text'])) {
		$text = $_POST[ 'text'];
	}
	$txt .= 'Text: '.$text.'<br>';

	$wiki = '';
	if( isset( $_POST[ 'wiki'])) {
		$wiki = $_POST[ 'wiki'];
	}
	$txt .= 'Wikipedia: '.$wiki.'<br>';

	$copyright = '';
	if( isset( $_POST[ 'copyright'])) {
		$copyright = $_POST[ 'copyright'];
	}
	$txt .= 'Copyright: '.$copyright.'<br>';
	$copyright = explode( ',', $copyright);

	$boy = '';
	if( isset( $_POST[ 'boy'])) {
		$boy = $_POST[ 'boy'];
	}
	$txt .= 'Boy: '.$boy.'<br>';
	$boy = explode( ',', $boy);

	$girl = '';
	if( isset( $_POST[ 'girl'])) {
		$girl = $_POST[ 'girl'];
	}
	$txt .= 'Girl: '.$girl.'<br>';
	$girl = explode( ',', $girl);

	echo( $txt);

//	gMeaningsToFile();
}

//--------------------------------------------------------------------------------------------------

function main()
{
	global $do;
	global $what;

	if( $do == '') {
		showPageHome();
	} else if( $do == 'name' && $what != '') {
		showPageName( $what);
	} else if( $do == 'source' && $what != '') {
		sourcesShowPageItem( $what);
	} else if( $do == 'browse' && $what == 'sources') {
		sourcesShowPageBrowseAll();
	} else if( $do == 'browse' && $what == 'names') {
		showPageBrowseNames();
	} else if( $do == 'browse' && $what == 'nuts') {
		nutsShowPageBrowse();
	} else if( $do == 'update' && $what == 'sourcemetadata') {
		metadataShowPageUpdate();
	} else if( $do == 'update' && $what == 'sourcedata') {
		showPageUpdateSourcedata();
	} else if( $do == 'update' && $what == 'namehitlist') {
		showPageUpdateNamehitlist();
	} else if( $do == 'save' && $what == 'sourcedata') {
		showPageSaveSourcedata();
	} else if( $do == 'save' && $what == 'name') {
		showPageSaveName();
	} else if( $do == 'export' && $what == 'dataSource.js') {
		exportShowPageDataSourceJS();
	} else if( $do == 'export' && $what == 'dataName.js') {
		exportShowPageDataNameJS();
	} else {
		echo( '<h1>ERROR</h1>');
		echo( '<br>Did not understand '.$do.' '.$what.'.');
	}
}

//--------------------------------------------------------------------------------------------------

	echo( "<!DOCTYPE html>\n");
	echo( "<html>\n");

	echo( "<head>\n");
	echo( "<title>Name your babe backend</title>\n");
	echo( "<meta http-equiv='content-type' content='text/html; charset=UTF-8' />\n");
	echo( "<style type='text/css'>\n");
	echo( "a {color:ForestGreen;}\n");
	echo( "h1 {border-bottom:1px solid ForestGreen;margin:-1em -1em 1em -1em;background:#444444;padding:1em;font-size:1em;}\n");
	echo( "hr {border-bottom:1px solid ForestGreen;margin:0 -1em 0 -1em;}\n");
	echo( "</style>\n");
	echo( "</head>\n");

	echo( "<body style='margin:0;padding:0;font-size:18px;font-family:\"Arial\";font-weight:300;background:#333333;color:#cccccc;'>\n");
	echo( "<div style='background:ForestGreen;color:white;margin:0;padding:1em;text-align:center;'>Name your babe backend</div>\n");
	echo( "<div style='margin:1em;'>\n");

	main();

	echo( "</div>\n");
	echo( "</body>\n");
	echo( "</html>\n");
?>
