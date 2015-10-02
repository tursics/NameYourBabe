<?php

//------------------------------------------------------------------------------

class HarvestNuts
{
	public function load()
	{
		global $dataHarvestNuts;

		if( isset( $dataHarvestNuts)) {
			return;
		}

		if( file_exists( dirname(__FILE__)."/data/harvest/nuts.php")) {
			include_once( "data/harvest/nuts.php");
		} else {
			$dataHarvestNuts = Array();
		}
	}

	public function save()
	{
		global $dataHarvestNuts;

		$contents = '<?php'."\n".'$dataHarvestNuts=';
		$contents .= var_export( $dataHarvestNuts, true);
		$contents .= ';'."\n".'?>'."\n";

		$file = dirname(__FILE__).'/data';
		if( !file_exists( $file)) {
			mkdir( $file, 0777);
		}
		$file .= '/harvest';
		if( !file_exists( $file)) {
			mkdir( $file, 0777);
		}
		$file .= '/nuts.php';
		file_put_contents( $file, $contents);

		$file = dirname(__FILE__).'/backup';
		if( !file_exists( $file)) {
			mkdir( $file, 0777);
		}
		$file .= '/harvest';
		if( !file_exists( $file)) {
			mkdir( $file, 0777);
		}
		$file .= '/nuts-' . date( 'Y-W') . '.php';
		file_put_contents( $file, $contents);
	}

	public function getId( $nuts)
	{
		global $dataHarvestNuts;

		$this->load();

		if(( count( $nuts) == 0) || (!array_key_exists( $nuts, $dataHarvestNuts))) {
			$dataHarvestNuts[ $nuts] = count( $dataHarvestNuts);
			$this->save();
		}

		return $dataHarvestNuts[ $nuts];
	}

	public function getNuts( $id)
	{
		global $dataHarvestNuts;

		$this->load();

		foreach( $dataHarvestNuts as $nuts => $value) {
			if( $value == $id) {
				return $nuts;
			}
		}

		return '';
	}
} // class HarvestNuts
$HarvestNuts = new HarvestNuts();

//------------------------------------------------------------------------------

?>
