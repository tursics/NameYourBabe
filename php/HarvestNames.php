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

	public function loadStats( $firstChar)
	{
		global $dataHarvestStatNames;

		if( isset( $dataHarvestStatNames) && array_key_exists( $firstChar, $dataHarvestStatNames)) {
			return;
		}

		if( file_exists( dirname(__FILE__)."/data/harvest/stat/$firstChar.php")) {
			include_once( "data/harvest/stat/$firstChar.php");
		} else {
			if( !isset( $dataHarvestStatNames)) {
				$dataHarvestStatNames = Array();
			}
			$dataHarvestStatNames[ $firstChar] = Array();
		}
	}

	public function saveStats( $firstChar)
	{
		global $dataHarvestStatNames;

		if( !isset( $dataHarvestStatNames) || !array_key_exists( $firstChar, $dataHarvestStatNames)) {
			return;
		}

		$contents = '<?php'."\n".'$dataHarvestStatNames["'.$firstChar.'"]=';
		$contents .= var_export( $dataHarvestStatNames[$firstChar], true);
		$contents .= ';'."\n".'?>'."\n";

		$file = dirname(__FILE__).'/data';
		if( !file_exists( $file)) {
			mkdir( $file, 0777);
		}
		$file .= '/harvest';
		if( !file_exists( $file)) {
			mkdir( $file, 0777);
		}
		$file .= '/stat';
		if( !file_exists( $file)) {
			mkdir( $file, 0777);
		}
		$file .= '/' . $firstChar . '.php';
		file_put_contents( $file, $contents);

		$file = dirname(__FILE__).'/backup';
		if( !file_exists( $file)) {
			mkdir( $file, 0777);
		}
		$file .= '/harvest';
		if( !file_exists( $file)) {
			mkdir( $file, 0777);
		}
		$file .= '/stat';
		if( !file_exists( $file)) {
			mkdir( $file, 0777);
		}
		$file .= '/' . $firstChar . '-' . date( 'Y-W') . '.php';
		file_put_contents( $file, $contents);
	}

	public function loadAllStats()
	{
		for( $i = 0; $i < 26; ++$i) {
			$this->loadStats( chr( 97 + $i));
		}
	}

	public function saveAllStats()
	{
		for( $i = 0; $i < 26; ++$i) {
			$this->saveStats( chr( 97 + $i));
		}
	}

	public function harvest( $name, $code)
	{
		global $dataHarvestStatNames;

		static $char = Array('A'=>'a','B'=>'b','C'=>'c','D'=>'d','E'=>'e','F'=>'f','G'=>'g','H'=>'h','I'=>'i','J'=>'j','K'=>'k','L'=>'l','M'=>'m','N'=>'n','O'=>'o','P'=>'p','Q'=>'q','R'=>'r','S'=>'s','T'=>'t','U'=>'u','V'=>'v','W'=>'w','X'=>'x','Y'=>'y','Z'=>'z');

		if( array_key_exists( $name[0], $char)) {
			$firstChar = $char[ $name[0]];
			$dataHarvestStatNames[$firstChar][$name] .= $code;
			return false;
		}

		echo( 'Error: Unknown first letter in ' . $name . '<br>');
		return true;
	}

} // class HarvestNames
$HarvestNames = new HarvestNames();

//------------------------------------------------------------------------------

?>
