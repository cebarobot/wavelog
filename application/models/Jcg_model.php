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
}

?>
