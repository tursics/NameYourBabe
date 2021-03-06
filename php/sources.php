<?php

//--------------------------------------------------------------------------------------------------
// $gSource
//--------------------------------------------------------------------------------------------------

include_once( "HarvestYears.php");
include_once( "data/sources.php");

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

// used in index.php
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
	if( $vecCount > 10) {
		$cells = 0;
		for( $i = 0; $i < 10; ++$i) {
			$cells += count($vec[ $i]);
		}
		if( 0 == $cells) {
			$vec = Array();
			for( $i = 0; $i < $rowsCount; ++$i) {
				$vec[] = explode( ",", $rows[ $i]);
			}
		}
	}

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
			$lastYear = substr( $lastYear, strlen( $lastYear) - 4);
			if(( 1900 < $lastYear) && ($lastYear < 2100)) {
				$theYear = $lastYear;
			}
		}
		parseSourcedataBerlinBonnChemnitzHamburgUlm( $vec, $sourceID, $urlID, $theYear, $quite);
	} else if(( $vecCount > 1) && ($vec[1][0] == 'Anzahl der  Kinder mit')) {
		parseSourcedataBremen( $vec, $sourceID, $urlID, $quite);
	} else if(( $vecCount > 1) && (trim( $vec[1][0]) == 'Anzahl der Kinder mit')) {
		parseSourcedataBremen( $vec, $sourceID, $urlID, $quite);
	} else if(( $vecCount > 2) && (trim( $vec[2][0]) == 'Anzahl der Kinder mit')) {
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

// Berlin + Bonn + Chemnitz + Hamburg + Ulm
function parseSourcedataBerlinBonnChemnitzHamburgUlm( $vec, $sourceID, $urlID, $theYear, $quite)
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
				} else if( $name == "Eigenname:") {
					continue;
				} else if( $name == "(Vorname") {
					continue;
				} else if( $name == "(Vorname)") {
					continue;
				} else if( $name == "(Vornamen") {
					continue;
				} else if( $name == "(Vor-") {
					continue;
				} else if( $name == "und") {
					continue;
				} else if( $name == "Vatersname)") {
					continue;
				} else if( $name == "(Vatersname)") {
					continue;
				} else if( $name == "Vatersname:") {
					continue;
				} else if( $name == "(Großvatersname)") {
					continue;
				} else if( $name == "Mittelname)") {
					continue;
				} else if( $name == "(Mittelname)") {
					continue;
				} else if( $name == "Namen") {
					continue;
				} else if( $name == "Jr.") {
					continue;
				} else if( $name == "A.") {
					continue;
				} else if( $name == "C.") {
					continue;
				} else if( $name == ".") {
					continue;
				} else if( $name == "al") {
					continue;
				} else if( $name == "da") {
					continue;
				} else if( $name == "de") {
					continue;
				} else if( $name == "del") {
					continue;
				} else if( $name == "Del") {
					continue;
				} else if( $name == "don") {
					continue;
				} else if( $name == "oglu") {
					continue;
				} else if( $name == "oğlu") {
					continue;
				} else if( $name == "ogly") {
					continue;
				} else if( $name == "kyzy") {
					continue;
				} else if( $name == "qizi") {
					continue;
				} else if( $name == "qızı") {
					continue;
				} else if( $name == "'evna") {
					continue;
				} else if( $name == "Jose'") {
					continue;
				} else if( $name == "Totgeborener") {
					continue;
				} else if( $name == "") {
					continue;
				} else if( $name == "Nana-akua") {
					$name = "Nana-Akua";
				} else if( $name == "Shawn,") {
					$name = "Shawn";
				} else if( $name == "ClaudiaRicarda") {
					$name = "Claudia-Ricarda";
//				} else if( $name == "LouAnn") { // correct name
//					$name = "Lou-Ann";
				} else if( $name == "kim") {
					$name = "Kim"; // ????
				} else if( $name == "gizi") {
					$name = "Gizi"; // ????
				} else if( $name == "mia") {
					$name = "Mia"; // ????
					continue;
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
	if( 'Vornamenstatistik' != explode( " ", $vec[ $row][0])) {
		++$row;
	}

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
		} else if( 'Rang Mädchen Anzahl Knaben Anzahl' == $vec[ $row][0]) {
			$row -= 2;
			break;
		} else if( 'Rang Mädchen Anzahl Jungen Anzahl' == $vec[ $row][0]) {
			$row -= 2;
			break;
		} else if(( 'Rang' == $vec[ $row][0]) && ( 'Mädchen' == $vec[ $row][1])) {
			$row -= 2;
			break;
		}
	}
	$row += 2;

	if( $row >= $vecCount) { echo( 'Unknown Bremen year format (less data)...'); return; }

	if( 1 == count( $vec[ $row])) {
		parseSourcedataBremenSpaces( $vec, $sourceID, $urlID, $quite, $row, $theYear);
		return;
	}

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

//--------------------------------------------------------------------------------------------------

function parseSourcedataBremenSpaces( $vec, $sourceID, $urlID, $quite, $row, $theYear)
{
	$vecCount = count( $vec);

	$current = explode( " ", $vec[ $row][0]);
	$previous = explode( " ", $vec[ $row-1][0]);

	if( 'Anzahl' != trim( $current[2])) {
		--$row;
		if( 'Anzahl' != trim( $previous[2])) {
			echo( 'Unknown Bremen format (Anzahl 1)...'); return;
		}
	}
	if( 'Knaben' != trim( $current[3])) {
		if( 'Knaben' != trim( $previous[3])) {
			if( 'Jungen' != trim( $current[3])) {
				if( 'Jungen' != trim( $previous[3])) {
					echo( 'Unknown Bremen format (Knaben)...'); return;
				}
			}
		}
	}
	if( 'Anzahl' != trim( $current[4])) {
		if( 'Anzahl' != trim( $previous[4])) {
			echo( 'Unknown Bremen format (Anzahl 2)...'); return;
		}
	}

	++$row;

//	$colYear = 0;
	$colPos = 0;
	$colNameBoy = 3;
	$colNameGirl = 1;
	$colCountGirl = 2;
	$data = Array();

	for( ; $row < $vecCount; ++$row) {
		$current = explode( " ", $vec[ $row][0]);
		if( intval( $current[ $colPos]) < 1) {
			continue;
		}
		if( count( $current) > 3) {
			$boy = trim( $current[ $colNameBoy]);
			if( 'Tot' == $boy) {} else
			if( 'geborener' == $boy) {} else
			if( '(Vorname' == $boy) {} else
			if( '(Vor' == $boy) {} else
			if( 'und' == $boy) {} else
			if( 'Vatersname)' == $boy) {} else
			if( '(Vatersname)' == $boy) {} else
			if( 'noch' == $boy) {} else
			if( 'kein' == $boy) {} else
			if( 'Vorname' == $boy) {} else
			if( 'oğlu' == $boy) {} else
			if( 'van' == $boy) {} else
			if( 'Alessandro-' == $boy) {} else // data corruption
			if( 'Maximilian-' == $boy) {} else // data corruption
			if( '1' == $boy) {} else // given name is empty, count is '1'
			{
				$data[] = Array(
					name=> $boy,
					sex=> 'boy',
					year=> $theYear,
					pos=> intval( $current[ $colPos]),
				);
			}

			$girl = trim( $current[ $colNameGirl]);
			if(( '' == $girl) && ( '' == $current[ $colCountGirl])) {
				continue;
			}

			if( 'Tot' == $girl) {} else
			if( 'geborenes' == $girl) {} else
			if( 'Mädchen' == $girl) {} else
			if( '(Vorname' == $girl) {} else
			if( 'und' == $girl) {} else
			if( 'Vatersname)' == $girl) {} else
			if( 'Vatersname' == $girl) {} else
			if( 'Vatersname:' == $girl) {} else
			if( 'Nameskette' == $girl) {} else
			if( 'Namenskette' == $girl) {} else
			if( '(Namenskette)' == $girl) {} else
			if( 'noch' == $girl) {} else
			if( 'kein' == $girl) {} else
			if( 'Vorname' == $girl) {} else
			if( '-Alexandra' == $girl) {} else // data corruption
			if( 'Irini-' == $girl) {} else // data corruption
			if( 'Jo-Essen' == $girl) {} else // found in database from Essen
			if( 'nana' == $girl) {} else
			if( 'kyzy' == $girl) {} else
			if( 'de' == $girl) {} else
			{
				$data[] = Array(
					name=> $girl,
					sex=> 'girl',
					year=> $theYear,
					pos=> intval( $current[ $colPos]),
				);
			}
		} else if( count( $current) > 1) {
			$girl = trim( $current[ 1]);
			if( 'Zwischennamen:' == $girl) {} else
			{
				$data[] = Array(
					name=> $girl,
					sex=> 'girl',
					year=> $theYear,
					pos=> intval( $current[ $colPos]),
				);
			}
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

function sortMetadataVecByNUTS( $a, $b)
{
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
	return ($a['name'] > $b['name']) ? -1 : 1;
}

//--------------------------------------------------------------------------------------------------

function sourcesShowPageBrowseAll()
{
	global $MetadataVec;
	global $dataHarvestMetadata;

	$txt = '';
	$txt .= '<div class="log">Source list<br>===========<br><br>';

	$year = intVal( date("Y")) - 1;

	$txt .= 'NUTS region&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Data&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$year.'...'.($year-9).'&nbsp;NUTS&nbsp;&nbsp;&nbsp;&nbsp;Copyright&nbsp;Name<br>';
	$txt .= '---------------- ----------- ----------- ------- --------- ----------------------------------------<br>';

	usort( $MetadataVec, "sortMetadataVecByNUTS");

	$skipped = 0;
	$group = '';
	$nuts1 = substr( $MetadataVec[0]['nuts'], 0, 2);
	$txtNUTS = '';
	$txtData = '';
	$txtYear = '';
	$txtNuts = '';
	$txtCopy = '';
	$txtHref = '';
	$txtName = '';
	$firstUpdateLink = '';

	for( $i = 0; $i < count( $MetadataVec); ++$i) {
		$harvest = $dataHarvestMetadata[ $MetadataVec[$i]['meta']];

		$dataYears = '';
		if( 0 < count( $harvest['years'])) {
			for( $j = 0; $j < 10; ++$j) {
				if( in_array( $year - $j, $harvest['years'])) {
					$dataYears .= 'X';
				} else {
					$dataYears .= '.';
				}
			}
			if( in_array( $year - $j, $harvest['years'])) {
				$dataYears .= '+';
			}
		}

		$dataUpdate = '';
		$dataUpdateLink = '';
		if( $harvest['update'] < 0) {
			$dataUpdate = 'no data';
		} else if( $harvest['update'] > 0) {
			$dataUpdate = 'need update';
			$dataUpdateLink = 'do=update&what=sourcedata&id='.md5($MetadataVec[$i]['meta']);
			if( '' == $firstUpdateLink) {
				$firstUpdateLink = $dataUpdateLink;
			}
		} else if( $harvest['update'] === 0) {
			$dataUpdate = '&radic;';
		} else {
			$dataUpdate = 'ERROR';
		}

		if( $group == $MetadataVec[$i]['nuts']) {
			++$skipped;
			$txtName = nutsGetName( $MetadataVec[$i]['nuts'])['en-US'];
			if( '' != $dataUpdateLink) {
				$txtData = str_replace( array( 'no data', 'need update', 'ERROR', '&nbsp;'), array( '', 'U', 'E', ''), $txtData);
				$txtData .= '<a href="'.$dataUpdateLink.'">U</a>';
			} else if( '&radic;' == $dataUpdate) {
				$txtData = str_replace( array( 'no data', 'need update', 'ERROR', '&nbsp;'), array( '', 'U', 'E', ''), $txtData);
				$txtData .= $dataUpdate;
			}
			for( $j = strlen(str_replace(array('&nbsp;','&radic;'),array(' ',' '),strip_tags($txtData))); $j < 12; ++$j) $txtData .= '&nbsp;';

			$txtYear = str_replace( '&nbsp;', ' ', $txtYear);
			for( $j = 0; $j < strlen( $dataYears); ++$j) {
				if( $txtYear[ $j] == '.') {
					$txtYear[ $j] = $dataYears[ $j];
				} else if( $txtYear[ $j] == ' ') {
					$txtYear[ $j] = $dataYears[ $j];
				}
			}
			$txtYear = str_replace( ' ', '&nbsp;', $txtYear);
			continue;
		}
		if( $group != '') {
			$txt .= $txtNUTS . $txtData . $txtYear . $txtNuts . $txtCopy . '<a href="'.$txtHref.'">'.$txtName.'</a><br>';
		}

		$group = $MetadataVec[$i]['nuts'];
		$txtName = $MetadataVec[$i]['name'];
		$txtHref = 'do=browse&what=source&id='.$MetadataVec[$i]['nuts'];

		if( $nuts1 != substr( $MetadataVec[$i]['nuts'], 0, 2)) {
			$nuts1 = substr( $MetadataVec[$i]['nuts'], 0, 2);
			$txt .= '---------------- ----------- ----------- ------- --------- ----------------------------------------<br>';
		}

		$dataNuts = '';
		if( 0 == strlen( $MetadataVec[$i]['nuts'])) {
			$dataNuts = 'missing';
		} else {
			if( nutsExists( $MetadataVec[$i]['nuts'])) {
				$dataNuts = '&radic;';
				$txtName = nutsGetName( $MetadataVec[$i]['nuts'])['en-US'];
			} else {
				$dataNuts = 'missing';
			}
		}

		$dataCopyright = 'ERROR';
		if( 0 == count( $harvest['url'])) {
			$dataCopyright = 'unknown';
		} else if( isset( $harvest['license']) && isset( $harvest['citation'])) {
			$dataCopyright = '&radic;';
		} else if( isset( $harvest['citation']) && isset( $MetadataVec[$i]['citation'])) {
			$dataCopyright = 'missing';
		} else if( isset( $harvest['license']) && isset( $MetadataVec[$i]['citation'])) {
			$dataCopyright = '&radic;';
		} else if( isset( $MetadataVec[$i]['license']) && isset( $MetadataVec[$i]['citation'])) {
			$dataCopyright = '&radic;';
		} else {
			$dataCopyright = 'missing';
		}

		$txtNUTS = $MetadataVec[$i]['nuts'];
		for( $j = strlen($txtNUTS); $j < 17; ++$j) $txtNUTS .= '&nbsp;';

		if( '' == $dataUpdateLink) {
			$txtData = $dataUpdate;
		} else {
			$txtData = '<a href="'.$dataUpdateLink.'">'.$dataUpdate.'</a>';
		}
		for( $j = ('&' == substr($dataUpdate,0,1) ? 1 : strlen($dataUpdate)); $j < 12; ++$j) $txtData .= '&nbsp;';
//		for( $j = strlen(strip_tags($txtData)); $j < 12; ++$j) $txtData .= '&nbsp;';

		$txtYear = $dataYears;
		for( $j = ('&' == substr($txtYear,0,1) ? 1 : strlen($txtYear)); $j < 12; ++$j) $txtYear .= '&nbsp;';

		$txtNuts = $dataNuts;
		for( $j = ('&' == substr($txtNuts,0,1) ? 1 : strlen($txtNuts)); $j < 8; ++$j) $txtNuts .= '&nbsp;';

		$txtCopy = $dataCopyright;
		for( $j = ('&' == substr($txtCopy,0,1) ? 1 : strlen($txtCopy)); $j < 10; ++$j) $txtCopy .= '&nbsp;';

	}

	$txt .= $txtNUTS . $txtData . $txtYear . $txtNuts . $txtCopy . '<a href="'.$txtHref.'">'.$txtName.'</a><br>';
	$txt .= '---------------- ----------- ----------- ------- --------- ----------------------------------------<br>';
	$txt .= '<br>';
	$txt .= (count( $MetadataVec) - $skipped) . ' sources (' . count( $MetadataVec) . ' source data sets)<br>';
	$txt .= '<br>';
	$txt .= '[<a href="do=browse&what=nuts">Show source regions</a>]<br>';
	$txt .= '[<a href="do=">Show admin area</a>]<br>';
	$txt .= '<br>';
	if( '' == $firstUpdateLink) {
		$txt .= '[Update dirty data]<br>';
	} else {
		$txt .= '[<a href="' . $firstUpdateLink . '">Update dirty data</a>]<br>';
	}
	$txt .= '[<a href="do=update&what=sourcemetadata">Update source list</a>]<br>';
	$txt .= '</div>';

	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

function sourcesShowPageUpdateDownload( $i)
{
	global $HarvestMetadata;
	global $MetadataVec;
	global $dataHarvestMetadata;

	$txt = '';
	$txt .= 'Number&nbsp;Copy from&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;To local path<br>';
	$txt .= '------ ------------------------------ ----------------------------------------<br>';
	echo( $txt);

	$harvest = & $dataHarvestMetadata[ $MetadataVec[$i]['meta']];
	$harvest['download'] = Array();

	$nuts = $MetadataVec[$i]['nuts'];

	$nutsVec = Array();
	for( $idx = 0; $idx < count( $harvest['url']); ++$idx) {
		$nutsVec[] = $nuts;

		if( isset( $MetadataVec[$i]['nutsVec']) && isset( $MetadataVec[$i]['nutsVec'][$idx])) {
			$nutsVec[ $idx] = $MetadataVec[$i]['nutsVec'][$idx];
		}
	}

	for( $idx = 0; $idx < count( $harvest['url']); ++$idx) {
		$name = $harvest['name'][$idx];
		$url = $harvest['url'][$idx];

		if( 0 === strpos( $url, '/katalog/storage')) {
			$url = 'http://data.gv.at' . $url;
		} else if( 0 === strpos( $url, '/at.gv.brz.ogd/storage')) {
			$url = 'http://data.gv.at/katalog/' . substr( $url, 15);
		} else if( 0 === strpos( $url, '/private/')) {
			$url = dirname(__FILE__) . '/data' . $url;
		}

		$path = substr($url,strrpos($url,'/') + 1);
		if( false !== strrpos($path,'?')) {
			$path = substr($path,0,strrpos($path,'?'));
		}
		if( strlen( $name) == 0) {
			$name = $path;
		}
		if( $name == '') {
			$name = $path;
		}

		$txt = '';

		$num = (string)($idx+1);
		$txt .= $num;
		for( $j = strlen($num); $j < 7; ++$j) $txt .= '&nbsp;';

		$txt .= '<a href="' . $url. '" title="'.$name.'" target="_blank">' . substr($name,0,30) . '</a>&nbsp;';
		for( $j = strlen($name); $j < 30; ++$j) $txt .= '&nbsp;';

		if( '' == $path) {
			$txt .= '[ignore path]';
		} else {
			$file = dirname(__FILE__).'/data/harvest/'.substr($nutsVec[ $idx], 0, 2);
			if( !file_exists( $file)) {
				mkdir( $file, 0777);
			}
			$file .= '/'.$nutsVec[ $idx];
			if( !file_exists( $file)) {
				mkdir( $file, 0777);
			}

			$path = 'data/harvest/'.substr($nutsVec[ $idx], 0, 2).'/'.$nutsVec[ $idx].'/'.$path;
			$txt .= $path;

			if( !copy( $url, dirname(__FILE__).'/'.$path)) {
				$txt .= '[failed to download file]';
			} else {
				$harvest['download'][] = $path;

				if( '.zip' == substr( $path, -4)) {
					$zip = new ZipArchive();
					$zip->open( dirname(__FILE__).'/'.$path);

					for( $num = 0; $num < $zip->numFiles; ++$num) {
						$info = $zip->statIndex( $num);
						$number = ($idx+1).'.'.($num+1);
						$txt .= '<br>'.$number;
						for( $j = strlen($number); $j < 37; ++$j) $txt .= '&nbsp;';

						$path = 'data/harvest/'.substr($nutsVec[ $idx], 0, 2).'/'.$nutsVec[ $idx].'/zip_'.substr( $info['name'], strrpos( $info['name'], '/') + 1);
						$txt .= $path;

						$url = 'zip://' . $zip->filename . '#' . $info['name'];

						if( !copy( $url, dirname(__FILE__).'/'.$path)) {
							$txt .= '[failed to unzip file '.$info['name'].']';
						} else {
							$harvest['download'][] = $path;
						}
					}
				}
			}
		}
		$txt .= '<br>';
		echo( $txt);
	}

	$txt = '';
	$txt .= '------ ------------------------------ ----------------------------------------<br>';
	echo( $txt);

	$HarvestMetadata->save();
}

//--------------------------------------------------------------------------------------------------

function sourcesShowPageUpdateNames( $i, $kind)
{
	global $HarvestData;
	global $HarvestMetadata;
	global $MetadataVec;
	global $dataHarvestMetadata;

	$doUpdate = ($kind == 'update');
	$doAdd = ($kind == 'add');

	$harvest = & $dataHarvestMetadata[ $MetadataVec[$i]['meta']];
	$nuts = $MetadataVec[$i]['nuts'];
	$vecDownload = $harvest['download'];
	$ret = '';

	$nutsVec = Array();
	for( $idx = 0; $idx < count( $vecDownload); ++$idx) {
		$nutsVec[] = $nuts;

		if( isset( $MetadataVec[$i]['nutsVec']) && isset( $MetadataVec[$i]['nutsVec'][$idx])) {
			$nutsVec[ $idx] = $MetadataVec[$i]['nutsVec'][$idx];
		}
	}

	for( $idx = 0; $idx < count( $vecDownload); ++$idx) {
		$url = $vecDownload[$idx];
		$file = dirname(__FILE__).'/'.$url;

		$txt = '';
		$txt .= '<br>';
		if( $doUpdate) {
			$txt .= 'Analyse '.filesize( $file).' bytes of '.$url.'<br>';
		} else {
			$txt .= 'Add names from '.$url.'<br>';
		}
		$txt .= '------------------------------------------------------------------------------<br>';
		echo( $txt);

		$contents = file_get_contents( $file);
//		$contents = utf8_encode( $contents);

		$vec = Array();
		$rows = explode( "\n", $contents);
		$rowsCount = count( $rows);
		for( $i = 0; $i < $rowsCount; ++$i) {
			$vec[] = explode( ";", $rows[ $i]);
		}

		$vecCount = count( $vec);
		if( $vecCount > 10) {
			$cells = 0;
			for( $i = 0; $i < 10; ++$i) {
				$cells += count($vec[ $i]);
			}
			if( $cells <= 10) {
				$vec = Array();
				for( $i = 0; $i < $rowsCount; ++$i) {
					$vec[] = explode( ",", $rows[ $i]);
				}
			}
		}

		$txt = '';

		if( '.pdf' == substr( $url, -4)) {
			$txt .= 'Ignore PDF files<br>';
			$txt .= '------------------------------------------------------------------------------<br>';
			echo( $txt);
			continue;
		} else if( '.zip' == substr( $url, -4)) {
			$txt .= 'Ignore ZIP files<br>';
			$txt .= '------------------------------------------------------------------------------<br>';
			echo( $txt);
			continue;
		} else if( '.json' == substr( $url, -5)) {
			$txt .= 'Ignore JSON files<br>';
			$txt .= '------------------------------------------------------------------------------<br>';
			echo( $txt);
			continue;
		} else if( '.xlsx' == substr( $url, -5)) {
			$txt .= 'Ignore Excel files<br>';
			$txt .= '------------------------------------------------------------------------------<br>';
			echo( $txt);
			continue;
		}

		if( $doUpdate) {
			$result = $HarvestData->parse( $vec, $vecCount, $nutsVec[ $idx], $url, true);
		} else {
			$result = $HarvestData->addNames( $vec, $vecCount, $nutsVec[ $idx], $url, true);
		}

		if( $result->error) {
			$txt .= 'Error: ' . $result->errorMsg . '<br>';
			$txt .= 'Used parser: ' . $HarvestData->getParserClass( $vec, $vecCount) . '<br>';
			if( $result->errorMsg == 'Unknown names found. No files saved!') {
				$ret = 'name';
			} else {
				$ret = $result->errorMsg;
			}
		} else {
			$dataCount = $result->dataCount;
			for( $it = 0; $it < $dataCount; ++$it) {
				$item = $result->data[ $it];
				if( '' != $item['error']) {
					// I got out of memory errors for Zurich data
//					$txt .= $item['error'].' (#' . $item['pos'] . ' '.($item['male']?'male':'female').' in ' . $item['year'] . ')'.'<br>';
					$txt = ' ';
					echo $item['error'].' (#' . $item['pos'] . ' '.($item['male']?'male':'female').' in ' . $item['year'] . ')'.'<br>';
				}
			}
			if( 0 == strlen( $txt)) {
				if( $doUpdate) {
					$txt .= $dataCount . ' entries saved in ' . count( $result->file) . ' files<br>';
					if( 0 < count( $result->years)) {
						if( 0 < count( $harvest['years'])) {
							$harvest['years'] = array_unique( array_merge( $harvest['years'], $result->years));
						} else {
							$harvest['years'] = array_unique( $result->years);
						}
					}
				} else {
					$txt .= 'New names are saved<br>';
				}
			} else {
				$txt .= $dataCount . ' entries collected but error found. No files saved!<br>';
				$txt .= 'Used parser: ' . $HarvestData->getParserClass( $vec, $vecCount) . '<br>';
				$ret = 'error';
			}

/*			$lastMod = strtotime( $harvest['modified']);
			$diffMod = intval(( $result->modified - $lastMod) /60 /60 /24);

			if( 0 >= $diffMod) {
				$dataData = '&radic;'.' Last mod: '.$lastMod;
				$harvest['update'] = 0;
			} else {
				if( 1 == $result->modDays) {
					$dataData = $result->modDays.' day';
				} else {
					$dataData = $result->modDays.' days';
				}
				$harvest['name'] = $result->vecName;
				$harvest['url'] = $result->vecURL;
				$harvest['license'] = $result->license;
				$harvest['citation'] = $result->citation;
				$harvest['update'] = $result->modDays;
				if( 0 == $harvest['update']) {
					$harvest['update'] = 1;
				}
			}*/
		}

		$txt .= '------------------------------------------------------------------------------<br>';
		echo( $txt);
	}

	if( $doUpdate) {
		$HarvestMetadata->save();
	}

	return $ret;
}

//--------------------------------------------------------------------------------------------------

function sourcesShowPageUpdateId( $id)
{
	global $MetadataVec;
	global $dataHarvestMetadata;

	$txt = '';
	$txt .= '<div class="log">Update source data<br>==================<br><br>';
	echo( $txt);

	for( $i = 0; $i < count( $MetadataVec); ++$i) {
		if( $id == md5($MetadataVec[$i]['meta'])) {
			$harvest = $dataHarvestMetadata[ $MetadataVec[$i]['meta']];

			$txt = '';
			$txt .= 'Name:&nbsp;&nbsp;&nbsp;&nbsp;'.nutsGetName( $MetadataVec[$i]['nuts'])['en-US'] . '<br>';
			$txt .= 'NUTS:&nbsp;&nbsp;&nbsp;&nbsp;'.$MetadataVec[$i]['nuts'] . '<br>';
			if( isset( $MetadataVec[$i]['nutsVec'])) {
				$txt .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				foreach( array_unique( $MetadataVec[$i]['nutsVec']) as $nuts) {
					$txt .= $nuts.' ';
				}
				$txt .= '<br>';
			}
			$txt .= 'Comment: '.$MetadataVec[$i]['name'] . '<br>';
			$txt .= 'Update:&nbsp;&nbsp;available since ' . $harvest['update'];
			if( 1 == $harvest['update']) {
				$txt .= ' day<br>';
			} else {
				$txt .= ' days<br>';
			}
			$txt .= '<br>';
			echo( $txt);

			sourcesShowPageUpdateDownload( $i);
			break;
		}
	}

	$txt = '';
	$txt .= '<br>';
	$txt .= 'Next step: [<a href="do=update&what=sourcedatanames&id='.$id.'">Check names</a>]<br>';
	$txt .= '<br>';
	$txt .= '[<a href="do=browse&what=sources">Show source list</a>]<br>';
	$txt .= '</div>';

	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

function sourcesShowPageUpdateNamesId( $id)
{
	global $MetadataVec;
	global $dataHarvestMetadata;

	$error = '';

	$txt = '';
	$txt .= '<div class="log">Check names<br>===========<br><br>';
	echo( $txt);

	for( $i = 0; $i < count( $MetadataVec); ++$i) {
		if( $id == md5($MetadataVec[$i]['meta'])) {
			$harvest = $dataHarvestMetadata[ $MetadataVec[$i]['meta']];

			$txt = '';
			$txt .= 'Name:&nbsp;&nbsp;&nbsp;&nbsp;'.nutsGetName( $MetadataVec[$i]['nuts'])['en-US'] . '<br>';
			$txt .= 'NUTS:&nbsp;&nbsp;&nbsp;&nbsp;'.$MetadataVec[$i]['nuts'] . '<br>';
			if( isset( $MetadataVec[$i]['nutsVec'])) {
				$txt .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				foreach( array_unique( $MetadataVec[$i]['nutsVec']) as $nuts) {
					$txt .= $nuts.' ';
				}
				$txt .= '<br>';
			}
			$txt .= 'Comment: '.$MetadataVec[$i]['name'] . '<br>';
			$txt .= 'Update:&nbsp;&nbsp;available since ' . $harvest['update'];
			if( 1 == $harvest['update']) {
				$txt .= ' day<br>';
			} else {
				$txt .= ' days<br>';
			}
			$txt .= '<br>';
			echo( $txt);

			$error = sourcesShowPageUpdateNames( $i, 'update');

			break;
		}
	}

	$txt = '';
	$txt .= '<br>';
	if( 'name' == $error) {
		$txt .= 'Next step: [Save names]<br>';
		$txt .= 'But first: [<a href="do=add&what=sourcedatanames&id='.$id.'">Add new names</a>]<br>';
	} else if( '' != $error) {
		$txt .= 'Next step: [Save names]<br>';
	} else {
		$txt .= 'Next step: [<a href="do=clean&what=sourcedatanames&id='.$id.'">Clean names</a>]<br>';
	}
	$txt .= '<br>';
	$txt .= '[<a href="do=browse&what=sources">Show source list</a>]<br>';
	$txt .= '</div>';

	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

function sourcesShowPageAddNamesId( $id)
{
	global $MetadataVec;
	global $dataHarvestMetadata;

	$error = '';

	$txt = '';
	$txt .= '<div class="log">Add new names<br>=============<br><br>';
	echo( $txt);

	for( $i = 0; $i < count( $MetadataVec); ++$i) {
		if( $id == md5($MetadataVec[$i]['meta'])) {
			$harvest = $dataHarvestMetadata[ $MetadataVec[$i]['meta']];

			$txt = '';
			$txt .= 'Name:&nbsp;&nbsp;&nbsp;&nbsp;'.nutsGetName( $MetadataVec[$i]['nuts'])['en-US'] . '<br>';
			$txt .= 'NUTS:&nbsp;&nbsp;&nbsp;&nbsp;'.$MetadataVec[$i]['nuts'] . '<br>';
			if( isset( $MetadataVec[$i]['nutsVec'])) {
				$txt .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				foreach( array_unique( $MetadataVec[$i]['nutsVec']) as $nuts) {
					$txt .= $nuts.' ';
				}
				$txt .= '<br>';
			}
			$txt .= 'Comment: '.$MetadataVec[$i]['name'] . '<br>';
			$txt .= 'Update:&nbsp;&nbsp;available since ' . $harvest['update'];
			if( 1 == $harvest['update']) {
				$txt .= ' day<br>';
			} else {
				$txt .= ' days<br>';
			}
			$txt .= '<br>';
			echo( $txt);

			$error = sourcesShowPageUpdateNames( $i, 'add');

			break;
		}
	}

	$txt = '';
	$txt .= '<br>';
	if( '' != $error) {
		$txt .= 'Next step: [Check names]<br>';
	} else {
		$txt .= 'Next step: [<a href="do=update&what=sourcedatanames&id='.$id.'">Check names</a>]<br>';
	}
	$txt .= '<br>';
	$txt .= '[<a href="do=browse&what=sources">Show source list</a>]<br>';
	$txt .= '</div>';

	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

function sourcesShowPageCleanNamesId( $id)
{
	global $MetadataVec;
	global $HarvestNames;
	global $HarvestNuts;
	global $HarvestYears;
	global $dataHarvestMetadata;
	global $dataHarvestStatNames;

	$error = false;
	$nuts = '';

	$txt = '';
	$txt .= '<div class="log">Clean names<br>=============<br><br>';
	echo( $txt);

	for( $i = 0; $i < count( $MetadataVec); ++$i) {
		if( $id == md5($MetadataVec[$i]['meta'])) {
			$harvest = $dataHarvestMetadata[ $MetadataVec[$i]['meta']];
			$nuts = $MetadataVec[$i]['nuts'];

			$txt = '';
			$txt .= 'Name:&nbsp;&nbsp;&nbsp;&nbsp;'.nutsGetName( $nuts)['en-US'] . '<br>';
			$txt .= 'NUTS:&nbsp;&nbsp;&nbsp;&nbsp;'.$nuts . '<br>';
			if( isset( $MetadataVec[$i]['nutsVec'])) {
				$txt .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				foreach( array_unique( $MetadataVec[$i]['nutsVec']) as $nuts_) {
					$txt .= $nuts_.' ';
				}
				$txt .= '<br>';
			}
			$txt .= 'Comment: '.$MetadataVec[$i]['name'] . '<br>';
			$txt .= 'Update:&nbsp;&nbsp;available since ' . $harvest['update'];
			if( 1 == $harvest['update']) {
				$txt .= ' day<br>';
			} else {
				$txt .= ' days<br>';
			}
			$txt .= '<br>';
			echo( $txt);

			break;
		}
	}

	$nutsStrVec = Array();
	if( isset( $MetadataVec[$i]['nutsVec'])) {
		foreach( array_unique( $MetadataVec[$i]['nutsVec']) as $nuts_) {
			$nutsStrVec[] = intEncodeBytes( $HarvestNuts->getId( $nuts_), 2);
		}
	} else {
		$nutsStrVec[] = intEncodeBytes( $HarvestNuts->getId( $nuts), 2);
	}

	$HarvestNames->loadAllStats();

	$txt = '';
	$txt .= '---------------------------------------------------<br>';
	echo( $txt);

	foreach( $nutsStrVec as $nutsStr) {
		foreach( $dataHarvestStatNames as $firstChar => &$names) {
			$txt = '';
			$txt .= $firstChar . ' ';
			foreach( $names as $name => &$str) {
				if( strlen( $str) >= 8) {
					for( $count = strlen( $str) - 8; $count >= 0; $count -= 8) {
						if(( $nutsStr[0] == $str[$count]) && ($nutsStr[1] == $str[$count+1])) {
							$str = substr_replace( $str, '', $count, 8);
						}
					}
				}
			}
			echo( $txt);
		}
		echo( '<br>');
	}
	$txt = '';
	$txt .= '---------------------------------------------------<br>';
	echo( $txt);

	$HarvestNames->saveAllStats();

	$txt = '';
	$txt .= '<br>';
//	if( $error) {
//		$txt .= 'Next step: [Harvest names]<br>';
//	} else {
		$txt .= 'Next step: [<a href="do=harvest&what=sourcedatanames&id='.$id.'">Harvest names</a>]<br>';
//	}
	$txt .= '<br>';
	$txt .= '[<a href="do=browse&what=sources">Show source list</a>]<br>';
	$txt .= '</div>';

	echo( $txt);
}

function sourcesShowPageHarvestNamesId( $id)
{
	global $MetadataVec;
	global $HarvestNames;
	global $HarvestNuts;
	global $HarvestYears;
	global $dataHarvestMetadata;

	$error = false;
	$nuts = '';

	$txt = '';
	$txt .= '<div class="log">Harvest names<br>=============<br><br>';
	echo( $txt);

	for( $i = 0; $i < count( $MetadataVec); ++$i) {
		if( $id == md5($MetadataVec[$i]['meta'])) {
			$harvest = $dataHarvestMetadata[ $MetadataVec[$i]['meta']];
			$nuts = $MetadataVec[$i]['nuts'];

			$txt = '';
			$txt .= 'Name:&nbsp;&nbsp;&nbsp;&nbsp;'.nutsGetName( $nuts)['en-US'] . '<br>';
			$txt .= 'NUTS:&nbsp;&nbsp;&nbsp;&nbsp;'.$nuts . '<br>';
			if( isset( $MetadataVec[$i]['nutsVec'])) {
				$txt .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				foreach( array_unique( $MetadataVec[$i]['nutsVec']) as $nuts_) {
					$txt .= $nuts_.' ';
				}
				$txt .= '<br>';
			}
			$txt .= 'Comment: '.$MetadataVec[$i]['name'] . '<br>';
			$txt .= 'Update:&nbsp;&nbsp;available since ' . $harvest['update'];
			if( 1 == $harvest['update']) {
				$txt .= ' day<br>';
			} else {
				$txt .= ' days<br>';
			}
			$txt .= '<br>';
			echo( $txt);

			break;
		}
	}

	$years = $HarvestYears->getYears( $nuts);
	rsort( $years);

	$nutsStrVec = Array();
	if( isset( $MetadataVec[$i]['nutsVec'])) {
		foreach( array_unique( $MetadataVec[$i]['nutsVec']) as $nuts_) {
			$nutsStrVec[ $nuts_ ] = intEncodeBytes( $HarvestNuts->getId( $nuts_), 2);
		}
	} else {
		$nutsStrVec[ $nuts ] = intEncodeBytes( $HarvestNuts->getId( $nuts), 2);
	}

	$HarvestNames->loadAllStats();

	if( count( $years) > 0) {
		foreach( $nutsStrVec as $nuts => $nutsStr) {
			if( $error) {
				echo( $txt);
				$txt = '';
				break;
			}
			foreach( $years as $year) {
				$txt = '';
				$txt .= 'Save names from year '.$year.' in '.nutsGetName( $nuts)['en-US'] . '<br>';
				$txt .= '----------------------------------------------------------------------------------------------<br>';
				$names = $HarvestYears->getOneYear( $nuts, $year, true);
				$i = 0;
				foreach( $names as $value) {
					if( $i < 6) {
						$txt .= '<a href="do=name&what='.$value['GIVEN_NAME'].'">'.$value['GIVEN_NAME'].'</a>, ';
					}
					$code = $nutsStr . intEncodeBytes( $value['RANKING'], 3) . intEncodeBytes( $year, 2) . 'm';
					$error = $HarvestNames->harvest( $value['GIVEN_NAME'], $code);
					if( $error) {
						echo( $txt);
						$txt = '';
						break;
					}
					++$i;
				}
				if( $error) {
					echo( $txt);
					$txt = '';
					break;
				}
				$txt .= '...<br>';
				$names = $HarvestYears->getOneYear( $nuts, $year, false);
				$i = 0;
				foreach( $names as $value) {
					if( $i < 6) {
						$txt .= '<a href="do=name&what='.$value['GIVEN_NAME'].'">'.$value['GIVEN_NAME'].'</a>, ';
					}
					$code = $nutsStr . intEncodeBytes( $value['RANKING'], 3) . intEncodeBytes( $year, 2) . 'f';
					$error = $HarvestNames->harvest( $value['GIVEN_NAME'], $code);
					if( $error) {
						echo( $txt);
						$txt = '';
						break;
					}
					++$i;
				}
				if( $error) {
					echo( $txt);
					$txt = '';
					break;
				}
				$txt .= '...<br>';
				$txt .= '----------------------------------------------------------------------------------------------<br>';
				$txt .= '<br>';
				echo( $txt);
			}
		}
	}

	if( !$error) {
		$HarvestNames->saveAllStats();
	}

	$txt = '';
	$txt .= '<br>';
	if( $error) {
		$txt .= 'Next step: [Save data]<br>';
	} else {
		$txt .= 'Next step: [<a href="do=save&what=sourcedatanames&id='.$id.'">Save data</a>]<br>';
	}
	$txt .= '<br>';
	$txt .= '[<a href="do=browse&what=sources">Show source list</a>]<br>';
	$txt .= '</div>';

	echo( $txt);
}

function sourcesShowPageSaveNamesId( $id)
{
	global $MetadataVec;
	global $HarvestMetadata;
	global $HarvestNames;
	global $HarvestNuts;
	global $HarvestYears;
	global $dataHarvestMetadata;

	$error = false;
	$nuts = '';

	$txt = '';
	$txt .= '<div class="log">Save names<br>==========<br><br>';
	echo( $txt);

	for( $i = 0; $i < count( $MetadataVec); ++$i) {
		if( $id == md5($MetadataVec[$i]['meta'])) {
			$harvest = $dataHarvestMetadata[ $MetadataVec[$i]['meta']];
			$nuts = $MetadataVec[$i]['nuts'];

			$txt = '';
			$txt .= 'Name:&nbsp;&nbsp;&nbsp;&nbsp;'.nutsGetName( $nuts)['en-US'] . '<br>';
			$txt .= 'NUTS:&nbsp;&nbsp;&nbsp;&nbsp;'.$nuts . '<br>';
			$txt .= 'Comment: '.$MetadataVec[$i]['name'] . '<br>';
			$txt .= 'Update:&nbsp;&nbsp;available since ' . $harvest['update'];
			if( 1 == $harvest['update']) {
				$txt .= ' day<br>';
			} else {
				$txt .= ' days<br>';
			}
			$txt .= '<br>';
			echo( $txt);

			break;
		}
	}

	if( $i < count( $MetadataVec)) {
		$harvest['modified'] = date( 'Y-m-d');
		$harvest['update'] = 0;
		$dataHarvestMetadata[ $MetadataVec[$i]['meta']] = $harvest;

		$HarvestMetadata->save();

		$txt = '';
		$txt .= '&radic; done<br>';
		echo( $txt);
	} else {
		$error = true;
	}

	$txt = '';
	$txt .= '<br>';
	$txt .= '[<a href="do=browse&what=sources">Show source list</a>]<br>';
	$txt .= '</div>';

	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

function sourcesShowPageItem( $nuts)
{
	global $MetadataVec;
	global $dataHarvestMetadata;
	global $HarvestMetadata;
	global $HarvestYears;

	$txt = '';
	$txt .= '<div class="log">Show source data<br>================<br><br>';
	echo( $txt);

	// nuts
	$txt = '';
	$nutsName = $nuts;
	$txtTab = '&nbsp;&nbsp;&nbsp;|- ';

	$txt .= 'Name: '.nutsGetName( $nutsName)['en-US'] . '<br>';
	$txt .= 'NUTS: '.$nutsName . '<br>';
	$txt .= 'Path: '.nutsGetName( $nutsName)['en-US'] . '<br>';
	do {
		$nutsName = substr( $nutsName, 0, -1);
		if( nutsExists( $nutsName)) {
			$names = nutsGetName( $nutsName);
			$txtTab = '&nbsp;&nbsp;&nbsp;'.$txtTab;
			$txt .= $txtTab. $names['en-US'] . '<br>';
		}
	} while( strlen( $nutsName) > 1);
	$txt .= '<br>';
	$txt .= '<br>';
	echo( $txt);

	for( $i = 0; $i < count( $MetadataVec); ++$i) {
		if( $nuts == $MetadataVec[$i]['nuts']) {
			$dataUpdateLink = 'do=update&what=sourcedata&id='.md5($MetadataVec[$i]['meta']);
			$harvest = $dataHarvestMetadata[ $MetadataVec[$i]['meta']];
			$txt = '';

			$name = $MetadataVec[$i]['name'];
			$txt .= $name.'<br>';
			$txt .= '----------------------------------------------------------------------------------------------<br>';
			$txt .= 'Metadata: [<a href="'.$MetadataVec[$i]['meta'].'" target="_blank">show metadata</a>] [<a href="'.$dataUpdateLink.'">update metadata</a>]<br>';

			for( $idx = 0; $idx < count( $harvest['url']); ++$idx) {
				$name = $harvest['name'][$idx];
				$url = $harvest['url'][$idx];

				if( 0 === strpos( $url, '/katalog/storage')) {
					$url = 'http://data.gv.at' . $url;
				} else if( 0 === strpos( $url, '/at.gv.brz.ogd/storage')) {
					$url = 'http://data.gv.at/katalog/' . substr( $url, 15);
				} else if( 0 === strpos( $url, '/private/')) {
					$url = dirname(__FILE__) . '/data' . $url;
				}

				$path = substr($url,strrpos($url,'/') + 1);
				if( false !== strrpos($path,'?')) {
					$path = substr($path,0,strrpos($path,'?'));
				}
				if( strlen( $name) == 0) {
					$name = $path;
				}
				if( $name == '') {
					$name = $path;
				}

				$txt .= 'Source:&nbsp;&nbsp;&nbsp;<a href="' . $url. '" target="_blank">' . $name . '</a><br>';
			}

			$vecDownload = $harvest['download'];
			for( $idx = 0; $idx < count( $vecDownload); ++$idx) {
				$url = $vecDownload[$idx];
				$file = dirname(__FILE__).'/'.$url;

				$path = substr($url,strrpos($url,'/') + 1);
				$txt .= 'Local:&nbsp;&nbsp;&nbsp;&nbsp;' . $url. ' ('.filesize( $file).' bytes)<br>';
			}

			$txt .= 'Years:&nbsp;&nbsp;&nbsp;&nbsp;';
			if( $harvest['years'] > 0) {
				$years = array_unique( $harvest['years']);
				rsort( $years);
				for( $j = 0; $j < count( $years); ++$j) {
					$txt .= $years[$j] . ' ';
				}
			} else {
				$txt .= 'none';
			}
			$txt .= '<br>';

			$txt .= '----------------------------------------------------------------------------------------------<br>';
			$txt .= '<br>';
			echo( $txt);
		}
	}

	$top = 3;
	$txt = '';
	$years = $HarvestYears->getYears( $nuts);

	if( count( $years) > 0) {
		rsort( $years);

		foreach( $years as $year) {
			$maleCount = 0;
			$femaleCount = 0;
			$maleData = $HarvestYears->getOneYear( $nuts, $year, true);
			$femaleData = $HarvestYears->getOneYear( $nuts, $year, false);

			$txt .= 'Top '.$top.' of '.count($maleData).' males and '.count($femaleData).' females in '.$year.'<br>';
			$txt .= '&nbsp;&nbsp;';
			foreach( $maleData as $value) {
				if( intVal( $value['RANKING']) <= $top) {
					if( $maleCount > 0) {
						$txt .= ', ';
					}
					++$maleCount;
					$txt .= '<a href="do=name&what='.$value['GIVEN_NAME'].'">'.$value['GIVEN_NAME'].'</a> ('.$value['NUMBER'].'x)';
				}
			}
			$txt .= '<br>&nbsp;&nbsp;';
			foreach( $femaleData as $value) {
				if( intVal( $value['RANKING']) <= $top) {
					if( $femaleCount > 0) {
						$txt .= ', ';
					}
					++$femaleCount;
					$txt .= '<a href="do=name&what='.$value['GIVEN_NAME'].'">'.$value['GIVEN_NAME'].'</a> ('.$value['NUMBER'].'x)';
				}
			}
			$txt .= '<br><br>';
		}
	}
	echo( $txt);

	$txt = '';
	$txt .= '<br>';
	$txt .= '[<a href="do=browse&what=sources">Show source list</a>]<br>';
	$txt .= '[<a href="do=">Show admin area</a>]<br>';
	$txt .= '</div>';
	echo( $txt);

	$txt = '<br>';
	$txt .= '<br>';
	$txt .= '<hr>';
	$txt .= '<br>';

	$txt .= '<a href="do=delete&what=sourcedata&id='.$id.'"><span style="color:red;">Delete all statistic data</span></a><br>';

	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

// used in index.php
function showPageDeleteSourcedata( $id)
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
	}

	foreach( $gBoys as $key => $value) {
		foreach( $value['ref'] as $refkey => $refvalue) {
			$posSource = strpos( $refvalue, '-');
			$strSource = substr( $refvalue, 0, $posSource);

			if( in_array( intval( $strSource), $idVec)) {
				unset( $value['ref'][$refkey]);
			}
		}
		$gBoys[$key] = $value;
	}

	foreach( $gGirls as $key => $value) {
		foreach( $value['ref'] as $refkey => $refvalue) {
			$posSource = strpos( $refvalue, '-');
			$strSource = substr( $refvalue, 0, $posSource);

			if( in_array( intval( $strSource), $idVec)) {
				unset( $value['ref'][$refkey]);
			}
		}
		$gGirls[$key] = $value;
	}

	$gSource[$i]['autoModified'] = '2001-01-01';

	gBoysToFile();
	gGirlsToFile();
	gSourceToFile();

	sourcesShowPageItem( $id);
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