<?php

require_once APPPATH . 'models/JapanAwardEntity_model.php';

class Jcc_model extends JapanAwardEntity_model {

	public $jaCities = array();

	function __construct() {
		parent::__construct();
		$this->initializeEntityConfig(array(
			'awardType' => 'JCC',
			'jsonPath' => 'assets/json/japan_award/jcc_list.json',
			'entityLabel' => 'City',
			'exportKey' => 'jcc',
			'workedBandsKey' => 'jcc',
			'cntyPatternSql' => "(COL_CNTY LIKE '____' OR COL_CNTY LIKE '10____')",
			'dxccList' => array('339', '177', '192'),
		));

		$this->jaCities = $this->entityData;
	}
}

?>
