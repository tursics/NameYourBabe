<?php

//--------------------------------------------------------------------------------------------------
// $gSource
//--------------------------------------------------------------------------------------------------

include_once( "data/sources.php");
// old tags (to be removed)
//   'Modified'

function gSourceToFile()
{
	global $gSource;

	$contents = '<?php'."\n".'$gSource=';
	$contents .= var_export( $gSource, true);
	$contents .= ';'."\n".'?>'."\n";

	file_put_contents( dirname(__FILE__) . '/data/sources.php', $contents);
	file_put_contents( dirname(__FILE__) . '/backup/sources-' . date( 'Y-W') . '.php', $contents);
}

//--------------------------------------------------------------------------------------------------

function parseSourcedataURL( $sourceIndex, $urlID, $quite)
{
	global $gSource;
	$ignore = false;

	$file = $gSource[$sourceIndex]['autoUrl'][$urlID];

	if( 'http://daten.ulm.de/sites/default/files/Vornamensstatistik_1998-2013_ulm_0.zip' == $file) {
		$urlID = 1;
	}

	if( 'http://www.tirol.gv.at/applikationen/e-government/data/datenkatalog/bevoelkerung/top-100-vornamen-in-tirol/' == $file) {
		$ignore = true;
	} else if( '.pdf' == substr( $file, -4)) {
		$ignore = true;
	} else if( '.zip' == substr( $file, -4)) {
		$ignore = true;

		$tmpfile = '../tmp_file.zip';
		if( !copy( $file, $tmpfile)) {
			if( !$quite) {
				echo "Failed to copy $file...<br>";
			}
		} else {
			$zip = new ZipArchive();
			$zip->open( $tmpfile);

			for( $i = 0; $i < $zip->numFiles; ++$i) {
				$info = $zip->statIndex( $i);
				if( !$quite) {
					echo '&nbsp;&nbsp;zip file ' . ($i+1) . '/' . $zip->numFiles . ' (' . $info['name'] . ')';
				}
				$file = 'zip://' . $zip->filename . '#' . $info['name'];
				parseSourcedataAll( $file, $sourceIndex, $urlID, $quite);
			}
		}
 	}

	if( $ignore) {
		if( !$quite) {
			echo( 'Ignore data<br>');
		}
	} else {
		parseSourcedataAll( $file, $sourceIndex, $urlID, $quite);
	}
}

//--------------------------------------------------------------------------------------------------

function parseSourcedataAll( $file, $sourceIndex, $urlID, $quite)
{
	global $gSource;

	$sourceID = $gSource[$sourceIndex]['id'];
	$contents = file_get_contents( $file);
//	$contents = utf8_encode( $contents);

	$vec = Array();
	$rows = explode( "\n", $contents);
	$rowsCount = count( $rows);
	for( $i = 0; $i < $rowsCount; ++$i) {
		$vec[] = explode( ";", $rows[ $i]);
	}

	$vecCount = count( $vec);
	if(( $vecCount > 2) && ($vec[2][1] == 'NUTS2')) {
		parseSourcedataNUTS( $vec, $sourceID, $urlID, $quite);
	} else if(( $vecCount > 0) && ($vec[0][0] == 'NUTS2')) {
		parseSourcedataNUTS( $vec, $sourceID, $urlID, $quite);
	} else if(( $vecCount > 0) && (substr( $vec[0][0], 0, 21) == 'GemeindeEngerwitzdorf')) {
		parseSourcedataEngerwitzdorf( $vec, $sourceID, $urlID, $quite);
	} else if(( $vecCount > 0) && ($vec[0][0] == 'Rang') && ($vec[0][1] == 'Geschlecht') && (trim( $vec[0][2]) == 'Vorname')) {
		parseSourcedataLinz( $vec, $sourceID, $urlID, $gSource[$sourceIndex]['autoName'][$urlID], $quite);
	} else if(( $vecCount > 0) && /*($vec[0][0] == 'Rang') &&*/ ($vec[0][1] == 'NUTS') && (trim( $vec[0][2]) == 'Geschlecht') && (trim( $vec[0][3]) == 'Vorname') && (trim( $vec[0][4]) == 'Jahr')) {
		parseSourcedataSalzburg( $vec, $sourceID, $urlID, $quite);
	} else if(( $vecCount > 0) && ($vec[0][0] == 'Jahr') && ($vec[0][1] == 'Geschlecht') && (trim( $vec[0][2]) == 'Vorname')) {
		parseSourcedataVorarlberg( $vec, $sourceID, $urlID, $quite);
	} else if(( $vecCount > 0) && ($vec[0][1] == '"Vorname"') && (trim( $vec[0][2]) == '"Geschlecht"') && (trim( $vec[0][3]) == '"Anzahl"')) {
		parseSourcedataZuerich( $vec, $sourceID, $urlID, $quite);
	} else if(( $vecCount > 0) && /*($vec[0][0] == 'vorname') &&*/ ($vec[0][1] == 'anzahl') && (trim( $vec[0][2]) == 'geschlecht')) {
		$theYear = 2012; // berlin missing year number in 2012
		preg_match_all('!\d+!', $file, $yearVec);
		if( 0 < count( $yearVec[0])) {
			$lastYear = $yearVec[0][count($yearVec[0])-1];
			if(( 1900 < $lastYear) && ($lastYear < 2100)) {
				$theYear = $lastYear;
			}
		}
		parseSourcedataBerlinChemnitzUlm( $vec, $sourceID, $urlID, $theYear, $quite);
	} else if(( $vecCount > 1) && ($vec[1][0] == 'Anzahl der  Kinder mit')) {
		parseSourcedataBremen( $vec, $sourceID, $urlID, $quite);
	} else if(( $vecCount > 1) && (trim( $vec[1][0]) == 'Anzahl der Kinder mit')) {
		parseSourcedataBremen( $vec, $sourceID, $urlID, $quite);
	} else {
		if( !$quite) {
			echo( '<span style="background-color:DarkOrange;padding:2px;">Unknown format!</span><br>');
		}
	}
}

//--------------------------------------------------------------------------------------------------

function parseSourcedataNUTS( $vec, $sourceID, $urlID, $quite)
{
	// Wien, ...
	$colName = -1;
	$colSex = -1;
	$colYear = -1;
	$colPos = -1;
	$startRow = 0;

	for( $row = 0; $row < 3; ++$row) {
		for( $col = 0; $col < count( $vec[ $row]); ++$col) {
			if( trim( $vec[ $row][ $col]) == 'GIVEN_NAME') {
				$colName = $col;
				$startRow = $row;
			} else if( trim( $vec[ $row][ $col]) == 'SEX') {
				$colSex = $col;
				$startRow = $row;
			} else if( trim( $vec[ $row][ $col]) == 'YEAR') {
				$colYear = $col;
				$startRow = $row;
			} else if( trim( $vec[ $row][ $col]) == 'REF_YEAR') {
				$colYear = $col;
				$startRow = $row;
			} else if( trim( $vec[ $row][ $col]) == 'RANKING_REF_YEAR') {
				$colPos = $col;
				$startRow = $row;
			}
		}
	}

	if(( -1 == $colName) || (-1 == $colSex) ||(-1 == $colYear)) {
		if( !$quite) {
			echo( '<span style="background-color:DarkOrange;padding:2px;">Unknown NUTS format!</span><br>');
		}
		return;
	}

	$data = Array();
	$posVec = Array();
	$posVec['1'] = Array();
	$posVec['2'] = Array();

	if( $vec[ $row][ $colSex] == '1') {
	} else if( $vec[ $row][ $colSex] == '2') {
	} else {
		if( !$quite) {
			echo( '<span style="background-color:DarkOrange;padding:2px;">Unknown sex in NUTS format!</span><br>');
		}
		return;
	}

	$name = trim( $vec[ $startRow + 1][ $colName], '* ');
	$needUC = ($name == strtoupper( $name));

	for( $row = $startRow + 1; $row < count( $vec); ++$row) {
		if( count( $vec[ $row]) > 1) {
			if( $posVec[ $vec[ $row][ $colSex]][ $vec[ $row][ $colYear]] == NULL) {
				$posVec[ $vec[ $row][ $colSex]][ $vec[ $row][ $colYear]] = 0;
			}
			++$posVec[ $vec[ $row][ $colSex]][ $vec[ $row][ $colYear]];

			$name = trim( $vec[ $row][ $colName], '* ');
			if( $needUC) {
				if( $name == "Z\xdcMRA") {
					$name = 'Zümra';
				} else if( $name == "\xd6MER") {
					$name = 'Ömer';
				} else if( $name == "H\xdcSEYIN") {
					$name = 'Hüseyin';
				} else if( $name == "YAGMUR") {
					$name = 'Yağmur';
				} else if( $name == "NOEL") {
					$name = 'Noël';
				} else if( $name == "IREM") {
					$name = 'İrem';
				} else if( $name == "ILAYDA") {
					$name = 'İlayda';
				} else if( $name == "ANNA-LENA") {
					$name = 'Anna-Lena';
				} else if( $name == "ANNA-MARIA") {
					$name = 'Anna-Maria';
				} else if( $name == "LISA-MARIE") {
					$name = 'Lisa-Marie';
				} else {
					$name = ucwords( strtolower( $name));
				}
			}

			$data[] = Array(
				name=> $name,
				sex=> $vec[ $row][ $colSex] == '1' ? 'boy' : 'girl',
				year=> $vec[ $row][ $colYear],
				pos=> $colPos == -1 ? $posVec[ $vec[ $row][ $colSex]][ $vec[ $row][ $colYear]] : $vec[ $row][ $colPos],
			);

			if( $data[count($data)-1]['name'] == 'Rene') {
				$data[count($data)-1]['name'] = 'René';
			}

		}
	}

	parseSourcedataVec( $data, $sourceID, $urlID, $quite);
}

//--------------------------------------------------------------------------------------------------

function cmpZuerich( $a, $b)
{
	if( $a['sex'] != $b['sex']) {
		return ($a['sex'] < $b['sex']) ? -1 : 1;
	}
	if ($a['sum'] == $b['sum']) {
		return 0;
	}
	return ($a['sum'] > $b['sum']) ? -1 : 1;
}

function oneYearZuerich( $data, $sourceID, $urlID, $quite)
{
	usort( $data, "cmpZuerich");

	$dataCount = count( $data);
	$currentPos = 0;
	$currentSum = 0;
	$currentSex = '';
	$yearPos = 1;

	for( $row = 0; $row < $dataCount; ++$row, ++$yearPos) {
		if( $currentSex != $data[ $row][ 'sex']) {
			$currentSex = $data[ $row][ 'sex'];
			$yearPos = 1;
		}
		if( $currentSum != $data[ $row][ 'sum']) {
			$currentSum = $data[ $row][ 'sum'];
			$currentPos = $yearPos;
		}
		unset($data[ $row][ 'sum']);
		$data[ $row][ 'pos'] = $currentPos;
	}

	parseSourcedataVec( $data, $sourceID, $urlID, $quite);
}

function parseSourcedataZuerich( $vec, $sourceID, $urlID, $quite)
{
	if( count( $vec) < 2) {
		if( !$quite) {
			echo( 'Unknown Zürich format.<br>');
		}
		return;
	}

	$vecCount = count( $vec);
	$colYear = 0;
	$colName = 1;
	$colSex = 2;
	$colCount = 3;
	$oldYear = 0;

	$data = Array();
	$start = 1;
	$end = $vecCount;

	for( $row = $start; $row < $end; ++$row) {
		if( count( $vec[ $row]) > 1) {
			if( $oldYear != intval( $vec[ $row][ $colYear])) {
				$oldYear = intval( $vec[ $row][ $colYear]);

				if( count( $data) > 0) {
					oneYearZuerich( $data, $sourceID, $urlID, $quite);
				}
				$data = Array();
			}

			$name = trim( $vec[ $row][ $colName], '"* ');
			if( $name == "LETIZIA") {
				$name = 'Letizia';
			} else if( $name == "Mariam-chantel") {
				$name = 'Mariam-Chantel';
			}

			$data[] = Array(
				name=> $name,
				sex=> $vec[ $row][ $colSex] == '"weiblich"' ? 'girl' : 'boy',
				year=> $vec[ $row][ $colYear],
//				pos=> $yearPos,
				sum=> intVal( $vec[ $row][ $colCount]),
			);
		}
	}

	oneYearZuerich( $data, $sourceID, $urlID, $quite);
}

//--------------------------------------------------------------------------------------------------

function parseSourcedataSalzburg( $vec, $sourceID, $urlID, $quite)
{
	if( count( $vec) < 2) {
		if( !$quite) {
			echo( 'Unknown Salzburg format.<br>');
		}
		return;
	}

	$colPos = 0;
//	$colNuts = 1;
	$colSex = 2;
	$colName = 3;
	$colYear = 4;
	$currentPos = 0;

	$data = Array();
	for( $row = 1; $row < count( $vec); ++$row) {
		if( count( $vec[ $row]) > 1) {
			if(( '' == $vec[ $row][ $colPos]) && ('' == $vec[ $row][ $colSex])) {
				continue;
			}
//			if( '' == $vec[ $row][ $colName]) {
//				continue;
//			}
			if( strlen( trim( $vec[ $row][ $colPos])) > 0) {
				$currentPos = $vec[ $row][ $colPos];
			}

			$data[] = Array(
				name=> trim( $vec[ $row][ $colName]),
				sex=> $vec[ $row][ $colSex] == 'weiblich' ? 'girl' : 'boy',
				year=> $vec[ $row][ $colYear],
//				year=> '2013',
				pos=> $currentPos,
			);
		}
	}

	parseSourcedataVec( $data, $sourceID, $urlID, $quite);
}

//--------------------------------------------------------------------------------------------------

// Berlin + Chemnitz + Ulm
function parseSourcedataBerlinChemnitzUlm( $vec, $sourceID, $urlID, $theYear, $quite)
{
	$vecCount = count( $vec);
	if( $vecCount < 2) {
		if( !$quite) {
			echo( 'Unknown Berlin format.<br>');
		}
		return;
	}

//	$colYear = 0;
//	$theYear = 2012;
	$colName = 0;
	$colCount = 1;
	$colSex = 2;
	$yearPos = Array();
	$posCounter = Array();
	$oldCount = Array();

	$data = Array();
	$yearPos['m'] = 1;
	$yearPos['w'] = 1;
	$posCounter['m'] = 1;
	$posCounter['w'] = 1;
	$oldCount['m'] = 0;
	$oldCount['w'] = 0;

	for( $row = 1; $row < $vecCount; ++$row) {
		if( count( $vec[ $row]) > 1) {
			$sex = trim( $vec[ $row][ $colSex]);
			if( $oldCount[ $sex] != intval( $vec[ $row][ $colCount])) {
				$oldCount[ $sex] = intval( $vec[ $row][ $colCount]);
				$yearPos[ $sex] = $posCounter[ $sex];
			}

			$name = trim( $vec[ $row][ $colName]);
			if( false) {
				if( $name == "noch") {
					continue;
				} else if( $name == "kein") {
					continue;
				} else if( $name == "Vorname") {
					continue;
				} else if( $name == "ohne") {
					continue;
				} else if( $name == "Vornamen") {
					continue;
				} else if( $name == "keinen") {
					continue;
				} else if( $name == "(Eigenname)") {
					continue;
				} else if( $name == "(Vorname") {
					continue;
				} else if( $name == "(Vornamen") {
					continue;
				} else if( $name == "und") {
					continue;
				} else if( $name == "Vatersname)") {
					continue;
				} else if( $name == "A.") {
					continue;
				} else if( $name == "de") {
					continue;
				} else if( $name == "del") {
					continue;
				} else if( $name == "don") {
					continue;
				} else if( $name == "oğlu") {
					continue;
				} else if( $name == "ogly") {
					continue;
				} else if( $name == "kyzy") {
					continue;
				} else if( $name == "Totgeborener") {
					continue;
				} else if( $name == "") {
					continue;
				} else if( $name == "Nana-akua") {
					$name = "Nana-Akua";
				} else if( $name == "Shawn,") {
					$name = "Shawn";
				}
			}

			$data[] = Array(
				name=> $name,
				sex=> $sex == 'm' ? 'boy' : 'girl',
				year=> $theYear,
				pos=> $yearPos[ $sex],
			);
			++$posCounter[ $sex];
		}
	}

	parseSourcedataVec( $data, $sourceID, $urlID, $quite);
}

//--------------------------------------------------------------------------------------------------

function sourcesShowPageBrowseAllCmp( $a, $b)
{
	if( isset( $a['group']) && isset( $b['group']) && ($a['group'] == $b['group'])) {
		if( $a['name'] == $b['name']) {
			return 0;
		}
		return ($a['name'] > $b['name']) ? -1 : 1;
	}
	if( $a['nuts'] != $b['nuts']) {
		if( $a['nuts'] == '') {
			return 1;
		}
		if( $b['nuts'] == '') {
			return -1;
		}
		return ($a['nuts'] < $b['nuts']) ? -1 : 1;
	}
	if( $a['name'] == $b['name']) {
		return 0;
	}
	return ($a['name'] < $b['name']) ? -1 : 1;
}

//--------------------------------------------------------------------------------------------------

function sourcesShowPageBrowseAll()
{
	global $gSource;

	$txt = '';
	$txt .= '<h1>Source list</h1>';

	$txt .= '<div><div style="display:inline;float:left;min-width:3.5em;">AT11</div><span>NUTS region</span></div>';
	$txt .= '<div><div style="display:inline;float:left;min-width:1.25em;background-color:ForestGreen;margin-right:2.15em;">&nbsp;</div><span>Data up to date</span></div>';
	$txt .= '<div><div style="display:inline;float:left;min-width:1.25em;background-color:ForestGreen;margin-right:2.15em;">&nbsp;</div><span>NUTS exists in own table and "manNUTS" in source file</span></div>';
	$txt .= '<div><div style="display:inline;float:left;min-width:1.25em;background-color:ForestGreen;margin-right:2.15em;text-align:center;">©</div><span>Well known copyright information</span></div>';

	$txt .= '<br>';
	$txt .= '<hr>';
	$txt .= '<br>';

	usort( $gSource, "sourcesShowPageBrowseAllCmp");

	$group = '';
	$skipped = 0;
	$bg = true;
	for( $i = 0; $i < count( $gSource); ++$i) {
		$name = $gSource[$i]['name'];
		if( isset( $gSource[$i]['group'])) {
			if( $group == $gSource[$i]['group']) {
				++$skipped;
				continue;
			}
			$group = $gSource[$i]['group'];
			$name = $group;
		} else {
			$group = '';
		}

		$bg = !$bg;
		$bgColor = $bg ? '#3a3a3a' : '#444444';

		$updateColor = '#000000';
		if( $gSource[$i]['autoUpdate'] < 0) {
			$updateColor = '$bgColor';
		} else if( $gSource[$i]['autoUpdate'] > 0) {
			$updateColor = 'DarkOrange';
		} else {
			$updateColor = 'ForestGreen';
		}

		$nutsColor = '#000000';
		if( 0 == count( $gSource[$i]['autoUrl'])) {
			$nutsColor = '$bgColor';
		} else if(( count( $gSource[$i]['autoUrl']) == count( $gSource[$i]['autoName'])) && (count( $gSource[$i]['autoName']) == count( $gSource[$i]['manNUTS']))) {
			$nutsColor = 'ForestGreen';
			for( $j = 0; $j < count( $gSource[$i]['manNUTS']); ++$j) {
				if( !nutsExists( $gSource[$i]['manNUTS'][$j])) {
					$nutsColor = 'DarkOrange';
				}
			}
		} else {
			$nutsColor = 'DarkOrange';
		}

		$copyColor = '#000000';
		$copyText = '&nbsp;';
		if( 0 == count( $gSource[$i]['autoUrl'])) {
			$copyColor = '$bgColor';
		} else if( isset( $gSource[$i]['autoLicense']) && isset( $gSource[$i]['autoCitation'])) {
			$copyColor = 'ForestGreen';
		} else if( isset( $gSource[$i]['autoCitation']) && isset( $gSource[$i]['manCitation'])) {
			$copyColor = 'DarkOrange';
		} else if( isset( $gSource[$i]['autoLicense']) && isset( $gSource[$i]['manCitation'])) {
			$copyColor = 'ForestGreen';
		} else if( isset( $gSource[$i]['manLicense']) && isset( $gSource[$i]['manCitation'])) {
			$copyColor = 'ForestGreen';
		} else {
			$copyColor = 'DarkOrange';
		}
		if( isset( $gSource[$i]['autoLicense']) || isset( $gSource[$i]['manLicense'])) {
			$copyText = '©';
		}

		$txt .= '<div style="background:' . $bgColor .';">';
		$txt .= '<div style="display:inline;float:left;min-width:3.5em;">' . $gSource[$i]['nuts'] . '&nbsp;</div>';
		$txt .= '<div style="display:inline;float:left;min-width:1.25em;background-color:'.$updateColor.';margin-right:0.15em;">&nbsp;</div>';
		$txt .= '<div style="display:inline;float:left;min-width:1.25em;background-color:'.$nutsColor.';margin-right:0.15em;">&nbsp;</div>';
		$txt .= '<div style="display:inline;float:left;min-width:1.25em;background-color:'.$copyColor.';margin-right:0.75em;text-align:center;">'.$copyText.'</div>';
		$txt .= '<span style="width:6em;"><a href="do=source&what='.$gSource[$i]['id'].'">'.$name.'</a></span>';
		$txt .= '</div>';
	}

	$txt .= (count( $gSource) - $skipped) . ' sources (' . count( $gSource) . ' source links)<br>';

	$txt .= '<br>';
	$txt .= '<hr>';
	$txt .= '<br>';

	$txt .= '<a href="do=">Back to main</a><br>';
	$txt .= '<br>';
	$txt .= '<a href="do=update&what=sourcemetadata">Update Metadata</a><br>';
	$txt .= '<a href="do=update&what=sourcedata">Update dirty data</a><br>';

	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

function sourcesShowPageItem( $id)
{
	global $gSource;
	global $gBoys;
	global $gGirls;

	for( $i = 0; $i < count( $gSource); ++$i) {
		if( intval( $id) == intval( $gSource[$i]['id'])) {
			break;
		}
	}

	$name = $gSource[$i]['name'];
	$group = '';
	if( isset( $gSource[$i]['group'])) {
		$group = $gSource[$i]['group'];
		$name = $group;
	}

	$txt = '';
	$txt .= '<h1>'.$name.'</h1>';
	$txt .= '<a href="do=browse&what=sources">Back to Source list</a>';
	$txt .= '<br>';
	$txt .= '<br>';
	echo( $txt);

	$txt = '';
	$txt .= 'Datasets:<br>';
	echo( $txt);

	$idVec = Array();
	for( $i = 0; $i < count( $gSource); ++$i) {
		if( $group == '') {
			if( intval( $id) != intval( $gSource[$i]['id'])) {
				continue;
			}
		} else {
			if( $group != $gSource[$i]['group']) {
				continue;
			}
		}

		$idVec[] = $gSource[$i]['id'];

		for( $j = 0; $j < count( $gSource[$i]['autoUrl']); ++$j) {
			if( 0 === strpos( $gSource[$i]['autoUrl'][$j], '/katalog/storage')) {
				$gSource[$i]['autoUrl'][$j] = 'http://data.gv.at' . $gSource[$i]['autoUrl'][$j];
			} else
			if( 0 === strpos( $gSource[$i]['autoUrl'][$j], '/at.gv.brz.ogd/storage')) {
				$gSource[$i]['autoUrl'][$j] = 'http://data.gv.at/katalog/' . substr( $gSource[$i]['autoUrl'][$j], 15);
			}

			$name = $gSource[$i]['autoName'][$j];
			if( strlen( $name) == 0) {
				$name = '[Data]';
			}
			if( $name == '') {
				$name = '[Data]';
			}
			$txt = '&nbsp;&nbsp;<a href="' . $gSource[$i]['autoUrl'][$j]. '">' . $name . '</a><br>';
			echo( $txt);
		}
	}

	$txt = '';
	$txt .= '<br>';
	$txt .= 'Boys:<br>';
	echo( $txt);

	$top = 3;

	$boys = 0;
	$allboys = 0;
	foreach( $gBoys as $value) {
		$desc = '';
		$foundName = 0;
		foreach( $value['ref'] as $refvalue) {
			$posSource = strpos( $refvalue, '-');
			$posNum = strpos( $refvalue, '#', $posSource);
			$posYear = strpos( $refvalue, ',', $posNum);

			$strSource = substr( $refvalue, 0, $posSource);
			$strNum = substr( $refvalue, $posNum + 1, $posYear - $posNum - 1);
			$strYear = substr( $refvalue, $posYear + 1);

			if( in_array( intval( $strSource), $idVec)) {
				$foundName = 1;
				if( intVal( $strNum) <= $top) {
					if( strlen( $desc) > 0) {
						$desc .= ', ';
					}
					$desc .= '#' . $strNum . ' in ' . $strYear;
				}
			}
		}

		$allboys += $foundName;
		if( strlen( $desc) > 0) {
			++$boys;
			echo( getNameLink( $value) . ' (' . $desc. ')<br>');
		}
	}

	$txt = '';
	$txt .= $boys . ' boys (from '.$allboys.' boys)<br>';
	$txt .= '<br>';
	$txt .= 'Girls:<br>';
	echo( $txt);

	$girls = 0;
	$allgirls = 0;
	foreach( $gGirls as $value) {
		$desc = '';
		$foundName = 0;
		foreach( $value['ref'] as $refvalue) {
			$posSource = strpos( $refvalue, '-');
			$posNum = strpos( $refvalue, '#', $posSource);
			$posYear = strpos( $refvalue, ',', $posNum);

			$strSource = substr( $refvalue, 0, $posSource);
			$strNum = substr( $refvalue, $posNum + 1, $posYear - $posNum - 1);
			$strYear = substr( $refvalue, $posYear + 1);

			if( in_array( intval( $strSource), $idVec)) {
				$foundName = 1;
				if( intVal( $strNum) <= $top) {
					if( strlen( $desc) > 0) {
						$desc .= ', ';
					}
					$desc .= '#' . $strNum . ' in ' . $strYear;
				}
			}
		}

		$allgirls += $foundName;
		if( strlen( $desc) > 0) {
			++$girls;
			echo( getNameLink( $value) . ' (' . $desc. ')<br>');
		}
	}

	$txt = '';
	$txt .= $girls . ' girls (from '.$allgirls.' girls)<br>';
	$txt .= '<br>';
	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

function getNameLink( $value)
{
	global $gSource;
	global $gMeanings;

	$style = '';
	if( !isset( $gMeanings['name'])) {
		$style = ' style="text-decoration:none;border-bottom:1px solid red;"';
	}

	return '<a href="do=name&what='.$value['name'].'" '.$style.'>'.$value['name'].'</a>';
}

//--------------------------------------------------------------------------