<?php
	set_time_limit( 0);
	ignore_user_abort( true);

	$do = '';
	$what = '';
	$whatId = '';
	if( isset( $_GET[ 'do'])) {
		$do = $_GET[ 'do'];
	}
	if( isset( $_GET[ 'what'])) {
		$what = $_GET[ 'what'];
	}
	if( isset( $_GET[ 'id'])) {
		$whatId = $_GET[ 'id'];
	}

	include_once( "HarvestMetadata.php");
	include_once( "HarvestData.php");
	include_once( "HarvestNames.php");
	include_once( "HarvestNuts.php");
	include_once( "HarvestTools.php");

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

//$gBoysMem = memory_get_peak_usage( false);
//include_once( "data/boys.php");
//$gBoysMem = memory_get_peak_usage( false) - $gBoysMem;

function gBoysToFile()
{
	global $gBoys;

	$contents = '<?php'."\n".'$gBoys=';
	$contents .= var_export( $gBoys, true);
	$contents .= ';'."\n".'?>'."\n";

//	file_put_contents( dirname(__FILE__) . '/data/boys.php', $contents);
	file_put_contents( dirname(__FILE__) . '/backup/boys-' . date( 'Y-W') . '.php', $contents);
}

//--------------------------------------------------------------------------------------------------
// $gGirls
//--------------------------------------------------------------------------------------------------

//$gGirlsMem = memory_get_peak_usage( false);
//include_once( "data/girls.php");
//$gGirlsMem = memory_get_peak_usage( false) - $gGirlsMem;

function gGirlsToFile()
{
	global $gGirls;

	$contents = '<?php'."\n".'$gGirls=';
	$contents .= var_export( $gGirls, true);
	$contents .= ';'."\n".'?>'."\n";

//	file_put_contents( dirname(__FILE__) . '/data/girls.php', $contents);
	file_put_contents( dirname(__FILE__) . '/backup/girls-' . date( 'Y-W') . '.php', $contents);
}

//--------------------------------------------------------------------------------------------------

function showPageHome()
{
	global $HarvestNames;
	global $MetadataVec;

	$memory_limit = ini_get( 'memory_limit');
	if( preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
		if( $matches[2] == 'M') {
			$memory_limit = $matches[1] * 1024 * 1024;
		} else if( $matches[2] == 'K') {
			$memory_limit = $matches[1] * 1024;
		}
	}

	$HarvestNames->load();

	$txt = '';
	$txt .= '<div class="log">Admin area<br>==========<br><br>';
	$txt .= 'Memory max:&nbsp;&nbsp;&nbsp;' . intval( $memory_limit /1024/1024*10)/10 . ' MByte<br>';
	$txt .= 'Memory used:&nbsp;&nbsp;' . intval( memory_get_peak_usage() /1024/1024*10)/10 . ' MByte<br>';
	$txt .= 'Memory:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . intval( memory_get_peak_usage() * 100 / $memory_limit) . '%<br>';
	$txt .= '<br>';
	$txt .= 'Male mem:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . intval( $HarvestNames->maleUsedMemory /1024/1024*10)/10 . ' MByte<br>';
	$txt .= 'Female mem:&nbsp;&nbsp;&nbsp;' . intval( $HarvestNames->femaleUsedMemory /1024/1024*10)/10 . ' MByte<br>';
	$txt .= '<br>';

	$names = array_merge( $HarvestNames->male, $HarvestNames->female);
	$numMales = count( $HarvestNames->male);
	$numFemales = count( $HarvestNames->female);
	$numNames = count( $names);
	$numUniqueMales = count( array_diff( $HarvestNames->male, $HarvestNames->female));
	$numUniqueFemales = count( array_diff( $HarvestNames->female, $HarvestNames->male));
	$numUniqueNames = count( array_count_values( $names));
//	$numUniqueNames = $numUnisex + $numUniqueMales + $numUniqueFemales;
	$numUnisex = $numNames - $numUniqueNames;

	$txt .= 'Male count:&nbsp;&nbsp;&nbsp;' . $numMales . ' (' . $numUniqueMales . ' unique) names<br>';
	$txt .= 'Female count: ' . $numFemales . ' (' . $numUniqueFemales . ' unique) names<br>';
	$txt .= 'Unisex count: ' . $numUnisex . ' names<br>';
	$txt .= 'Sum:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $numUniqueNames . ' names<br>';
	$txt .= '<br>';
	$txt .= 'Source count: ' . count( $MetadataVec) . '<br>';
	$txt .= '<br>';
	$txt .= '[<a href="do=browse&what=names">Show names</a>]<br>';
	$txt .= '[<a href="do=browse&what=sources">Show source list</a>]<br>';
	$txt .= '[<a href="do=browse&what=nuts">Show source regions</a>]<br>';
	$txt .= '</div>';
	echo( $txt);

	$txt = '';
	$txt .= '<br>';
	$txt .= '<hr>';
	$txt .= '<br>';
	$txt .= '<a href="do=export&what=dataSource.js">Export files</a><br>';
	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

// used in sources.php
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

//--------------------------------------------------------------------------------------------------

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

	if( false) {
		if( $name == 'ohne') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
		if( $name == 'noch') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
		if( $name == 'kein') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
		if( $name == 'keinen') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
		if( $name == 'Vorname') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
		if( $name == 'Vornamen') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
		if( $name == '(Eigenname)') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
		if( $name == 'de') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
		if( $name == 'del') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
		if( $name == 'don') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
		if( $name == 'oğlu') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
		if( $name == '(Vorname') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
		if( $name == '(Vornamen') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
		if( $name == 'und') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
		if( $name == 'Vatersname)') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
		if( $name == 'A.') return '&nbsp;&nbsp;<span style="background-color:white;color:#333333;padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	}

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
//if( $refPos < intval( $item['pos'])) { $ret.='XXXXXX<br>'; continue; }
						$ret .= '<span style="background-color:Chocolate;padding:2px;">Updated pos</span> Old pos: ' . $refPos . ' New pos: ' . intval( $item['pos']) . '<br>';
						$gBoys[$nameUFT8]['ref'][$j] = $strRef;
					}
				}
			}
			if( !$refFound) {
				$ret .= '<span style="background-color:#444444;padding:2px;">Added reference data</span><br>';
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
//if( $refPos < intval( $item['pos'])) { $ret.='XXXXXX<br>'; continue; }
						$ret .= '<span style="background-color:Chocolate;padding:2px;">Updated pos</span> Old pos: ' . $refPos . ' New pos: ' . intval( $item['pos']) . '<br>';
						$gGirls[$nameUFT8]['ref'][$j] = $strRef;
					}
				}
			}
			if( !$refFound) {
				$ret .= '<span style="background-color:#444444;padding:2px;">Added reference data</span><br>';
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
			$ret .= 'RoyalBlue';
		} else {
			$ret .= 'MediumVioletRed ';
		}
		return $ret . ';padding:2px;">' . $name . '</span> (#' . $item['pos'] . ' in ' . $item['year'] . ')<br>';
	}

	return '&nbsp;&nbsp;' . $name . ' | ' . $item['sex'] . ' | #' . $item['pos'] . ' | ' . $item['year'] . ' | ' . $ret;
}

//--------------------------------------------------------------------------------------------------

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
//		for( $j = 0; $j < 3; ++$j) {
			if( 0 === strpos( $gSource[$i]['autoUrl'][$j], '/katalog/storage')) {
				$gSource[$i]['autoUrl'][$j] = 'http://data.gv.at' . $gSource[$i]['autoUrl'][$j];
			} else
			if( 0 === strpos( $gSource[$i]['autoUrl'][$j], '/at.gv.brz.ogd/storage')) {
				$gSource[$i]['autoUrl'][$j] = 'http://data.gv.at/katalog/' . substr( $gSource[$i]['autoUrl'][$j], 15);
			} else
			if( 0 === strpos( $gSource[$i]['autoUrl'][$j], '/private/')) {
				$gSource[$i]['autoUrl'][$j] = dirname(__FILE__) . '/data' . $gSource[$i]['autoUrl'][$j];
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
	global $HarvestNames;
	global $dataHarvestStatNames;

	$txt = '';
	$txt .= '<div class="log">Names list<br>==========<br><br>';
	echo( $txt);

	$txt = '';
	$txt .= '- -------------------------------------------------------------------------------<br>';
	$txt .= '<form action="index.php" method="get" accept-charset="UTF-8">';
	$txt .= '<input type="hidden" name="do" value="name">';
	$txt .= '&nbsp;&nbsp;<input type="search" name="what" placeholder="Given name">';
	$txt .= '<input type="submit" value="Search">';
	$txt .= '</form>';
	$txt .= '- -------------------------------------------------------------------------------<br>';
	echo( $txt);

	$HarvestNames->loadAllStats();

	foreach( $dataHarvestStatNames as $firstChar => $names) {
		$txt = '';
		$sum = '';
		$txt .= strtoupper( $firstChar) . ' ';
		foreach( $names as $name => $str) {
			if( strlen( $sum) < 80) {
				$sum .= $name .', ';
				$txt .= '<a href="do=name&what='.$name.'">'.$name.'</a>, ';
				if( strlen( $sum) >= 80) {
					$txt .= '...';
				}
			}
		}
		$txt .= '<br>';
		echo( $txt);
	}

	$txt = '';
	$txt .= '- -------------------------------------------------------------------------------<br>';
	$txt .= '<br>';
	$txt .= '[<a href="do=">Show admin area</a>]<br>';
	$txt .= '</div>';

	$txt .= '<br>';
	$txt .= '<hr>';
	$txt .= '<br>';

	$txt .= '<a href="do=update&what=namehitlist">Update hit lists</a><br>';
	$txt .= '<br>';

	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

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
						$nuts = nutsGetName( $source['manNUTS'][$strUrl]);
						$bestSource = $nuts['de-DE'];
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

//--------------------------------------------------------------------------------------------------

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

	$txt = '<br><br>' . (count( $gBoys)+count( $gGirls)) . ' given names analysed';
	$txt .= '<br><br>';
	$txt .= '<a href="do=browse&what=names">Back to list</a><br>';
	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

function cmpCharts( $a, $b)
{
	if( $a['ranking'] != $b['ranking']) {
		return ($a['ranking'] < $b['ranking']) ? -1 : 1;
	}
	if( $a['year'] != $b['year']) {
		return ($a['year'] < $b['year']) ? 1 : -1;
	}
	return ($a['nuts'] > $b['nuts']) ? -1 : 1;
}

//--------------------------------------------------------------------------------------------------

function getPageNameRef( $items)
{
	global $HarvestNuts;

	$txt = '';

	usort( $items, "cmpCharts");

	foreach( $items as $item) {
		$nuts = $HarvestNuts->getNuts( $item['nuts']);
		$nutsStr = nutsGetName( $nuts);

		$txt .= '&nbsp;&nbsp;Place ' . $item['ranking'];
		$txt .= ' of the most popular name in ' . $item['year'];
		$txt .= ' in <a href="do=browse&what=source&id=' . $nuts . '">' . $nutsStr['en-US'] . '</a><br>';
	}

	return $txt;
}

//--------------------------------------------------------------------------------------------------

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

//--------------------------------------------------------------------------------------------------

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

//--------------------------------------------------------------------------------------------------

function showPageName( $name)
{
	global $HarvestNames;

	$txt = '';
	$txt .= '<div class="log">'.$name.'<br>';
	for( $i = 0; $i < mb_strlen( $name); ++$i) {
		$txt .= '=';
	}
	$txt .= '<br><br>';
	echo( $txt);

	$names = $HarvestNames->getStats( $name);
	$male = array();
	$female = array();

	if( array_key_exists( $name, $names)) {
		$str = $names[ $name];

		if( strlen( $str) >= 8) {
			for( $count = strlen( $str) - 8; $count >= 0; $count -= 8) {
				$item = Array(
					nuts => intDecode( substr( $str, $count, 2)),
					ranking => intDecode( substr( $str, $count + 2, 3)),
					year => intDecode( substr( $str, $count + 5, 2))
				);
				if( 'm' == $str[ $count + 7]) {
					$male[] = $item;
				} else {
					$female[] = $item;
				}
			}
		}
	}

	$txt = '';
	if(( count( $male) > 0) && (count( $female) > 0)) {
		$txt .= $name . ' is a male and female first name.<br><br>';
	} else if( count( $male) > 0) {
		$txt .= $name . ' is a male first name.<br><br>';
	} else if( count( $female) > 0) {
		$txt .= $name . ' is a female first name.<br><br>';
	} else {
		$txt .= $name . ' is an unknown first name.<br><br>';
	}
	echo( $txt);

	$txt = '';
	if( count( $male) > 0) {
		$txt .= 'Male charts:<br>';
		$txt .= getPageNameRef( $male);
	}
	if( count( $female) > 0) {
		$txt .= 'Female charts:<br>';
		$txt .= getPageNameRef( $female);
	}
	echo( $txt);

	$txt = '';
	$txt .= '<br>';
	$txt .= '[<a href="do=browse&what=names">Show names</a>]<br>';
	$txt .= '[<a href="do=">Show admin area</a>]<br>';
	$txt .= '</div>';

	$txt .= '<br>';
	$txt .= '<hr>';
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
	}
}

//--------------------------------------------------------------------------------------------------

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
	global $whatId;

	if( $do == '') {
		showPageHome();
	} else if( $do == 'name' && $what != '') {
		showPageName( $what);
	} else if( $do == 'browse' && $what == 'source' && $whatId != '') {
		sourcesShowPageItem( $whatId);
	} else if( $do == 'browse' && $what == 'sources') {
		sourcesShowPageBrowseAll();
	} else if( $do == 'browse' && $what == 'names') {
		showPageBrowseNames();
	} else if( $do == 'browse' && $what == 'nuts') {
		nutsShowPageBrowse();
	} else if( $do == 'update' && $what == 'sourcemetadata') {
		metadataShowPageUpdate();
	} else if( $do == 'update' && $what == 'sourcedata' && $whatId != '') {
		sourcesShowPageUpdateId( $whatId);
	} else if( $do == 'update' && $what == 'sourcedatanames' && $whatId != '') {
		sourcesShowPageUpdateNamesId( $whatId);
	} else if( $do == 'clean' && $what == 'sourcedatanames' && $whatId != '') {
		sourcesShowPageCleanNamesId( $whatId);
	} else if( $do == 'harvest' && $what == 'sourcedatanames' && $whatId != '') {
		sourcesShowPageHarvestNamesId( $whatId);
	} else if( $do == 'update' && $what == 'namehitlist') {
		showPageUpdateNamehitlist();
	} else if( $do == 'add' && $what == 'sourcedatanames' && $whatId != '') {
		sourcesShowPageAddNamesId( $whatId);
	} else if( $do == 'save' && $what == 'sourcedatanames' && $whatId != '') {
		sourcesShowPageSaveNamesId( $whatId);
	} else if( $do == 'save' && $what == 'sourcedata') {
		showPageSaveSourcedata();
	} else if( $do == 'save' && $what == 'name') {
		showPageSaveName();
	} else if( $do == 'export' && $what == 'dataSource.js') {
		exportShowPageDataSourceJS();
	} else if( $do == 'export' && $what == 'dataName.js') {
		exportShowPageDataNameJS();
	} else if( $do == 'delete' && $what == 'sourcedata' && $whatId != '') {
		showPageDeleteSourcedata( $whatId);
	} else {
		$txt = '';
		$txt .= '<div class="log">ERROR<br>=====<br><br>';
		$txt .= 'Did not understand "'.$do.' '.$what.'".<br>';
		$txt .= '<br>';
		$txt .= '[<a href="do=">Show admin area</a>]<br>';
		$txt .= '</div>';
		echo( $txt);
	}
}

//--------------------------------------------------------------------------------------------------

	echo( "<!DOCTYPE html>\n");
	echo( "<html>\n");

	echo( "<head>\n");
	echo( "<title>Name your babe backend</title>\n");
	echo( "<meta http-equiv='content-type' content='text/html; charset=UTF-8' />\n");
	echo( "<link rel='stylesheet' href='//cdn.jsdelivr.net/font-hack/2.013/css/hack-extended.min.css'>\n");
	echo( "<style type='text/css'>\n");
	echo( "body {margin:0;padding:0;font-size:18px;font-family:Arial;font-weight:300;background:#333333;color:#cccccc;}\n");
	echo( "a {color:ForestGreen;}\n");
	echo( "h1 {border-bottom:1px solid ForestGreen;margin:-1em -1em 1em -1em;background:#444444;padding:1em;font-size:1em;}\n");
	echo( "hr {border-bottom:1px solid ForestGreen;margin:0 -1em 0 -1em;}\n");
	echo( ".log {margin:.5em 0 .5em .5em;font-family:Hack,monospace;font-size:.9em;color:#fff;white-space:nowrap;overflow-x:hidden;}\n");
	echo( ".log a {color:MediumSpringGreen;text-decoration:none;}\n");
	echo( ".log a:hover {color:MediumSpringGreen;text-decoration:underline;}\n");
	echo( ".log input {font-family:Hack,monospace;font-size:1em;}\n");
	echo( ".log input[type=search] {background:none;border:0 none;border-bottom:1px MediumSpringGreen solid;color:white;padding:0;}\n");
	echo( ".log input[type=submit] {background:none;border:0 none;color:MediumSpringGreen;cursor:pointer;}\n");
	echo( ".log input[type=submit]:hover {text-decoration:underline;}\n");
	echo( "</style>\n");
	echo( "</head>\n");

	echo( "<body>\n");
	echo( "<div class='log' style='color:MediumSpringGreen;margin:0 2em 0 2.5em;font-weight:900;font-size:.6em;'>");
	// http://patorjk.com/software/taag/#p=display&f=Small%20Slant&t=Name%20your%20babe
	echo( "&nbsp;&nbsp;&nbsp;_&nbsp;&nbsp;__&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;__&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;__<br>\n");
	echo( "&nbsp;&nbsp;/&nbsp;|/&nbsp;/__&nbsp;___&nbsp;_&nbsp;&nbsp;___&nbsp;&nbsp;&nbsp;__&nbsp;_____&nbsp;&nbsp;__&nbsp;______&nbsp;&nbsp;/&nbsp;/&nbsp;&nbsp;___&nbsp;_/&nbsp;/&nbsp;&nbsp;___<br>\n");
	echo( "&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;_&nbsp;`/&nbsp;&nbsp;'&nbsp;\/&nbsp;-_)&nbsp;/&nbsp;//&nbsp;/&nbsp;_&nbsp;\/&nbsp;//&nbsp;/&nbsp;__/&nbsp;/&nbsp;_&nbsp;\/&nbsp;_&nbsp;`/&nbsp;_&nbsp;\/&nbsp;-_)<br>\n");
	echo( "/_/|_/\_,_/_/_/_/\__/&nbsp;&nbsp;\_,&nbsp;/\___/\_,_/_/&nbsp;&nbsp;&nbsp;/_.__/\_,_/_.__/\__/<br>\n");
	echo( "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/___/<br>\n");
	echo( "</div>\n");

	echo( "<div style='margin:1em;'>\n");

	main();

	echo( "</div>\n");
	echo( "</body>\n");
	echo( "</html>\n");
?>
