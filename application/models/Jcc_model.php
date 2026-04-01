<?php

require_once(APPPATH . 'models/Aja_model.php');

class Jcc_model extends Aja_model {

	public $ja_cities = array();
	public $ja_kus = array();

	function __construct() {
		parent::__construct();
		$this->load_jcc_data_from_json();
		$this->load_ku_data_from_json();
	}

	/**
	 * Load JCC data from JSON file into $this->ja_cities
	 */
	private function load_jcc_data_from_json() {
		$this->ja_cities = json_decode(file_get_contents(FCPATH . 'assets/json/japan_award/jcc_list.json'), true);
	}

	/**
	 * Load KU data from JSON file into $this->ja_kus
	 */
	private function load_ku_data_from_json() {
		$this->ja_kus = json_decode(file_get_contents(FCPATH . 'assets/json/japan_award/ku_list.json'), true);
	}

	/**
	 * Build query groups for JCC award
	 * JCC has 2 groups:
	 *   1. Cities: match by col_cnty in jcc_list
	 *   2. Designated city wards (kus): match by col_cnty in ku_list, entity = left(col_cnty, 4)
	 *
	 * @param array $postdata The postdata containing filter options
	 * @return array Array of query groups
	 */
	private function build_jcc_query_groups($postdata) {
		$jcc_data = $this->filter_entity_data($this->ja_cities, $postdata);
		$ku_data = $this->filter_entity_data($this->ja_kus, $postdata);
		$jcc_in_list = $this->build_entity_in_list_sql($jcc_data);
		$ku_in_list = $this->build_entity_in_list_sql($ku_data);

		return array(
			array(
				'entity_expr' => 'col_cnty',
				'entity_cond' => "col_dxcc in ('339') and col_cnty in (" . $jcc_in_list . ")",
			),
			array(
				'entity_expr' => 'left(col_cnty, 4)',
				'entity_cond' => "col_dxcc in ('339') and col_cnty in (" . $ku_in_list . ")",
			),
		);
	}

	/**
	 * Query JCC entity status
	 *
	 * @param array $postdata The postdata containing filter options
	 * @param string $key_col The column to use as key_col: 'band', 'mode' or 'none'
	 * @return array The result set as an array of rows
	 */
	function query_jcc_entity_status($postdata, $key_col = "none") {
		return $this->query_entity_status($this->build_jcc_query_groups($postdata), $key_col, $postdata);
	}

	/**
	 * Query JCC export QSOs (first confirmed QSO per city)
	 *
	 * @param array $postdata The postdata containing filter options
	 * @return array The result set as an array of rows
	 */
	function query_jcc_export_qsos($postdata) {
		return $this->query_export_qsos($this->build_jcc_query_groups($postdata), 'none', $postdata);
	}

	/**
	 * Build grouped slot data for the JCC demo grid.
	 *
	 * @param array $postdata The postdata containing filter options
	 * @param array|null $entity_status The pre-query entity status to use
	 * @return array Grouped slot data keyed by prefecture code
	 */
	function get_jcc_grouped_grid($postdata, $entity_status = null) {
		if ($entity_status === null) {
			$entity_status = $this->query_jcc_entity_status($postdata, 'none');
		}

		$jcc_list = $this->filter_entity_data($this->ja_cities, $postdata);
		$slot_status = array();
		foreach ($jcc_list as $entity => $city_data) {
			$slot_status[$entity] = '-';
		}

		foreach ($entity_status as $row) {
			$entity = $row['entity'];
			if (!array_key_exists($entity, $jcc_list)) {
				continue;
			}

			if ($row['confirmed'] == 1) {
				$slot_status[$entity] = 'C';
				continue;
			}

			if ($slot_status[$entity] !== 'C') {
				$slot_status[$entity] = 'W';
			}
		}

		$groups = array();
		foreach ($jcc_list as $entity => $city_data) {
			$prefecture_code = substr((string) $entity, 0, 2);
			if (!isset($groups[$prefecture_code])) {
				$groups[$prefecture_code] = array(
					'prefecture_code' => $prefecture_code,
					'prefecture_name' => $this->get_ja_prefecture_name($prefecture_code),
					'slots' => array(),
				);
			}

			$status = $slot_status[$entity] ?? '-';

			$groups[$prefecture_code]['slots'][] = array(
				'entity' => $entity,
				'number' => $entity,
				'short_number' => substr((string) $entity, 2),
				'city_name' => $city_data['name'] ?? '',
				'ja_city_name' => $city_data['ja_name'] ?? '',
				'status' => $status,
				'deleted' => !empty($city_data['deleted']),
				'is_designated_city' => !empty($city_data['designated_city']),
			);
		}

		ksort($groups, SORT_STRING);
		foreach ($groups as &$group) {
			usort($group['slots'], function ($left, $right) {
				return strcmp($left['entity'], $right['entity']);
			});
		}
		unset($group);

		return $groups;
	}

	/**
	 * Build the overall summary for the grouped JCC grid.
	 *
	 * @param array $postdata The postdata containing filter options
	 * @param array|null $entity_status The pre-query entity status to use
	 * @return array The summary data for the grouped grid
	 */
	function get_jcc_summary($postdata, $entity_status = null) {
		if ($entity_status === null) {
			$entity_status = $this->query_jcc_entity_status($postdata, 'none');
		}

		$jcc_list = $this->filter_entity_data($this->ja_cities, $postdata);
		$worked_entities = array();
		$confirmed_entities = array();

		foreach ($entity_status as $row) {
			$entity = $row['entity'];
			if (!array_key_exists($entity, $jcc_list)) {
				continue;
			}

			$worked_entities[$entity] = true;
			if ($row['confirmed'] == 1) {
				$confirmed_entities[$entity] = true;
			}
		}

		$total = count($jcc_list);
		$deleted = 0;
		foreach ($jcc_list as $city_data) {
			if (!empty($city_data['deleted'])) {
				$deleted += 1;
			}
		}
		$worked = count($worked_entities);
		$confirmed = count($confirmed_entities);
		$worked_only = max(0, $worked - $confirmed);

		return array(
			'deleted' => $deleted,
			'total' => $total,
			'worked' => $worked,
			'confirmed' => $confirmed,
			'worked_only' => $worked_only,
			'worked_percent' => $total > 0 ? round(($worked / $total) * 100, 1) : 0,
			'confirmed_percent' => $total > 0 ? round(($confirmed / $total) * 100, 1) : 0,
			'worked_only_percent' => $total > 0 ? round(($worked_only / $total) * 100, 1) : 0,
		);
	}

	/**
	 * Get the JCC map array for display on the map
	 * 	array[city] = [worked, confirmed]
	 *
	 * @param array $postdata The postdata containing filter options
	 * @param array|null $entity_status The pre-query entity status to use
	 * @return array The JCC map array for display on the map
	 */
	function get_jcc_map_array($postdata, $entity_status = null) {
		if ($entity_status === null) {
			$entity_status = $this->query_jcc_entity_status($postdata, 'none');
		}

		$jccs = array();
		foreach ($entity_status as $row) {
			$entity = $row['entity'];
			if (!isset($jccs[$entity])) {
				$jccs[$entity] = array(1, 0);
			}

			if ($row['confirmed'] == 1) {
				$jccs[$entity][1] = 1;
			}
		}

		ksort($jccs, SORT_STRING);

		return $jccs;
	}

	/**
	 * Export QSOs for JCC award based on postdata
	 * Return first confirmed QSO for each city, with QSO details and city name
	 *
	 * @param array $postdata The postdata containing filter options
	 * @return array The result set as an array of rows with QSO details and city name
	 */
	function get_jcc_export($postdata) {
		$rows = $this->query_jcc_export_qsos($postdata);

		foreach ($rows as &$row) {
			$row['entity_name'] = $this->ja_cities[$row['entity']]['name'] ?? '';
		}
		unset($row);

		return $rows;
	}

}
?>
