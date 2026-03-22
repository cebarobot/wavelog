<?php

require_once APPPATH . 'models/JapanAwardEntity_model.php';

class Jcc_model extends JapanAwardEntity_model {
	protected $entityConfig = array(
		'awardType' => 'JCC',
		'jsonPath' => 'assets/json/japan_award/jcc_list.json',
		'workedBandsKey' => 'jcc',
		'cntyPatternSql' => "(COL_CNTY LIKE '____' OR COL_CNTY LIKE '10____')",
		'dxccList' => array('339', '177', '192'),
	);
}

?>
