<?php

require_once APPPATH . 'models/JapanAwardEntity_model.php';

class Jcg_model extends JapanAwardEntity_model {

	public $jaGuns = array();

	function __construct() {
		parent::__construct();
		$this->initializeEntityConfig(array(
			'awardType' => 'JCG',
			'jsonPath' => 'assets/json/japan_award/jcg_list.json',
			'entityLabel' => 'Gun',
			'exportKey' => 'jcg',
			'workedBandsKey' => 'jcg',
			'cntyPatternSql' => "COL_CNTY LIKE '_____'",
			'dxccList' => array('339', '177', '192'),
		));

		$this->jaGuns = $this->entityData;
	}

	function get_jcg_array($bands, $postdata) {
		return $this->get_entity_array($bands, $postdata);
	}

	function getJcgWorked($location_list, $band, $postdata) {
		return $this->getWorked($location_list, $band, $postdata);
	}

	function getJcgConfirmed($location_list, $band, $postdata) {
		return $this->getConfirmed($location_list, $band, $postdata);
	}

	function get_jcg_summary($bands, $postdata) {
		return $this->get_entity_summary($bands, $postdata);
	}

	function exportJcg($postdata) {
		return $this->exportEntities($postdata);
	}

	function fetch_jcg_wkd($postdata) {
		return $this->fetch_entity_wkd($postdata);
	}

	function fetch_jcg_cnfm($postdata) {
		return $this->fetch_entity_cnfm($postdata);
	}
}

?>
