<?php

require_once(APPPATH . 'models/Aja_model.php');

class Jcg_model extends Aja_model {

	public $ja_guns = array();

	function __construct() {
		parent::__construct();
		$this->load_jcg_data_from_json();
	}

	/**
	 * Load JCG data from JSON file into $this->ja_guns
	 */
	private function load_jcg_data_from_json() {
		$this->ja_guns = json_decode(file_get_contents(FCPATH . 'assets/json/japan_award/jcg_list.json'), true);
	}

	/**
	 * Build query groups for JCG award
	 * JCG has 2 groups:
	 *   1. Regular guns: match by col_cnty in jcg_list
	 *   2. Ogasawara-Shicho: match by col_dxcc in ('177', '192'), entity = '10007'
	 *
	 * @param array $postdata The postdata containing filter options
	 * @return array Array of query groups
	 */
	private function build_jcg_query_groups($postdata) {
		$jcg_data = $this->filter_entity_data($this->ja_guns, $postdata);
		$jcg_in_list = $this->build_entity_in_list_sql($jcg_data);

		return array(
			array(
				'entity_expr' => 'col_cnty',
				'entity_cond' => "col_dxcc in ('339') and col_cnty in (" . $jcg_in_list . ")",
			),
			array(
				'entity_expr' => "'10007'",
				'entity_cond' => "col_dxcc in ('177', '192')",
			),
		);
	}

	/**
	 * Query JCG entity status
	 *
	 * @param array $postdata The postdata containing filter options
	 * @param string $key_col The column to use as key_col: 'band', 'mode' or 'none'
	 * @return array The result set as an array of rows
	 */
	function query_jcg_entity_status($postdata, $key_col = "none") {
		return $this->query_entity_status($this->build_jcg_query_groups($postdata), $key_col, $postdata);
	}

	/**
	 * Query JCG export QSOs (first confirmed QSO per gun)
	 *
	 * @param array $postdata The postdata containing filter options
	 * @return array The result set as an array of rows
	 */
	function query_jcg_export_qsos($postdata) {
		return $this->query_export_qsos($this->build_jcg_query_groups($postdata), 'none', $postdata);
	}

	/**
	 * Get the JCG status array for display on the table
	 * 	array[gun][band] = 'C' if confirmed, 'W' if worked but not confirmed, '-' if not worked
	 *
	 * @param array $bands The list of bands to include in the result
	 * @param array $postdata The postdata containing filter options
	 * @param array|null $entity_status The pre-query entity status to use
	 */
	function get_jcg_array($bands, $postdata, $entity_status = null) {
		if ($entity_status === null) {
			$entity_status = $this->query_jcg_entity_status($postdata, 'band');
		}

		$jcg_list = $this->filter_entity_data($this->ja_guns, $postdata);

		$guns = array();
		foreach ($jcg_list as $gun => $gun_data) {
			$guns[$gun]['Number'] = $gun;
			$guns[$gun]['Gun'] = $gun_data['name'];
			$guns[$gun]['count'] = 0;
			foreach ($bands as $band) {
				$guns[$gun][$band] = '-';
			}
		}

		foreach ($entity_status as $row) {
			if (!isset($guns[$row['entity']])) continue;
			if ($row['confirmed'] == 1) {
				if ($postdata['confirmed'] != NULL) {
					$guns[$row['entity']][$row['key_col']] = 'C';
					$guns[$row['entity']]['count'] += 1;
				}
			} else {
				if ($postdata['worked'] != NULL) {
					$guns[$row['entity']][$row['key_col']] = 'W';
					$guns[$row['entity']]['count'] += 1;
				}
			}
		}

		if ($postdata['notworked'] == NULL) {
			foreach ($guns as $gun => $gun_data) {
				if ($gun_data['count'] == 0) {
					unset($guns[$gun]);
				}
			}
		}

		if (!empty($guns)) {
			return $guns;
		} else {
			return 0;
		}
	}

	/**
	 * Get the JCG summary array for display on the table
	 *
	 * @param array $bands The list of bands to include in the result
	 * @param array $postdata The postdata containing filter options
	 * @param array|null $entity_status The pre-query entity status to use
	 */
	function get_jcg_summary($bands, $postdata, $entity_status = null) {
		if ($entity_status === null) {
			$entity_status = $this->query_jcg_entity_status($postdata, 'band');
		}

		$summary = array(
			'worked' => array(),
			'confirmed' => array(),
		);

		foreach ($bands as $band) {
			$summary['worked'][$band] = 0;
			$summary['confirmed'][$band] = 0;
		}

		$worked_total = array();
		$confirmed_total = array();

		foreach ($entity_status as $row) {
			$worked_total[$row['entity']] = true;
			$summary['worked'][$row['key_col']] += 1;
			if ($row['confirmed'] == 1) {
				$confirmed_total[$row['entity']] = true;
				$summary['confirmed'][$row['key_col']] += 1;
			}
		}

		$summary['worked']['Total'] = count($worked_total);
		$summary['confirmed']['Total'] = count($confirmed_total);

		if (isset($summary['worked']['SAT']) && isset($summary['confirmed']['SAT'])) {
			$summary_worked_sat = $summary['worked']['SAT'];
			$summary_confirmed_sat = $summary['confirmed']['SAT'];

			unset($summary['worked']['SAT']);
			unset($summary['confirmed']['SAT']);

			$summary['worked']['SAT'] = $summary_worked_sat;
			$summary['confirmed']['SAT'] = $summary_confirmed_sat;
		}

		return $summary;
	}

	/**
	 * Get the JCG map array for display on the map
	 * 	array[gun] = [worked, confirmed]
	 *
	 * @param array $postdata The postdata containing filter options
	 * @param array|null $entity_status The pre-query entity status to use
	 * @return array The JCG map array
	 */
	function get_jcg_map_array($postdata, $entity_status = null) {
		if ($entity_status === null) {
			$entity_status = $this->query_jcg_entity_status($postdata, 'none');
		}

		$jcgs = array();
		foreach ($entity_status as $row) {
			$entity = $row['entity'];
			if (!isset($jcgs[$entity])) {
				$jcgs[$entity] = array(1, 0);
			}

			if ($row['confirmed'] == 1) {
				$jcgs[$entity][1] = 1;
			}
		}

		ksort($jcgs, SORT_STRING);

		return $jcgs;
	}

	/**
	 * Export QSOs for JCG award
	 * Return first confirmed QSO for each gun, with QSO details and gun name
	 *
	 * @param array $postdata The postdata containing filter options
	 * @return array The result set
	 */
	function get_jcg_export($postdata) {
		$rows = $this->query_jcg_export_qsos($postdata);

		foreach ($rows as &$row) {
			$row['entity_name'] = $this->ja_guns[$row['entity']]['name'] ?? '';
		}
		unset($row);

		return $rows;
	}

}
?>
