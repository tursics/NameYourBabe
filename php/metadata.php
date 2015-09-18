<?php

//--------------------------------------------------------------------------------------------------

function metadataShowPageUpdate()
{
	global $HarvestMetadata;
	global $MetadataVec;
	global $dataHarvestMetadata;

	$txt = '';
	$txt .= '<div class="log">Update metadata<br>===============<br><br>';

	$txt .= 'NUTS region&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Update&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Name<br>';
	$txt .= '---------------- ----------- ----------------------------------------<br>';
	echo( $txt);

	usort( $MetadataVec, "sortMetadataVecByNUTS");

	for( $i = 0; $i < count( $MetadataVec); ++$i) {
		$txt = '';

		$harvest = $dataHarvestMetadata[ $MetadataVec[$i]['meta']];
		if( 0 == count( $harvest)) {
			$harvest = Array(
				name => Array(),
				url => Array(),
				license => '',
				citation => '',
				modified => '2000-01-01',
				update => -1
			);
		}

		$txt .= $MetadataVec[$i]['nuts'];
		for( $j = strlen($MetadataVec[$i]['nuts']); $j < 17; ++$j) $txt .= '&nbsp;';

		$dataData = 'ERROR';
		$name = $MetadataVec[$i]['name'];

		if( !isset( $MetadataVec[$i]['meta'])) {
			$dataData = 'no metadata';

			$txt .= $dataData;
			for( $j = ('&' == substr($dataData,0,1) ? 1 : strlen($dataData)); $j < 12; ++$j) $txt .= '&nbsp;';

			$txt .= $name.'<br>';
			echo( $txt);
			continue;
		}

		$filename = $MetadataVec[$i]['meta'];
		if( '/' ==  substr( $filename, 0, 1)) {
			$filename = dirname(__FILE__) . '/data' . $filename;
		}

//		if( !file_exists( $filename)) {
//			$dataData = 'no file';
//
//			$txt .= $dataData;
//			for( $j = ('&' == substr($dataData,0,1) ? 1 : strlen($dataData)); $j < 12; ++$j) $txt .= '&nbsp;';
//
//			$txt .= $name.'<br>';
//			echo( $txt);
//			continue;
//		}

		if( false !== strpos( $MetadataVec[$i]['meta'], 'zuerich.ch')) {
			$contents = 'zuerich.ch';
		} else {
			$contents = file_get_contents( $filename);
//			$contents = utf8_encode( $contents);
		}
		$json = json_decode( $contents, true);
//		$txt .= var_dump( json_decode( $json));

		$result = $HarvestMetadata->parse( $contents, $json);

		if( $result->error) {
			if( '-' == $result->errorMsg) {
				$dataData = 'no data';
			} else {
				$name .= '<span style="color:DarkOrange;padding-left:1em;">Error: ' . $result->errorMsg . '</span>';
			}
			$harvest['update'] = -1;
		} else {
			$lastMod = strtotime( $harvest['modified']);
			$diffMod = intval(( $result->modified - $lastMod) /60 /60 /24);

			if( 0 >= $diffMod) {
				$dataData = '&#10003;'.' Last mod: '.$lastMod;
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
			}
		}

		$dataHarvestMetadata[ $MetadataVec[$i]['meta']] = $harvest;

		$txt .= $dataData;
		for( $j = ('&' == substr($dataData,0,1) ? 1 : strlen($dataData)); $j < 12; ++$j) $txt .= '&nbsp;';

		$txt .= $name.'<br>';
		echo( $txt);
	}

	$txt = '';
	$txt .= '---------------- ----------- ----------------------------------------<br>';
	$txt .= '<br>';
	echo( $txt);

	$HarvestMetadata->save();

	$txt = count( $MetadataVec). ' meta data items collected<br>';

	$txt .= '<br>';
	$txt .= '[<a href="do=browse&what=sources">Show source list</a>]<br>';
	$txt .= '</div>';
	$txt .= '<br>';
	$txt .= '<hr>';
	$txt .= '<br>';

	$txt .= '<a href="do=update&what=sourcedata">Update dirty data</a><br>';
	echo( $txt);
}

//--------------------------------------------------------------------------------------------------

?>
