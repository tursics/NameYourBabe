<?php

//------------------------------------------------------------------------------

class HarvestDataParserBase
{
	public function accept( $vec, $vecCount)
	{
		return false;
	}

	public function parse( $vec, $vecCount)
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
			$item['error'] = $this->parseSourcedataVecItem( $item, $isBoy);
		}
	}

	public function parseSourcedataVecItem( $item, $isBoy)
	{
		$ret = '';
		$found = false;
		$good = false;
//		$name = ucwords( strtolower( $item['name']));
//		$nameUFT8 = mb_strtoupper( $item['name'], 'UTF-8');
		$name = $item['name'];
		$nameUFT8 = $name;

		if( $isBoy) {
			$found = ($gBoys[$nameUFT8] !== NULL);
		} else {
			$found = ($gGirls[$nameUFT8] !== NULL);
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

//		$strRef = intval( $sourceID).'-'.intval( $urlID).'#'.intval( $item['pos']).','.intval( $item['year']);

/*	if( $found) {
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
	}*/

		if( $good) {
			return '';
		}

		if( !$found) {
			if( $isBoy) {
				$gBoys[$nameUFT8] = Array( name=> $name, id=> count($gBoys), ref=> Array($strRef) );
			} else {
				$gGirls[$nameUFT8] = Array( name=> $name, id=> count($gGirls), ref=> Array($strRef) );
			}

/*			$ret .= '$nameUFT8 '.$nameUFT8;
			for( $i = 0; $i < strlen( $nameUFT8); ++$i) {
				$ret .= ' \\x' . dechex( ord( $nameUFT8[ $i]));
			}
			$ret .= '<br>';*/
//			$ret .= '<br>Name in database: '.mb_strtoupper( gGirls[101]['name'], 'UTF-8');
//			for( $i = 0; $i < strlen( mb_strtoupper( gGirls[101]['name'], 'UTF-8')); ++$i) {
//				$ret .= ' \\x' . dechex( ord( mb_strtoupper( gGirls[101]['name'], 'UTF-8')[ $i]));
//			}

			$ret .= 'New name <span style="color:';
			if( $isBoy) {
				$ret .= 'RoyalBlue';
			} else {
				$ret .= 'MediumVioletRed';
			}
			return $ret . '">' . $name . '</span>';
		}

		return '&nbsp;&nbsp;' . $name . ' | ' . $item['sex'] . ' | #' . $item['pos'] . ' | ' . $item['year'] . ' | ' . $ret;
	}
} // class HarvestDataParserBase

//------------------------------------------------------------------------------

class HarvestDataResult
{
	public $error = true;
	public $errorMsg = 'not parsed';
	public $data = Array();
} // class HarvestDataResult

//------------------------------------------------------------------------------

class HarvestData
{
	public $parserVec = Array();

	public function addParser( $name)
	{
		$this->parserVec[] = $name;
	}

	public function parse( $vec, $vecCount)
	{
		for( $i = 0; $i < count( $this->parserVec); ++$i) {
			$parser = new $this->parserVec[$i]();
			if( $parser->accept( $vec, $vecCount)) {
				return $parser->parse( $vec, $vecCount);
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

	public function parse( $vec, $vecCount)
	{
		// Lower Austria, Wien, ...
		$ret = new HarvestDataResult();

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
					error=> '',
				);
			}
		}

		$this->parseData( $ret->data);

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

?>
