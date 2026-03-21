<?php

class JapanAwardEntity_model extends CI_Model {

	protected $location_list = null;
	protected $entityData = array();
	protected $entityConfig = array(
		'awardType' => '',
		'jsonPath' => '',
		'entityLabel' => 'Entity',
		'exportKey' => 'entity',
		'workedBandsKey' => 'jcc',
		'cntyPatternSql' => '',
		'dxccList' => array('339', '177', '192'),
	);

	function __construct() {
		$this->load->library('Genfunctions');
		$this->load->model('logbooks_model');
		$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));
		$this->location_list = "'" . implode("','", $logbooks_locations_array) . "'";

		$this->loadEntityDataFromJson();
	}

	protected function loadEntityDataFromJson() {
		$this->entityData = json_decode(file_get_contents(FCPATH . $this->entityConfig['jsonPath']), true);
		if (!is_array($this->entityData)) {
			$this->entityData = array();
		}
	}

	function get_entity_array($bands, $postdata) {
		$entityArray = array_keys($this->entityData);

		$entities = array();
		foreach ($entityArray as $entity) {
			$entities[$entity]['count'] = 0;
		}

		$qsl = $this->genfunctions->gen_qsl_from_postdata($postdata);

		foreach ($bands as $band) {
			foreach ($entityArray as $entity) {
				$bandEntity[$entity]['Number'] = $entity;
				$bandEntity[$entity][$this->entityConfig['entityLabel']] = $this->entityData[$entity]['name'];
				$bandEntity[$entity][$band] = '-';
			}

			if ($postdata['worked'] != NULL) {
				$entityBand = $this->getWorked($this->location_list, $band, $postdata);
				foreach ($entityBand as $line) {
					$bandEntity[$line->col_cnty][$band] = '<div class="bg-danger awardsBgWarning"><a href=\'javascript:displayContacts("' . $line->col_cnty . '","' . $band . '","All","All","' . $postdata['mode'] . '","' . $this->entityConfig['awardType'] . '", "")\'>W</a></div>';
					$entities[$line->col_cnty]['count']++;
				}
			}

			if ($postdata['confirmed'] != NULL) {
				$entityBand = $this->getConfirmed($this->location_list, $band, $postdata);
				foreach ($entityBand as $line) {
					$bandEntity[$line->col_cnty][$band] = '<div class="bg-success awardsBgSuccess"><a href=\'javascript:displayContacts("' . $line->col_cnty . '","' . $band . '","All","All","' . $postdata['mode'] . '","' . $this->entityConfig['awardType'] . '", "' . $qsl . '")\'>C</a></div>';
					$entities[$line->col_cnty]['count']++;
				}
			}
		}

		if ($postdata['worked'] == NULL) {
			$entityBand = $this->getWorked($this->location_list, $postdata['band'], $postdata);
			foreach ($entityBand as $line) {
				unset($bandEntity[$line->col_cnty]);
			}
		}

		if ($postdata['confirmed'] == NULL) {
			$entityBand = $this->getConfirmed($this->location_list, $postdata['band'], $postdata);
			foreach ($entityBand as $line) {
				unset($bandEntity[$line->col_cnty]);
			}
		}

		if ($postdata['notworked'] == NULL && isset($bandEntity)) {
			foreach ($entityArray as $entity) {
				if ($entities[$entity]['count'] == 0) {
					unset($bandEntity[$entity]);
				}
			}
		}

		if (isset($bandEntity)) {
			return $bandEntity;
		}

		return 0;
	}

	function getWorked($location_list, $band, $postdata) {
		$bindings = array();
		$sql = 'SELECT distinct col_cnty FROM ' . $this->config->item('table_name') . ' thcv where station_id in (' . $location_list . ')';

		if ($postdata['mode'] != 'All') {
			$sql .= ' and (col_mode = ? or col_submode = ?)';
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= $this->addStateToQuery();
		$sql .= $this->genfunctions->addBandToQuery($band, $bindings);
		$sql .= ' and not exists (select 1 from ' . $this->config->item('table_name') . ' where station_id in (' . $location_list . ') and col_cnty = thcv.col_cnty';

		if ($postdata['mode'] != 'All') {
			$sql .= ' and (col_mode = ? or col_submode = ?)';
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= $this->genfunctions->addBandToQuery($band, $bindings);
		$sql .= $this->genfunctions->addQslToQuery($postdata);
		$sql .= $this->addStateToQuery();
		$sql .= ')';

		$query = $this->db->query($sql, $bindings);
		return $query->result();
	}

	function getConfirmed($location_list, $band, $postdata) {
		$bindings = array();
		$sql = 'SELECT distinct col_cnty FROM ' . $this->config->item('table_name') . ' thcv where station_id in (' . $location_list . ')';

		if ($postdata['mode'] != 'All') {
			$sql .= ' and (col_mode = ? or col_submode = ?)';
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= $this->addStateToQuery();
		$sql .= $this->genfunctions->addBandToQuery($band, $bindings);
		$sql .= $this->genfunctions->addQslToQuery($postdata);

		$query = $this->db->query($sql, $bindings);
		return $query->result();
	}

	function get_entity_summary($bands, $postdata) {
		$summary = array('worked' => array(), 'confirmed' => array());

		foreach ($bands as $band) {
			if ($band != 'SAT') {
				$worked = $this->getSummaryByBand($band, $postdata, $this->location_list);
				$confirmed = $this->getSummaryByBandConfirmed($band, $postdata, $this->location_list);
				$summary['worked'][$band] = $worked[0]->count;
				$summary['confirmed'][$band] = $confirmed[0]->count;
			}
		}

		$workedTotal = $this->getSummaryByBand($postdata['band'], $postdata, $this->location_list);
		$confirmedTotal = $this->getSummaryByBandConfirmed($postdata['band'], $postdata, $this->location_list);
		$summary['worked']['Total'] = $workedTotal[0]->count;
		$summary['confirmed']['Total'] = $confirmedTotal[0]->count;

		if (in_array('SAT', $bands)) {
			$worked = $this->getSummaryByBand('SAT', $postdata, $this->location_list);
			$confirmed = $this->getSummaryByBandConfirmed('SAT', $postdata, $this->location_list);
			$summary['worked']['SAT'] = $worked[0]->count;
			$summary['confirmed']['SAT'] = $confirmed[0]->count;
		}

		return $summary;
	}

	function getSummaryByBand($band, $postdata, $location_list) {
		$bindings = array();
		$sql = 'SELECT count(distinct thcv.col_cnty) as count FROM ' . $this->config->item('table_name') . ' thcv';
		$sql .= ' where station_id in (' . $location_list . ')';

		if ($band == 'SAT') {
			$sql .= ' and thcv.col_prop_mode = ?';
			$bindings[] = $band;
		} else if ($band == 'All') {
			$this->load->model('bands');
			$bandslots = $this->bands->get_worked_bands($this->entityConfig['workedBandsKey']);

			if (empty($bandslots)) {
				$sql .= ' and 1 = 0';
			} else {
				$bandslots_list = "'" . implode("','", $bandslots) . "'";
				$sql .= ' and thcv.col_band in (' . $bandslots_list . ') and thcv.col_prop_mode != \'SAT\'';
			}
		} else {
			$sql .= ' and thcv.col_prop_mode != \'SAT\'';
			$sql .= ' and thcv.col_band = ?';
			$bindings[] = $band;
		}

		if ($postdata['mode'] != 'All') {
			$sql .= ' and (col_mode = ? or col_submode = ?)';
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= $this->addStateToQuery();
		$query = $this->db->query($sql, $bindings);
		return $query->result();
	}

	function getSummaryByBandConfirmed($band, $postdata, $location_list) {
		$bindings = array();
		$sql = 'SELECT count(distinct thcv.col_cnty) as count FROM ' . $this->config->item('table_name') . ' thcv';
		$sql .= ' where station_id in (' . $location_list . ')';

		if ($band == 'SAT') {
			$sql .= ' and thcv.col_prop_mode = ?';
			$bindings[] = $band;
		} else if ($band == 'All') {
			$this->load->model('bands');
			$bandslots = $this->bands->get_worked_bands($this->entityConfig['workedBandsKey']);

			if (empty($bandslots)) {
				$sql .= ' and 1 = 0';
			} else {
				$bandslots_list = "'" . implode("','", $bandslots) . "'";
				$sql .= ' and thcv.col_band in (' . $bandslots_list . ') and thcv.col_prop_mode != \'SAT\'';
			}
		} else {
			$sql .= ' and thcv.col_prop_mode != \'SAT\'';
			$sql .= ' and thcv.col_band = ?';
			$bindings[] = $band;
		}

		if ($postdata['mode'] != 'All') {
			$sql .= ' and (col_mode = ? or col_submode = ?)';
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= $this->genfunctions->addQslToQuery($postdata);
		$sql .= $this->addStateToQuery();
		$query = $this->db->query($sql, $bindings);
		return $query->result();
	}

	function addStateToQuery() {
		if (empty($this->entityData)) {
			return ' and 1 = 0';
		}

		$keys = array_map(function ($key) {
			return $this->db->escape((string) $key);
		}, array_keys($this->entityData));

		$dxccList = array_map(function ($dxcc) {
			return $this->db->escape((string) $dxcc);
		}, $this->entityConfig['dxccList']);

		$sql = '';
		$sql .= ' and COL_DXCC in (' . implode(',', $dxccList) . ')';
		$sql .= ' and ' . $this->entityConfig['cntyPatternSql'];
		$sql .= ' and COL_CNTY in (' . implode(',', $keys) . ')';
		return $sql;
	}

	function exportEntities($postdata) {
		$bindings = array();
		$sql = 'SELECT distinct col_cnty FROM ' . $this->config->item('table_name') . ' thcv where station_id in (' . $this->location_list . ')';

		if ($postdata['mode'] != 'All') {
			$sql .= ' and (col_mode = ? or col_submode = ?)';
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= $this->addStateToQuery();
		$sql .= $this->genfunctions->addBandToQuery($postdata['band'], $bindings);
		$sql .= $this->genfunctions->addQslToQuery($postdata);
		$sql .= ' ORDER BY COL_CNTY ASC';

		$query = $this->db->query($sql, $bindings);
		$entities = array();
		foreach ($query->result() as $line) {
			$entities[] = $line->col_cnty;
		}

		$qsos = array();
		foreach ($entities as $entityCode) {
			$qso = $this->getFirstQso($this->location_list, $entityCode, $postdata);
			if (!empty($qso)) {
				$qsos[] = array(
					'call' => $qso[0]->COL_CALL,
					'date' => $qso[0]->COL_TIME_ON,
					'band' => $qso[0]->COL_BAND,
					'mode' => $qso[0]->COL_MODE,
					'prop_mode' => $qso[0]->COL_PROP_MODE,
					'cnty' => $qso[0]->COL_CNTY,
					$this->entityConfig['exportKey'] => isset($this->entityData[$qso[0]->COL_CNTY]) ? $this->entityData[$qso[0]->COL_CNTY]['name'] : '',
				);
			}
		}

		return $qsos;
	}

	function getFirstQso($location_list, $entityCode, $postdata) {
		$bindings = array();
		$sql = 'SELECT COL_CNTY, COL_CALL, COL_TIME_ON, COL_BAND, COL_MODE, COL_PROP_MODE FROM ' . $this->config->item('table_name') . ' t1 WHERE station_id in (' . $location_list . ')';

		if ($postdata['mode'] != 'All') {
			$sql .= ' and (col_mode = ? or col_submode = ?)';
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= $this->addStateToQuery();
		$sql .= $this->genfunctions->addBandToQuery($postdata['band'], $bindings);
		$sql .= $this->genfunctions->addQslToQuery($postdata);
		$sql .= ' AND COL_CNTY = ?';
		$bindings[] = $entityCode;
		$sql .= ' ORDER BY COL_TIME_ON ASC LIMIT 1';

		$query = $this->db->query($sql, $bindings);
		return $query->result();
	}

	function fetch_entity_wkd($postdata) {
		$bindings = array();
		$sql = 'SELECT DISTINCT `COL_CNTY` FROM ' . $this->config->item('table_name') . ' WHERE 1 and station_id in (' . $this->location_list . ')';
		$sql .= $this->addStateToQuery();
		$sql .= $this->genfunctions->addBandToQuery($postdata['band'], $bindings);

		if ($postdata['mode'] != 'All') {
			$sql .= ' and (col_mode = ? or col_submode = ?)';
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= ' ORDER BY COL_CNTY ASC';
		$query = $this->db->query($sql, $bindings);
		return $query->result();
	}

	function fetch_entity_cnfm($postdata) {
		$bindings = array();
		$sql = 'SELECT DISTINCT `COL_CNTY` FROM ' . $this->config->item('table_name') . ' WHERE 1 and station_id in (' . $this->location_list . ')';
		$sql .= $this->addStateToQuery();
		$sql .= $this->genfunctions->addBandToQuery($postdata['band'], $bindings);

		if ($postdata['mode'] != 'All') {
			$sql .= ' and (col_mode = ? or col_submode = ?)';
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= $this->genfunctions->addQslToQuery($postdata);
		$sql .= ' ORDER BY COL_CNTY ASC';
		$query = $this->db->query($sql, $bindings);
		return $query->result();
	}
}

?>