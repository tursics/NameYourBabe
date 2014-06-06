<?php

//--------------------------------------------------------------------------------------------------

function parseMetadataCopyright( $index, $title, $url, $citation)
{
	global $gSource;

	if(( 'Creative Commons Namensnennung 3.0 Österreich' == $title) && ('https://creativecommons.org/licenses/by/3.0/at/deed.de' == $url)) {
		$gSource[$index]['autoLicense'] = 'CC BY 3.0 AT';
	} else if(( 'Creative Commons Namensnennung (CC-BY)' == $title) && ('http://www.opendefinition.org/licenses/cc-by' == $url)) {
		$gSource[$index]['autoLicense'] = 'CC BY';
	} else if(( 'Creative Commons Namensnennung' == $title) && ('http://creativecommons.org/licenses/by/3.0/de/' == $url)) {
		$gSource[$index]['autoLicense'] = 'CC BY 3.0 DE';
	} else if(( 'Datenlizenz Deutschland - Namensnennung - Version 1.0' == $title) && ('http://www.daten-deutschland.de/bibliothek/Datenlizenz_Deutschland/dl-de-by-1.0' == $url)) {
		$gSource[$index]['autoLicense'] = 'DL DE BY 1.0';
	}

	if( 0 === strpos( $citation, 'Datenquelle:')) {
		$gSource[$index]['autoCitation'] = $citation;
	}
}

//--------------------------------------------------------------------------------------------------

function parseMetadataOGDAustria11( $index, $json)
{
	global $gSource;

	$metaMod = strtotime( $json['metadata_modified']);
	$lastMod = strtotime( $gSource[$index]['autoModified']);
	$diffMod = intval(( $metaMod - $lastMod) /60 /60 /24);

	if( 0 >= $diffMod) {
		$gSource[$index]['autoUpdate'] = 0;
		return;
	}

	$gSource[$index]['autoUpdate'] = intval(( strtotime( 'now') - $metaMod) /60 /60 /24);
	if( 0 == $gSource[$index]['autoUpdate']) {
		$gSource[$index]['autoUpdate'] = 1;
	}

	$gSource[$index]['autoUrl'] = Array();
	$gSource[$index]['autoName'] = Array();
	for( $i = 0; $i < count( $json['resources']); ++$i) {
		$gSource[$index]['autoUrl'][] = $json['resources'][$i]['url'];
		$gSource[$index]['autoName'][] = $json['resources'][$i]['name'];
	}
	parseMetadataCopyright( $index, $json['license_title'], $json['license_url'], $json['extras']['license_citation']);
 }

//--------------------------------------------------------------------------------------------------

function parseMetadataOGDGermany( $index, $json)
{
	global $gSource;

	$metaMod = strtotime( $json['metadata_modified']);
	$lastMod = strtotime( $gSource[$index]['autoModified']);
	$diffMod = intval(( $metaMod - $lastMod) /60 /60 /24);

	if( 0 >= $diffMod) {
		$gSource[$index]['autoUpdate'] = 0;
		return;
	}

	$gSource[$index]['autoUpdate'] = intval(( strtotime( 'now') - $metaMod) /60 /60 /24);
	if( 0 == $gSource[$index]['autoUpdate']) {
		$gSource[$index]['autoUpdate'] = 1;
	}

	$gSource[$index]['autoUrl'] = Array();
	$gSource[$index]['autoName'] = Array();
	for( $i = 0; $i < count( $json['resources']); ++$i) {
		$gSource[$index]['autoUrl'][] = $json['resources'][$i]['url'];
		$gSource[$index]['autoName'][] = $json['resources'][$i]['name'];
	}
	parseMetadataCopyright( $index, $json['license_title'], $json['license_url'], '');
}

//--------------------------------------------------------------------------------------------------

function parseMetadataOGDD10( $index, $json)
{
	global $gSource;

	$metaMod = strtotime( $json['metadata_modified']);
	$lastMod = strtotime( $gSource[$index]['autoModified']);
	$diffMod = intval(( $metaMod - $lastMod) /60 /60 /24);

	if( 0 >= $diffMod) {
		$gSource[$index]['autoUpdate'] = 0;
		return;
	}

	$gSource[$index]['autoUpdate'] = intval(( strtotime( 'now') - $metaMod) /60 /60 /24);
	if( 0 == $gSource[$index]['autoUpdate']) {
		$gSource[$index]['autoUpdate'] = 1;
	}

	$gSource[$index]['autoUrl'] = Array();
	$gSource[$index]['autoName'] = Array();
	for( $i = 0; $i < count( $json['resources']); ++$i) {
		$gSource[$index]['autoUrl'][] = $json['resources'][$i]['url'];
		$gSource[$index]['autoName'][] = $json['resources'][$i]['description'];
	}
	parseMetadataCopyright( $index, $json['license_title'], $json['license_url'], '');
}

//--------------------------------------------------------------------------------------------------

function parseWebsiteMoers( $index, $contents)
{
	global $gSource;

	$posName = strpos( $contents, '<title>') + strlen( '<title>');
	$posModified = strpos( $contents, 'Veröffentlicht am');
	$posLicence = strpos( $contents, 'Lizenz');
	$posUrl = strpos( $contents, '>Download<');

	if( false === $posModified) {
		return;
	}
	if( false === $posLicence) {
		return;
	}
	if( false === $posUrl) {
		return;
	}
	if( false === $posName) {
		return;
	}

	$strName = substr( $contents, $posName, strpos( $contents, '</title>', $posName) - $posName);

	$posModified = strpos( $contents, '>', strpos( $contents, '<td', $posModified)) + 1;
	$strModified = substr( $contents, $posModified, strpos( $contents, '</td>', $posModified) - $posModified);

	$posLicence = strpos( $contents, '>', strpos( $contents, '<td', $posLicence)) + 1;
	$strLicence = substr( $contents, $posLicence, strpos( $contents, '</td>', $posLicence) - $posLicence);

	$posLicUrl = strpos( $strLicence, 'href="') + strlen( 'href="');
	$strLicUrl = substr( $strLicence, $posLicUrl, strpos( $strLicence, '"', $posLicUrl) - $posLicUrl);

	$posLicName = strpos( $strLicence, '>', $posLicUrl) + strlen( '>');
	$strLicName = substr( $strLicence, $posLicName, strpos( $strLicence, '</a', $posLicName) - $posLicName);

	$posUrl = strpos( $contents, 'href="', $posUrl) + strlen( 'href="');
	$strUrl = substr( $contents, $posUrl, strpos( $contents, '"', $posUrl) - $posUrl);
	$strUrl = 'http://www.moers.de' . $strUrl;

	$metaMod = strtotime( $strModified);
	$lastMod = strtotime( $gSource[$index]['autoModified']);
	$diffMod = intval(( $metaMod - $lastMod) /60 /60 /24);

	if( 0 >= $diffMod) {
		$gSource[$index]['autoUpdate'] = 0;
		return;
	}

	$gSource[$index]['autoUpdate'] = intval(( strtotime( 'now') - $metaMod) /60 /60 /24);
	if( 0 == $gSource[$index]['autoUpdate']) {
		$gSource[$index]['autoUpdate'] = 1;
	}

	$gSource[$index]['autoUrl'] = Array();
	$gSource[$index]['autoName'] = Array();
	$gSource[$index]['autoUrl'][] = $strUrl;
	$gSource[$index]['autoName'][] = $strName;
	parseMetadataCopyright( $index, $strLicName, $strLicUrl, '');
}

//--------------------------------------------------------------------------------------------------

function parseWebsiteBerlin( $index, $contents)
{
	global $gSource;

	$posContent = strpos( $contents, 'id="content"');
	$posSidebar = strpos( $contents, 'id="sidebar_right"');
	$strContent = substr( $contents, $posContent, $posSidebar - $posContent);

	$posName = strpos( $strContent, '>', strpos( $strContent, '<h1')) + 1;

	if( false === $posName) {
		return;
	}

	$strName = substr( $strContent, $posName, strpos( $strContent, '</h1>', $posName) - $posName);

	$posLicUrl = strpos( $strContent, 'href="', strpos( $strContent, 'title="License text') - 100) + strlen( 'href="');
	$strLicUrl = substr( $strContent, $posLicUrl, strpos( $strContent, '"', $posLicUrl) - $posLicUrl);

	$posLicName = strpos( $strContent, '>', $posLicUrl) + strlen( '>');
	$strLicName = trim( substr( $strContent, $posLicName, strpos( $strContent, '</a', $posLicName) - $posLicName));

	$posModified = strpos( $strContent, 'Aktualisiert:');
	if( false === $posModified) {
		$posModified = strpos( $strContent, 'Veröffentlicht:');
	}
	$posModified = strpos( $strContent, '>', strpos( $strContent, '<span', $posModified)) + 1;
	$strModified = substr( $strContent, $posModified, strpos( $strContent, '</span>', $posModified) - $posModified);

	$metaMod = strtotime( $strModified);
	$lastMod = strtotime( $gSource[$index]['autoModified']);
	$diffMod = intval(( $metaMod - $lastMod) /60 /60 /24);

	if( 0 >= $diffMod) {
		$gSource[$index]['autoUpdate'] = 0;
		return;
	}

	$posUrl = 0;
	$gSource[$index]['autoUrl'] = Array();
	$gSource[$index]['autoName'] = Array();

	do {
		$posUrl = strpos( $strContent, 'node-ckan-ressource', $posUrl);
		if( false === $posUrl) {
			break;
		}
		$posUrl = strpos( $strContent, 'href="', $posUrl) + strlen( 'href="');
		$strUrl = substr( $strContent, $posUrl, strpos( $strContent, '"', $posUrl) - $posUrl);

		$gSource[$index]['autoUrl'][] = $strUrl;
		$gSource[$index]['autoName'][] = '';
	} while( true);

	$gSource[$index]['autoUpdate'] = intval(( strtotime( 'now') - $metaMod) /60 /60 /24);
	if( 0 == $gSource[$index]['autoUpdate']) {
		$gSource[$index]['autoUpdate'] = 1;
	}

	parseMetadataCopyright( $index, $strLicName, $strLicUrl, '');
}

//--------------------------------------------------------------------------------------------------

function parseWebsiteUlm( $index, $contents)
{
	global $gSource;

	$posTable = strpos( $contents, '<thead>');

	if( false === $posTable) {
		return;
	}

	$posTable = strpos( $contents, '<tbody>', $posTable);
	$strTable = substr( $contents, $posTable, strpos( $contents, '</tbody>', $posTable) - $posTable);

	$posUrl = 0;

	do {
		$posUrl = strpos( $strTable, 'href="', $posUrl);
		if( false === $posUrl) {
			break;
		}
		$posUrl = $posUrl + strlen( 'href="');
		$strUrl = substr( $strTable, $posUrl, strpos( $strTable, '"', $posUrl) - $posUrl);

		$posName = strpos( $strTable, '>', $posUrl);
		if( false === $posName) {
			break;
		}
		++$posName;
		$strName = substr( $strTable, $posName, strpos( $strTable, '</a>', $posName) - $posName);

		$gSource[$index]['autoUrl'][] = $strUrl;
		$gSource[$index]['autoName'][] = $strName;
	} while( true);
}

//--------------------------------------------------------------------------------------------------

function parseISO19139( $index, $contents)
{
	global $gSource;

	$posName = strpos( $contents, '<gmd:citation>');
	$posModified = strpos( $contents, '<gmd:dateStamp>');
	$posUrl = strpos( $contents, '<gmd:citation>');

	if( false === $posModified) {
		return;
	}
	if( false === $posUrl) {
		return;
	}
	if( false === $posName) {
		return;
	}

//	$posName = strpos( $contents, '<gmd:CI_Citation>', $posName);
//	$posName = strpos( $contents, '<gmd:title>', $posName);
//	$posName = strpos( $contents, '<gco:CharacterString>', $posName) + strlen( '<gco:CharacterString>');
//	$strName = substr( $contents, $posName, strpos( $contents, '</gco:CharacterString>', $posName) - $posName);

	$posModified = strpos( $contents, '>', strpos( $contents, '<gco:DateTime', $posModified)) + 1;
	$strModified = substr( $contents, $posModified, strpos( $contents, '</gco:DateTime>', $posModified) - $posModified);

	$posUrl = strpos( $contents, '<gmd:CI_Citation>', $posUrl);
	$posUrl = strpos( $contents, '<gmd:identifier>', $posUrl);
	$posUrl = strpos( $contents, '<gmd:MD_Identifier>', $posUrl);
	$posUrl = strpos( $contents, '<gmd:code>', $posUrl);
	$posUrl = strpos( $contents, '<gco:CharacterString>', $posUrl) + strlen( '<gco:CharacterString>');
	$strUrl = substr( $contents, $posUrl, strpos( $contents, '</gco:CharacterString>', $posUrl) - $posUrl);

	$metaMod = strtotime( $strModified);
	$lastMod = strtotime( $gSource[$index]['autoModified']);
	$diffMod = intval(( $metaMod - $lastMod) /60 /60 /24);

	if( 0 >= $diffMod) {
		$gSource[$index]['autoUpdate'] = 0;
		return;
	}

	$gSource[$index]['autoUpdate'] = intval(( strtotime( 'now') - $metaMod) /60 /60 /24);
	if( 0 == $gSource[$index]['autoUpdate']) {
		$gSource[$index]['autoUpdate'] = 1;
	}

	$gSource[$index]['autoUrl'] = Array();
	$gSource[$index]['autoName'] = Array();
//	$gSource[$index]['autoUrl'][] = $strUrl;
//	$gSource[$index]['autoName'][] = $strName;

	if( false !== strpos( $strUrl, 'ulm.de')) {
		$urlContents = file_get_contents( $strUrl);
		parseWebsiteUlm( $index, $urlContents);
	} else {
		echo( 'ERROR');
	}
}

//--------------------------------------------------------------------------------------------------

function parseWebsiteZuerich( $index, $contents)
{
	global $gSource;

	// can't get infos

	$strName = 'Vornamen von Neugeborenen mit Wohnsitz in der Stadt Zürich';
	$strModified = '02.07.2013';
//	$strUrl = 'http://data.stadt-zuerich.ch/ogd.BIqTNQe.link';
	$strUrl = 'http://www.tursics.de/file/vornamen_1993-2012.csv';

	$metaMod = strtotime( $strModified);
	$lastMod = strtotime( $gSource[$index]['autoModified']);
	$diffMod = intval(( $metaMod - $lastMod) /60 /60 /24);

	if( 0 >= $diffMod) {
		$gSource[$index]['autoUpdate'] = 0;
		return;
	}

	$gSource[$index]['autoUpdate'] = intval(( strtotime( 'now') - $metaMod) /60 /60 /24);
	if( 0 == $gSource[$index]['autoUpdate']) {
		$gSource[$index]['autoUpdate'] = 1;
	}

	$gSource[$index]['autoUrl'] = Array();
	$gSource[$index]['autoName'] = Array();
	$gSource[$index]['autoUrl'][] = $strUrl;
	$gSource[$index]['autoName'][] = $strName;
}

//--------------------------------------------------------------------------------------------------

function metadataShowPageUpdate()
{
	global $gSource;

	$txt = '';
	$txt .= '<h1>Update Metadata</h1>';
	$txt .= '<br>';
	$txt .= 'Start parsing';
	$txt .= '<br>';
	echo( $txt);

	for( $i = 0; $i < count( $gSource); ++$i) {
//	for( $i = 48; $i < count( $gSource); ++$i) {
		$txt = '';
		$txt .= $gSource[$i]['name'] . ': ';

		if( !isset( $gSource[$i]['meta'])) {
			$txt = 'x';
			echo( $txt);
			continue;
		}

		$filename = $gSource[$i]['meta'];
		if( '/' ==  substr( $filename, 0, 1)) {
			$filename = dirname(__FILE__) . $filename;
		}

//		if( !file_exists( $filename)) {
//			$txt = '?';
//			echo( $txt);
//			continue;
//		}

		$contents = file_get_contents( $filename);
//		$contents = utf8_encode( $contents);
		$json = json_decode( $contents, true);

//		$txt .= var_dump( json_decode( $json));
		if( $json['extras']['schema_name'] == 'OGD Austria Metadaten 1.1') {
			parseMetadataOGDAustria11( $i, $json);
			if( $gSource[$i]['autoUpdate'] > 0) {
				$txt .= '<span style="background-color:orange;padding:2px;">Outdated since ' . $gSource[$i]['autoUpdate'] . ' days!</span>';
				$txt .= '<br>';
			} else {
				$txt = '.';
			}
		} else if( $json['extras']['schema_name'] == 'OGD Austria Metadata 2.1') {
			parseMetadataOGDAustria11( $i, $json);
			if( $gSource[$i]['autoUpdate'] > 0) {
				$txt .= '<span style="background-color:orange;padding:2px;">Outdated since ' . $gSource[$i]['autoUpdate'] . ' days!</span>';
				$txt .= '<br>';
			} else {
				$txt = '.';
			}
		} else if( $json['extras']['schema_name'] == 'NOE Metadata 1.0') {
			parseMetadataOGDAustria11( $i, $json);
			if( $gSource[$i]['autoUpdate'] > 0) {
				$txt .= '<span style="background-color:orange;padding:2px;">Outdated since ' . $gSource[$i]['autoUpdate'] . ' days!</span>';
				$txt .= '<br>';
			} else {
				$txt = '.';
			}
		} else if( $json['extras']['sector'] == 'oeffentlich') {
			parseMetadataOGDGermany( $i, $json);
			if( $gSource[$i]['autoUpdate'] > 0) {
				$txt .= '<span style="background-color:orange;padding:2px;">Outdated since ' . $gSource[$i]['autoUpdate'] . ' days!</span>';
				$txt .= '<br>';
			} else {
				$txt = '.';
			}
		} else if( $json['extras']['ogdd_version'] == 'v1.0') {
			parseMetadataOGDD10( $i, $json);
			if( $gSource[$i]['autoUpdate'] > 0) {
				$txt .= '<span style="background-color:orange;padding:2px;">Outdated since ' . $gSource[$i]['autoUpdate'] . ' days!</span>';
				$txt .= '<br>';
			} else {
				$txt = '.';
			}
		} else if( 0 == count( $json)) {
			if( false !== strpos( $contents, 'moers.de')) {
				parseWebsiteMoers( $i, $contents);
				if( $gSource[$i]['autoUpdate'] > 0) {
					$txt .= '<span style="background-color:orange;padding:2px;">Outdated since ' . $gSource[$i]['autoUpdate'] . ' days!</span>';
					$txt .= '<br>';
				} else {
					$txt = '.';
				}
			} else if( false !== strpos( $contents, 'daten.berlin.de/sites')) {
				parseWebsiteBerlin( $i, $contents);
				if( $gSource[$i]['autoUpdate'] > 0) {
					$txt .= '<span style="background-color:orange;padding:2px;">Outdated since ' . $gSource[$i]['autoUpdate'] . ' days!</span>';
					$txt .= '<br>';
				} else {
					$txt = '.';
				}
			} else if( false !== strpos( $gSource[$i]['meta'], 'zuerich.ch')) {
				parseWebsiteZuerich( $i, $contents);
				if( $gSource[$i]['autoUpdate'] > 0) {
					$txt .= '<span style="background-color:orange;padding:2px;">Outdated since ' . $gSource[$i]['autoUpdate'] . ' days!</span>';
					$txt .= '<br>';
				} else {
					$txt = '?';
				}
			} else if( false !== strpos( $contents, 'ISO 19139')) {
				parseISO19139( $i, $contents);
				if( $gSource[$i]['autoUpdate'] > 0) {
					$txt .= '<span style="background-color:orange;padding:2px;">Outdated since ' . $gSource[$i]['autoUpdate'] . ' days!</span>';
					$txt .= '<br>';
				} else {
					$txt = '.';
				}
			} else {
				$txt = '-';
				$gSource[$i]['autoUpdate'] = -1;
			}
		} else if( $json['extras']['schema_name'] == '') {
			$txt = '-';
			$gSource[$i]['autoUpdate'] = -1;
		} else {
			$txt .= 'Unknown metadata format (' . $json['extras']['schema_name'] . ') found';
			$txt .= '<br>';
			$gSource[$i]['autoUpdate'] = -1;
		}

		echo( $txt);
	}

	gSourceToFile();

	$txt = '<br><br>' . count( $gSource). ' meta data items collected';
	$txt .= '<br><br>';
	$txt .= '<a href="/do=browse&what=sources">Back to list</a> - ';
	$txt .= '<a href="/do=update&what=sourcedata">Update dirty data</a><br>';
	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

?>
