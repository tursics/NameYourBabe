<?php

//--------------------------------------------------------------------------------------------------

function encodeURIComponent( $str)
{
	$revert = array( '%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
	return strtr( rawurlencode( $str), $revert);
}

//--------------------------------------------------------------------------------------------------

function exportShowPageSourceJSCmp( $a, $b)
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

function exportShowPageDataNameGetHitCountCmp( $a, $b)
{
	// "26-2011-M-AT12" and "35-2011-M-AT130"
	$left = explode( '-', $a);
	$right = explode( '-', $b);

	if( $left[0] == $right[0]) {
		if( $left[1] == $right[1]) {
			if( $left[2] == $right[2]) {
				if( $left[3] == $right[3]) {
					return 0;
				}

				return ($left[3] < $right[3]) ? -1 : 1;
			}

			return ($left[2] < $right[2]) ? -1 : 1;
		}

		return (intVal($left[1]) > intVal($right[1])) ? -1 : 1;
	}

	return (intVal($left[0]) < intVal($right[0])) ? -1 : 1;
}

function exportShowPageDataNameGetAltNameCmp( $a, $b)
{
	if( $a == $b) {
		return 0;
	}

	return ($a < $b) ? -1 : 1;
}

//--------------------------------------------------------------------------------------------------

function exportShowPageDataSourceJSNUTS( & $nuts, $nutsName, $source)
{
	$txt = '';

	if( !isset( $nuts[ $nutsName])) {
		$nuts[ $nutsName] = true;

		$license = '';
		if( isset( $source['autoLicense']) && ('' != $source['autoLicense'])) {
			$license = $source['autoLicense'];
		} else if( isset( $source['manLicense'])) {
			$license = $source['manLicense'];
		}

		$citation = '';
		if( isset( $source['autoCitation']) && ('' != $source['autoCitation'])) {
			$citation = $source['autoCitation'];
		} else if( isset( $source['manCitation'])) {
			$citation = $source['manCitation'];
		}

		$txt .= "{";
		$txt .= "\t\"nuts\":\"".$nutsName."\",\n";
		$txt .= "\t\"name\":{";
		$names = nutsGetName( $nutsName);
		foreach( $names as $key => $value) {
			$txt .= "\"" . $key . "\":\"" . $value . "\",";
		}
		$txt = rtrim( $txt, ",");
		$txt .= "},\n";
		$txt .= "\t\"license\":\"".$license."\",\n";
		$txt .= "\t\"citation\":\"".$citation."\",\n";
		$txt = rtrim( $txt, ",\n");
		$txt .= "\n},\n";
	}

	return $txt;
}

function exportShowPageDataSourceJS()
{
	global $gSource;

	$txt = '';
	$txt .= '<h1>Export files</h1>';
	echo( $txt);

	usort( $gSource, "exportShowPageSourceJSCmp");

	$data = '';
	$data .= "gDataSource=[\n";

	$nuts = Array();
	for( $i = 0; $i < count( $gSource); ++$i) {
		if( !isset( $gSource[$i]['manNUTS'])) {
			continue;
		}
		if( 0 == count( $gSource[$i]['manNUTS'])) {
			continue;
		}

		for( $j = 0; $j < count( $gSource[$i]['manNUTS']); ++$j) {
			$name = $gSource[$i]['manNUTS'][$j];

			for( $n = 1; $n < strlen( $name); ++$n) {
				if( nutsExists( substr( $name, 0, $n))) {
					$data .= exportShowPageDataSourceJSNUTS( $nuts, substr( $name, 0, $n), array());
				}
			}

			$data .= exportShowPageDataSourceJSNUTS( $nuts, $name, $gSource[$i]);
		}
	}
	$data = rtrim( $data, ",\n");
	$data .= "\n];\n";

	$fileName = 'dataSource.js';

	$txt = '';
	$txt .= 'Download file <a href="data:text/plain;charset=utf-8,' . encodeURIComponent( $data) . '" download="' . $fileName . '" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-a">' . $fileName . '</a><br>';

	$txt .= '<br>';
	$txt .= '<hr>';
	$txt .= '<br>';

	$txt .= '<a href="do=">Back to main</a><br>';
	$txt .= '<a href="do=export&what=dataName.js">Next</a><br>';

	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

function exportShowPageDataNameCollectHitCount( $value, $gender, & $ret, $top, $yearFrom, $yearTo)
{
	global $gSource;

	foreach( $value['ref'] as $refvalue) {
		$posSource = strpos( $refvalue, '-');
		$posNum = strpos( $refvalue, '#', $posSource);
		$posYear = strpos( $refvalue, ',', $posNum);

		$strSource = substr( $refvalue, 0, $posSource);
		$strUrl = substr( $refvalue, $posSource + 1, $posNum - $posSource - 1);
		$strNum = substr( $refvalue, $posNum + 1, $posYear - $posNum - 1);
		$strYear = substr( $refvalue, $posYear + 1);

		if(( $strNum <= $top) && ($yearFrom <= $strYear) && ($strYear <= $yearTo)) {
			foreach( $gSource as $source) {
				if( $strSource == $source['id']) {
					$ret[] = $strNum.'-'.$strYear.'-'.$gender.'-'.$source['manNUTS'][$strUrl];
				}
			}
		}
	}
}

function exportShowPageDataNameGetHitCount( $hits)
{
	usort( $hits, "exportShowPageDataNameGetHitCountCmp");

	$txt = '';
	foreach( $hits as $value) {
		$txt .= '"'.$value.'",';
	}
	$txt = rtrim( $txt, ",");

	return $txt;
}

function exportShowPageDataNameGetAltName( $name)
{
	global $gBoys;
	global $gGirls;

	$names = Array();

	if( isset( $gBoys[ $name])) {
		$value = $gBoys[ $name];
		if( isset( $value['altM']) && (0 < count( $value['altM']))) {
			foreach( $value['altM'] as $altvalue) {
				$names[ $altvalue] = $altvalue;
			}
		}
		if( isset( $value['altF']) && (0 < count( $value['altF']))) {
			foreach( $value['altF'] as $altvalue) {
				$names[ $altvalue] = $altvalue;
			}
		}
	}

	if( isset( $gGirls[ $name])) {
		$value = $gGirls[ $name];
		if( isset( $value['altM']) && (0 < count( $value['altM']))) {
			foreach( $value['altM'] as $altvalue) {
				$names[ $altvalue] = $altvalue;
			}
		}
		if( isset( $value['altF']) && (0 < count( $value['altF']))) {
			foreach( $value['altF'] as $altvalue) {
				$names[ $altvalue] = $altvalue;
			}
		}
	}

	usort( $names, "exportShowPageDataNameGetAltNameCmp");

	$txt = '';
	foreach( $names as $val) {
		if( $val != $name) {
			$txt .= $val.',';
		}
	}
	$txt = rtrim( $txt, ",");

	return $txt;
}

function exportShowPageDataNameCheckSimilar( $name, $names, & $namesSimilar)
{
	global $gBoys;
	global $gGirls;

	if( isset( $gBoys[ $name])) {
		$value = $gBoys[ $name];
		if( isset( $value['altM']) && (0 < count( $value['altM']))) {
			foreach( $value['altM'] as $altvalue) {
				if( !isset( $names[ $altvalue]) && !isset( $namesSimilar[ $altvalue])) {
					$namesSimilar[$altvalue] = Array( name=> $altvalue, gender=> 'm' );
				}
			}
		}
		if( isset( $value['altF']) && (0 < count( $value['altF']))) {
			foreach( $value['altF'] as $altvalue) {
				if( !isset( $names[ $altvalue]) && !isset( $namesSimilar[ $altvalue])) {
					$namesSimilar[$altvalue] = Array( name=> $altvalue, gender=> 'f' );
				}
			}
		}
	}

	if( isset( $gGirls[ $name])) {
		$value = $gGirls[ $name];
		if( isset( $value['altM']) && (0 < count( $value['altM']))) {
			foreach( $value['altM'] as $altvalue) {
				if( !isset( $names[ $altvalue]) && !isset( $namesSimilar[ $altvalue])) {
					$namesSimilar[$altvalue] = Array( name=> $altvalue, gender=> 'm' );
				}
			}
		}
		if( isset( $value['altF']) && (0 < count( $value['altF']))) {
			foreach( $value['altF'] as $altvalue) {
				if( !isset( $names[ $altvalue]) && !isset( $namesSimilar[ $altvalue])) {
					$namesSimilar[$altvalue] = Array( name=> $altvalue, gender=> 'f' );
				}
			}
		}
	}
}

function exportShowPageDataNameGetData( $name, $top, $yearFrom, $yearTo)
{
	global $gSource;
	global $gBoys;
	global $gGirls;

	$txt = '';
	$txt .= "{";
	$txt .= "\t\"name\":\"".$name."\",\n";

	$isMale = isset( $gBoys[ $name]);
	$isFemale = isset( $gGirls[ $name]);
	if( $isMale && $isFemale) {
		$isMale = exportShowPageDataNameIsInList( $gBoys[ $name], /*$top*/100, 1000, 3000);
		$isFemale = exportShowPageDataNameIsInList( $gGirls[ $name], /*$top*/100, 1000, 3000);
	}

	if( $isMale && $isFemale) {
		$txt .= "\t\"gender\":\"b\",\n";
		if( isset( $gBoys[ $name]['text'])) {
			if( isset( $gBoys[ $name]['text']) && isset( $gGirls[ $name]['text'])) {
				echo( 'Alert!!! '.$name.' has two [text]<br>');
			}
			$txt .= "\t\"text\":\"".str_replace('•','\"',str_replace('"','•',$gBoys[ $name]['text']))."\",\n";
		} else if( isset( $gGirls[ $name]['text'])) {
			$txt .= "\t\"text\":\"".str_replace('•','\"',str_replace('"','•',$gGirls[ $name]['text']))."\",\n";
		} else {
			$txt .= "\t\"text\":\"\",\n";
		}
		if( isset( $gBoys[ $name]['url'])) {
			if( isset( $gBoys[ $name]['url']) && isset( $gGirls[ $name]['url'])) {
				echo( 'Alert!!! '.$name.' has two [url]<br>');
			}
			$txt .= "\t\"url\":\"".$gBoys[ $name]['url']."\",\n";
		} else if( isset( $gGirls[ $name]['url'])) {
			$txt .= "\t\"url\":\"".$gGirls[ $name]['url']."\",\n";
		} else {
			$txt .= "\t\"url\":\"\",\n";
		}
	} else if( $isMale) {
		$txt .= "\t\"gender\":\"m\",\n";
		if( isset( $gBoys[ $name]['text'])) {
			$txt .= "\t\"text\":\"".str_replace('•','\"',str_replace('"','•',$gBoys[ $name]['text']))."\",\n";
		} else {
			$txt .= "\t\"text\":\"\",\n";
		}
		if( isset( $gBoys[ $name]['url'])) {
			$txt .= "\t\"url\":\"".$gBoys[ $name]['url']."\",\n";
		} else {
			$txt .= "\t\"url\":\"\",\n";
		}
	} else if( $isFemale) {
		$txt .= "\t\"gender\":\"f\",\n";
		if( isset( $gGirls[ $name]['text'])) {
			$txt .= "\t\"text\":\"".str_replace('•','\"',str_replace('"','•',$gGirls[ $name]['text']))."\",\n";
		} else {
			$txt .= "\t\"text\":\"\",\n";
		}
		if( isset( $gGirls[ $name]['url'])) {
			$txt .= "\t\"url\":\"".$gGirls[ $name]['url']."\",\n";
		} else {
			$txt .= "\t\"url\":\"\",\n";
		}
	}

	$txt .= "\t\"similar\":\"".exportShowPageDataNameGetAltName( $name)."\",\n";

	$hitnames = Array();
	if( isset( $gBoys[ $name])) {
		exportShowPageDataNameCollectHitCount( $gBoys[ $name], 'M', $hitnames, $top, $yearFrom, $yearTo);
	}
	if( isset( $gGirls[ $name])) {
		exportShowPageDataNameCollectHitCount( $gGirls[ $name], 'F', $hitnames, $top, $yearFrom, $yearTo);
	}
	$txt .= "\t\"charts\":[".exportShowPageDataNameGetHitCount( $hitnames)."],\n";

	$txt = rtrim( $txt, ",\n");
	$txt .= "\n},\n";

	return $txt;
}

function exportShowPageDataNameGetSimilarData( $value)
{
	$txt = '';
	$txt .= "{";
	$txt .= "\t\"name\":\"".$value['name']."\",\n";
	$txt .= "\t\"gender\":\"".$value['gender']."\",\n";
	$txt .= "\t\"text\":\"\",\n";
	$txt .= "\t\"url\":\"\",\n";
	$txt .= "\t\"similar\":\"\",\n";
	$txt .= "\t\"charts\":[],\n";
	$txt = rtrim( $txt, ",\n");
	$txt .= "\n},\n";

	return $txt;
}

function exportShowPageDataNameIsInList( $value, $top, $yearFrom, $yearTo)
{
	global $gSource;

	foreach( $value['ref'] as $refvalue) {
		$posSource = strpos( $refvalue, '-');
		$posNum = strpos( $refvalue, '#', $posSource);
		$posYear = strpos( $refvalue, ',', $posNum);

		$strNum = substr( $refvalue, $posNum + 1, $posYear - $posNum - 1);
		$strYear = substr( $refvalue, $posYear + 1);

		if(( $strNum <= $top) && ($yearFrom <= $strYear) && ($strYear <= $yearTo)) {
			return true;
		}
	}

	return false;
}

function exportShowPageDataNameJS()
{
	global $gBoys;
	global $gGirls;

	$top = 100;
	$yearFrom = 2004;
	$yearTo = 2013;

	$txt = '';
	$txt .= '<h1>Export files</h1>';
	$txt .= 'Time period from '.$yearFrom.' to '.$yearTo.' ('.($yearTo-$yearFrom+1).' years)<br>';
	$txt .= 'Top '.$top.' names<br>';
	$txt .= '<br>';
	echo( $txt);

	$names = Array();
	$namesSimilar = Array();
	foreach( $gBoys as $value) {
		if( exportShowPageDataNameIsInList( $value, $top, $yearFrom, $yearTo)) {
			$names[ $value['name']] = $value['name'];
		}
	}
	foreach( $gGirls as $value) {
		if( exportShowPageDataNameIsInList( $value, $top, $yearFrom, $yearTo)) {
			$names[ $value['name']] = $value['name'];
		}
	}
	foreach( $names as $value) {
		exportShowPageDataNameCheckSimilar( $value, $names, $namesSimilar);
	}

	$data = '';
	$data .= "gDataName=[\n";

	foreach( $names as $value) {
		$data .= exportShowPageDataNameGetData( $value, $top, $yearFrom, $yearTo);
	}
	foreach( $namesSimilar as $value) {
		$data .= exportShowPageDataNameGetSimilarData( $value);
	}

	$data = rtrim( $data, ",\n");
	$data .= "\n];\n";

	$fileName = 'dataName.js';

	$txt = '';
	$txt .= count( $names).' names and additional '.count( $namesSimilar).' similar names<br>';
	$txt .= '<br>';
	$txt .= 'Download file <a href="data:text/plain;charset=utf-8,' . encodeURIComponent( $data) . '" download="' . $fileName . '" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-a">' . $fileName . '</a><br>';

	$txt .= '<br>';
	$txt .= '<hr>';
	$txt .= '<br>';

	$txt .= '<a href="do=">Back to main</a><br>';
	$txt .= '<br>';
	$txt .= '<a href="do=export&what=dataSource.js">Back</a><br>';
	$txt .= '<a href="do=export&what=dataFoo.js">Next</a><br>';

	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

?>
