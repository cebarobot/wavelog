<?php

class Aja_model extends CI_Model {

	protected $location_list = null;

	// AJA data (lazy-loaded)
	private $aja_cities = null;
	private $aja_kus = null;
	private $aja_guns = null;

	protected $ja_prefectures = array(
		'01' => 'Hokkaido',
		'02' => 'Aomori',
		'03' => 'Iwate',
		'04' => 'Akita',
		'05' => 'Yamagata',
		'06' => 'Miyagi',
		'07' => 'Fukushima',
		'08' => 'Niigata',
		'09' => 'Nagano',
		'10' => 'Tokyo',
		'11' => 'Kanagawa',
		'12' => 'Chiba',
		'13' => 'Saitama',
		'14' => 'Ibaraki',
		'15' => 'Tochigi',
		'16' => 'Gunma',
		'17' => 'Yamanashi',
		'18' => 'Shizuoka',
		'19' => 'Gifu',
		'20' => 'Aichi',
		'21' => 'Mie',
		'22' => 'Kyoto',
		'23' => 'Shiga',
		'24' => 'Nara',
		'25' => 'Osaka',
		'26' => 'Wakayama',
		'27' => 'Hyogo',
		'28' => 'Toyama',
		'29' => 'Fukui',
		'30' => 'Ishikawa',
		'31' => 'Okayama',
		'32' => 'Shimane',
		'33' => 'Yamaguchi',
		'34' => 'Tottori',
		'35' => 'Hiroshima',
		'36' => 'Kagawa',
		'37' => 'Tokushima',
		'38' => 'Ehime',
		'39' => 'Kochi',
		'40' => 'Fukuoka',
		'41' => 'Saga',
		'42' => 'Nagasaki',
		'43' => 'Kumamoto',
		'44' => 'Oita',
		'45' => 'Miyazaki',
		'46' => 'Kagoshima',
		'47' => 'Okinawa',
	);

	function __construct() {
		$this->load->library('Genfunctions');
		$this->load->model('logbooks_model');
		$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));
		$this->location_list = "'" . implode("','", $logbooks_locations_array) . "'";
	}

	/**
	 * Get the display name for a Japanese prefecture code.
	 *
	 * @param string $prefecture_code The 2-digit prefecture code
	 * @return string The prefecture name
	 */
	protected function get_ja_prefecture_name($prefecture_code) {
		return $this->ja_prefectures[$prefecture_code] ?? $prefecture_code;
	}

	/**
	 * Load all 3 JSON files for AJA award (lazy, only on first call)
	 */
	private function load_aja_data() {
		if ($this->aja_cities !== null) return;
		$this->aja_cities = json_decode(file_get_contents(FCPATH . 'assets/json/japan_award/jcc_list.json'), true);
		$this->aja_kus = json_decode(file_get_contents(FCPATH . 'assets/json/japan_award/ku_list.json'), true);
		$this->aja_guns = json_decode(file_get_contents(FCPATH . 'assets/json/japan_award/jcg_list.json'), true);
	}

	/**
	 * Filters out entities(cities, guns or kus) that are marked as deleted
	 *
	 * @param array $entity_data The list of entities to filter
	 * @param array $postdata The postdata containing filter options
	 * @return array The filtered list of entities
	 */
	protected function filter_entity_data($entity_data, $postdata) {
		if (($postdata['includedeleted'] ?? null) != null) {
			return $entity_data;
		}

		return array_filter($entity_data, function ($entity) {
			return !(isset($entity['deleted']) && $entity['deleted'] == true);
		});
	}

	/**
	 * Build SQL expression for key_col based on band
	 *
	 * SAT is treated as a separate "band" in wavelog award system
	 *
	 * @return string The SQL expression for key_col
	 */
	protected function build_band_key_expr() {
		return "case
			when col_prop_mode = 'SAT' then 'SAT'
			else col_band
		end";
	}

	/**
	 * Build SQL expression for key_col based on mode
	 *
	 * Based on JARL supported mode endorsements
	 *
	 * @return string The SQL expression for key_col
	 */
	protected function build_mode_key_expr() {
		return "case
			when col_submode = 'DSTAR' then 'DSTAR'
			when col_mode in ('AM', 'FM', 'CW', 'SSB', 'ATV', 'FAX', 'SSTV', 'DIGITALVOICE') then col_mode
			else 'DIGITAL'
		end";
	}

	/**
	 * Build SQL condition for confirmation based on postdata
	 *  cond1 OR cond2 OR cond3 ...
	 *
	 * @param array $postdata The postdata containing filter options
	 * @return string The SQL condition expr
	 */
	protected function get_qsl_condition_sql($postdata) {
		$qsl = array();
		if (($postdata['qsl'] ?? null) == 1) {
			$qsl[] = "col_qsl_rcvd = 'Y'";
		}
		if (($postdata['lotw'] ?? null) == 1) {
			$qsl[] = "col_lotw_qsl_rcvd = 'Y'";
		}
		if (($postdata['eqsl'] ?? null) == 1) {
			$qsl[] = "col_eqsl_qsl_rcvd = 'Y'";
		}
		if (($postdata['qrz'] ?? null) == 1) {
			$qsl[] = "COL_QRZCOM_QSO_DOWNLOAD_STATUS = 'Y'";
		}
		if (($postdata['clublog'] ?? null) == 1) {
			$qsl[] = "COL_CLUBLOG_QSO_DOWNLOAD_STATUS = 'Y'";
		}

		return count($qsl) > 0 ? implode(' or ', $qsl) : '1=0';
	}

	/**
	 * Build SQL expression for confirmation based on postdata
	 * 	CASE WHEN (cond1 OR cond2 OR cond3 ...) THEN 1 ELSE 0 END
	 *
	 * @param array $postdata The postdata containing filter options
	 * @return string The SQL expression for confirmed
	 */
	protected function get_qsl_confirmed_expr($postdata) {
		return 'case when (' . $this->get_qsl_condition_sql($postdata) . ') then 1 else 0 end';
	}

	/**
	 * Build SQL for entity(cities, guns or kus) IN (list)
	 * 	id1, id2, id3 ...
	 *
	 * @param array $entity_data The list of entities to build IN clause for
	 * @return string The SQL string for IN clause
	 */
	protected function build_entity_in_list_sql($entity_data) {
		$keys = array_map(function ($key) {
			return $this->db->escape((string) $key);
		}, array_keys($entity_data));

		return implode(',', $keys);
	}

	/**
	 * Build SQL WHERE clause for entity query based on postdata
	 *
	 * @param string $entity_cond Full condition SQL for entity matching,
	 *   e.g. "col_dxcc in ('339') and col_cnty in (...)"
	 * @param array $postdata The postdata containing filter options
	 * @param bool $confirmed_only Whether to include only confirmed QSOs
	 * @return array ['sql' => string, 'bindings' => array]
	 */
	protected function build_entity_query_where_sql($entity_cond, $postdata, $confirmed_only = false) {
		$bindings = array();
		$band = $postdata['band'] ?? 'All';
		$mode = $postdata['mode'] ?? 'All';
		$prop_mode = $postdata['prop_mode'] ?? 'All';

		$where = array(
			"(" . $entity_cond . ")",
			"station_id in (" . $this->location_list . ")",
		);
		if ($band != 'All') {
			if ($band === 'SAT') {
				$where[] = "(col_prop_mode = ?)";
				$bindings[] = $band;
			} else {
				$where[] = "(col_band = ?)";
				$bindings[] = $band;
			}
		}
		if ($mode != 'All') {
			$where[] = "(col_mode = ? or col_submode = ?)";
			$bindings[] = $mode;
			$bindings[] = $mode;
		}
		if ($prop_mode != 'All') {
			$where[] = "(col_prop_mode = ?)";
			$bindings[] = $prop_mode;
		}
		if ($confirmed_only) {
			$where[] = '(' . $this->get_qsl_condition_sql($postdata) . ')';
		}

		return [
			'sql' => implode(" and ", $where),
			'bindings' => $bindings,
		];
	}

	/**
	 * Build the base SQL query for entity status
	 * The query turns eligible QSOs into rows of (entity, key_col and confirmed)
	 *
	 * @param string $entity_expr The SQL expression for entity, e.g. col_cnty or left(col_cnty, 4)
	 * @param string $entity_cond Full condition SQL for entity matching
	 * @param string $key_col The column to use as key_col: 'band', 'mode' or 'none'
	 * @param array $postdata The postdata containing filter options
	 * @return array ['sql' => string, 'bindings' => array]
	 */
	protected function build_entity_status_base_query($entity_expr, $entity_cond, $key_col, $postdata) {
		$confirmed_expr = $this->get_qsl_confirmed_expr($postdata);

		$select = array(
			$entity_expr . ' as entity',
			$confirmed_expr . ' as confirmed',
		);
		if ($key_col === 'band') {
			$select[] = $this->build_band_key_expr() . ' as key_col';
		} else if ($key_col === 'mode') {
			$select[] = $this->build_mode_key_expr() . ' as key_col';
		} else {
			$select[] = "'All' as key_col";
		}
		$select_str = implode(", ", $select);

		$from = $this->config->item('table_name') . " thcv";

		$where = $this->build_entity_query_where_sql($entity_cond, $postdata);

		return [
			'sql' => "select " . $select_str . " from " . $from . " where " . $where['sql'],
			'bindings' => $where['bindings'],
		];
	}

	/**
	 * Build SQL to group the base query by entity and key_col, and aggregate confirmed
	 *
	 * @param string $source_sql The SQL string for the source query
	 * @return string The SQL string for the grouped and aggregated query
	 */
	protected function build_entity_status_max_confirmed_group_by_sql($source_sql) {
		return "select entity, key_col, max(confirmed) as confirmed from (" . $source_sql . ") entity_status group by entity, key_col";
	}

	/**
	 * Build UNION ALL of N query pairs
	 *
	 * @param array $queries Array of ['sql' => string, 'bindings' => array]
	 * @return array ['sql' => string, 'bindings' => array]
	 */
	protected function build_union_all_sql($queries) {
		$sqls = array();
		$bindings = array();
		foreach ($queries as $q) {
			$sqls[] = $q['sql'];
			$bindings = array_merge($bindings, $q['bindings']);
		}
		return [
			'sql' => implode(" union all ", $sqls),
			'bindings' => $bindings,
		];
	}

	/**
	 * Query the entity status from query groups
	 * Return rows of (entity, key_col and confirmed)
	 * The row exists if the slot is worked, and confirmed is 1 if the slot is confirmed
	 *
	 * @param array $query_groups Array of ['entity_expr' => string, 'entity_cond' => string]
	 * @param string $key_col The column to use as key_col: 'band', 'mode' or 'none'
	 * @param array $postdata The postdata containing filter options
	 * @return array The result set as an array of rows
	 */
	function query_entity_status($query_groups, $key_col, $postdata) {
		$group_queries = array();
		foreach ($query_groups as $group) {
			$base = $this->build_entity_status_base_query(
				$group['entity_expr'], $group['entity_cond'], $key_col, $postdata
			);
			$group_sql = $this->build_entity_status_max_confirmed_group_by_sql($base['sql']);
			$group_queries[] = ['sql' => $group_sql, 'bindings' => $base['bindings']];
		}

		$union = $this->build_union_all_sql($group_queries);
		$final_sql = $this->build_entity_status_max_confirmed_group_by_sql($union['sql']);

		$query = $this->db->query($final_sql, $union['bindings']);
		return $query->result_array();
	}

	/**
	 * Build the base SQL query for exporting QSOs for entities
	 * The query selects eligible QSOs for further processing
	 *
	 * @param string $entity_expr The SQL expression for entity
	 * @param string $entity_cond Full condition SQL for entity matching
	 * @param string $key_col The column to use as key_col: 'band', 'mode' or 'none'
	 * @param array $postdata The postdata containing filter options
	 * @return array ['sql' => string, 'bindings' => array]
	 */
	protected function build_export_entity_source_query($entity_expr, $entity_cond, $key_col, $postdata) {
		$select = array(
			$entity_expr . ' as entity',
			'COL_PRIMARY_KEY',
			'COL_CALL',
			'COL_TIME_ON',
			'COL_BAND',
			'COL_MODE',
			'COL_PROP_MODE',
		);
		if ($key_col === 'band') {
			$select[] = $this->build_band_key_expr() . ' as key_col';
		} else if ($key_col === 'mode') {
			$select[] = $this->build_mode_key_expr() . ' as key_col';
		} else {
			$select[] = "'All' as key_col";
		}

		$select_str = implode(", ", $select);

		$from = $this->config->item('table_name') . " thcv";

		$where = $this->build_entity_query_where_sql($entity_cond, $postdata, true);

		return [
			'sql' => 'select ' . $select_str . ' from ' . $from . ' where ' . $where['sql'],
			'bindings' => $where['bindings'],
		];
	}

	/**
	 * Query export QSOs from query groups
	 * Return first confirmed QSO for each entity (+ key_col if applicable)
	 *
	 * @param array $query_groups Array of ['entity_expr' => string, 'entity_cond' => string]
	 * @param string $key_col The column to use as key_col: 'band', 'mode' or 'none'
	 * @param array $postdata The postdata containing filter options
	 * @return array The result set as an array of rows with QSO details
	 */
	function query_export_qsos($query_groups, $key_col, $postdata) {
		$source_queries = array();
		foreach ($query_groups as $group) {
			$source_queries[] = $this->build_export_entity_source_query(
				$group['entity_expr'], $group['entity_cond'], $key_col, $postdata
			);
		}

		$source = $this->build_union_all_sql($source_queries);

		$ranked_sql = 'select source.*, row_number() over (partition by entity, key_col order by COL_TIME_ON asc, COL_PRIMARY_KEY asc) as rn from (' . $source['sql'] . ') source';
		$final_sql = 'select entity, key_col, COL_CALL, COL_TIME_ON, COL_BAND, COL_MODE, COL_PROP_MODE from (' . $ranked_sql . ') ranked where rn = 1 order by entity asc, key_col asc';

		$query = $this->db->query($final_sql, $source['bindings']);
		return $query->result_array();
	}

	// ========== AJA Award Methods ==========

	/**
	 * Build query groups for AJA award (4 groups)
	 *
	 * @param array $postdata The postdata containing filter options
	 * @return array Array of query groups
	 */
	private function build_aja_query_groups($postdata) {
		$this->load_aja_data();

		$jcc_data = $this->filter_entity_data($this->aja_cities, $postdata);
		$ku_data = $this->filter_entity_data($this->aja_kus, $postdata);
		$jcg_data = $this->filter_entity_data($this->aja_guns, $postdata);

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
		$this->load_aja_data();
		$type = $this->get_entity_type($code);
		switch ($type) {
			case 'ku':
				return ['name' => $this->aja_kus[$code]['name'] ?? '', 'type' => 'ku'];
			case 'gun':
				return ['name' => $this->aja_guns[$code]['name'] ?? '', 'type' => 'gun'];
			default:
				return ['name' => $this->aja_cities[$code]['name'] ?? '', 'type' => 'city'];
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
		$this->load_aja_data();

		$jcc_data = $this->filter_entity_data($this->aja_cities, $postdata);
		$ku_data = $this->filter_entity_data($this->aja_kus, $postdata);
		$jcg_data = $this->filter_entity_data($this->aja_guns, $postdata);

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
