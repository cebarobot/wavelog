<?php

class Jcg_model extends CI_Model {

	private $location_list = null;

	function __construct() {
		$this->load->library('Genfunctions');
		$this->load->model('logbooks_model');
		$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));
		$this->location_list = "'" . implode("','", $logbooks_locations_array) . "'";
        $this->loadJcgDataFromJson();
	}

    public $jaGuns = array();

    private function loadJcgDataFromJson() {
        $this->jaGuns = json_decode(file_get_contents(FCPATH . 'assets/json/japan_award/jcg_list.json'), true);
    }

	/*
	 * Build the JCG table dataset for the award page.
	 */
	function get_jcg_array($bands, $postdata) {

		$jcgArray = array_keys($this->jaGuns);

		$guns = array();
		foreach ($jcgArray as $gun) {
			$guns[$gun]['count'] = 0;
		}

		$qsl = $this->genfunctions->gen_qsl_from_postdata($postdata);

		foreach ($bands as $band) {
			foreach ($jcgArray as $gun) {
				$bandJcg[$gun]['Number'] = $gun;
				$bandJcg[$gun]['Gun'] = $this->jaGuns[$gun]['name'];
				$bandJcg[$gun][$band] = '-';
			}

			if ($postdata['worked'] != NULL) {
				$jcgBand = $this->getJcgWorked($this->location_list, $band, $postdata);
				foreach ($jcgBand as $line) {
					$bandJcg[$line->col_cnty][$band] = '<div class="bg-danger awardsBgWarning"><a href=\'javascript:displayContacts("' . $line->col_cnty . '","' . $band . '","All","All","' . $postdata['mode'] . '","JCG", "")\'>W</a></div>';
					$guns[$line->col_cnty]['count']++;
				}
			}
			if ($postdata['confirmed'] != NULL) {
				$jcgBand = $this->getJcgConfirmed($this->location_list, $band, $postdata);
				foreach ($jcgBand as $line) {
					$bandJcg[$line->col_cnty][$band] = '<div class="bg-success awardsBgSuccess"><a href=\'javascript:displayContacts("' . $line->col_cnty . '","' . $band . '","All","All","' . $postdata['mode'] . '","JCG", "' . $qsl . '")\'>C</a></div>';
					$guns[$line->col_cnty]['count']++;
				}
			}
		}

		// Remove worked guns when user does not want worked entities.
		if ($postdata['worked'] == NULL) {
			$jcgBand = $this->getJcgWorked($this->location_list, $postdata['band'], $postdata);
			foreach ($jcgBand as $line) {
				unset($bandJcg[$line->col_cnty]);
			}
		}

		// Remove confirmed guns when user does not want confirmed entities.
		if ($postdata['confirmed'] == NULL) {
			$jcgBand = $this->getJcgConfirmed($this->location_list, $postdata['band'], $postdata);
			foreach ($jcgBand as $line) {
				unset($bandJcg[$line->col_cnty]);
			}
		}

		if ($postdata['notworked'] == NULL) {
			if (isset($bandJcg)) {
				foreach ($jcgArray as $gun) {
					if ($guns[$gun]['count'] == 0) {
						unset($bandJcg[$gun]);
					}
				}
			}
		}

		if (isset($bandJcg)) {
			return $bandJcg;
		} else {
			return 0;
		}
	}

	/*
	 * Function returns all worked, but not confirmed guns.
	 * $postdata contains data from the form, e.g. LoTW/QSL confirmation filters.
	 */
	function getJcgWorked($location_list, $band, $postdata) {
		$bindings = array();
		$sql = "SELECT distinct col_cnty FROM " . $this->config->item('table_name') . " thcv
			where station_id in (" . $location_list . ")";

		if ($postdata['mode'] != 'All') {
			$sql .= " and (col_mode = ? or col_submode = ?)";
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= $this->addStateToQuery();
		$sql .= $this->genfunctions->addBandToQuery($band, $bindings);
		$sql .= " and not exists (select 1 from " . $this->config->item('table_name') .
			" where station_id in (" . $location_list . ")" .
			" and col_cnty = thcv.col_cnty";

		if ($postdata['mode'] != 'All') {
			$sql .= " and (col_mode = ? or col_submode = ?)";
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= $this->genfunctions->addBandToQuery($band, $bindings);
		$sql .= $this->genfunctions->addQslToQuery($postdata);
		$sql .= $this->addStateToQuery();
		$sql .= ")";

		$query = $this->db->query($sql, $bindings);
		return $query->result();
	}

	/*
	 * Function returns all confirmed guns on given band and confirmation source.
	 */
	function getJcgConfirmed($location_list, $band, $postdata) {
		$bindings = array();
		$sql = "SELECT distinct col_cnty FROM " . $this->config->item('table_name') . " thcv
			where station_id in (" . $location_list . ")";

		if ($postdata['mode'] != 'All') {
			$sql .= " and (col_mode = ? or col_submode = ?)";
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= $this->addStateToQuery();
		$sql .= $this->genfunctions->addBandToQuery($band, $bindings);
		$sql .= $this->genfunctions->addQslToQuery($postdata);

		$query = $this->db->query($sql, $bindings);
		return $query->result();
	}

	/*
	 * Function gets worked and confirmed summary on each band
	 * for the active station profile.
	 */
	function get_jcg_summary($bands, $postdata) {
		foreach ($bands as $band) {
			if ($band != 'SAT') {
				$worked = $this->getSummaryByBand($band, $postdata, $this->location_list);
				$confirmed = $this->getSummaryByBandConfirmed($band, $postdata, $this->location_list);
				$jcgSummary['worked'][$band] = $worked[0]->count;
				$jcgSummary['confirmed'][$band] = $confirmed[0]->count;
			}
		}

		$workedTotal = $this->getSummaryByBand($postdata['band'], $postdata, $this->location_list);
		$confirmedTotal = $this->getSummaryByBandConfirmed($postdata['band'], $postdata, $this->location_list);

		$jcgSummary['worked']['Total'] = $workedTotal[0]->count;
		$jcgSummary['confirmed']['Total'] = $confirmedTotal[0]->count;

		if (in_array('SAT', $bands)) {
			$worked = $this->getSummaryByBand('SAT', $postdata, $this->location_list);
			$confirmed = $this->getSummaryByBandConfirmed('SAT', $postdata, $this->location_list);
			$jcgSummary['worked']['SAT'] = $worked[0]->count;
			$jcgSummary['confirmed']['SAT'] = $confirmed[0]->count;
		}

		return $jcgSummary;
	}

	function getSummaryByBand($band, $postdata, $location_list) {
		$bindings = array();
		$sql = "SELECT count(distinct thcv.col_cnty) as count FROM " . $this->config->item('table_name') . " thcv";
		$sql .= " where station_id in (" . $location_list . ")";

		if ($band == 'SAT') {
			$sql .= " and thcv.col_prop_mode = ?";
			$bindings[] = $band;
		} else if ($band == 'All') {
			$this->load->model('bands');
			$bandslots = $this->bands->get_worked_bands('jcg');
			$bandslots_list = "'" . implode("','", $bandslots) . "'";
			$sql .= " and thcv.col_band in (" . $bandslots_list . ") and thcv.col_prop_mode != 'SAT'";
		} else {
			$sql .= " and thcv.col_prop_mode != 'SAT'";
			$sql .= " and thcv.col_band = ?";
			$bindings[] = $band;
		}

		if ($postdata['mode'] != 'All') {
			$sql .= " and (col_mode = ? or col_submode = ?)";
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= $this->addStateToQuery();
		$query = $this->db->query($sql, $bindings);
		return $query->result();
	}

	function getSummaryByBandConfirmed($band, $postdata, $location_list) {
		$bindings = array();
		$sql = "SELECT count(distinct thcv.col_cnty) as count FROM " . $this->config->item('table_name') . " thcv";
		$sql .= " where station_id in (" . $location_list . ")";

		if ($band == 'SAT') {
			$sql .= " and thcv.col_prop_mode = ?";
			$bindings[] = $band;
		} else if ($band == 'All') {
			$this->load->model('bands');
			$bandslots = $this->bands->get_worked_bands('jcg');
			$bandslots_list = "'" . implode("','", $bandslots) . "'";
			$sql .= " and thcv.col_band in (" . $bandslots_list . ") and thcv.col_prop_mode != 'SAT'";
		} else {
			$sql .= " and thcv.col_prop_mode != 'SAT'";
			$sql .= " and thcv.col_band = ?";
			$bindings[] = $band;
		}

		if ($postdata['mode'] != 'All') {
			$sql .= " and (col_mode = ? or col_submode = ?)";
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= $this->genfunctions->addQslToQuery($postdata);
		$sql .= $this->addStateToQuery();
		$query = $this->db->query($sql, $bindings);
		return $query->result();
	}

	/*
	 * Restrict queries to valid Japan/JCG entities only.
	 */
	function addStateToQuery() {
		$keys = array_map(function ($key) {
			return $this->db->escape((string) $key);
		}, array_keys($this->jaGuns));

		$sql = '';
		$sql .= " and COL_DXCC in ('339', '177', '192')";   // Japan, Minami Torishima, Ogasawara
		$sql .= " and COL_CNTY LIKE '_____'";
		$sql .= " and COL_CNTY in (" . implode(',', $keys) . ")";
		return $sql;
	}

	/*
	 * Export first matching QSO for each qualified JCG entity.
	 */
	function exportJcg($postdata) {
		$bindings = array();
		$sql = "SELECT distinct col_cnty FROM " . $this->config->item('table_name') . " thcv
			where station_id in (" . $this->location_list . ")";

		if ($postdata['mode'] != 'All') {
			$sql .= " and (col_mode = ? or col_submode = ?)";
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= $this->addStateToQuery();
		$sql .= $this->genfunctions->addBandToQuery($postdata['band'], $bindings);
		$sql .= $this->genfunctions->addQslToQuery($postdata);
		$sql .= ' ORDER BY COL_CNTY ASC';

		$query = $this->db->query($sql, $bindings);

		$jcgs = array();
		foreach ($query->result() as $line) {
			$jcgs[] = $line->col_cnty;
		}

		$qsos = array();
		foreach ($jcgs as $jcg) {
			$qso = $this->getFirstQso($this->location_list, $jcg, $postdata);
			if (!empty($qso)) {
				$qsos[] = array(
					'call' => $qso[0]->COL_CALL,
					'date' => $qso[0]->COL_TIME_ON,
					'band' => $qso[0]->COL_BAND,
					'mode' => $qso[0]->COL_MODE,
					'prop_mode' => $qso[0]->COL_PROP_MODE,
					'cnty' => $qso[0]->COL_CNTY,
					'jcg' => isset($this->jaGuns[$qso[0]->COL_CNTY]) ? $this->jaGuns[$qso[0]->COL_CNTY]['name'] : ''
				);
			}
		}

		return $qsos;
	}

	/*
	 * Get first QSO that matches the provided JCG code and filters.
	 */
	function getFirstQso($location_list, $jcg, $postdata) {
		$bindings = array();
		$sql = 'SELECT COL_CNTY, COL_CALL, COL_TIME_ON, COL_BAND, COL_MODE, COL_PROP_MODE FROM ' . $this->config->item('table_name') . ' t1
			WHERE station_id in (' . $location_list . ')';
		if ($postdata['mode'] != 'All') {
			$sql .= " and (col_mode = ? or col_submode = ?)";
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}
		$sql .= $this->addStateToQuery();
		$sql .= $this->genfunctions->addBandToQuery($postdata['band'], $bindings);
		$sql .= $this->genfunctions->addQslToQuery($postdata);
		$sql .= ' AND COL_CNTY = ?';
		$bindings[] = $jcg;
		$sql .= ' ORDER BY COL_TIME_ON ASC LIMIT 1';
		$query = $this->db->query($sql, $bindings);
		return $query->result();
	}

	/*
	 * Fetch worked JCG codes for map rendering.
	 */
	function fetch_jcg_wkd($postdata) {
		$bindings = array();
		$sql = 'SELECT DISTINCT `COL_CNTY` FROM ' . $this->config->item('table_name') . ' WHERE 1
			and station_id in (' . $this->location_list . ')';
		$sql .= $this->addStateToQuery();
		$sql .= $this->genfunctions->addBandToQuery($postdata['band'], $bindings);
		if ($postdata['mode'] != 'All') {
			$sql .= " and (col_mode = ? or col_submode = ?)";
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}
		$sql .= ' ORDER BY COL_CNTY ASC';
		$query = $this->db->query($sql, $bindings);
		return $query->result();
	}

	/*
	 * Fetch confirmed JCG codes for map rendering.
	 */
	function fetch_jcg_cnfm($postdata) {
		$bindings = array();
		$sql = 'SELECT DISTINCT `COL_CNTY` FROM ' . $this->config->item('table_name') . ' WHERE 1
			and station_id in (' . $this->location_list . ')';
		$sql .= $this->addStateToQuery();
		$sql .= $this->genfunctions->addBandToQuery($postdata['band'], $bindings);
		if ($postdata['mode'] != 'All') {
			$sql .= " and (col_mode = ? or col_submode = ?)";
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
