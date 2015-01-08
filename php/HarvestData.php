<?php

//------------------------------------------------------------------------------

class HarvestDataParserBase
{
	public function accept( $vec, $vecCount)
	{
		return false;
	}

	public function parse( $vec, $vecCount, $nuts, $url)
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

	public function saveData( & $data, & $fileVec, & $checksumVec, & $yearVec, $nuts)
	{
		$contents = Array();
		$dataCount = count( $data);
		for( $it = 0; $it < $dataCount; ++$it) {
			$item = $data[ $it];
			$dest = $item['year'].($item['male']?'_m':'_f');
			$contents[$dest][] = $nuts.';'.$item['name'].';'.$item['number'].';'.$item['pos'].';'.($item['male']?'male':'female').';'.$item['year'];

			if( 0 < strlen( $item['error'])) {
				return;
			}
		}

		$fileVec = Array();
		$checksumVec = Array();
		$yearVec = Array();

		foreach( $contents as $key => $value) {
			$path = 'data/harvest/'.substr($nuts, 0, 2).'/'.$nuts.'/'.$nuts.'_'.$key.'.csv';
			$file = dirname(__FILE__) . '/' . $path;
			$out = "NUTS;GIVEN_NAME;NUMBER;RANKING;SEX;YEAR\n";
			$out .= implode( "\n", $value);
			file_put_contents( $file, $out);

			$fileVec[] = $path;
			$checksumVec[] = md5( $out);
			if( !in_array( substr( $key, 0, 4), $yearVec)) {
				$yearVec[] = substr( $key, 0, 4);
			}
		}

		// check md5!
	}

	public function parseNames( $item, $isBoy)
	{
		global $HarvestNames;

		$ret = '';
		$found = false;
		$name = $item['name'];
		$nameUFT8 = $name;

		$HarvestNames->load();

		if( $isBoy) {
			$found = in_array( $nameUFT8, $HarvestNames->male);
		} else {
			$found = in_array( $nameUFT8, $HarvestNames->female);
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
			} else {
				$HarvestNames->female[] = $name;
			}

			$ret .= 'New name <span style="color:';
			if( $isBoy) {
				$ret .= 'RoyalBlue';
			} else {
				$ret .= 'MediumVioletRed';
			}
			return $ret . '">' . $name . '</span>';
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

	public function parse( $vec, $vecCount, $nuts, $url)
	{
		for( $i = 0; $i < count( $this->parserVec); ++$i) {
			$parser = new $this->parserVec[$i]();
			if( $parser->accept( $vec, $vecCount)) {
				return $parser->parse( $vec, $vecCount, $nuts, $url);
			}
		}

		$ret = new HarvestDataResult();
		$ret->error = true;
		$ret->errorMsg = 'Unknown data format found';

		return $ret;
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

	public function parse( $vec, $vecCount, $nuts, $url)
	{
		// Lower Austria, Wien, ...
		$ret = new HarvestDataResult();

		$colName = -1;
		$colSex = -1;
		$colYear = -1;
		$colPos = -1;
		$colCount = -1;
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
				}

				if( $name == 'Rene') {
					$name = 'René';
				}

				$ret->data[] = Array(
					name=> $name,
					male=> $vec[ $row][ $colSex] == '1' ? true : false,
					year=> $vec[ $row][ $colYear],
					pos=> $colPos == -1 ? $posVec[ $vec[ $row][ $colSex]][ $vec[ $row][ $colYear]] : $vec[ $row][ $colPos],
					number=> $vec[ $row][ $colCount],
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

class HarvestDataParserAutiSta extends HarvestDataParserBase
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 0) && /*($vec[0][0] == 'vorname') &&*/ ($vec[0][1] == 'anzahl') && (trim( $vec[0][2]) == 'geschlecht');
	}

	public function parse( $vec, $vecCount, $nuts, $url)
	{
		// AutiSta used in Berlin, Bonn, Chemnitz, Hamburg, Moers, Ulm ...
		$ret = new HarvestDataResult();

		$colName = 0;
		$colSex = 2;
//		$colYear = -1;
		$colPos = -1;
		$colCount = 1;
		$startRow = 0;

		$theYear = 2012; // berlin missing year number in 2012
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
					year=> $theYear,
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

class HarvestDataParserZuerich extends HarvestDataParserBase
{
	public function accept( $vec, $vecCount)
	{
		return ($vecCount > 0) && ($vec[0][1] == '"Vorname"') && (trim( $vec[0][2]) == '"Geschlecht"') && (trim( $vec[0][3]) == '"Anzahl"');
	}

	public function parse( $vec, $vecCount, $nuts, $url)
	{
		// AutiSta used in Berlin, Bonn, Chemnitz, Hamburg, Moers, Ulm ...
		$ret = new HarvestDataResult();

		$colName = 1;
		$colSex = 2;
		$colYear = 0;
//		$colPos = -1;
		$colCount = 3;
		$startRow = 0;

		$vecCount = count( $vec);
		if( $vecCount < 2) {
			$ret->errorMsg = 'Unknown Zürich format!';
			return $ret;
		}

		$ret->data = Array();

		for( $row = $startRow + 1; $row < $vecCount; ++$row) {
			if( count( $vec[ $row]) > 1) {
				$name = trim( $vec[ $row][ $colName], '"* ');

				if( $name == "LETIZIA") {
					$name = 'Letizia';
				} else if( $name == "Mariam-chantel") {
					$name = 'Mariam-Chantel';
				}

				$ret->data[] = Array(
					name=> $name,
					male=> $vec[ $row][ $colSex] == '"weiblich"' ? false : true,
					year=> $vec[ $row][ $colYear],
					pos=> 0,
					number=> intVal( $vec[ $row][ $colCount]),
					error=> '',
				);
			}
		}

		$this->generateDataPos( $ret->data);

		$this->parseData( $ret->data);
//		$this->saveData( $ret->data, $ret->file, $ret->checksum, $ret->years, $nuts);

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
} // class HarvestDataParserZuerich
$HarvestData->addParser('HarvestDataParserZuerich');

//------------------------------------------------------------------------------

?>
