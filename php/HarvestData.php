<?php

//------------------------------------------------------------------------------

class HarvestDataParserBase
{
	public $reverseMales = Array();
	public $reverseFemales = Array();

	public function accept( $vec, $vecCount)
	{
		return false;
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		$ret = new HarvestDataResult();
		return $ret;
	}

	public function parseData( & $data)
	{
		$dataCount = count( $data);

		for( $it = 0; $it < $dataCount; ++$it) {
			$item = & $data[ $it];
			$isBoy = $item['male'];
			$item['error'] = $this->parseNames( $item, $isBoy);
		}
	}

	public function saveData( $data, & $fileVec, & $checksumVec, & $yearVec, $nuts)
	{
		$filenames = Array();
		$contents = Array();
		$fileVec = Array();
		$checksumVec = Array();
		$yearVec = Array();
		$dataCount = count( $data);

		for( $it = 0; $it < $dataCount; ++$it) {
			if( 0 < strlen( $data[ $it]['error'])) {
				return;
			}
		}

		for( $it = 0; $it < $dataCount; ++$it) {
			$item = $data[ $it];
			$dest = $item['year'].($item['male']?'_m':'_f');
			$filenames[$dest] = 0;
		}

		foreach( $filenames as $filename => $foo) {
			$content = Array();
			for( $it = 0; $it < $dataCount; ++$it) {
				$item = $data[ $it];
				$dest = $item['year'].($item['male']?'_m':'_f');
				if( $dest == $filename) {
					$content[] = $nuts.';'.$item['name'].';'.$item['number'].';'.$item['pos'].';'.($item['male']?'male':'female').';'.$item['year'];
				}
			}

			$this->saveDataToFile( $filename, $content, $fileVec, $checksumVec, $yearVec, $nuts);
		}

		// check md5!
		// $fileVec, $checksumVec and $yearVec are overwritten ;-(
	}

	public function saveDataToFile( $filename, $content, & $fileVec, & $checksumVec, & $yearVec, $nuts)
	{
		$path = 'data/harvest/'.substr($nuts, 0, 2).'/'.$nuts.'/'.$nuts.'_'.$filename.'.csv';
		$file = dirname(__FILE__) . '/' . $path;
		$out = "NUTS;GIVEN_NAME;NUMBER;RANKING;SEX;YEAR\n";
		$out .= implode( "\n", $content);
		file_put_contents( $file, $out);

		$fileVec[] = $path;
		$checksumVec[] = md5( $out);
		if( !isset( $yearVec[substr( $filename, 0, 4)])) {
			$yearVec[] = substr( $filename, 0, 4);
		}
	}

	public function parseNames( $item, $isBoy)
	{
		global $HarvestNames;

		$ret = '';
		$found = false;
		$name = $item['name'];
		$nameUFT8 = $name;

		$HarvestNames->load();
		if( 0 == count( $this->reverseMales)) {
			$this->reverseMales = array_flip( $HarvestNames->male);
		}
		if( 0 == count( $this->reverseFemales)) {
			$this->reverseFemales = array_flip( $HarvestNames->female);
		}

		if( $isBoy) {
//			$found = in_array($nameUFT8, $HarvestNames->male);
			$found = isset( $this->reverseMales[ $nameUFT8]);
		} else {
//			$found = in_array($nameUFT8, $HarvestNames->female);
			$found = isset( $this->reverseFemales[ $nameUFT8]);
		}

		if( $found) {
			return '';
		}

//		if( false)
		{
			if( $name == 'ohne') return $name;
			if( $name == 'noch') return $name;
			if( $name == 'kein') return $name;
			if( $name == 'keinen') return $name;
			if( $name == 'Vorname') return $name;
			if( $name == 'Vornamen') return $name;
			if( $name == '(Eigenname)') return $name;
			if( $name == 'de') return $name;
			if( $name == 'del') return $name;
			if( $name == 'don') return $name;
			if( $name == 'oğlu') return $name;
			if( $name == '(Vorname') return $name;
			if( $name == '(Vornamen') return $name;
			if( $name == 'und') return $name;
			if( $name == 'Vatersname)') return $name;
			if( $name == 'A.') return $name;
		}
		if( $name == '') return '<no name>';

		// lowercase at beginning
		if( $name != ucwords( strtolower( $item['name']))) {
			if( false !== strpos( $name, '-')) {
			} else {
				return $name;
			}
		}
		// placeholder
		if( false !== strpos( $name, 'name')) {
			return $name;
		}
		// abbreviation
		if( false !== strpos( $name, '.')) {
			return $name;
		}

		if( !$found) {
			if( $isBoy) {
				$HarvestNames->male[] = $name;
//				unset( $this->reverseMales);
				$this->reverseMales = array_flip( $HarvestNames->male);
			} else {
				$HarvestNames->female[] = $name;
//				unset( $this->reverseFemales);
				$this->reverseFemales = array_flip( $HarvestNames->female);
			}

			$ret .= 'New name <span style="color:';
			if( $isBoy) {
				$ret .= 'DeepSkyBlue';
			} else {
				$ret .= 'Coral';
			}
			return $ret . '">' . $name . '</span>';
		}

		return $ret;
	}

	public function echoDataErrors( $data, $echoDataErrors)
	{
		$ret = true;
		$dataCount = count( $data);

		for( $it = 0; $it < $dataCount; ++$it) {
			$item = $data[ $it];
			if( '' != $item['error']) {
				$ret = false;

				if( $echoDataErrors) {
					echo $item['error'] . ' (#' . $item['pos'] . ' ' . ($item['male']?'male':'female') . ' in ' . $item['year'] . ')<br>';
				} else {
					return $ret;
				}
			}
		}

		return $ret;
	}
} // class HarvestDataParserBase

//------------------------------------------------------------------------------

class HarvestDataResult
{
	public $error = true;
	public $errorMsg = 'not parsed';
	public $data = Array();
	public $file = Array();
	public $checksum = Array();
	public $years = Array();
} // class HarvestDataResult

//------------------------------------------------------------------------------

class HarvestData
{
	public $parserVec = Array();

	public function addParser( $name)
	{
		$this->parserVec[] = $name;
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		for( $i = 0; $i < count( $this->parserVec); ++$i) {
			$parser = new $this->parserVec[$i]();
			if( $parser->accept( $vec, $vecCount)) {
				return $parser->parse( $vec, $vecCount, $nuts, $url, $echoDataErrors);
			}
		}

		$ret = new HarvestDataResult();
		$ret->error = true;
		$ret->errorMsg = 'Unknown data format found';

		return $ret;
	}

	public function getParserClass( $vec, $vecCount)
	{
		for( $i = 0; $i < count( $this->parserVec); ++$i) {
			$parser = new $this->parserVec[$i]();
			if( $parser->accept( $vec, $vecCount)) {
				return get_class( $parser);
			}
		}

		return 'No parser found';
	}
} // class HarvestData
$HarvestData = new HarvestData();

//------------------------------------------------------------------------------

class HarvestDataParserNUTS extends HarvestDataParserBase
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 0) && ($vec[0][0] == 'NUTS2');
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		// Lower Austria, ...
		$ret = new HarvestDataResult();

		$colName = -1;
		$colSex = -1;
		$colYear = -1;
		$colPos = -1;
		$colCount = -1;
		$startRow = 0;

		$row = 0;
		// häufigste
		if( 6 === strpos( $vec[ $row][0], 'ufigste')) {
			if( 228 == ord( $vec[ $row][0][5])) {
				convertVecVecToUTF8( $vec);
			}
		}

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
				} else if( trim( $vec[ $row][ $col]) == 'NUMBER') {
					$colCount = $col;
					$startRow = $row;
				} else if( trim( $vec[ $row][ $col]) == 'RANKING_REF_YEAR') {
					$colPos = $col;
					$startRow = $row;
				}
			}
		}

		if(( -1 == $colName) || (-1 == $colSex) ||(-1 == $colYear)) {
			$ret->errorMsg = 'Unknown NUTS format!';
			return $ret;
		}

		$ret->data = Array();
		$posVec = Array();
		$posVec['1'] = Array(); // male
		$posVec['2'] = Array(); // female

		if( $vec[ $row][ $colSex] == '1') {
		} else if( $vec[ $row][ $colSex] == '2') {
		} else {
			$ret->errorMsg = 'Unknown sex in NUTS format!';
			return $ret;
		}

		$name = trim( $vec[ $startRow + 1][ $colName], '* ');
		$needUC = ($name == strtoupper( $name));

		for( $row = $startRow + 1; $row < count( $vec); ++$row) {
			if( count( $vec[ $row]) > 1) {
				if(( '' == $vec[ $row][ $colName]) && ('' == $vec[ $row][ $colCount]) && ('' == $vec[ $row][ $colSex])) {
					continue;
				}

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
					} else if( $name == "ANNA-SOPHIE") {
						$name = 'Anna-Sophie';
					} else if( $name == "LISA-MARIE") {
						$name = 'Lisa-Marie';
					} else {
						$name = ucwords( strtolower( $name));
					}
				} else {
					if( $name == "Lisa-marie") {
						$name = 'Lisa-Marie';
					}
				}

				if( $name == 'Rene') {
					$name = 'René';
				}

				$ret->data[] = Array(
					name=> $name,
					male=> $vec[ $row][ $colSex] == '1' ? true : false,
					year=> trim( $vec[ $row][ $colYear]),
					pos=> $colPos == -1 ? $posVec[ $vec[ $row][ $colSex]][ $vec[ $row][ $colYear]] : $vec[ $row][ $colPos],
					number=> trim( $vec[ $row][ $colCount]),
					error=> '',
				);
			}
		}

		$this->parseData( $ret->data);
		$this->saveData( $ret->data, $ret->file, $ret->checksum, $ret->years, $nuts);

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}

	public function strtotimeLoc( $date_string)
	{
		$ret = strtotime( $date_string);
		if( $ret === false) {
			$date_string = strtr( strtolower( $date_string), array('januar'=>'jan','februar'=>'feb','märz'=>'march','april'=>'apr','mai'=>'may','juni'=>'jun','juli'=>'jul','august'=>'aug','september'=>'sep','oktober'=>'oct','november'=>'nov','dezember'=>'dec'));
			$ret = strtotime( $date_string);
			if( $ret === false) {
				$date_string = strtr( $date_string, array('-'=>''));
				$ret = strtotime( $date_string);
			}
		}
		return $ret;
	}
} // class HarvestDataParserNUTS
$HarvestData->addParser('HarvestDataParserNUTS');

//------------------------------------------------------------------------------

class HarvestDataParserNUTSAlt1 extends HarvestDataParserNUTS
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 2) && ($vec[2][1] == 'NUTS2');
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		// Vienna, ...
		return parent::parse( $vec, $vecCount, $nuts, $url, $echoDataErrors);
	}
} // class HarvestDataParserNUTSAlt1
$HarvestData->addParser('HarvestDataParserNUTSAlt1');

//------------------------------------------------------------------------------

class HarvestDataParserNUTSAlt2 extends HarvestDataParserNUTS
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 0) && ($vec[0][1] == 'NUTS2');
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		// Styria, ...
		return parent::parse( $vec, $vecCount, $nuts, $url, $echoDataErrors);
	}
} // class HarvestDataParserNUTSAlt2
$HarvestData->addParser('HarvestDataParserNUTSAlt2');

//------------------------------------------------------------------------------

class HarvestDataParserAutiSta extends HarvestDataParserBase
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 0) && /*($vec[0][0] == 'vorname') &&*/ ($vec[0][1] == 'anzahl') && (trim( $vec[0][2]) == 'geschlecht');
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		global $dataHarvestMetadata;

		// AutiSta used in Berlin, Bonn, Chemnitz, Hamburg, Moers, Ulm ...
		$ret = new HarvestDataResult();

		$colName = 0;
		$colSex = 2;
//		$colYear = -1;
		$colPos = -1;
		$colCount = 1;
		$startRow = 0;

		foreach( $dataHarvestMetadata as $datasetUrl => $harvest) {
			for( $idx = 0; $idx < count( $harvest['url']); ++$idx) {
				$download = $harvest['download'][$idx];
				if( $url == $download) {
					$url = $harvest['url'][$idx];
				}
			}
		}

		$theYear = 2012; // berlin missing year number in 2012
		if( false !== strpos( $url, '_0.')) {
			$pos = strpos( $url, '_0.');
			$url = substr( $url, 0, $pos).substr( $url, $pos + 2);
		} else if( false !== strpos( $url, '_1.')) {
			$pos = strpos( $url, '_1.');
			$url = substr( $url, 0, $pos).substr( $url, $pos + 2);
		} else if( false !== strpos( $url, '_2.')) {
			$pos = strpos( $url, '_2.');
			$url = substr( $url, 0, $pos).substr( $url, $pos + 2);
		}
		preg_match_all('!\d+!', $url, $yearVec);
		if( 0 < count( $yearVec[0])) {
			$lastYear = $yearVec[0][count($yearVec[0])-1];
			$lastYear = substr( $lastYear, strlen( $lastYear) - 4);
			if(( 1900 < $lastYear) && ($lastYear < 2100)) {
				$theYear = $lastYear;
			} else if(( 10 < $lastYear) && ($lastYear < 100)) {
				// Moers use 2 digits for the year 2014
				$theYear = 2000 + $lastYear;
			}
		}

		$vecCount = count( $vec);
		if( $vecCount < 2) {
			$ret->errorMsg = 'Unknown AutiSta format!';
			return $ret;
		}

		$yearPos = Array();
		$posCounter = Array();
		$oldCount = Array();

		$ret->data = Array();
		$yearPos['m'] = 1;
		$yearPos['w'] = 1;
		$posCounter['m'] = 1;
		$posCounter['w'] = 1;
		$oldCount['m'] = 0;
		$oldCount['w'] = 0;

		for( $row = $startRow + 1; $row < $vecCount; ++$row) {
			if( count( $vec[ $row]) > 1) {
				$sex = trim( $vec[ $row][ $colSex]);
				if( $oldCount[ $sex] != intval( $vec[ $row][ $colCount])) {
					$oldCount[ $sex] = intval( $vec[ $row][ $colCount]);
					$yearPos[ $sex] = $posCounter[ $sex];
				}

				$name = trim( $vec[ $row][ $colName]);
//				if( false)
				{
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
					} else if( $name == "LouAnn") { // correct name
						$name = "Lou-Ann";
					} else if( $name == "kim") {
						$name = "Kim"; // ????
					} else if( $name == "gizi") {
						$name = "Gizi"; // ????
					} else if( $name == "mia") {
						$name = "Mia"; // ????
						continue;
					}
				}

				$ret->data[] = Array(
					name=> $name,
					male=> $sex == 'm' ? true : false,
					year=> trim( $theYear),
					pos=> $yearPos[ $sex],
					number=> $vec[ $row][ $colCount],
					error=> '',
				);

				++$posCounter[ $sex];
			}
		}

		$this->parseData( $ret->data);
		$this->saveData( $ret->data, $ret->file, $ret->checksum, $ret->years, $nuts);

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}
} // class HarvestDataParserAutiSta
$HarvestData->addParser('HarvestDataParserAutiSta');

//------------------------------------------------------------------------------

class HarvestDataParserAutiStaScan extends HarvestDataParserBase
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 1) && (trim( $vec[1][0]) == 'Anzahl der Kinder mit');
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		// AutiSta used in Bremen, Moers, ...
		$ret = new HarvestDataResult();

		$vecCount = count( $vec);
		if( $vecCount < 20) {
			$ret->errorMsg = 'Unknown AutiSta scan format!';
			return $ret;
		}

		$row = 0;
		if( 'Vornamenstatistik' != explode( " ", trim( $vec[ $row][0]))[0]) {
			++$row;
		}

		$theYearStr = trim( $vec[ $row][0]);
		$theYearStr = substr( $theYearStr, strlen( $theYearStr) - 4);
		$theYear = intval( $theYearStr);
		if( $theYear < 2000) {
			$ret->errorMsg = 'Unknown AutiSta scan year format... ' . $theYear . ' != ' . $theYearStr;
			return $ret;
		}

		for( ; $row < $vecCount; ++$row) {
			if( '' == $vec[ $row][0]) {
				if( $row > 3) {
					// Häufigkeit
					if( 2 === strpos( $vec[ $row + 1][0], 'ufigkeit')) {
						if( 228 == ord( $vec[ $row + 1][0][1])) {
							convertVecVecToUTF8( $vec);
						}
					}
					break;
				}
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

		if( $row >= $vecCount) {
			$ret->errorMsg = 'Unknown AutiSta scan year format (less data)...';
			return $ret;
		}

		if( 1 == count( $vec[ $row])) {
			return $this->parseFromPDF( $vec, $vecCount, $nuts, $url, $row, $theYear);
		}

		if( 'Anzahl' != trim( $vec[ $row][2])) {
			--$row;
			if( 'Anzahl' != trim( $vec[ $row-1][2])) {
				$row += 2;
				if( 'Anzahl' != trim( $vec[ $row][2])) {
					$ret->errorMsg = 'Unknown AutiSta scan year format (Anzahl 1)...';
					return $ret;
				}
			}
		}
		if( 'Knaben' != trim( $vec[ $row][3])) {
			if( 'Knaben' != trim( $vec[ $row-1][3])) {
				if( 'Jungen' != trim( $vec[ $row][3])) {
					$ret->errorMsg = 'Unknown AutiSta scan year format (Knaben)...';
					return $ret;
				}
			}
		}
		if( 'Anzahl' != trim( $vec[ $row][4])) {
			if( 'Anzahl' != trim( $vec[ $row-1][4])) {
				$ret->errorMsg = 'Unknown AutiSta scan year format (Anzahl 2)...';
				return $ret;
			}
		}

		++$row;

//		$colName = -1;
		$colNameMale = 3;
		$colNameFemale = 1;
//		$colSex = -1;
//		$colYear = 0;
		$colPos = 0;
//		$colCount = -1;
		$colCountMale = 4;
		$colCountFemale = 2;

		$ret->data = Array();

		for( ; $row < $vecCount; ++$row) {
			if( intval( $vec[ $row][ $colPos]) < 1) {
				break;
			}
			if( count( $vec[ $row]) > 1) {
				$ret->data[] = Array(
					name=> trim( $vec[ $row][ $colNameMale]),
					male=> true,
					year=> $theYear,
					pos=> intval( $vec[ $row][ $colPos]),
					number=> trim( $vec[ $row][ $colCountMale]),
					error=> '',
				);
				$ret->data[] = Array(
					name=> trim( $vec[ $row][ $colNameFemale]),
					male=> false,
					year=> $theYear,
					pos=> intval( $vec[ $row][ $colPos]),
					number=> trim( $vec[ $row][ $colCountFemale]),
					error=> '',
				);
			}
		}

		$this->parseData( $ret->data);
		$this->saveData( $ret->data, $ret->file, $ret->checksum, $ret->years, $nuts);

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}

	public function parseFromPDF( $vec, $vecCount, $nuts, $url, $row, $theYear)
	{
		// Used in Essen, ...
		$ret = new HarvestDataResult();
		$vecCount = count( $vec);

		$current = explode( " ", $vec[ $row][0]);
		$previous = explode( " ", $vec[ $row-1][0]);

		if( 'Anzahl' != trim( $current[2])) {
			--$row;
			if( 'Anzahl' != trim( $previous[2])) {
				$ret->errorMsg = 'Unknown AutiSta scan pdf format (Anzahl 1)...';
				return $ret;
			}
		}
		if( 'Knaben' != trim( $current[3])) {
			if( 'Knaben' != trim( $previous[3])) {
				if( 'Jungen' != trim( $current[3])) {
					if( 'Jungen' != trim( $previous[3])) {
						$ret->errorMsg = 'Unknown AutiSta scan pdf format (Knaben)...';
						return $ret;
					}
				}
			}
		}
		if( 'Anzahl' != trim( $current[4])) {
			if( 'Anzahl' != trim( $previous[4])) {
				$ret->errorMsg = 'Unknown AutiSta scan pdf format (Anzahl 2)...';
				return $ret;
			}
		}

		++$row;

//		$colName = -1;
		$colNameMale = 3;
		$colNameFemale = 1;
//		$colSex = -1;
//		$colYear = 0;
		$colPos = 0;
//		$colCount = -1;
		$colCountMale = 4;
		$colCountFemale = 2;

		$ret->data = Array();

		for( ; $row < $vecCount; ++$row) {
			$current = explode( " ", $vec[ $row][0]);
			if( intval( $current[ $colPos]) < 1) {
				continue;
			}
			if( count( $current) > 3) {
				$male = trim( $current[ $colNameMale]);
				if( 'Tot' == $male) {} else
				if( 'geborener' == $male) {} else
				if( '(Vorname' == $male) {} else
				if( '(Vor' == $male) {} else
				if( 'und' == $male) {} else
				if( 'Vatersname)' == $male) {} else
				if( '(Vatersname)' == $male) {} else
				if( 'noch' == $male) {} else
				if( 'kein' == $male) {} else
				if( 'Vorname' == $male) {} else
				if( 'oğlu' == $male) {} else
				if( 'van' == $male) {} else
				if( 'Alessandro-' == $male) {} else // data corruption
				if( 'Maximilian-' == $male) {} else // data corruption
				if( '1' == $male) {} else // given name is empty, count is '1'
				{
					$ret->data[] = Array(
						name=> $male,
						male=> true,
						year=> $theYear,
						pos=> intval( $current[ $colPos]),
						number=> trim( $current[ $colCountMale]),
						error=> '',
					);
				}

				$female = trim( $current[ $colNameFemale]);
				if(( '' == $female) && ( '' == $current[ $colCountFemale])) {
					continue;
				}

				if( 'Tot' == $female) {} else
				if( 'geborenes' == $female) {} else
				if( 'Mädchen' == $female) {} else
				if( '(Vorname' == $female) {} else
				if( 'und' == $female) {} else
				if( 'Vatersname)' == $female) {} else
				if( 'Vatersname' == $female) {} else
				if( 'Vatersname:' == $female) {} else
				if( 'Nameskette' == $female) {} else
				if( 'Namenskette' == $female) {} else
				if( '(Namenskette)' == $female) {} else
				if( 'noch' == $female) {} else
				if( 'kein' == $female) {} else
				if( 'Vorname' == $female) {} else
				if( '-Alexandra' == $female) {} else // data corruption
				if( 'Irini-' == $female) {} else // data corruption
				if( 'Jo-Essen' == $female) {} else // found in database from Essen
				if( 'nana' == $female) {} else
				if( 'kyzy' == $female) {} else
				if( 'de' == $female) {} else
				{
					$ret->data[] = Array(
						name=> $female,
						male=> false,
						year=> $theYear,
						pos=> intval( $current[ $colPos]),
						number=> trim( $current[ $colCountFemale]),
						error=> '',
					);
				}
			} else if( count( $current) > 1) {
				$female = trim( $current[ 1]);
				if( 'Zwischennamen:' == $female) {} else
				{
					$ret->data[] = Array(
						name=> $female,
						male=> false,
						year=> $theYear,
						pos=> intval( $current[ $colPos]),
						number=> trim( $current[ $colCountFemale]),
						error=> '',
					);
				}
			}
		}

		$this->parseData( $ret->data);
		$this->saveData( $ret->data, $ret->file, $ret->checksum, $ret->years, $nuts);

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}
} // class HarvestDataParserAutiStaScan
$HarvestData->addParser('HarvestDataParserAutiStaScan');

//------------------------------------------------------------------------------

class HarvestDataParserAutiStaScan2 extends HarvestDataParserAutiStaScan
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 1) && ($vec[1][0] == 'Anzahl der  Kinder mit');
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		// Bremen, ...
		return parent::parse( $vec, $vecCount, $nuts, $url, $echoDataErrors);
	}
} // class HarvestDataParserAutiStaScan2
$HarvestData->addParser('HarvestDataParserAutiStaScan2');

//------------------------------------------------------------------------------

class HarvestDataParserAutiStaScan3 extends HarvestDataParserAutiStaScan
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 2) && (trim( $vec[2][0]) == 'Anzahl der Kinder mit');
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		// Munich, ...
		return parent::parse( $vec, $vecCount, $nuts, $url, $echoDataErrors);
	}
} // class HarvestDataParserAutiStaScan3
$HarvestData->addParser('HarvestDataParserAutiStaScan3');

//------------------------------------------------------------------------------

class HarvestDataParserAutiStaScan4 extends HarvestDataParserAutiStaScan
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 3) && (trim( $vec[3][0]) == 'Anzahl der Kinder mit');
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		// Bochum, ...
		return parent::parse( $vec, $vecCount, $nuts, $url, $echoDataErrors);
	}
} // class HarvestDataParserAutiStaScan4
$HarvestData->addParser('HarvestDataParserAutiStaScan4');

//------------------------------------------------------------------------------

function cmpHarvestDataParserZuerich( $a, $b)
{
	if( $a['year'] != $b['year']) {
		return ($a['year'] > $b['year']) ? -1 : 1;
	}
	if( $a['male'] != $b['male']) {
		return $a['male'] ? -1 : 1;
	}
	if ($a['number'] == $b['number']) {
		return 0;
	}
	return ($a['number'] > $b['number']) ? -1 : 1;
}

function cmpHarvestDataParserZuerichVec( $a, $b)
{
//	$colYear = 0;
//	$colSex = 2;
//	$colCount = 3;

	if( $a[0] != $b[0]) {
		return ($a[0] > $b[0]) ? -1 : 1;
	}
	if( $a[2] != $b[2]) {
		return ($a[2] > $b[2]) ? -1 : 1;
	}
	if ($a[3] == $b[3]) {
		return 0;
	}
	return ($a[3] > $b[3]) ? -1 : 1;
}

class HarvestDataParserZuerich extends HarvestDataParserBase
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 0) && ($vec[0][1] == '"Vorname"') && (trim( $vec[0][2]) == '"Geschlecht"') && (trim( $vec[0][3]) == '"Anzahl"');
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		// Used in Zurich
		$ret = new HarvestDataResult();

		$colName = 1;
		$colSex = 2;
		$colYear = 0;
//		$colPos = -1;
		$colCount = 3;
		$startRow = 0;

		$vecCount = count( $vec);
		if( $vecCount < 2) {
			$ret->errorMsg = 'Unknown Zurich format!';
			return $ret;
		}

		array_shift( $vec);

		usort( $vec, "cmpHarvestDataParserZuerichVec");

		$ret->data = Array();
		$ret->error = false;
		$ret->errorMsg = '';

		$lastYear = 0;
		for( $row = $startRow; $row < $vecCount; ++$row) {
			if( count( $vec[ $row]) > 1) {
				if( $lastYear != intval( $vec[ $row][ $colYear])) {
					$lastYear = intval( $vec[ $row][ $colYear]);

					if( count($ret->data) > 0) {
						$this->generateDataPos( $ret->data);
						$this->parseData( $ret->data);

						if( !$this->echoDataErrors( $ret->data, $echoDataErrors)) {
							$ret->error = true;
							$ret->errorMsg = 'Unknown names found. No files saved!';
						}

						$ret->data = Array();
					}
				}

				$name = trim( $vec[ $row][ $colName], '"* ');

				if( $name == "LETIZIA") {
					$name = 'Letizia';
				} else if( $name == "Mariam-chantel") {
					$name = 'Mariam-Chantel';
				}

				$ret->data[] = Array(
					name=> $name,
					male=> $vec[ $row][ $colSex] == '"weiblich"' ? false : true,
					year=> trim( $vec[ $row][ $colYear]),
					pos=> 0,
					number=> intVal( $vec[ $row][ $colCount]),
					error=> '',
				);
			}
		}

		if( count($ret->data) > 0) {
			$this->generateDataPos( $ret->data);
			$this->parseData( $ret->data);

			if( !$this->echoDataErrors( $ret->data, $echoDataErrors)) {
				$ret->error = true;
				$ret->errorMsg = 'Unknown names found. No files saved!';
			}

			$ret->data = Array();
		}

		if( !$ret->error) {
			$this->saveData( $ret->data, $ret->file, $ret->checksum, $ret->years, $nuts);
		}

		return $ret;
	}

	public function generateDataPos( & $data)
	{
		usort( $data, "cmpHarvestDataParserZuerich");

		$dataCount = count( $data);
		$currentPos = 0;
		$currentNumber = 0;
		$currentMale = true;
		$yearPos = 1;

		for( $row = 0; $row < $dataCount; ++$row, ++$yearPos) {
			if( $currentMale != $data[ $row][ 'male']) {
				$currentMale = $data[ $row][ 'male'];
				$yearPos = 1;
			}
			if( $currentNumber != $data[ $row][ 'number']) {
				$currentNumber = $data[ $row][ 'number'];
				$currentPos = $yearPos;
			}
			$data[ $row][ 'pos'] = $currentPos;
		}
	}
} // class HarvestDataParserZuerich
$HarvestData->addParser('HarvestDataParserZuerich');

//------------------------------------------------------------------------------

class HarvestDataParserLinz extends HarvestDataParserBase
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 0) && ($vec[0][0] == 'Rang') && ($vec[0][1] == 'Geschlecht') && (trim( $vec[0][2]) == 'Vorname');
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		// Used in Linz
		$ret = new HarvestDataResult();

		// Beliebteste Vornamen 0-4 Jährige am 01.01.$year  | /Beliebteste_Vornamen_0-4_Jaehrige_am_1_1_$year.csv
		// Beliebteste Vornamen 5-9 Jährige am 01.01.$year  | /Beliebteste_Vornamen_5-9_Jaehrige_am_1_1_$year.csv
		// Beliebteste Vornamen des Jahres $year            | /Beliebteste_Vornamen_des_Jahres_$year.csv
		// Beliebteste Vornamen aller Linzer am 01.01.$year | /Beliebteste_Vornamen_aller_Linzer_am_1_1_$year.csv

		$theYear = intval( substr( $url, strlen( $url) - 8, 4));

		if( false !== strpos( $url, 'Beliebteste_Vornamen_0-4_Jaehrige')) {
			// ignore data
			$ret->error = false;
			$ret->errorMsg = '';
			return $ret;
		}
		if( false !== strpos( $url, 'Beliebteste_Vornamen_5-9_Jaehrige')) {
			// ignore data
			$ret->error = false;
			$ret->errorMsg = '';
			return $ret;
		}
		if( false !== strpos( $url, 'Beliebteste_Vornamen_aller_Linzer')) {
			// ignore data
			$ret->error = false;
			$ret->errorMsg = '';
			return $ret;
		}

		$colName = 2;
		$colSex = 1;
//		$colYear = -1;
		$colPos = 0;
//		$colCount = -1;
		$startRow = 0;

		$vecCount = count( $vec);
		if( $vecCount < 2) {
			$ret->errorMsg = 'Unknown Linz format!';
			return $ret;
		}

		$ret->data = Array();

		$lastPos = 1;
		for( $row = $startRow + 1; $row < $vecCount; ++$row) {
			if( count( $vec[ $row]) > 1) {
				$pos = intVal( $vec[ $row][ $colPos]);
				if( 0 != $pos) {
					$lastPos = $pos;
				}

				$ret->data[] = Array(
					name=> trim( $vec[ $row][ $colName]),
					male=> $vec[ $row][ $colSex] == 'weiblich' ? false : true,
					year=> $theYear,
					pos=> $lastPos,
					number=> 0,
					error=> '',
				);
			}
		}

		$this->parseData( $ret->data);
		$this->saveData( $ret->data, $ret->file, $ret->checksum, $ret->years, $nuts);

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}
} // class HarvestDataParserLinz
$HarvestData->addParser('HarvestDataParserLinz');

//------------------------------------------------------------------------------

class HarvestDataParserVorarlberg extends HarvestDataParserBase
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 0) && ($vec[0][0] == 'Jahr') && ($vec[0][1] == 'Geschlecht') && (trim( $vec[0][2]) == 'Vorname');
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		// Used in Vorarlberg
		$ret = new HarvestDataResult();

		$colName = 2;
		$colSex = 1;
		$colYear = 0;
		$colPos = 3;
//		$colCount = -1;
		$startRow = 0;
		$yearPos = 0;
		$oldYear = 0;

		// Mädchen
		if( 2 === strpos( $vec[ 1][1], 'dchen')) {
			if( 228 == ord( $vec[ 1][1][1])) {
				convertVecVecToUTF8( $vec);
			}
		}

		$vecCount = count( $vec);
		if( count( $vec) < 2) {
			$ret->errorMsg = 'Unknown Vorarlberg format!';
			return $ret;
		}

		$ret->data = Array();

		for( $row = $startRow + 1; $row < $vecCount; ++$row, ++$yearPos) {
			if( count( $vec[ $row]) > 1) {
				if( $oldYear != intval( $vec[ $row][ $colYear])) {
					$oldYear = intval( $vec[ $row][ $colYear]);
					$yearPos = 1;
				}

				$ret->data[] = Array(
					name=> trim( $vec[ $row][ $colName], '* '),
					male=> $vec[ $row][ $colSex] == 'Knaben' ? true : false,
					year=> trim( $vec[ $row][ $colYear]),
					pos=> $yearPos,
					number=> trim( $vec[ $row][ $colPos]),
					error=> '',
				);
			}
		}

		$this->parseData( $ret->data);
		$this->saveData( $ret->data, $ret->file, $ret->checksum, $ret->years, $nuts);

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}
} // class HarvestDataParserVorarlberg
$HarvestData->addParser('HarvestDataParserVorarlberg');

//------------------------------------------------------------------------------

class HarvestDataParserEngerwitzdorf extends HarvestDataParserBase
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 0) && (substr( $vec[0][0], 0, 21) == 'GemeindeEngerwitzdorf');
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		// Used in Engerwitzdorf
		$ret = new HarvestDataResult();

		$vecCount = count( $vec);

		if( $vecCount < 12) {
			$ret->errorMsg = 'Unknown Engerwitzdorf format!';
			return $ret;
		}

		$row = 3;
		$theYear = intval( $vec[ $row][0]);
		$row += 2;
		if( $theYear != intval( $vec[ $row][0])) {
			$ret->errorMsg = 'Unknown Engerwitzdorf year format... ' . $theYear . ' != ' . $vec[ $row][0];
			return $ret;
		}

		$isMale = true;
		$row += 3;
		if( 'weibl.' == trim( $vec[ $row][0])) {
			$isMale = false;
		} else if( "m\xe4nnl." != trim( $vec[ $row][0])) {
			$ret->errorMsg = 'Unknown Engerwitzdorf sex format... ' . $vec[ $row][0];
			return $ret;
		}

		if(( '' != trim( $vec[ $row + 1][0])) || ('' != trim( $vec[ $row + 2][0]))) {
			$ret->errorMsg = 'Unknown Engerwitzdorf line feed format...';
			return $ret;
		}
		$row += 3;

		$ret->data = Array();
		$thePos = 1;

		for( ; ($row < $vecCount) && (0 < strlen( trim( $vec[ $row][0]))); ++$row) {
			$theName = trim( $vec[ $row][0]);
			if( is_numeric( $theName)) {
				continue;
			}

			$nameExists = false;
			for( $i = 0; $i < count( $ret->data); ++$i) {
				if( $ret->data[ $i]['name'] == $theName) {
					$nameExists = true;
				}
			}

			if( $nameExists) {
				continue;
			}

			$ret->data[] = Array(
				name=> $theName,
				male=> $isMale,
				year=> $theYear,
				pos=> $thePos,
				number=> 0,
				error=> '',
			);
			++$thePos;
		}

		$this->parseData( $ret->data);
		$this->saveData( $ret->data, $ret->file, $ret->checksum, $ret->years, $nuts);

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}
} // class HarvestDataParserEngerwitzdorf
$HarvestData->addParser('HarvestDataParserEngerwitzdorf');

//------------------------------------------------------------------------------

class HarvestDataParserSalzburg extends HarvestDataParserBase
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 0) && /*($vec[0][0] == 'Rang') &&*/ ($vec[0][1] == 'NUTS') && (trim( $vec[0][2]) == 'Geschlecht') && (trim( $vec[0][3]) == 'Vorname') && (trim( $vec[0][4]) == 'Jahr');
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		// Used in Salzburg
		$ret = new HarvestDataResult();

		$colName = 3;
		$colSex = 2;
		$colYear = 4;
		$colPos = 0;
//		$colCount = -1;
//		$colNuts = 1;
		$currentPos = 0;

		$vecCount = count( $vec);
		if( $vecCount < 2) {
			$ret->errorMsg = 'Unknown Salzburg format!';
			return $ret;
		}

		$ret->data = Array();

		for( $row = 1; $row < $vecCount; ++$row) {
			if( count( $vec[ $row]) > 1) {
				if(( '' == $vec[ $row][ $colPos]) && ('' == $vec[ $row][ $colSex])) {
					continue;
				}
//				if( '' == $vec[ $row][ $colName]) {
//					continue;
//				}
				if( strlen( trim( $vec[ $row][ $colPos])) > 0) {
					$currentPos = $vec[ $row][ $colPos];
				}

				$ret->data[] = Array(
					name=> trim( $vec[ $row][ $colName]),
					male=> $vec[ $row][ $colSex] == 'weiblich' ? false : true,
					year=> trim( $vec[ $row][ $colYear]),
//					year=> '2013',
					pos=> $currentPos,
					number=> 0,
					error=> '',
				);
			}
		}

		$this->parseData( $ret->data);
		$this->saveData( $ret->data, $ret->file, $ret->checksum, $ret->years, $nuts);

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}
} // class HarvestDataParserSalzburg
$HarvestData->addParser('HarvestDataParserSalzburg');

//------------------------------------------------------------------------------

class HarvestDataParserCKANDataset extends HarvestDataParserBase
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 0) && (substr( $vec[0][0], 0, 14) == '<!DOCTYPE html');
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		global $MetadataVec;
		global $HarvestMetadata;
		global $dataHarvestMetadata;

		// Used in Cologne, Bonn
		$ret = new HarvestDataResult();
		$ret->errorMsg = 'Unknown CKAN dataset format!';

		$vecCount = count( $vec);
		for( $row = 0; $row < $vecCount; ++$row) {
			if( false !== strpos( $vec[$row][0], 'class="download"')) {
				$posLicUrl = strpos( $vec[$row][0], 'href="') + strlen( 'href="');
				$strLicUrl = substr( $vec[$row][0], $posLicUrl, strpos( $vec[$row][0], '"', $posLicUrl) - $posLicUrl);

				for( $i = 0; $i < count( $MetadataVec); ++$i) {
					if( $nuts == $MetadataVec[$i]['nuts']) {
						$harvest = & $dataHarvestMetadata[ $MetadataVec[$i]['meta']];

						for( $idx = 0; $idx < count( $harvest['url']); ++$idx) {
							$download = $harvest['download'][$idx];
							if( $url == $download) {
//								$url = $harvest['url'][$idx];
//								$url = substr( $url, 0, strpos( $url, '/', strpos( $url, '//') + 2));
//								$url .= $strLicUrl;
								$url = $strLicUrl;

								$ret->errorMsg = 'Updated metadata. Please reload this site!';

								$harvest['url'][$idx] = $url;
								$harvest['download'][$idx] = '';
							}
						}

						$HarvestMetadata->save();
					}
				}
			}
		}

		return $ret;
	}
} // class HarvestDataParserCKANDataset
$HarvestData->addParser('HarvestDataParserCKANDataset');

//------------------------------------------------------------------------------

class HarvestDataParserMunich extends HarvestDataParserBase
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 0) && ($vec[0][0] == '"Vorname"') && (trim( $vec[0][1]) == '"Anzahl"');
	}

	public function parse( $vec, $vecCount, $nuts, $url, $echoDataErrors)
	{
		// Used in Munich
		$ret = new HarvestDataResult();

		// Vornamen 2013 männlich        | /vornamenmaennlich2013.csv
		// Vornamen 2013 weiblich        | /vornamenweiblich2013.csv

		// Beliebteste Vornamen 0-4 Jährige am 01.01.$year  | /Beliebteste_Vornamen_0-4_Jaehrige_am_1_1_$year.csv
		// Beliebteste Vornamen 5-9 Jährige am 01.01.$year  | /Beliebteste_Vornamen_5-9_Jaehrige_am_1_1_$year.csv
		// Beliebteste Vornamen des Jahres $year            | /Beliebteste_Vornamen_des_Jahres_$year.csv
		// Beliebteste Vornamen aller Linzer am 01.01.$year | /Beliebteste_Vornamen_aller_Linzer_am_1_1_$year.csv

		$theYear = intval( substr( $url, strlen( $url) - 8, 4));
		$theMale = true;
		if( false !== strpos( $url, 'weiblich')) {
			$theMale = false;
		}

		$colName = 0;
//		$colSex = -1;
//		$colYear = -1;
//		$colPos = -1;
		$colCount = 1;
		$startRow = 0;

		$vecCount = count( $vec);
		if( $vecCount < 2) {
			$ret->errorMsg = 'Unknown Munich format!';
			return $ret;
		}

		$ret->data = Array();

		for( $row = $startRow + 1; $row < $vecCount; ++$row) {
			if( count( $vec[ $row]) > 1) {
				$name = trim( $vec[ $row][ $colName], '"* ');

				if( $name == "Summe") {
					continue;
				}

				$ret->data[] = Array(
					name=> $name,
					male=> $theMale,
					year=> $theYear,
					pos=> 0,
					number=> intVal( $vec[ $row][ $colCount]),
					error=> '',
				);
			}
		}

		$this->generateDataPos( $ret->data);

		$this->parseData( $ret->data);
		$this->saveData( $ret->data, $ret->file, $ret->checksum, $ret->years, $nuts);

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}

	public function generateDataPos( & $data)
	{
		usort( $data, "cmpHarvestDataParserZuerich");

		$dataCount = count( $data);
		$currentPos = 0;
		$currentNumber = 0;
		$currentMale = true;
		$yearPos = 1;

		for( $row = 0; $row < $dataCount; ++$row, ++$yearPos) {
			if( $currentMale != $data[ $row][ 'male']) {
				$currentMale = $data[ $row][ 'male'];
				$yearPos = 1;
			}
			if( $currentNumber != $data[ $row][ 'number']) {
				$currentNumber = $data[ $row][ 'number'];
				$currentPos = $yearPos;
			}
			$data[ $row][ 'pos'] = $currentPos;
		}
	}
} // class HarvestDataParserMunich
$HarvestData->addParser('HarvestDataParserMunich');

//------------------------------------------------------------------------------

?>
