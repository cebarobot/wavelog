<?php

require_once(APPPATH . 'models/Japan_award_model.php');

class Aja_model extends Japan_award_model {

	function __construct() {
		parent::__construct();
		$this->load_jcc_data_from_json();
		$this->load_ku_data_from_json();
		$this->load_jcg_data_from_json();
	}

	/**
	 * Build query groups for AJA award (4 groups)
	 *
	 * @param array $postdata The postdata containing filter options
	 * @return array Array of query groups
	 */
	private function build_aja_query_groups($postdata) {
		$jcc_data = $this->filter_entity_data($this->ja_cities, $postdata);
		$ku_data = $this->filter_entity_data($this->ja_kus, $postdata);
		$jcg_data = $this->filter_entity_data($this->ja_guns, $postdata);

		$jcc_in_list = $this->build_entity_in_list_sql($jcc_data);
		$ku_in_list = $this->build_entity_in_list_sql($ku_data);
		$jcg_in_list = $this->build_entity_in_list_sql($jcg_data);

		return array(
			array(
				'entity_expr' => 'col_cnty',
				'entity_cond' => "col_dxcc in ('339') and col_cnty in (" . $jcc_in_list . ")",
			),
			array(
				'entity_expr' => 'col_cnty',
				'entity_cond' => "col_dxcc in ('339') and col_cnty in (" . $ku_in_list . ")",
			),
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
	 * Query AJA entity status
	 *
	 * @param array $postdata The postdata containing filter options
	 * @param string $key_col The column to use as key_col: 'band', 'mode' or 'none'
	 * @return array The result set as an array of rows
	 */
	function query_aja_entity_status($postdata, $key_col = "band") {
		return $this->query_entity_status($this->build_aja_query_groups($postdata), $key_col, $postdata);
	}

	/**
	 * Query AJA export QSOs (first confirmed QSO per entity+band)
	 *
	 * @param array $postdata The postdata containing filter options
	 * @return array The result set as an array of rows
	 */
	function query_aja_export_qsos($postdata) {
		return $this->query_export_qsos($this->build_aja_query_groups($postdata), 'band', $postdata);
	}

	/**
	 * Determine the entity type from its code
	 *
	 * @param string $code The entity code
	 * @return string 'city', 'gun' or 'ku'
	 */
	private function get_entity_type($code) {
		$len = strlen($code);
		if ($len === 6) return 'ku';
		if ($len === 5) return 'gun';
		return 'city';
	}

	/**
	 * Get the entity name and type from its code
	 *
	 * @param string $code The entity code
	 * @return array ['name' => string, 'type' => string]
	 */
	private function get_entity_info($code) {
		$type = $this->get_entity_type($code);
		switch ($type) {
			case 'ku':
				return ['name' => $this->ja_kus[$code]['name'] ?? '', 'type' => 'ku'];
			case 'gun':
				return ['name' => $this->ja_guns[$code]['name'] ?? '', 'type' => 'gun'];
			default:
				return ['name' => $this->ja_cities[$code]['name'] ?? '', 'type' => 'city'];
		}
	}

	/**
	 * Build ordered entity list for AJA
	 * Order: by prefecture number, cities before guns,
	 *   designated cities followed by their wards
	 *
	 * @param array $postdata The postdata containing filter options
	 * @return array Ordered array of ['code' => string, 'name' => string, 'type' => string]
	 */
	private function build_aja_ordered_entities($postdata) {
		$jcc_data = $this->filter_entity_data($this->ja_cities, $postdata);
		$ku_data = $this->filter_entity_data($this->ja_kus, $postdata);
		$jcg_data = $this->filter_entity_data($this->ja_guns, $postdata);

		// Group entities by prefecture (first 2 digits)
		$pref_cities = array(); // 4-digit codes
		$pref_guns = array();   // 5-digit codes
		$pref_kus = array();    // 6-digit codes grouped by parent city (first 4 digits)

		foreach ($jcc_data as $code => $data) {
			$pref = substr($code, 0, 2);
			$pref_cities[$pref][$code] = $data;
		}

		foreach ($jcg_data as $code => $data) {
			$pref = substr($code, 0, 2);
			$pref_guns[$pref][$code] = $data;
		}

		// Group kus by parent city code (first 4 digits)
		$city_kus = array();
		foreach ($ku_data as $code => $data) {
			$parent_city = substr($code, 0, 4);
			$city_kus[$parent_city][$code] = $data;
		}

		// Build ordered list: for each prefecture, cities (with kus after designated cities) then guns
		$ordered = array();
		$prefs = array_unique(array_merge(
			array_keys($pref_cities),
			array_keys($pref_guns)
		));
		sort($prefs);

		foreach ($prefs as $pref) {
			// Cities first, with wards after designated cities
			$cities = $pref_cities[$pref] ?? array();
			ksort($cities, SORT_STRING);
			foreach ($cities as $code => $data) {
				$ordered[] = ['code' => $code, 'name' => $data['name'], 'type' => 'city'];
				// Insert wards for designated cities
				if (!empty($data['designated_city']) && isset($city_kus[$code])) {
					$kus = $city_kus[$code];
					ksort($kus, SORT_STRING);
					foreach ($kus as $ku_code => $ku_data) {
						$ordered[] = ['code' => $ku_code, 'name' => $ku_data['name'], 'type' => 'ku'];
					}
				}
			}

			// Guns after cities
			$guns = $pref_guns[$pref] ?? array();
			ksort($guns, SORT_STRING);
			foreach ($guns as $code => $data) {
				$ordered[] = ['code' => $code, 'name' => $data['name'], 'type' => 'gun'];
			}
		}

		return $ordered;
	}

	/**
	 * Get the AJA status array for display on the table
	 * 	array[entity]['bands'][band] = 'C' | 'W' | '-'
	 *
	 * @param array $bands The list of bands
	 * @param array $postdata The postdata containing filter options
	 * @param array|null $entity_status Pre-queried entity status
	 * @return array|int Ordered entity array or 0 if empty
	 */
	function get_aja_array($bands, $postdata, $entity_status = null) {
		if ($entity_status === null) {
			$entity_status = $this->query_aja_entity_status($postdata, 'band');
		}

		$ordered_entities = $this->build_aja_ordered_entities($postdata);

		// Build status lookup: entity_code => band => {confirmed}
		$status_map = array();
		foreach ($entity_status as $row) {
			$status_map[$row['entity']][$row['key_col']] = $row['confirmed'];
		}

		$result = array();
		foreach ($ordered_entities as $entity) {
			$code = $entity['code'];
			$row = array(
				'Number' => $code,
				'Name' => $entity['name'],
				'Type' => $entity['type'],
				'count' => 0,
			);

			foreach ($bands as $band) {
				$row[$band] = '-';
			}

			if (isset($status_map[$code])) {
				foreach ($status_map[$code] as $band => $confirmed) {
					if ($confirmed == 1) {
						if ($postdata['confirmed'] != null) {
							$row[$band] = 'C';
							$row['count'] += 1;
						}
					} else {
						if ($postdata['worked'] != null) {
							$row[$band] = 'W';
							$row['count'] += 1;
						}
					}
				}
			}

			if ($postdata['notworked'] == null && $row['count'] == 0) {
				continue;
			}

			$result[$code] = $row;
		}

		return !empty($result) ? $result : 0;
	}

	/**
	 * Get the AJA summary array split by entity type (city/gun/ku)
	 *
	 * @param array $bands The list of bands
	 * @param array $postdata The postdata containing filter options
	 * @param array|null $entity_status Pre-queried entity status
	 * @return array ['city' => ..., 'gun' => ..., 'ku' => ..., 'total' => ...]
	 *   each with ['worked' => [band => count, 'Total' => slot_count], 'confirmed' => [...]]
	 */
	function get_aja_summary($bands, $postdata, $entity_status = null) {
		if ($entity_status === null) {
			$entity_status = $this->query_aja_entity_status($postdata, 'band');
		}

		$types = array('city', 'gun', 'ku', 'total');
		$summary = array();
		foreach ($types as $type) {
			$summary[$type] = array(
				'worked' => array(),
				'confirmed' => array(),
			);
			foreach ($bands as $band) {
				$summary[$type]['worked'][$band] = 0;
				$summary[$type]['confirmed'][$band] = 0;
			}
		}

		foreach ($entity_status as $row) {
			$entity = $row['entity'];
			$type = $this->get_entity_type($entity);
			$band = $row['key_col'];

			$summary[$type]['worked'][$band] = ($summary[$type]['worked'][$band] ?? 0) + 1;
			$summary['total']['worked'][$band] = ($summary['total']['worked'][$band] ?? 0) + 1;

			if ($row['confirmed'] == 1) {
				$summary[$type]['confirmed'][$band] = ($summary[$type]['confirmed'][$band] ?? 0) + 1;
				$summary['total']['confirmed'][$band] = ($summary['total']['confirmed'][$band] ?? 0) + 1;
			}
		}

		foreach ($types as $type) {
			$summary[$type]['worked']['Total'] = array_sum($summary[$type]['worked']);
			$summary[$type]['confirmed']['Total'] = array_sum($summary[$type]['confirmed']);

			// Move SAT after Total
			if (isset($summary[$type]['worked']['SAT'])) {
				$sat_w = $summary[$type]['worked']['SAT'];
				$sat_c = $summary[$type]['confirmed']['SAT'];
				unset($summary[$type]['worked']['SAT']);
				unset($summary[$type]['confirmed']['SAT']);
				$summary[$type]['worked']['SAT'] = $sat_w;
				$summary[$type]['confirmed']['SAT'] = $sat_c;
			}
		}

		return $summary;
	}

	/**
	 * Get the AJA map array for display on the map
	 * 	array[entity] = [worked, confirmed]
	 *
	 * @param array $postdata The postdata containing filter options
	 * @param array|null $entity_status Pre-queried entity status
	 * @return array The AJA map array
	 */
	function get_aja_map_array($postdata, $entity_status = null) {
		if ($entity_status === null) {
			$entity_status = $this->query_aja_entity_status($postdata, 'none');
		}

		$entities = array();
		foreach ($entity_status as $row) {
			$entity = $row['entity'];
			if (!isset($entities[$entity])) {
				$entities[$entity] = array(1, 0);
			}
			if ($row['confirmed'] == 1) {
				$entities[$entity][1] = 1;
			}
		}

		ksort($entities, SORT_STRING);

		return $entities;
	}

	/**
	 * Export QSOs for AJA award in 2-row per entity format
	 * Each entity has 2 rows per band (Mode+Callsign / Check+Date)
	 *
	 * @param array $bands The list of bands (ordered)
	 * @param array $postdata The postdata containing filter options
	 * @return array Array of CSV rows (each row is an array of cell values)
	 */
	function get_aja_export($bands, $postdata) {
		$qsos = $this->query_aja_export_qsos($postdata);

		// Build lookup: entity => band => qso
		$qso_map = array();
		foreach ($qsos as $qso) {
			$qso_map[$qso['entity']][$qso['key_col']] = $qso;
		}

		$ordered_entities = $this->build_aja_ordered_entities($postdata);

		// Build header rows
		// Row 1: empty, "JCC/JCG/Ku", empty, empty, then per band: band_label, empty
		$header1 = array('', 'JCC/JCG/Ku', '', '');
		foreach ($bands as $band) {
			$header1[] = $band;
			$header1[] = '';
		}

		// Row 2: empty, "Name", empty, "Number", then per band: "Mode", "Callsign"
		$header2 = array('', 'Name', '', 'Number');
		foreach ($bands as $band) {
			$header2[] = 'Mode';
			$header2[] = 'Callsign';
		}

		// Row 3: empty, empty, empty, empty, then per band: "check", "Date"
		$header3 = array('', '', '', '');
		foreach ($bands as $band) {
			$header3[] = 'check';
			$header3[] = 'Date';
		}

		$rows = array($header1, $header2, $header3);

		// Track current prefecture for prefecture header
		$current_pref = '';

		foreach ($ordered_entities as $entity) {
			$code = $entity['code'];
			$name = $entity['name'];
			$type = $entity['type'];

			$pref = substr($code, 0, 2);

			// Prefecture label in first column of first entity in a prefecture
			$pref_label = '';
			if ($pref !== $current_pref) {
				$current_pref = $pref;
				$pref_label = $pref;
			}

			// Row 1: pref_label, name, empty, number, then per band: Mode, Callsign
			$row1 = array('', $pref_label, $name, $code);
			// Row 2: empty, empty, empty, empty, then per band: check(empty), Date
			$row2 = array('', '', '', '');

			foreach ($bands as $band) {
				if (isset($qso_map[$code][$band])) {
					$qso = $qso_map[$code][$band];
					$mode = $qso['COL_MODE'];
					if ($qso['COL_PROP_MODE'] === 'SAT') {
						$mode .= '/SAT';
					}
					$row1[] = $mode;
					$row1[] = $qso['COL_CALL'];
					$row2[] = '';
					$row2[] = substr($qso['COL_TIME_ON'], 0, 10);
				} else {
					$row1[] = '';
					$row1[] = '';
					$row2[] = '';
					$row2[] = '';
				}
			}

			$rows[] = $row1;
			$rows[] = $row2;
		}

		return $rows;
	}

}
?>
