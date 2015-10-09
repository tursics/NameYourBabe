<?php

//------------------------------------------------------------------------------

include_once( "data/MetadataVec.php");

if( file_exists( dirname(__FILE__)."/data/harvest/metadata.php")) {
	include_once( "data/harvest/metadata.php");
} else {
	$dataHarvestMetadata = Array();
}

//------------------------------------------------------------------------------

class HarvestMetadataResult
{
	public $error = true;
	public $errorMsg = 'not parsed';
	public $modified = 0;
	public $modDays = 0;
	public $vecURL = null;
	public $vecName = null;
	public $license = '';
	public $citation = '';
} // class HarvestMetadataResult

//------------------------------------------------------------------------------

class HarvestMetadataParserBase
{
	public function accept( $contents, $json)
	{
		return false;
	}

	public function parse( $contents, $json)
	{
		$ret = new HarvestMetadataResult();
		return $ret;
	}

	public function parseCopyright( & $ret, $title, $url, $citation)
	{
		if(( 'Creative Commons Namensnennung 3.0 Österreich' == $title) && ('https://creativecommons.org/licenses/by/3.0/at/deed.de' == $url)) {
			$ret->license = 'CC BY 3.0 AT';
		} else if(( 'Creative Commons Namensnennung (CC-BY)' == $title) && ('http://www.opendefinition.org/licenses/cc-by' == $url)) {
			$ret->license = 'CC BY';
		} else if(( 'Creative Commons Namensnennung' == $title) && ('http://creativecommons.org/licenses/by/3.0/de/' == $url)) {
			$ret->license = 'CC BY 3.0 DE';
		} else if(( 'Creative Commons CCZero' == $title) && ('http://www.opendefinition.org/licenses/cc-zero' == $url)) {
			$ret->license = 'CC 0';
		} else if(( 'Datenlizenz Deutschland - Namensnennung - Version 1.0' == $title) && ('http://www.daten-deutschland.de/bibliothek/Datenlizenz_Deutschland/dl-de-by-1.0' == $url)) {
			$ret->license = 'DL DE BY 1.0';
		} else if( 'public' == $title) {
			$ret->license = 'public';
		} else {
			$ret->license = 'unknown';
		}

		if( 0 === strpos( $citation, 'Datenquelle:')) {
			$ret->citation = $citation;
		} else if(( 'public' == $ret->license) && (count( $citation) > 0)) {
			$ret->citation = $citation;
		} else {
			$ret->citation = '';
		}
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
} // class HarvestMetadataParserBase

//------------------------------------------------------------------------------

class HarvestMetadata
{
	public $parserVec = Array();

	public function addParser( $name)
	{
		$this->parserVec[] = $name;
	}

	public function parse( $contents, $json)
	{
		for( $i = 0; $i < count( $this->parserVec); ++$i) {
			$parser = new $this->parserVec[$i]();
			if( $parser->accept( $contents, $json)) {
				return $parser->parse( $contents, $json);
			}
		}

		$ret = new HarvestMetadataResult();
		$ret->error = true;
		$ret->errorMsg = 'Unknown metadata format (' . $json['extras']['schema_name'] . ') found';

		if( 0 == count( $json)) {
			$ret->errorMsg = '-';
		}

		return $ret;
	}

	public function save()
	{
		global $dataHarvestMetadata;

		$contents = '<?php'."\n".'$dataHarvestMetadata=';
		$contents .= var_export( $dataHarvestMetadata, true);
		$contents .= ';'."\n".'?>'."\n";

		$file = dirname(__FILE__).'/data';
		if( !file_exists( $file)) {
			mkdir( $file, 0777);
		}
		$file .= '/harvest';
		if( !file_exists( $file)) {
			mkdir( $file, 0777);
		}
		$file .= '/metadata.php';
		file_put_contents( $file, $contents);

		$file = dirname(__FILE__).'/backup';
		if( !file_exists( $file)) {
			mkdir( $file, 0777);
		}
		$file .= '/harvest';
		if( !file_exists( $file)) {
			mkdir( $file, 0777);
		}
		$file .= '/metadata-' . date( 'Y-W') . '.php';
		file_put_contents( $file, $contents);
	}
} // class HarvestMetadata
$HarvestMetadata = new HarvestMetadata();

//------------------------------------------------------------------------------

class HarvestMetadataParserOGDAustria11 extends HarvestMetadataParserBase
{
	public function accept( $contents, $json)
	{
		return false;
//		return $json['extras']['schema_name'] == 'OGD Austria Metadaten 1.1';
	}

	public function parse( $contents, $json)
	{
		$ret = new HarvestMetadataResult();
		$ret->modified = $this->strtotimeLoc( $json['metadata_modified']);
		$ret->modDays = intval(( strtotime( 'now') - $ret->modified) /60 /60 /24);
		$ret->vecURL = Array();
		$ret->vecName = Array();

		for( $i = 0; $i < count( $json['resources']); ++$i) {
			$ret->vecURL[] = $json['resources'][$i]['url'];
			$ret->vecName[] = $json['resources'][$i]['name'];
		}

		$this->parseCopyright( $ret, $json['license_title'], $json['license_url'], $json['extras']['license_citation']);

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}
} // class HarvestMetadataParserOGDAustria11
$HarvestMetadata->addParser('HarvestMetadataParserOGDAustria11');

//------------------------------------------------------------------------------

class HarvestMetadataParserOGDAustria21 extends HarvestMetadataParserOGDAustria11
{
	public function accept( $contents, $json)
	{
		return $json['extras']['schema_name'] == 'OGD Austria Metadata 2.1';
	}

	public function parse( $contents, $json)
	{
		return parent::parse( $contents, $json);
	}
} // class HarvestMetadataParserOGDAustria21
$HarvestMetadata->addParser('HarvestMetadataParserOGDAustria21');

//------------------------------------------------------------------------------

class HarvestMetadataParserOGDAustria3 extends HarvestMetadataParserOGDAustria11
{
	public function accept( $contents, $json)
	{
		return $json['success'] == 'true';
	}

	public function parse( $contents, $json)
	{
		if( 1 == count( $json['result'])) {
			return parent::parse( $contents, $json['result'][0]);
		}
		return parent::parse( $contents, $json['result']);
	}
} // class HarvestMetadataParserOGDAustria3
$HarvestMetadata->addParser('HarvestMetadataParserOGDAustria3');

//------------------------------------------------------------------------------

class HarvestMetadataParserOGDGermany extends HarvestMetadataParserBase
{
	public function accept( $contents, $json)
	{
		return $json['extras']['sector'] == 'oeffentlich';
	}

	public function parse( $contents, $json)
	{
		$ret = new HarvestMetadataResult();
		$ret->modified = strtotime( $json['metadata_modified']);
		$ret->modDays = intval(( strtotime( 'now') - $ret->modified) /60 /60 /24);
		$ret->vecURL = Array();
		$ret->vecName = Array();

		for( $i = 0; $i < count( $json['resources']); ++$i) {
			$ret->vecURL[] = $json['resources'][$i]['url'];
			$ret->vecName[] = $json['resources'][$i]['name'];
		}

		$this->parseCopyright( $ret, $json['license_title'], $json['license_url'], '');

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}
} // class HarvestMetadataParserOGDGermany
$HarvestMetadata->addParser('HarvestMetadataParserOGDGermany');

//------------------------------------------------------------------------------

class HarvestMetadataParserOGDD1 extends HarvestMetadataParserBase
{
	public function accept( $contents, $json)
	{
		return $json['extras']['ogdd_version'] == 'v1.0.0';
	}

	public function parse( $contents, $json)
	{
		$ret = new HarvestMetadataResult();
		$ret->modified = strtotime( $json['metadata_modified']);
		$ret->modDays = intval(( strtotime( 'now') - $ret->modified) /60 /60 /24);
		$ret->vecURL = Array();
		$ret->vecName = Array();

		for( $i = 0; $i < count( $json['resources']); ++$i) {
			$ret->vecURL[] = $json['resources'][$i]['url'];
			$ret->vecName[] = $json['resources'][$i]['description'];
		}

		$this->parseCopyright( $ret, $json['license_title'], $json['license_url'], '');

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}
} // class HarvestMetadataParserOGDD1
$HarvestMetadata->addParser('HarvestMetadataParserOGDD1');

//------------------------------------------------------------------------------

class HarvestMetadataParserMoers extends HarvestMetadataParserBase
{
	public function accept( $contents, $json)
	{
		return (0 == count( $json)) && (false !== strpos( $contents, 'moers.de'));
	}

	public function parse( $contents, $json)
	{
		$ret = new HarvestMetadataResult();
		$ret->errorMsg = 'Could not parse metadata of Moers';

		$posName = strpos( $contents, '<title>') + strlen( '<title>');
		$posModified = strpos( $contents, 'Veröffentlicht am');
		$posLicence = strpos( $contents, 'Lizenz');
		$posUrl = strpos( $contents, '>Daten<');

		if( false === $posModified) {
			$ret->errorMsg .= ' (pos modified)';
			return $ret;
		}
		if( false === $posLicence) {
			$ret->errorMsg .= ' (pos licence)';
			return $ret;
		}
		if( false === $posUrl) {
			$ret->errorMsg .= ' (pos url)';
			return $ret;
		}
		if( false === $posName) {
			$ret->errorMsg .= ' (pos name)';
			return $ret;
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

		$ret->modified = strtotime( $strModified);
		$ret->modDays = intval(( strtotime( 'now') - $ret->modified) /60 /60 /24);
		$ret->vecURL = Array();
		$ret->vecName = Array();

		$ret->vecURL[] = $strUrl;
		$ret->vecName[] = $strName;

		$this->parseCopyright( $ret, $strLicName, $strLicUrl, '');

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}
} // class HarvestMetadataParserMoers
$HarvestMetadata->addParser('HarvestMetadataParserMoers');

//------------------------------------------------------------------------------

class HarvestMetadataParserBochum extends HarvestMetadataParserBase
{
	public function accept( $contents, $json)
	{
		return (0 == count( $json)) && (false !== strpos( $contents, 'Stadt Bochum'));
	}

	public function parse( $contents, $json)
	{
		$ret = new HarvestMetadataResult();
		$ret->errorMsg = 'Could not parse metadata of Bochum';

		$posStart = strpos( $contents, '<h2 id');
		$posName = strpos( $contents, 'Vornamen', $posStart);
		$posModified = strpos( $contents, 'zuletzt geändert', $posStart);
		$posLicence = strpos( $contents, 'Lizenz', $posStart);
		$posUrl = strpos( $contents, '<table', $posStart);
		$posUrlEnd = strpos( $contents, '</table>', $posStart);

		if( false === $posModified) {
			$ret->errorMsg .= ' (pos modified)';
			return $ret;
		}
		if( false === $posLicence) {
			$ret->errorMsg .= ' (pos licence)';
			return $ret;
		}
		if( false === $posUrl) {
			$ret->errorMsg .= ' (pos url)';
			return $ret;
		}
		if( false === $posName) {
			$ret->errorMsg .= ' (pos name)';
			return $ret;
		}

		$strName = substr( $contents, $posName, strpos( $contents, '</h2>', $posName) - $posName);

		$posModified = strpos( $contents, '>', strpos( $contents, '<td', $posModified)) + 1;
		$strModified = substr( $contents, $posModified, strpos( $contents, '<br/>', $posModified) - $posModified);

		$posLicence = strpos( $contents, '>', strpos( $contents, '<a', $posLicence)) + 1;
		$strLicence = substr( $contents, $posLicence, strpos( $contents, '</a>', $posLicence) - $posLicence);

		$posLicUrl = strpos( $strLicence, 'href="') + strlen( 'href="');
		$strLicUrl = substr( $strLicence, $posLicUrl, strpos( $strLicence, '"', $posLicUrl) - $posLicUrl);

		$posLicName = strpos( $strLicence, '>', $posLicUrl) + strlen( '>');
		$strLicName = substr( $strLicence, $posLicName, strpos( $strLicence, '</a', $posLicName) - $posLicName);

		$ret->vecURL = Array();
		$ret->vecName = Array();

		do {
			$posUrl = strpos( $contents, '<tr>', $posUrl);
			if( false === $posUrl) {
				break;
			}
			if( $posUrl > $posUrlEnd) {
				break;
			}

			$posUrl = strpos( $contents, '<strong>', $posUrl) + strlen( '<strong>');
			$strName = substr( $contents, $posUrl, strpos( $contents, '</strong>', $posUrl) - $posUrl);
			$strName = strip_tags( $strName);

			$posUrl = strpos( $contents, 'href="', $posUrl) + strlen( 'href="');
			$strUrl = substr( $contents, $posUrl, strpos( $contents, '"', $posUrl) - $posUrl);
			$strUrl = 'http://www.bochum.de' . $strUrl;

			$ret->vecURL[] = $strUrl;
			$ret->vecName[] = $strName;
		} while( true);

		$ret->modified = $this->strtotimeLoc( $strModified);
		$ret->modDays = intval(( strtotime( 'now') - $ret->modified) /60 /60 /24);

		$this->parseCopyright( $ret, $strLicName, $strLicUrl, '');

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}
} // class HarvestMetadataParserBochum
$HarvestMetadata->addParser('HarvestMetadataParserBochum');

//------------------------------------------------------------------------------
/*
class HarvestMetadataParserZuerich extends HarvestMetadataParserBase
{
	public function accept( $contents, $json)
	{
		return (0 == count( $json)) && ('zuerich.ch' == $contents);
	}

	public function parse( $contents, $json)
	{
		$ret = new HarvestMetadataResult();

		// can't get infos

		$strName = 'Vornamen von Neugeborenen mit Wohnsitz in der Stadt Zürich';
		$strModified = '04.06.2014';

		// failed to open stream: HTTP request failed! HTTP/1.1 403 Forbidden
		// $strUrl = 'http://data.stadt-zuerich.ch/ogd.BIqTNQe.link';
		$strUrl = 'http://www.tursics.de/file/vornamen_1993-2013.csv';

		$ret->modified = strtotime( $strModified);
		$ret->modDays = intval(( strtotime( 'now') - $ret->modified) /60 /60 /24);
		$ret->vecURL = Array();
		$ret->vecName = Array();

		$ret->vecURL[] = $strUrl;
		$ret->vecName[] = $strName;

//		$this->parseCopyright( $ret, $json['license_title'], $json['license_url'], $json['extras']['license_citation']);

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}
} // class HarvestMetadataParserZuerich
$HarvestMetadata->addParser('HarvestMetadataParserZuerich');
*/
//------------------------------------------------------------------------------
/*
class HarvestMetadataParserBerlin extends HarvestMetadataParserBase
{
	public function accept( $contents, $json)
	{
		return (0 == count( $json)) && (false !== strpos( $contents, 'daten.berlin.de/sites'));
	}

	public function parse( $contents, $json)
	{
		$ret = new HarvestMetadataResult();
		$ret->errorMsg = 'Could not parse metadata of Berlin';

		$posContent = strpos( $contents, 'id="content"');
		$posSidebar = strpos( $contents, 'id="sidebar_right"');
		$strContent = substr( $contents, $posContent, $posSidebar - $posContent);

		$posName = strpos( $strContent, '>', strpos( $strContent, '<h1')) + 1;

		if( false === $posName) {
			return $ret;
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

		$ret->modified = strtotime( $strModified);
		$ret->modDays = intval(( strtotime( 'now') - $ret->modified) /60 /60 /24);
		$ret->vecURL = Array();
		$ret->vecName = Array();
		$posUrl = 0;

		do {
			$posUrl = strpos( $strContent, 'node-ckan-ressource', $posUrl);
			if( false === $posUrl) {
				break;
			}
			$posUrl = strpos( $strContent, 'href="', $posUrl) + strlen( 'href="');
			$strUrl = substr( $strContent, $posUrl, strpos( $strContent, '"', $posUrl) - $posUrl);

			$ret->vecURL[] = $strUrl;
			$ret->vecName[] = '';
		} while( true);

		$this->parseCopyright( $ret, $strLicName, $strLicUrl, '');

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}
} // class HarvestMetadataParserBerlin
$HarvestMetadata->addParser('HarvestMetadataParserBerlin');
*/
//------------------------------------------------------------------------------

class HarvestMetadataParserISO19139 extends HarvestMetadataParserBase
{
	public function accept( $contents, $json)
	{
		return (0 == count( $json)) && (false !== strpos( $contents, 'ISO 19139'));
	}

	public function parse( $contents, $json)
	{
		$ret = new HarvestMetadataResult();
		$ret->errorMsg = 'Could not parse metadata of ISO 19139';

		$posName = strpos( $contents, '<gmd:citation>');
		$posModified = strpos( $contents, '<gmd:dateStamp>');
		$posUrl = strpos( $contents, '<gmd:citation>');

		if( false === $posModified) {
			return $ret;
		}
		if( false === $posUrl) {
			return $ret;
		}
		if( false === $posName) {
			return $ret;
		}

//		$posName = strpos( $contents, '<gmd:CI_Citation>', $posName);
//		$posName = strpos( $contents, '<gmd:title>', $posName);
//		$posName = strpos( $contents, '<gco:CharacterString>', $posName) + strlen( '<gco:CharacterString>');
//		$strName = substr( $contents, $posName, strpos( $contents, '</gco:CharacterString>', $posName) - $posName);

		$posModified = strpos( $contents, '>', strpos( $contents, '<gco:DateTime', $posModified)) + 1;
		$strModified = substr( $contents, $posModified, strpos( $contents, '</gco:DateTime>', $posModified) - $posModified);

		$posUrl = strpos( $contents, '<gmd:CI_Citation>', $posUrl);
		$posUrl = strpos( $contents, '<gmd:identifier>', $posUrl);
		$posUrl = strpos( $contents, '<gmd:MD_Identifier>', $posUrl);
		$posUrl = strpos( $contents, '<gmd:code>', $posUrl);
		$posUrl = strpos( $contents, '<gco:CharacterString>', $posUrl) + strlen( '<gco:CharacterString>');
		$strUrl = substr( $contents, $posUrl, strpos( $contents, '</gco:CharacterString>', $posUrl) - $posUrl);

		$ret->modified = strtotime( $strModified);
		$ret->modDays = intval(( strtotime( 'now') - $ret->modified) /60 /60 /24);
		$ret->vecURL = Array();
		$ret->vecName = Array();
//		$ret->vecURL[] = $strUrl;
//		$ret->vecName[] = $strName;

		if( false !== strpos( $strUrl, 'ulm.de')) {
			$ret->errorMsg .= ' (Ulm)';
			$urlContents = file_get_contents( $strUrl);
			$this->parseWebsiteUlm( $ret, $urlContents);
		} else {
			$ret->errorMsg .= ' (' . $strUrl . ')';
			return $ret;
		}

//		$this->parseCopyright( $ret, $json['license_title'], $json['license_url'], '');

		return $ret;
	}

	public function parseWebsiteUlm( & $ret, $contents)
	{
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

			$ret->vecURL[] = $strUrl;
			$ret->vecName[] = $strName;
		} while( true);

		$ret->error = false;
		$ret->errorMsg = '';
	}
} // class HarvestMetadataParserISO19139
$HarvestMetadata->addParser('HarvestMetadataParserISO19139');

//------------------------------------------------------------------------------

class HarvestMetadataParserMunich extends HarvestMetadataParserBase
{
	public function accept( $contents, $json)
	{
		if( is_array( $json['extras'])) {
			return array_key_exists( 'Aktualisierungszyklus', $json['extras']);
		}
		return false;
	}

	public function parse( $contents, $json)
	{
		$ret = new HarvestMetadataResult();
		$ret->modified = strtotime( $json['metadata_modified']);
		$ret->modDays = intval(( strtotime( 'now') - $ret->modified) /60 /60 /24);
		$ret->vecURL = Array();
		$ret->vecName = Array();

		for( $i = 0; $i < count( $json['resources']); ++$i) {
			$ret->vecURL[] = $json['resources'][$i]['url'];
			$ret->vecName[] = $json['resources'][$i]['name'];
		}

		$this->parseCopyright( $ret, $json['license_title'], $json['license_url'], '');

		$ret->error = false;
		$ret->errorMsg = '';

		return $ret;
	}
} // class HarvestMetadataParserMunich
$HarvestMetadata->addParser('HarvestMetadataParserMunich');

//------------------------------------------------------------------------------

?>
