<?php

//------------------------------------------------------------------------------
// NUTS  GIVEN_NAME  NUMBER  RANKING  SEX  YEAR
//------------------------------------------------------------------------------

class HarvestYears
{
	public function getOneYear( $nuts, $year, $isMale)
	{
		$path = 'data/harvest/'.substr($nuts, 0, 2).'/'.$nuts.'/'.$nuts.'_'.$year.'_'.($isMale ? 'm' : 'f').'.csv';
		$file = dirname(__FILE__) . '/' . $path;

		if( file_exists( $file)) {
			$contents = file_get_contents( $file);
			$rows = explode( "\n", $contents);
			$rowsCount = count( $rows);
			$vec = Array();
			$vecLabel = explode( ";", $rows[ 0]);
			$colsCount = count( $vecLabel);

			for( $i = 1; $i < $rowsCount; ++$i) {
				$tmp = Array();
				$cols = explode( ";", $rows[ $i]);
				for( $c = 0; $c < $colsCount; ++$c) {
					$tmp[ $vecLabel[ $c]] = $cols[ $c];
				}
				$vec[] = $tmp;
			}

			return $vec;
		}

		return Array();
	}

	public function getYears( $nuts)
	{
		global $dataHarvestMetadata;
		global $MetadataVec;

		$years = Array();

		for( $i = 0; $i < count( $MetadataVec); ++$i) {
			if( $nuts == $MetadataVec[$i]['nuts']) {
				$harvest = $dataHarvestMetadata[ $MetadataVec[$i]['meta']];
				if( array_key_exists( 'years', $harvest)) {
					$years = array_unique( array_merge( $harvest['years'], $years));
				}
			}
		}

		return $years;
	}

} // class HarvestYears
$HarvestYears = new HarvestYears();

//------------------------------------------------------------------------------

?>
