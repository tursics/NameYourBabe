<?php

//------------------------------------------------------------------------------

class HarvestNames
{
	public $male = Array();
	public $female = Array();
	public $maleUsedMemory = 0;
	public $femaleUsedMemory = 0;

	public function load()
	{
		if( 0 == count( $this->male)) {
			$this->maleUsedMemory = memory_get_peak_usage( false);
			$file = dirname(__FILE__) . '/data/harvest/male.csv';

			if( file_exists( $file)) {
				$contents = file_get_contents( $file);
				$this->male = explode( "\n", $contents);
			}
			$this->maleUsedMemory = memory_get_peak_usage( false) - $this->maleUsedMemory;
		}

		if( 0 == count( $this->female)) {
			$this->femaleUsedMemory = memory_get_peak_usage( false);
			$file = dirname(__FILE__) . '/data/harvest/female.csv';

			if( file_exists( $file)) {
				$contents = file_get_contents( $file);
				$this->female = explode( "\n", $contents);
			}
			$this->femaleUsedMemory = memory_get_peak_usage( false) - $this->femaleUsedMemory;
		}
	}

	public function save()
	{
		$file = dirname(__FILE__) . '/data/harvest/male.csv';
		$backupfile = dirname(__FILE__) . '/backup/harvest/male-' . date( 'Y-W') . '.csv';
		$contents = implode( "\n", $this->male);
		file_put_contents( $file, $contents);
		file_put_contents( $backupfile, $contents);

		$file = dirname(__FILE__) . '/data/harvest/female.csv';
		$backupfile = dirname(__FILE__) . '/backup/harvest/female-' . date( 'Y-W') . '.csv';
		$contents = implode( "\n", $this->female);
		file_put_contents( $file, $contents);
		file_put_contents( $backupfile, $contents);
	}
} // class HarvestNames
$HarvestNames = new HarvestNames();

//------------------------------------------------------------------------------

?>
