<?php

require_once(APPPATH . 'models/Japan_award_model.php');

class Waku_model extends Japan_award_model {

	public $ja_kus = array();

	function __construct() {
		parent::__construct();
		$this->load_ku_data_from_json();
	}

	/**
	 * Build query groups for WAKU award
	 * WAKU has 1 group:
	 *   1. Wards (kus): match by col_cnty in ku_list
	 *
	 * @param array $postdata The postdata containing filter options
	 * @return array Array of query groups
	 */
	private function build_waku_query_groups($postdata) {
		$ku_data = $this->filter_entity_data($this->ja_kus, $postdata);
		$ku_in_list = $this->build_entity_in_list_sql($ku_data);

		return array(
			array(
				'entity_expr' => 'col_cnty',
				'entity_cond' => "col_dxcc in ('339') and col_cnty in (" . $ku_in_list . ")",
			),
		);
	}

	/**
	 * Query WAKU entity status
	 *
	 * @param array $postdata The postdata containing filter options
	 * @param string $key_col The column to use as key_col: 'band', 'mode' or 'none'
	 * @return array The result set as an array of rows
	 */
	function query_waku_entity_status($postdata, $key_col = "none") {
		return $this->query_entity_status($this->build_waku_query_groups($postdata), $key_col, $postdata);
	}

	/**
	 * Query WAKU export QSOs (first confirmed QSO per ward)
	 *
	 * @param array $postdata The postdata containing filter options
	 * @return array The result set as an array of rows
	 */
	function query_waku_export_qsos($postdata) {
		return $this->query_export_qsos($this->build_waku_query_groups($postdata), 'none', $postdata);
	}

	/**
	 * Get the WAKU status array for display on the table
	 *
	 * @param array $bands The list of bands to include in the result
	 * @param array $postdata The postdata containing filter options
	 * @param array|null $entity_status The pre-query entity status to use
	 */
	function get_waku_array($bands, $postdata, $entity_status = null) {
		if ($entity_status === null) {
			$entity_status = $this->query_waku_entity_status($postdata, 'band');
		}

		$ku_list = $this->filter_entity_data($this->ja_kus, $postdata);

		$kus = array();
		foreach ($ku_list as $ku => $ku_data) {
			$kus[$ku]['Number'] = $ku;
			$kus[$ku]['Ward'] = $ku_data['name'];
			$kus[$ku]['count'] = 0;
			foreach ($bands as $band) {
				$kus[$ku][$band] = '-';
			}
		}

		foreach ($entity_status as $row) {
			if (!isset($kus[$row['entity']])) continue;
			if ($row['confirmed'] == 1) {
				if ($postdata['confirmed'] != NULL) {
					$kus[$row['entity']][$row['key_col']] = 'C';
					$kus[$row['entity']]['count'] += 1;
				}
			} else {
				if ($postdata['worked'] != NULL) {
					$kus[$row['entity']][$row['key_col']] = 'W';
					$kus[$row['entity']]['count'] += 1;
				}
			}
		}

		if ($postdata['notworked'] == NULL) {
			foreach ($kus as $ku => $ku_data) {
				if ($ku_data['count'] == 0) {
					unset($kus[$ku]);
				}
			}
		}

		if (!empty($kus)) {
			return $kus;
		} else {
			return 0;
		}
	}

	/**
	 * Get the WAKU summary array for display on the table
	 *
	 * @param array $bands The list of bands to include in the result
	 * @param array $postdata The postdata containing filter options
	 * @param array|null $entity_status The pre-query entity status to use
	 */
	function get_waku_summary($bands, $postdata, $entity_status = null) {
		if ($entity_status === null) {
			$entity_status = $this->query_waku_entity_status($postdata, 'band');
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
	 * Get the WAKU map array for display on the map
	 *
	 * @param array $postdata The postdata containing filter options
	 * @param array|null $entity_status The pre-query entity status to use
	 * @return array The WAKU map array
	 */
	function get_waku_map_array($postdata, $entity_status = null) {
		if ($entity_status === null) {
			$entity_status = $this->query_waku_entity_status($postdata, 'none');
		}

		$wakus = array();
		foreach ($entity_status as $row) {
			$entity = $row['entity'];
			if (!isset($wakus[$entity])) {
				$wakus[$entity] = array(1, 0);
			}

			if ($row['confirmed'] == 1) {
				$wakus[$entity][1] = 1;
			}
		}

		ksort($wakus, SORT_STRING);

		return $wakus;
	}

	/**
	 * Export QSOs for WAKU award
	 *
	 * @param array $postdata The postdata containing filter options
	 * @return array The result set
	 */
	function get_waku_export($postdata) {
		$rows = $this->query_waku_export_qsos($postdata);

		foreach ($rows as &$row) {
			$row['entity_name'] = $this->ja_kus[$row['entity']]['name'] ?? '';
		}
		unset($row);

		return $rows;
	}

}
?>
