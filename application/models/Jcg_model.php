<?php

require_once APPPATH . 'models/JapanAwardEntity_model.php';

class Jcg_model extends JapanAwardEntity_model {
	protected $entityConfig = array(
		'awardType' => 'JCG',
		'jsonPath' => 'assets/json/japan_award/jcg_list.json',
		'workedBandsKey' => 'jcg',
		'cntyPatternSql' => "COL_CNTY LIKE '_____'",
		'dxccList' => array('339', '177', '192'),
	);
}

?>
