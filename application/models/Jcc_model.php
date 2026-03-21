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

	function get_jcc_array($bands, $postdata) {
		return $this->get_entity_array($bands, $postdata);
	}

	function getJccWorked($location_list, $band, $postdata) {
		return $this->getWorked($location_list, $band, $postdata);
	}

	function getJccConfirmed($location_list, $band, $postdata) {
		return $this->getConfirmed($location_list, $band, $postdata);
	}

	function get_jcc_summary($bands, $postdata) {
		return $this->get_entity_summary($bands, $postdata);
	}

	function exportJcc($postdata) {
		return $this->exportEntities($postdata);
	}

	function fetch_jcc_wkd($postdata) {
		return $this->fetch_entity_wkd($postdata);
	}

	function fetch_jcc_cnfm($postdata) {
		return $this->fetch_entity_cnfm($postdata);
	}
}

?>
