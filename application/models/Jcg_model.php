<?php

class Jcg_model extends CI_Model {

	private $location_list = null;

	function __construct() {
		$this->load->library('Genfunctions');
		$this->load->model('logbooks_model');
		$logbooks_locations_array = $this->logbooks_model->list_logbook_relationships($this->session->userdata('active_station_logbook'));
		$this->location_list = "'" . implode("','", $logbooks_locations_array) . "'";
	}

	// TODO: Fill JCG master data.
	public $jaGuns = array(
        '01001' => array('name' => 'Akan', 'lat' => 43.230150, 'lon' => 144.321125, 'deleted' => false),
        '01002' => array('name' => 'Ashoro', 'lat' => 43.415556, 'lon' => 143.618333, 'deleted' => false),
        '01003' => array('name' => 'Atsukeshi', 'lat' => 43.135743, 'lon' => 144.916261, 'deleted' => false),
        '01004' => array('name' => 'Atsuta', 'lat' => 43.400139, 'lon' => 141.434194, 'deleted' => true),
        '01005' => array('name' => 'Abashiri', 'lat' => 43.730000, 'lon' => 144.130000, 'deleted' => false),
        '01006' => array('name' => 'Abuta(Shiribeshi)', 'lat' => 42.810111, 'lon' => 140.824125, 'deleted' => false),
        '01007' => array('name' => 'Abuta(Iburi)', 'lat' => 42.567292, 'lon' => 140.738014, 'deleted' => false),
        '01008' => array('name' => 'Ishikari', 'lat' => 43.381000, 'lon' => 141.567000, 'deleted' => false),
        '01009' => array('name' => 'Isoya', 'lat' => 42.835000, 'lon' => 140.468056, 'deleted' => false),
        '01010' => array('name' => 'Iwanai', 'lat' => 42.993056, 'lon' => 140.615000, 'deleted' => false),
        '01011' => array('name' => 'Usu', 'lat' => 42.573000, 'lon' => 140.946000, 'deleted' => false),
        '01012' => array('name' => 'Utasutsu', 'lat' => 42.725042, 'lon' => 140.341028, 'deleted' => true),
        '01013' => array('name' => 'Urakawa', 'lat' => 42.304610, 'lon' => 142.880210, 'deleted' => false),
        '01014' => array('name' => 'Uryu(Sorachi)', 'lat' => 43.729811, 'lon' => 141.924128, 'deleted' => false),
        '01015' => array('name' => 'Esashi', 'lat' => 44.865969, 'lon' => 142.435189, 'deleted' => false),
        '01016' => array('name' => 'Okushiri', 'lat' => 42.166000, 'lon' => 139.463000, 'deleted' => false),
        '01017' => array('name' => 'Oshoro', 'lat' => 43.209306, 'lon' => 140.921889, 'deleted' => true),
        '01018' => array('name' => 'Kasai', 'lat' => 42.781000, 'lon' => 142.930000, 'deleted' => false),
        '01019' => array('name' => 'Kato', 'lat' => 43.237000, 'lon' => 143.170000, 'deleted' => false),
        '01020' => array('name' => 'Kabato', 'lat' => 43.576000, 'lon' => 141.754000, 'deleted' => false),
        '01021' => array('name' => 'Kamiiso', 'lat' => 41.681000, 'lon' => 140.383000, 'deleted' => false),
        '01022' => array('name' => 'Kamikawa(Tokachi)', 'lat' => 43.012000, 'lon' => 142.866000, 'deleted' => false),
        '01023' => array('name' => 'Kamikawa(Kamikawa)', 'lat' => 43.632150, 'lon' => 142.930500, 'deleted' => false),
        '01024' => array('name' => 'Kameda', 'lat' => 41.953056, 'lon' => 140.705000, 'deleted' => false),
        '01025' => array('name' => 'Kayabe', 'lat' => 42.069000, 'lon' => 140.622000, 'deleted' => false),
        '01026' => array('name' => 'Kawakami', 'lat' => 43.425000, 'lon' => 144.530000, 'deleted' => false),
        '01027' => array('name' => 'Kushiro', 'lat' => 43.029000, 'lon' => 144.502000, 'deleted' => false),
        '01028' => array('name' => 'Kudo', 'lat' => 42.246944, 'lon' => 139.986944, 'deleted' => false),
        '01029' => array('name' => 'Sapporo', 'lat' => 42.985500, 'lon' => 141.563083, 'deleted' => true),
        '01030' => array('name' => 'Samani', 'lat' => 42.181000, 'lon' => 143.028000, 'deleted' => false),
        '01031' => array('name' => 'Saru', 'lat' => 42.532700, 'lon' => 142.102000, 'deleted' => false),
        '01032' => array('name' => 'Shizunai', 'lat' => 42.341278, 'lon' => 142.368583, 'deleted' => true),
        '01033' => array('name' => 'Shibetsu', 'lat' => 43.650000, 'lon' => 144.840000, 'deleted' => false),
        '01034' => array('name' => 'Shimamaki', 'lat' => 42.633056, 'lon' => 140.030000, 'deleted' => false),
        '01035' => array('name' => 'Shakotan', 'lat' => 43.297000, 'lon' => 140.466000, 'deleted' => false),
        '01036' => array('name' => 'Shari', 'lat' => 43.888056, 'lon' => 144.780000, 'deleted' => false),
        '01037' => array('name' => 'Shiraoi', 'lat' => 42.550000, 'lon' => 141.250000, 'deleted' => false),
        '01038' => array('name' => 'Shiranuka', 'lat' => 43.159000, 'lon' => 143.918000, 'deleted' => false),
        '01039' => array('name' => 'Suttsu', 'lat' => 42.702000, 'lon' => 140.327000, 'deleted' => false),
        '01040' => array('name' => 'Setana', 'lat' => 42.429413, 'lon' => 140.008639, 'deleted' => false),
        '01041' => array('name' => 'Soya', 'lat' => 45.240830, 'lon' => 142.121140, 'deleted' => false),
        '01042' => array('name' => 'Sorachi(Sorachi)', 'lat' => 43.323880, 'lon' => 141.839037, 'deleted' => false),
        '01043' => array('name' => 'Sorachi(Kamikawa)', 'lat' => 43.341778, 'lon' => 142.486907, 'deleted' => false),
        '01044' => array('name' => 'Chitose', 'lat' => 42.866670, 'lon' => 141.633330, 'deleted' => true),
        '01045' => array('name' => 'Teshio(Rumoi)', 'lat' => 44.805319, 'lon' => 141.768792, 'deleted' => false),
        '01046' => array('name' => 'Teshio(Soya)', 'lat' => 45.060431, 'lon' => 141.813597, 'deleted' => false),
        '01047' => array('name' => 'Tokachi', 'lat' => 42.915000, 'lon' => 143.691000, 'deleted' => false),
        '01048' => array('name' => 'Tokoro', 'lat' => 44.030000, 'lon' => 143.830000, 'deleted' => false),
        '01049' => array('name' => 'Tomamae', 'lat' => 44.310000, 'lon' => 141.870000, 'deleted' => false),
        '01050' => array('name' => 'Nakagawa(Kamikawa)', 'lat' => 44.483333, 'lon' => 142.350000, 'deleted' => false),
        '01051' => array('name' => 'Nakagawa(Tokachi)', 'lat' => 42.894255, 'lon' => 143.433720, 'deleted' => false),
        '01052' => array('name' => 'Niikappu', 'lat' => 42.569000, 'lon' => 142.509000, 'deleted' => false),
        '01053' => array('name' => 'Nishi', 'lat' => 42.022000, 'lon' => 140.186000, 'deleted' => false),
        '01054' => array('name' => 'Nemuro', 'lat' => 43.281056, 'lon' => 145.420306, 'deleted' => true),
        '01055' => array('name' => 'Notsuke', 'lat' => 43.395000, 'lon' => 145.050000, 'deleted' => false),
        '01056' => array('name' => 'Hanasaki', 'lat' => 43.343583, 'lon' => 145.754250, 'deleted' => true),
        '01057' => array('name' => 'Hamamasu', 'lat' => 43.600639, 'lon' => 141.386750, 'deleted' => true),
        '01058' => array('name' => 'Bikuni', 'lat' => 43.298722, 'lon' => 140.598000, 'deleted' => true),
        '01059' => array('name' => 'Hiyama', 'lat' => 41.429000, 'lon' => 140.286000, 'deleted' => false),
        '01060' => array('name' => 'Hiroo', 'lat' => 42.470000, 'lon' => 143.170000, 'deleted' => false),
        '01061' => array('name' => 'Futoro', 'lat' => 42.358222, 'lon' => 139.906944, 'deleted' => true),
        '01062' => array('name' => 'Furuu', 'lat' => 43.160151, 'lon' => 140.456055, 'deleted' => false),
        '01063' => array('name' => 'Furubira', 'lat' => 43.266667, 'lon' => 140.633333, 'deleted' => false),
        '01064' => array('name' => 'Horoizumi', 'lat' => 42.074000, 'lon' => 143.226000, 'deleted' => false),
        '01065' => array('name' => 'Horobetsu', 'lat' => 42.439153, 'lon' => 141.091208, 'deleted' => true),
        '01066' => array('name' => 'Mashike', 'lat' => 43.793056, 'lon' => 141.523056, 'deleted' => false),
        '01067' => array('name' => 'Matsumae', 'lat' => 41.521000, 'lon' => 140.150000, 'deleted' => false),
        '01068' => array('name' => 'Mitsuishi', 'lat' => 42.248333, 'lon' => 142.560306, 'deleted' => true),
        '01069' => array('name' => 'Menashi', 'lat' => 44.023000, 'lon' => 145.142000, 'deleted' => false),
        '01070' => array('name' => 'Mombetsu', 'lat' => 44.390000, 'lon' => 143.190000, 'deleted' => false),
        '01071' => array('name' => 'Yamakoshi', 'lat' => 42.528056, 'lon' => 140.323056, 'deleted' => false),
        '01072' => array('name' => 'Yubari', 'lat' => 43.000000, 'lon' => 141.759000, 'deleted' => false),
        '01073' => array('name' => 'Yufutsu(Iburi)', 'lat' => 42.687083, 'lon' => 141.874241, 'deleted' => false),
        '01074' => array('name' => 'Yufutsu(Kamikawa)', 'lat' => 42.979833, 'lon' => 142.398389, 'deleted' => false),
        '01075' => array('name' => 'Yoichi', 'lat' => 43.117000, 'lon' => 140.798000, 'deleted' => false),
        '01076' => array('name' => 'Rishiri', 'lat' => 45.180000, 'lon' => 141.240000, 'deleted' => false),
        '01077' => array('name' => 'Rumoi', 'lat' => 44.037000, 'lon' => 141.832000, 'deleted' => false),
        '01078' => array('name' => 'Rebun', 'lat' => 45.387000, 'lon' => 141.018000, 'deleted' => false),
        '01079' => array('name' => 'Futami', 'lat' => 42.250000, 'lon' => 140.266667, 'deleted' => false),
        '01080' => array('name' => 'Hidaka', 'lat' => 42.438000, 'lon' => 142.637000, 'deleted' => false),
        '01081' => array('name' => 'Uryu(Kamikawa)', 'lat' => 44.009917, 'lon' => 142.153944, 'deleted' => false),
        '02001' => array('name' => 'Kamikita', 'lat' => 40.773889, 'lon' => 141.260833, 'deleted' => false),
        '02002' => array('name' => 'Kitatsugaru', 'lat' => 40.984000, 'lon' => 140.450000, 'deleted' => false),
        '02003' => array('name' => 'Sannohe', 'lat' => 40.436944, 'lon' => 141.321389, 'deleted' => false),
        '02004' => array('name' => 'Shimokita', 'lat' => 41.283333, 'lon' => 141.216667, 'deleted' => false),
        '02005' => array('name' => 'Nakatsugaru', 'lat' => 40.515000, 'lon' => 140.255000, 'deleted' => false),
        '02006' => array('name' => 'Nishitsugaru', 'lat' => 40.611000, 'lon' => 140.056000, 'deleted' => false),
        '02007' => array('name' => 'Higashitsugaru', 'lat' => 41.127000, 'lon' => 140.542000, 'deleted' => false),
        '02008' => array('name' => 'Minamitsugaru', 'lat' => 40.475000, 'lon' => 140.548056, 'deleted' => false),
        '03001' => array('name' => 'Isawa', 'lat' => 39.201000, 'lon' => 141.007000, 'deleted' => false),
        '03002' => array('name' => 'Iwate', 'lat' => 39.810278, 'lon' => 141.122778, 'deleted' => false),
        '03003' => array('name' => 'Esashi', 'lat' => 39.191472, 'lon' => 141.173611, 'deleted' => true),
        '03004' => array('name' => 'Kamihei', 'lat' => 39.431000, 'lon' => 141.808000, 'deleted' => false),
        '03005' => array('name' => 'Kunohe', 'lat' => 40.300000, 'lon' => 141.560000, 'deleted' => false),
        '03006' => array('name' => 'Kesen', 'lat' => 39.176000, 'lon' => 141.535000, 'deleted' => false),
        '03007' => array('name' => 'Shimohei', 'lat' => 39.866667, 'lon' => 141.783333, 'deleted' => false),
        '03008' => array('name' => 'Shiwa', 'lat' => 39.567000, 'lon' => 141.133000, 'deleted' => false),
        '03009' => array('name' => 'Nishiiwai', 'lat' => 38.990248, 'lon' => 141.093708, 'deleted' => false),
        '03010' => array('name' => 'Ninohe', 'lat' => 40.266700, 'lon' => 141.267000, 'deleted' => false),
        '03011' => array('name' => 'Hienuki', 'lat' => 39.477667, 'lon' => 141.195556, 'deleted' => true),
        '03012' => array('name' => 'Higashiiwai', 'lat' => 38.934722, 'lon' => 141.126667, 'deleted' => true),
        '03013' => array('name' => 'Waga', 'lat' => 39.437000, 'lon' => 140.776000, 'deleted' => false),
        '04001' => array('name' => 'Ogachi', 'lat' => 39.181000, 'lon' => 140.404000, 'deleted' => false),
        '04002' => array('name' => 'Kazuno', 'lat' => 40.429000, 'lon' => 140.796000, 'deleted' => false),
        '04003' => array('name' => 'Kawabe', 'lat' => 39.652778, 'lon' => 140.215278, 'deleted' => true),
        '04004' => array('name' => 'Kitaakita', 'lat' => 39.960000, 'lon' => 140.321000, 'deleted' => false),
        '04005' => array('name' => 'Semboku', 'lat' => 39.433000, 'lon' => 140.597000, 'deleted' => false),
        '04006' => array('name' => 'Hiraka', 'lat' => 39.311389, 'lon' => 140.553333, 'deleted' => true),
        '04007' => array('name' => 'Minamiakita', 'lat' => 39.943000, 'lon' => 140.139000, 'deleted' => false),
        '04008' => array('name' => 'Yamamoto', 'lat' => 40.284000, 'lon' => 140.260000, 'deleted' => false),
        '04009' => array('name' => 'Yuri', 'lat' => 39.385000, 'lon' => 140.048000, 'deleted' => true),
        '05001' => array('name' => 'Akumi', 'lat' => 39.016667, 'lon' => 139.900000, 'deleted' => false),
        '05002' => array('name' => 'Kitamurayama', 'lat' => 38.604000, 'lon' => 140.332000, 'deleted' => false),
        '05003' => array('name' => 'Nishiokitama', 'lat' => 38.032990, 'lon' => 139.845540, 'deleted' => false),
        '05004' => array('name' => 'Nishitagawa', 'lat' => 38.620889, 'lon' => 139.586972, 'deleted' => true),
        '05005' => array('name' => 'Nishimurayama', 'lat' => 38.384000, 'lon' => 140.045000, 'deleted' => false),
        '05006' => array('name' => 'Higashiokitama', 'lat' => 38.012000, 'lon' => 140.084000, 'deleted' => false),
        '05007' => array('name' => 'Higashitagawa', 'lat' => 38.785000, 'lon' => 139.966000, 'deleted' => false),
        '05008' => array('name' => 'Higashimurayama', 'lat' => 38.294700, 'lon' => 140.230760, 'deleted' => false),
        '05009' => array('name' => 'Minamiokitama', 'lat' => 37.919111, 'lon' => 139.881194, 'deleted' => true),
        '05010' => array('name' => 'Minamimurayama', 'lat' => 38.185639, 'lon' => 140.224722, 'deleted' => true),
        '05011' => array('name' => 'Mogami', 'lat' => 38.925000, 'lon' => 140.260000, 'deleted' => false),
        '06001' => array('name' => 'Igu', 'lat' => 37.878056, 'lon' => 140.765000, 'deleted' => false),
        '06002' => array('name' => 'Oshika', 'lat' => 38.453056, 'lon' => 141.445000, 'deleted' => false),
        '06003' => array('name' => 'Katta', 'lat' => 38.055000, 'lon' => 140.437000, 'deleted' => false),
        '06004' => array('name' => 'Kami', 'lat' => 38.587000, 'lon' => 140.700000, 'deleted' => false),
        '06005' => array('name' => 'Kurihara', 'lat' => 38.741667, 'lon' => 141.017250, 'deleted' => true),
        '06006' => array('name' => 'Kurokawa', 'lat' => 38.442000, 'lon' => 140.876000, 'deleted' => false),
        '06007' => array('name' => 'Shida', 'lat' => 38.504259, 'lon' => 141.031870, 'deleted' => true),
        '06008' => array('name' => 'Shibata', 'lat' => 38.186000, 'lon' => 140.647000, 'deleted' => false),
        '06009' => array('name' => 'Tamatsukuri', 'lat' => 38.698417, 'lon' => 140.797708, 'deleted' => true),
        '06010' => array('name' => 'Toda', 'lat' => 38.537000, 'lon' => 141.137000, 'deleted' => false),
        '06011' => array('name' => 'Tome', 'lat' => 38.679705, 'lon' => 141.224292, 'deleted' => true),
        '06012' => array('name' => 'Natori', 'lat' => 38.258389, 'lon' => 140.671361, 'deleted' => true),
        '06013' => array('name' => 'Miyagi', 'lat' => 38.372000, 'lon' => 141.036000, 'deleted' => false),
        '06014' => array('name' => 'Motoyoshi', 'lat' => 38.696000, 'lon' => 141.452000, 'deleted' => false),
        '06015' => array('name' => 'Monou', 'lat' => 38.492257, 'lon' => 141.282736, 'deleted' => true),
        '06016' => array('name' => 'Watari', 'lat' => 38.000000, 'lon' => 140.882000, 'deleted' => false),
        '07001' => array('name' => 'Asaka', 'lat' => 37.420151, 'lon' => 140.305784, 'deleted' => true),
        '07002' => array('name' => 'Adachi', 'lat' => 37.583330, 'lon' => 140.416670, 'deleted' => false),
        '07003' => array('name' => 'Ishikawa', 'lat' => 37.132000, 'lon' => 140.556000, 'deleted' => false),
        '07004' => array('name' => 'Ishiki', 'lat' => 37.087365, 'lon' => 140.798413, 'deleted' => true),
        '07005' => array('name' => 'Iwase', 'lat' => 37.288056, 'lon' => 140.075000, 'deleted' => false),
        '07006' => array('name' => 'Onuma', 'lat' => 37.448000, 'lon' => 139.544000, 'deleted' => false),
        '07007' => array('name' => 'Kawanuma', 'lat' => 37.550000, 'lon' => 139.754000, 'deleted' => false),
        '07008' => array('name' => 'Kitaaizu', 'lat' => 37.491556, 'lon' => 139.874083, 'deleted' => true),
        '07009' => array('name' => 'Shinobu', 'lat' => 37.758667, 'lon' => 140.396222, 'deleted' => true),
        '07010' => array('name' => 'Soma', 'lat' => 37.714000, 'lon' => 140.784000, 'deleted' => false),
        '07011' => array('name' => 'Date', 'lat' => 37.713360, 'lon' => 140.565717, 'deleted' => false),
        '07012' => array('name' => 'Tamura', 'lat' => 37.346000, 'lon' => 140.564000, 'deleted' => false),
        '07013' => array('name' => 'Nishishirakawa', 'lat' => 37.159000, 'lon' => 140.122000, 'deleted' => false),
        '07014' => array('name' => 'Higashishirakawa', 'lat' => 36.954000, 'lon' => 140.472000, 'deleted' => false),
        '07015' => array('name' => 'Futaba', 'lat' => 37.447222, 'lon' => 141.005556, 'deleted' => false),
        '07016' => array('name' => 'Minamiaizu', 'lat' => 37.156000, 'lon' => 139.440000, 'deleted' => false),
        '07017' => array('name' => 'Yama', 'lat' => 37.652000, 'lon' => 140.040000, 'deleted' => false),
        '08001' => array('name' => 'Iwafune', 'lat' => 38.061000, 'lon' => 139.600000, 'deleted' => false),
        '08002' => array('name' => 'Kariwa', 'lat' => 37.420300, 'lon' => 138.644000, 'deleted' => false),
        '08003' => array('name' => 'Kitauonuma', 'lat' => 37.436472, 'lon' => 138.838861, 'deleted' => true),
        '08004' => array('name' => 'Kitakambara', 'lat' => 37.981000, 'lon' => 139.264000, 'deleted' => false),
        '08005' => array('name' => 'Koshi', 'lat' => 37.326583, 'lon' => 138.890000, 'deleted' => true),
        '08006' => array('name' => 'Sado', 'lat' => 38.083333, 'lon' => 138.333056, 'deleted' => true),
        '08007' => array('name' => 'Santo', 'lat' => 37.529100, 'lon' => 138.706000, 'deleted' => false),
        '08008' => array('name' => 'Nakauonuma', 'lat' => 36.995000, 'lon' => 138.636000, 'deleted' => false),
        '08009' => array('name' => 'Nakakambara', 'lat' => 37.693472, 'lon' => 139.174528, 'deleted' => true),
        '08010' => array('name' => 'Nakakubiki', 'lat' => 37.095813, 'lon' => 138.308507, 'deleted' => true),
        '08011' => array('name' => 'Nishikambara', 'lat' => 37.693900, 'lon' => 138.836000, 'deleted' => false),
        '08012' => array('name' => 'Nishikubiki', 'lat' => 37.000000, 'lon' => 138.000000, 'deleted' => true),
        '08013' => array('name' => 'Higashikambara', 'lat' => 37.664923, 'lon' => 139.464178, 'deleted' => false),
        '08014' => array('name' => 'Higashikubiki', 'lat' => 37.120713, 'lon' => 138.496620, 'deleted' => true),
        '08015' => array('name' => 'Minamiuonuma', 'lat' => 36.869000, 'lon' => 138.817000, 'deleted' => false),
        '08016' => array('name' => 'Minamikambara', 'lat' => 37.701500, 'lon' => 139.069000, 'deleted' => false),
        '09001' => array('name' => 'Kamiina', 'lat' => 35.796000, 'lon' => 137.862000, 'deleted' => false),
        '09002' => array('name' => 'Kamitakai', 'lat' => 36.666000, 'lon' => 138.412000, 'deleted' => false),
        '09003' => array('name' => 'Kamiminochi', 'lat' => 36.783000, 'lon' => 138.154000, 'deleted' => false),
        '09004' => array('name' => 'Kiso', 'lat' => 35.816000, 'lon' => 137.620000, 'deleted' => false),
        '09005' => array('name' => 'Kitaazumi', 'lat' => 36.719480, 'lon' => 137.881270, 'deleted' => false),
        '09006' => array('name' => 'Kitasaku', 'lat' => 36.353000, 'lon' => 138.552000, 'deleted' => false),
        '09007' => array('name' => 'Sarashina', 'lat' => 36.506444, 'lon' => 137.988639, 'deleted' => true),
        '09008' => array('name' => 'Shimoina', 'lat' => 35.432000, 'lon' => 137.740000, 'deleted' => false),
        '09009' => array('name' => 'Shimotakai', 'lat' => 36.794000, 'lon' => 138.491000, 'deleted' => false),
        '09010' => array('name' => 'Shimominochi', 'lat' => 36.890000, 'lon' => 138.574000, 'deleted' => false),
        '09011' => array('name' => 'Suwa', 'lat' => 35.962000, 'lon' => 138.245000, 'deleted' => false),
        '09012' => array('name' => 'Chiisagata', 'lat' => 36.313762, 'lon' => 138.082867, 'deleted' => false),
        '09013' => array('name' => '(Reserved)', 'lat' => null, 'lon' => null, 'deleted' => true),
        '09014' => array('name' => 'Hanishina', 'lat' => 36.462508, 'lon' => 138.182560, 'deleted' => false),
        '09015' => array('name' => 'Higashichikuma', 'lat' => 36.428056, 'lon' => 138.010000, 'deleted' => false),
        '09016' => array('name' => 'Minamiazumi', 'lat' => 36.240774, 'lon' => 137.842099, 'deleted' => true),
        '09017' => array('name' => 'Minamisaku', 'lat' => 36.059000, 'lon' => 138.504000, 'deleted' => false),
        '10001' => array('name' => 'Kitatama', 'lat' => 35.754833, 'lon' => 139.387361, 'deleted' => true),
        '10002' => array('name' => 'Nishitama', 'lat' => 35.788290, 'lon' => 139.095140, 'deleted' => false),
        '10003' => array('name' => 'Minamitama', 'lat' => 35.637444, 'lon' => 139.475417, 'deleted' => true),
        '10004' => array('name' => 'Oshima-Shicho', 'lat' => 34.465465, 'lon' => 139.257160, 'deleted' => false),
        '10005' => array('name' => 'Miyake-Shicho', 'lat' => 33.988736, 'lon' => 139.577444, 'deleted' => false),
        '10006' => array('name' => 'Hachijo-Shicho', 'lat' => 32.018731, 'lon' => 139.953500, 'deleted' => false),
        '10007' => array('name' => 'Ogasawara-Shicho', 'lat' => 27.094194, 'lon' => 142.191917, 'deleted' => false),
        '11001' => array('name' => 'Aiko', 'lat' => 35.499722, 'lon' => 139.253333, 'deleted' => false),
        '11002' => array('name' => 'Ashigarakami', 'lat' => 35.403889, 'lon' => 139.073889, 'deleted' => false),
        '11003' => array('name' => 'Ashigarashimo', 'lat' => 35.210556, 'lon' => 139.054444, 'deleted' => false),
        '11004' => array('name' => 'Koza', 'lat' => 35.374167, 'lon' => 139.390833, 'deleted' => false),
        '11005' => array('name' => 'Tsukui', 'lat' => 35.602951, 'lon' => 139.225819, 'deleted' => true),
        '11006' => array('name' => 'Naka', 'lat' => 35.311389, 'lon' => 139.273889, 'deleted' => false),
        '11007' => array('name' => 'Miura', 'lat' => 35.267222, 'lon' => 139.603889, 'deleted' => false),
        '12001' => array('name' => 'Awa', 'lat' => 35.116667, 'lon' => 139.833333, 'deleted' => false),
        '12002' => array('name' => 'Isumi', 'lat' => 35.231167, 'lon' => 140.233611, 'deleted' => false),
        '12003' => array('name' => 'Ichihara', 'lat' => 35.368222, 'lon' => 140.141403, 'deleted' => true),
        '12004' => array('name' => 'Inba', 'lat' => 35.824000, 'lon' => 140.256000, 'deleted' => false),
        '12005' => array('name' => 'Kaijo', 'lat' => 35.721000, 'lon' => 140.702722, 'deleted' => true),
        '12006' => array('name' => 'Katori', 'lat' => 35.780000, 'lon' => 140.478056, 'deleted' => false),
        '12007' => array('name' => 'Kimitsu', 'lat' => 35.383333, 'lon' => 139.916667, 'deleted' => true),
        '12008' => array('name' => 'Sambu', 'lat' => 35.638000, 'lon' => 140.514000, 'deleted' => false),
        '12009' => array('name' => 'Sosa', 'lat' => 35.663167, 'lon' => 140.536806, 'deleted' => true),
        '12010' => array('name' => 'Chiba', 'lat' => 35.722417, 'lon' => 140.099889, 'deleted' => true),
        '12011' => array('name' => 'Chosei', 'lat' => 35.383333, 'lon' => 140.366667, 'deleted' => false),
        '12012' => array('name' => 'Higashikatsushika', 'lat' => 35.841444, 'lon' => 140.007750, 'deleted' => true),
        '13001' => array('name' => 'Iruma', 'lat' => 35.921000, 'lon' => 139.297000, 'deleted' => false),
        '13002' => array('name' => 'Osato', 'lat' => 36.120100, 'lon' => 139.194000, 'deleted' => false),
        '13003' => array('name' => 'Kitaadachi', 'lat' => 35.972000, 'lon' => 139.636000, 'deleted' => false),
        '13004' => array('name' => 'Kitakatsushika', 'lat' => 36.010000, 'lon' => 139.769000, 'deleted' => false),
        '13005' => array('name' => 'Kitasaitama', 'lat' => 36.131389, 'lon' => 139.601667, 'deleted' => true),
        '13006' => array('name' => 'Kodama', 'lat' => 36.206000, 'lon' => 139.092000, 'deleted' => false),
        '13007' => array('name' => 'Chichibu', 'lat' => 36.023611, 'lon' => 139.015000, 'deleted' => false),
        '13008' => array('name' => 'Hiki', 'lat' => 36.027000, 'lon' => 139.304000, 'deleted' => false),
        '13009' => array('name' => 'Minamisaitama', 'lat' => 36.026600, 'lon' => 139.681000, 'deleted' => false),
        '14001' => array('name' => 'Inashiki', 'lat' => 35.994000, 'lon' => 140.275000, 'deleted' => false),
        '14002' => array('name' => 'Kashima', 'lat' => 35.965556, 'lon' => 140.644722, 'deleted' => true),
        '14003' => array('name' => 'Kitasoma', 'lat' => 35.868200, 'lon' => 140.165000, 'deleted' => false),
        '14004' => array('name' => 'Kuji', 'lat' => 36.801000, 'lon' => 140.345000, 'deleted' => false),
        '14005' => array('name' => 'Sashima', 'lat' => 36.114900, 'lon' => 139.802000, 'deleted' => false),
        '14006' => array('name' => 'Taga', 'lat' => 36.716667, 'lon' => 140.683333, 'deleted' => true),
        '14007' => array('name' => 'Tsukuba', 'lat' => 35.976403, 'lon' => 140.023847, 'deleted' => true),
        '14008' => array('name' => 'Naka', 'lat' => 36.460556, 'lon' => 140.588056, 'deleted' => false),
        '14009' => array('name' => 'Namegata', 'lat' => 36.100000, 'lon' => 140.433333, 'deleted' => true),
        '14010' => array('name' => 'Niihari', 'lat' => 36.166542, 'lon' => 140.298361, 'deleted' => true),
        '14011' => array('name' => 'Nishiibaraki', 'lat' => 36.361667, 'lon' => 140.266667, 'deleted' => true),
        '14012' => array('name' => 'Higashiibaraki', 'lat' => 36.387220, 'lon' => 140.375680, 'deleted' => false),
        '14013' => array('name' => 'Makabe', 'lat' => 36.300000, 'lon' => 139.983333, 'deleted' => true),
        '14014' => array('name' => 'Yuki', 'lat' => 36.184722, 'lon' => 139.895000, 'deleted' => false),
        '15001' => array('name' => 'Ashikaga', 'lat' => 36.344139, 'lon' => 139.438472, 'deleted' => true),
        '15002' => array('name' => 'Aso', 'lat' => 36.388528, 'lon' => 139.590514, 'deleted' => true),
        '15003' => array('name' => 'Kamitsuga', 'lat' => 36.381389, 'lon' => 139.730278, 'deleted' => true),
        '15004' => array('name' => 'Kawachi', 'lat' => 36.438056, 'lon' => 139.910000, 'deleted' => false),
        '15005' => array('name' => 'Shioya', 'lat' => 36.777000, 'lon' => 139.828000, 'deleted' => false),
        '15006' => array('name' => 'Shimotsuga', 'lat' => 36.359000, 'lon' => 139.651000, 'deleted' => false),
        '15007' => array('name' => 'Nasu', 'lat' => 37.032000, 'lon' => 140.125000, 'deleted' => false),
        '15008' => array('name' => 'Haga', 'lat' => 36.503100, 'lon' => 140.112410, 'deleted' => false),
        '16001' => array('name' => 'Agatsuma', 'lat' => 36.566667, 'lon' => 138.816667, 'deleted' => false),
        '16002' => array('name' => 'Usui', 'lat' => 36.314583, 'lon' => 138.790472, 'deleted' => true),
        '16003' => array('name' => 'Ora', 'lat' => 36.232800, 'lon' => 139.492000, 'deleted' => false),
        '16004' => array('name' => 'Kanra', 'lat' => 36.221000, 'lon' => 138.716000, 'deleted' => false),
        '16005' => array('name' => 'Kitagumma', 'lat' => 36.447800, 'lon' => 138.967000, 'deleted' => false),
        '16006' => array('name' => 'Gumma', 'lat' => 36.385167, 'lon' => 138.881917, 'deleted' => true),
        '16007' => array('name' => 'Sawa', 'lat' => 36.303800, 'lon' => 139.126000, 'deleted' => false),
        '16008' => array('name' => 'Seta', 'lat' => 36.493060, 'lon' => 139.115260, 'deleted' => true),
        '16009' => array('name' => 'Tano', 'lat' => 36.094000, 'lon' => 138.792000, 'deleted' => false),
        '16010' => array('name' => 'Tone', 'lat' => 36.860000, 'lon' => 139.090000, 'deleted' => false),
        '16011' => array('name' => 'Nitta', 'lat' => 36.394833, 'lon' => 139.281083, 'deleted' => true),
        '16012' => array('name' => 'Yamada', 'lat' => 36.432750, 'lon' => 139.273278, 'deleted' => true),
        '17001' => array('name' => 'Kitakoma', 'lat' => 35.862444, 'lon' => 138.318944, 'deleted' => true),
        '17002' => array('name' => 'Kitatsuru', 'lat' => 35.794000, 'lon' => 138.908000, 'deleted' => false),
        '17003' => array('name' => 'Nakakoma', 'lat' => 35.625100, 'lon' => 138.535000, 'deleted' => false),
        '17004' => array('name' => 'Nishiyatsushiro', 'lat' => 35.541200, 'lon' => 138.511000, 'deleted' => false),
        '17005' => array('name' => 'Higashiyatsushiro', 'lat' => 35.553417, 'lon' => 138.665083, 'deleted' => true),
        '17006' => array('name' => 'Higashiyamanashi', 'lat' => 35.652167, 'lon' => 138.751056, 'deleted' => true),
        '17007' => array('name' => 'Minamikoma', 'lat' => 35.417000, 'lon' => 138.412000, 'deleted' => false),
        '17008' => array('name' => 'Minamitsuru', 'lat' => 35.507000, 'lon' => 138.754000, 'deleted' => false),
        '18001' => array('name' => 'Abe', 'lat' => 34.975000, 'lon' => 138.383611, 'deleted' => true),
        '18002' => array('name' => 'Inasa', 'lat' => 34.814583, 'lon' => 137.625676, 'deleted' => true),
        '18003' => array('name' => 'Ihara', 'lat' => 35.127931, 'lon' => 138.590208, 'deleted' => true),
        '18004' => array('name' => 'Iwata', 'lat' => 34.857299, 'lon' => 137.850302, 'deleted' => true),
        '18005' => array('name' => 'Ogasa', 'lat' => 34.672278, 'lon' => 138.021681, 'deleted' => true),
        '18006' => array('name' => 'Kamo', 'lat' => 34.798000, 'lon' => 138.886000, 'deleted' => false),
        '18007' => array('name' => 'Shida', 'lat' => 34.916667, 'lon' => 138.283333, 'deleted' => true),
        '18008' => array('name' => 'Shuchi', 'lat' => 34.833333, 'lon' => 137.933333, 'deleted' => false),
        '18009' => array('name' => 'Sunto', 'lat' => 35.368000, 'lon' => 138.927000, 'deleted' => false),
        '18010' => array('name' => 'Tagata', 'lat' => 35.119444, 'lon' => 138.936111, 'deleted' => false),
        '18011' => array('name' => 'Haibara', 'lat' => 34.769486, 'lon' => 138.251717, 'deleted' => false),
        '18012' => array('name' => 'Hamana', 'lat' => 34.694300, 'lon' => 137.559800, 'deleted' => true),
        '18013' => array('name' => 'Fuji', 'lat' => 35.250000, 'lon' => 138.633330, 'deleted' => true),
        '19001' => array('name' => 'Anpachi', 'lat' => 35.334928, 'lon' => 136.647908, 'deleted' => false),
        '19002' => array('name' => 'Inaba', 'lat' => 35.400479, 'lon' => 136.867431, 'deleted' => true),
        '19003' => array('name' => 'Ibi', 'lat' => 35.553000, 'lon' => 136.477000, 'deleted' => false),
        '19004' => array('name' => 'Ena', 'lat' => 35.449571, 'lon' => 137.409360, 'deleted' => true),
        '19005' => array('name' => 'Ono', 'lat' => 36.210000, 'lon' => 136.869000, 'deleted' => false),
        '19006' => array('name' => 'Kaizu', 'lat' => 35.232509, 'lon' => 136.623741, 'deleted' => true),
        '19007' => array('name' => 'Kani', 'lat' => 35.432400, 'lon' => 137.146000, 'deleted' => false),
        '19008' => array('name' => 'Kamo', 'lat' => 35.571260, 'lon' => 137.210260, 'deleted' => false),
        '19009' => array('name' => 'Gujo', 'lat' => 35.807028, 'lon' => 136.954802, 'deleted' => true),
        '19010' => array('name' => 'Toki', 'lat' => 35.350000, 'lon' => 137.183333, 'deleted' => true),
        '19011' => array('name' => 'Hashima', 'lat' => 35.374366, 'lon' => 136.777482, 'deleted' => false),
        '19012' => array('name' => 'Fuwa', 'lat' => 35.370867, 'lon' => 136.480119, 'deleted' => false),
        '19013' => array('name' => 'Mashita', 'lat' => 35.833567, 'lon' => 137.210272, 'deleted' => true),
        '19014' => array('name' => 'Mugi', 'lat' => 35.604622, 'lon' => 136.903206, 'deleted' => true),
        '19015' => array('name' => 'Motosu', 'lat' => 35.431700, 'lon' => 136.690800, 'deleted' => false),
        '19016' => array('name' => 'Yamagata', 'lat' => 34.655337, 'lon' => 132.354627, 'deleted' => true),
        '19017' => array('name' => 'Yoro', 'lat' => 35.299200, 'lon' => 136.564000, 'deleted' => false),
        '19018' => array('name' => 'Yoshiki', 'lat' => 36.248708, 'lon' => 137.287903, 'deleted' => true),
        '20001' => array('name' => 'Aichi', 'lat' => 35.150000, 'lon' => 137.050000, 'deleted' => false),
        '20002' => array('name' => 'Atsumi', 'lat' => 34.623222, 'lon' => 137.109556, 'deleted' => true),
        '20003' => array('name' => 'Ama', 'lat' => 35.094858, 'lon' => 136.793633, 'deleted' => false),
        '20004' => array('name' => 'Kitashitara', 'lat' => 35.133333, 'lon' => 137.650000, 'deleted' => false),
        '20005' => array('name' => 'Chita', 'lat' => 34.836250, 'lon' => 136.915770, 'deleted' => false),
        '20006' => array('name' => 'Nakashima', 'lat' => 35.212889, 'lon' => 136.739611, 'deleted' => true),
        '20007' => array('name' => 'Nishikasugai', 'lat' => 35.255000, 'lon' => 136.917300, 'deleted' => false),
        '20008' => array('name' => 'Nishikamo', 'lat' => 35.083333, 'lon' => 137.066667, 'deleted' => true),
        '20009' => array('name' => 'Niwa', 'lat' => 35.346500, 'lon' => 136.913000, 'deleted' => false),
        '20010' => array('name' => 'Nukata', 'lat' => 34.866667, 'lon' => 137.166667, 'deleted' => false),
        '20011' => array('name' => 'Haguri', 'lat' => 35.337111, 'lon' => 136.778139, 'deleted' => true),
        '20012' => array('name' => 'Hazu', 'lat' => 34.823230, 'lon' => 137.021192, 'deleted' => true),
        '20013' => array('name' => 'Higashikasugai', 'lat' => 35.216528, 'lon' => 137.035361, 'deleted' => true),
        '20014' => array('name' => 'Higashikamo', 'lat' => 35.083333, 'lon' => 137.066667, 'deleted' => true),
        '20015' => array('name' => 'Hekikai', 'lat' => 34.964444, 'lon' => 137.019250, 'deleted' => true),
        '20016' => array('name' => 'Hoi', 'lat' => 34.823100, 'lon' => 137.380000, 'deleted' => true),
        '20017' => array('name' => 'Minamishitara', 'lat' => 34.900000, 'lon' => 137.500000, 'deleted' => true),
        '20018' => array('name' => 'Yana', 'lat' => 34.842500, 'lon' => 137.422222, 'deleted' => true),
        '21001' => array('name' => 'Age', 'lat' => 34.768215, 'lon' => 136.451382, 'deleted' => true),
        '21002' => array('name' => 'Ano', 'lat' => 34.759657, 'lon' => 136.420500, 'deleted' => true),
        '21003' => array('name' => 'Ayama', 'lat' => 34.797090, 'lon' => 136.167035, 'deleted' => true),
        '21004' => array('name' => 'Iinan', 'lat' => 34.439125, 'lon' => 136.361667, 'deleted' => true),
        '21005' => array('name' => 'Ichishi', 'lat' => 34.623854, 'lon' => 136.394278, 'deleted' => true),
        '21006' => array('name' => 'Inabe', 'lat' => 35.075200, 'lon' => 136.584000, 'deleted' => false),
        '21007' => array('name' => 'Kawage', 'lat' => 34.796083, 'lon' => 136.477160, 'deleted' => true),
        '21008' => array('name' => 'Kitamuro', 'lat' => 34.178889, 'lon' => 136.238056, 'deleted' => false),
        '21009' => array('name' => 'Kuwana', 'lat' => 35.075800, 'lon' => 136.734000, 'deleted' => false),
        '21010' => array('name' => 'Shima', 'lat' => 34.328194, 'lon' => 136.829667, 'deleted' => true),
        '21011' => array('name' => 'Suzuka', 'lat' => 34.854833, 'lon' => 136.391139, 'deleted' => true),
        '21012' => array('name' => 'Taki', 'lat' => 34.371000, 'lon' => 136.330000, 'deleted' => false),
        '21013' => array('name' => 'Naga', 'lat' => 34.669611, 'lon' => 136.177861, 'deleted' => true),
        '21014' => array('name' => 'Mie', 'lat' => 35.046000, 'lon' => 136.476000, 'deleted' => false),
        '21015' => array('name' => 'Minamimuro', 'lat' => 33.816944, 'lon' => 135.996944, 'deleted' => false),
        '21016' => array('name' => 'Watarai', 'lat' => 34.348900, 'lon' => 136.494000, 'deleted' => false),
        '22001' => array('name' => 'Amada', 'lat' => 35.300000, 'lon' => 135.133333, 'deleted' => true),
        '22002' => array('name' => 'Ikaruga', 'lat' => 35.317556, 'lon' => 135.187222, 'deleted' => true),
        '22003' => array('name' => 'Otokuni', 'lat' => 34.900000, 'lon' => 135.690556, 'deleted' => false),
        '22004' => array('name' => 'Kasa', 'lat' => 35.409556, 'lon' => 135.197667, 'deleted' => true),
        '22005' => array('name' => 'Kitakuwada', 'lat' => 35.213653, 'lon' => 135.591653, 'deleted' => true),
        '22006' => array('name' => 'Kuse', 'lat' => 34.887600, 'lon' => 135.747000, 'deleted' => false),
        '22007' => array('name' => 'Kumano', 'lat' => 35.603444, 'lon' => 134.895000, 'deleted' => true),
        '22008' => array('name' => 'Soraku', 'lat' => 34.769000, 'lon' => 135.961000, 'deleted' => false),
        '22009' => array('name' => 'Takeno', 'lat' => 35.693306, 'lon' => 135.073426, 'deleted' => true),
        '22010' => array('name' => 'Tsuzuki', 'lat' => 34.846500, 'lon' => 135.874000, 'deleted' => false),
        '22011' => array('name' => 'Naka', 'lat' => 35.303208, 'lon' => 139.283417, 'deleted' => true),
        '22012' => array('name' => 'Funai', 'lat' => 35.166667, 'lon' => 135.416667, 'deleted' => false),
        '22013' => array('name' => 'Minamikuwada', 'lat' => 35.004194, 'lon' => 135.605417, 'deleted' => true),
        '22014' => array('name' => 'Yoza', 'lat' => 35.511000, 'lon' => 135.111000, 'deleted' => false),
        '23001' => array('name' => 'Ika', 'lat' => 35.533900, 'lon' => 136.198700, 'deleted' => true),
        '23002' => array('name' => 'Inukami', 'lat' => 35.204000, 'lon' => 136.334000, 'deleted' => false),
        '23003' => array('name' => 'Echi', 'lat' => 35.166667, 'lon' => 136.216667, 'deleted' => false),
        '23004' => array('name' => 'Gamou', 'lat' => 35.027036, 'lon' => 136.220007, 'deleted' => false),
        '23005' => array('name' => 'Kanzaki', 'lat' => 35.001287, 'lon' => 134.754417, 'deleted' => true),
        '23006' => array('name' => 'Kurita', 'lat' => 35.010278, 'lon' => 135.985278, 'deleted' => true),
        '23007' => array('name' => 'Koka', 'lat' => 34.946639, 'lon' => 136.148056, 'deleted' => true),
        '23008' => array('name' => 'Sakata', 'lat' => 35.337250, 'lon' => 136.303889, 'deleted' => true),
        '23009' => array('name' => 'Shiga', 'lat' => 35.206333, 'lon' => 135.921167, 'deleted' => true),
        '23010' => array('name' => 'Takashima', 'lat' => 35.353000, 'lon' => 136.035722, 'deleted' => true),
        '23011' => array('name' => 'Higashiazai', 'lat' => 35.371300, 'lon' => 136.394600, 'deleted' => true),
        '23012' => array('name' => 'Yasu', 'lat' => 35.085347, 'lon' => 136.019028, 'deleted' => true),
        '24001' => array('name' => 'Ikoma', 'lat' => 34.624722, 'lon' => 135.703056, 'deleted' => false),
        '24002' => array('name' => 'Uda', 'lat' => 34.496000, 'lon' => 136.142000, 'deleted' => false),
        '24003' => array('name' => 'Uchi', 'lat' => 34.335361, 'lon' => 135.701250, 'deleted' => true),
        '24004' => array('name' => 'Kitakatsuragi', 'lat' => 34.565900, 'lon' => 135.728000, 'deleted' => false),
        '24005' => array('name' => 'Shiki', 'lat' => 34.564000, 'lon' => 135.788000, 'deleted' => false),
        '24006' => array('name' => 'Soekami', 'lat' => 34.709472, 'lon' => 136.044000, 'deleted' => true),
        '24007' => array('name' => 'Takaichi', 'lat' => 34.449700, 'lon' => 135.809000, 'deleted' => false),
        '24008' => array('name' => 'Minamikatsuragi', 'lat' => 34.444694, 'lon' => 135.738521, 'deleted' => true),
        '24009' => array('name' => 'Yamabe', 'lat' => 34.664800, 'lon' => 136.022000, 'deleted' => false),
        '24010' => array('name' => 'Yoshino', 'lat' => 34.137000, 'lon' => 135.970000, 'deleted' => false),
        '25001' => array('name' => 'Kitakawachi', 'lat' => 34.787944, 'lon' => 135.679944, 'deleted' => true),
        '25002' => array('name' => 'Sennan', 'lat' => 34.314000, 'lon' => 135.151000, 'deleted' => false),
        '25003' => array('name' => 'Senboku', 'lat' => 34.483333, 'lon' => 135.383333, 'deleted' => false),
        '25004' => array('name' => 'Toyono', 'lat' => 34.973000, 'lon' => 135.426000, 'deleted' => false),
        '25005' => array('name' => 'Nakakawachi', 'lat' => 34.579278, 'lon' => 135.628611, 'deleted' => true),
        '25006' => array('name' => 'Mishima', 'lat' => 34.897500, 'lon' => 135.653500, 'deleted' => false),
        '25007' => array('name' => 'Minamikawachi', 'lat' => 34.465833, 'lon' => 135.650000, 'deleted' => false),
        '26001' => array('name' => 'Arida', 'lat' => 34.038170, 'lon' => 135.194133, 'deleted' => false),
        '26002' => array('name' => 'Ito', 'lat' => 34.214000, 'lon' => 135.557000, 'deleted' => false),
        '26003' => array('name' => 'Kaiso', 'lat' => 34.154000, 'lon' => 135.393000, 'deleted' => false),
        '26004' => array('name' => 'Naga', 'lat' => 33.857417, 'lon' => 134.496639, 'deleted' => true),
        '26005' => array('name' => 'Nishimuro', 'lat' => 33.604000, 'lon' => 135.486000, 'deleted' => false),
        '26006' => array('name' => 'Higashimuro', 'lat' => 33.600000, 'lon' => 135.791000, 'deleted' => false),
        '26007' => array('name' => 'Hidaka', 'lat' => 33.897000, 'lon' => 135.336000, 'deleted' => false),
        '27001' => array('name' => 'Akou', 'lat' => 34.866670, 'lon' => 134.366670, 'deleted' => false),
        '27002' => array('name' => 'Asago', 'lat' => 35.233330, 'lon' => 134.833330, 'deleted' => true),
        '27003' => array('name' => 'Arima', 'lat' => 34.889972, 'lon' => 135.225444, 'deleted' => true),
        '27004' => array('name' => 'Izushi', 'lat' => 35.465875, 'lon' => 134.912583, 'deleted' => true),
        '27005' => array('name' => 'Ibo', 'lat' => 34.838700, 'lon' => 134.589200, 'deleted' => false),
        '27006' => array('name' => 'Innami', 'lat' => 34.821250, 'lon' => 134.819333, 'deleted' => true),
        '27007' => array('name' => 'Kako', 'lat' => 34.750100, 'lon' => 134.919000, 'deleted' => false),
        '27008' => array('name' => 'Kasai', 'lat' => 34.923815, 'lon' => 134.863759, 'deleted' => true),
        '27009' => array('name' => 'Kato', 'lat' => 34.920620, 'lon' => 134.994648, 'deleted' => true),
        '27010' => array('name' => 'Kawabe', 'lat' => 34.937000, 'lon' => 135.353000, 'deleted' => false),
        '27011' => array('name' => 'Kanzaki', 'lat' => 35.001389, 'lon' => 134.754167, 'deleted' => false),
        '27012' => array('name' => 'Kinosaki', 'lat' => 35.596076, 'lon' => 134.740799, 'deleted' => true),
        '27013' => array('name' => 'Sayo', 'lat' => 35.018000, 'lon' => 134.372000, 'deleted' => false),
        '27014' => array('name' => 'Shikama', 'lat' => 34.824319, 'lon' => 134.608444, 'deleted' => true),
        '27015' => array('name' => 'Shiso', 'lat' => 34.985750, 'lon' => 134.595583, 'deleted' => true),
        '27016' => array('name' => 'Taka', 'lat' => 35.069000, 'lon' => 134.902000, 'deleted' => false),
        '27017' => array('name' => 'Taki', 'lat' => 35.059757, 'lon' => 135.170847, 'deleted' => true),
        '27018' => array('name' => 'Tsuna', 'lat' => 34.383000, 'lon' => 134.836000, 'deleted' => true),
        '27019' => array('name' => 'Hikami', 'lat' => 35.168148, 'lon' => 135.064926, 'deleted' => true),
        '27020' => array('name' => 'Mikata', 'lat' => 35.567000, 'lon' => 134.551000, 'deleted' => false),
        '27021' => array('name' => 'Mino', 'lat' => 34.886667, 'lon' => 135.124167, 'deleted' => true),
        '27022' => array('name' => 'Mihara', 'lat' => 34.300840, 'lon' => 134.766264, 'deleted' => true),
        '27023' => array('name' => 'Muko', 'lat' => 34.737161, 'lon' => 135.352360, 'deleted' => true),
        '27024' => array('name' => 'Yabu', 'lat' => 35.404611, 'lon' => 134.767611, 'deleted' => true),
        '28001' => array('name' => 'Imizu', 'lat' => 36.727007, 'lon' => 137.089958, 'deleted' => true),
        '28002' => array('name' => 'Kaminiikawa', 'lat' => 36.594722, 'lon' => 137.251861, 'deleted' => true),
        '28003' => array('name' => 'Shimoniikawa', 'lat' => 36.914000, 'lon' => 137.616000, 'deleted' => false),
        '28004' => array('name' => 'Nakaniikawa', 'lat' => 36.632000, 'lon' => 137.502000, 'deleted' => false),
        '28005' => array('name' => 'Nishitonami', 'lat' => 36.632556, 'lon' => 136.899639, 'deleted' => true),
        '28006' => array('name' => 'Nei', 'lat' => 36.588535, 'lon' => 137.152201, 'deleted' => true),
        '28007' => array('name' => 'Himi', 'lat' => 36.855550, 'lon' => 136.965703, 'deleted' => true),
        '28008' => array('name' => 'Higashitonami', 'lat' => 36.509785, 'lon' => 136.948073, 'deleted' => true),
        '29001' => array('name' => 'Asuwa', 'lat' => 35.998722, 'lon' => 136.360611, 'deleted' => true),
        '29002' => array('name' => 'Imadate', 'lat' => 35.912222, 'lon' => 136.276389, 'deleted' => false),
        '29003' => array('name' => 'Oi', 'lat' => 35.427000, 'lon' => 135.564000, 'deleted' => false),
        '29004' => array('name' => 'Ono', 'lat' => 36.270944, 'lon' => 136.898556, 'deleted' => true),
        '29005' => array('name' => 'Onyu', 'lat' => 35.400028, 'lon' => 135.682111, 'deleted' => true),
        '29006' => array('name' => 'Sakai', 'lat' => 36.166811, 'lon' => 136.218849, 'deleted' => true),
        '29007' => array('name' => 'Tsuruga', 'lat' => 35.645194, 'lon' => 136.055500, 'deleted' => true),
        '29008' => array('name' => 'Nanjo', 'lat' => 35.776944, 'lon' => 136.226111, 'deleted' => false),
        '29009' => array('name' => 'Nyuu', 'lat' => 35.970000, 'lon' => 136.068056, 'deleted' => false),
        '29010' => array('name' => 'Mikata', 'lat' => 35.596000, 'lon' => 135.978000, 'deleted' => false),
        '29011' => array('name' => 'Yoshida', 'lat' => 36.079100, 'lon' => 136.357000, 'deleted' => false),
        '29012' => array('name' => 'Mikatakaminaka', 'lat' => 35.521000, 'lon' => 135.891000, 'deleted' => false),
        '30001' => array('name' => 'Ishikawa', 'lat' => 36.540000, 'lon' => 136.610000, 'deleted' => true),
        '30002' => array('name' => 'Enuma', 'lat' => 36.246528, 'lon' => 136.371972, 'deleted' => true),
        '30003' => array('name' => 'Kashima', 'lat' => 36.980000, 'lon' => 136.902000, 'deleted' => false),
        '30004' => array('name' => 'Kahoku', 'lat' => 36.669000, 'lon' => 136.773000, 'deleted' => false),
        '30005' => array('name' => 'Suzu', 'lat' => 37.353250, 'lon' => 137.244528, 'deleted' => true),
        '30006' => array('name' => 'Nomi', 'lat' => 36.467700, 'lon' => 136.533800, 'deleted' => false),
        '30007' => array('name' => 'Hakui', 'lat' => 36.866667, 'lon' => 136.800000, 'deleted' => false),
        '30008' => array('name' => 'Fugeshi', 'lat' => 37.259153, 'lon' => 136.840153, 'deleted' => true),
        '30009' => array('name' => 'Housu', 'lat' => 37.295000, 'lon' => 137.034000, 'deleted' => false),
        '31001' => array('name' => 'Aida', 'lat' => 35.166667, 'lon' => 134.333333, 'deleted' => false),
        '31002' => array('name' => 'Akaiwa', 'lat' => 34.733889, 'lon' => 134.039028, 'deleted' => true),
        '31003' => array('name' => 'Asakuchi', 'lat' => 34.514333, 'lon' => 133.556094, 'deleted' => false),
        '31004' => array('name' => 'Atetsu', 'lat' => 34.985243, 'lon' => 133.436451, 'deleted' => true),
        '31005' => array('name' => 'Oku', 'lat' => 34.661824, 'lon' => 134.114130, 'deleted' => true),
        '31006' => array('name' => 'Oda', 'lat' => 34.633800, 'lon' => 133.588000, 'deleted' => false),
        '31007' => array('name' => 'Katsuta', 'lat' => 35.110000, 'lon' => 134.162000, 'deleted' => false),
        '31008' => array('name' => 'Kawakami', 'lat' => 43.394292, 'lon' => 144.529958, 'deleted' => true),
        '31009' => array('name' => 'Kibi', 'lat' => 34.629028, 'lon' => 133.692222, 'deleted' => true),
        '31010' => array('name' => 'Kume', 'lat' => 34.967000, 'lon' => 133.943000, 'deleted' => false),
        '31011' => array('name' => 'Kojima', 'lat' => 34.544000, 'lon' => 133.864944, 'deleted' => true),
        '31012' => array('name' => 'Shitsuki', 'lat' => 34.632500, 'lon' => 133.432417, 'deleted' => true),
        '31013' => array('name' => 'Jodo', 'lat' => 34.699556, 'lon' => 134.054444, 'deleted' => true),
        '31014' => array('name' => 'Jobo', 'lat' => 34.961611, 'lon' => 133.632917, 'deleted' => true),
        '31015' => array('name' => 'Tsukubo', 'lat' => 34.610500, 'lon' => 133.823100, 'deleted' => false),
        '31016' => array('name' => 'Tomata', 'lat' => 35.210000, 'lon' => 133.898056, 'deleted' => false),
        '31017' => array('name' => 'Maniwa', 'lat' => 35.204722, 'lon' => 133.560000, 'deleted' => false),
        '31018' => array('name' => 'Mitsu', 'lat' => 34.869319, 'lon' => 133.903786, 'deleted' => true),
        '31019' => array('name' => 'Wake', 'lat' => 34.846000, 'lon' => 134.135000, 'deleted' => false),
        '31020' => array('name' => 'Kaga', 'lat' => 34.869000, 'lon' => 133.745000, 'deleted' => false),
        '32001' => array('name' => 'Ano', 'lat' => 35.201639, 'lon' => 132.533281, 'deleted' => true),
        '32002' => array('name' => 'Ama', 'lat' => 36.096528, 'lon' => 133.096806, 'deleted' => true),
        '32003' => array('name' => 'Iishi', 'lat' => 35.046000, 'lon' => 132.766000, 'deleted' => false),
        '32004' => array('name' => 'Ochi', 'lat' => 34.921000, 'lon' => 132.513000, 'deleted' => false),
        '32005' => array('name' => 'Ohara', 'lat' => 35.319509, 'lon' => 132.926120, 'deleted' => true),
        '32006' => array('name' => 'Oki', 'lat' => 36.225000, 'lon' => 133.245000, 'deleted' => false),
        '32007' => array('name' => 'Ochi', 'lat' => 34.921000, 'lon' => 132.513000, 'deleted' => true),
        '32008' => array('name' => 'Kanoashi', 'lat' => 34.460000, 'lon' => 131.881000, 'deleted' => false),
        '32009' => array('name' => 'Suki', 'lat' => 36.251347, 'lon' => 133.339458, 'deleted' => true),
        '32010' => array('name' => 'Chibu', 'lat' => 36.053528, 'lon' => 133.016944, 'deleted' => true),
        '32011' => array('name' => 'Naka', 'lat' => 33.921796, 'lon' => 134.595861, 'deleted' => true),
        '32012' => array('name' => 'Nita', 'lat' => 35.187000, 'lon' => 133.059000, 'deleted' => false),
        '32013' => array('name' => 'Nima', 'lat' => 35.119264, 'lon' => 132.381806, 'deleted' => true),
        '32014' => array('name' => 'Nogi', 'lat' => 35.360708, 'lon' => 133.225097, 'deleted' => true),
        '32015' => array('name' => 'Hikawa', 'lat' => 35.390000, 'lon' => 132.830000, 'deleted' => true),
        '32016' => array('name' => 'Mino', 'lat' => 34.618833, 'lon' => 132.000264, 'deleted' => true),
        '32017' => array('name' => 'Yatsuka', 'lat' => 35.465000, 'lon' => 133.051111, 'deleted' => true),
        '33001' => array('name' => 'Asa', 'lat' => 34.050681, 'lon' => 131.192750, 'deleted' => true),
        '33002' => array('name' => 'Abu', 'lat' => 34.555740, 'lon' => 131.573670, 'deleted' => false),
        '33003' => array('name' => 'Oshima', 'lat' => 33.911000, 'lon' => 132.288000, 'deleted' => false),
        '33004' => array('name' => 'Otsu', 'lat' => 34.374565, 'lon' => 131.137630, 'deleted' => true),
        '33005' => array('name' => 'Kuga', 'lat' => 34.189500, 'lon' => 132.211500, 'deleted' => false),
        '33006' => array('name' => 'Kumage', 'lat' => 33.934000, 'lon' => 132.068000, 'deleted' => false),
        '33007' => array('name' => 'Saba', 'lat' => 34.189353, 'lon' => 131.655486, 'deleted' => true),
        '33008' => array('name' => 'Tsuno', 'lat' => 34.232028, 'lon' => 131.817222, 'deleted' => true),
        '33009' => array('name' => 'Toyoura', 'lat' => 34.191067, 'lon' => 130.995528, 'deleted' => true),
        '33010' => array('name' => 'Mine', 'lat' => 34.219403, 'lon' => 131.317944, 'deleted' => true),
        '33011' => array('name' => 'Yoshiki', 'lat' => 34.043139, 'lon' => 131.396315, 'deleted' => true),
        '34001' => array('name' => 'Iwami', 'lat' => 35.534000, 'lon' => 134.381000, 'deleted' => false),
        '34002' => array('name' => 'Ketaka', 'lat' => 35.487639, 'lon' => 134.131472, 'deleted' => true),
        '34003' => array('name' => 'Saihaku', 'lat' => 35.363056, 'lon' => 133.430000, 'deleted' => false),
        '34004' => array('name' => 'Tohaku', 'lat' => 35.483300, 'lon' => 133.767000, 'deleted' => false),
        '34005' => array('name' => 'Hino', 'lat' => 35.155000, 'lon' => 133.293056, 'deleted' => false),
        '34006' => array('name' => 'Yazu', 'lat' => 35.311000, 'lon' => 134.363000, 'deleted' => false),
        '35001' => array('name' => 'Aki', 'lat' => 34.353200, 'lon' => 132.557400, 'deleted' => false),
        '35002' => array('name' => 'Asa', 'lat' => 34.470343, 'lon' => 132.494981, 'deleted' => true),
        '35003' => array('name' => 'Ashina', 'lat' => 34.557125, 'lon' => 133.273181, 'deleted' => true),
        '35004' => array('name' => 'Kamo', 'lat' => 34.741117, 'lon' => 138.888267, 'deleted' => true),
        '35005' => array('name' => 'Konu', 'lat' => 34.728352, 'lon' => 133.088861, 'deleted' => true),
        '35006' => array('name' => 'Saeki', 'lat' => 34.295829, 'lon' => 132.306509, 'deleted' => true),
        '35007' => array('name' => 'Jinseki', 'lat' => 34.758056, 'lon' => 133.265000, 'deleted' => false),
        '35008' => array('name' => 'Sera', 'lat' => 34.615000, 'lon' => 133.009000, 'deleted' => false),
        '35009' => array('name' => 'Takata', 'lat' => 34.681537, 'lon' => 132.689495, 'deleted' => true),
        '35010' => array('name' => 'Toyota', 'lat' => 34.235300, 'lon' => 132.885000, 'deleted' => false),
        '35011' => array('name' => 'Numakuma', 'lat' => 34.521100, 'lon' => 133.336400, 'deleted' => true),
        '35012' => array('name' => 'Hiba', 'lat' => 34.953817, 'lon' => 133.037206, 'deleted' => true),
        '35013' => array('name' => 'Fukayasu', 'lat' => 34.546333, 'lon' => 133.374000, 'deleted' => true),
        '35014' => array('name' => 'Futami', 'lat' => 34.795528, 'lon' => 132.857356, 'deleted' => true),
        '35015' => array('name' => 'Mitsugi', 'lat' => 34.453306, 'lon' => 133.171972, 'deleted' => true),
        '35016' => array('name' => 'Yamagata', 'lat' => 34.689000, 'lon' => 132.349000, 'deleted' => false),
        '36001' => array('name' => 'Ayauta', 'lat' => 34.219913, 'lon' => 133.944991, 'deleted' => false),
        '36002' => array('name' => 'Okawa', 'lat' => 34.197000, 'lon' => 134.239000, 'deleted' => true),
        '36003' => array('name' => 'Kagawa', 'lat' => 34.461000, 'lon' => 133.983000, 'deleted' => false),
        '36004' => array('name' => 'Kita', 'lat' => 34.238056, 'lon' => 134.135000, 'deleted' => false),
        '36005' => array('name' => 'Syozu', 'lat' => 34.508000, 'lon' => 134.269000, 'deleted' => false),
        '36006' => array('name' => 'Nakatado', 'lat' => 34.192000, 'lon' => 133.839000, 'deleted' => false),
        '36007' => array('name' => 'Mitoyo', 'lat' => 34.169584, 'lon' => 133.705701, 'deleted' => true),
        '37001' => array('name' => 'Awa', 'lat' => 34.090194, 'lon' => 134.284944, 'deleted' => true),
        '37002' => array('name' => 'Itano', 'lat' => 34.131000, 'lon' => 134.482000, 'deleted' => false),
        '37003' => array('name' => 'Oe', 'lat' => 34.052324, 'lon' => 134.312630, 'deleted' => true),
        '37004' => array('name' => 'Kaifu', 'lat' => 33.691000, 'lon' => 134.375000, 'deleted' => false),
        '37005' => array('name' => 'Katsuura', 'lat' => 33.913000, 'lon' => 134.416000, 'deleted' => false),
        '37006' => array('name' => 'Naka', 'lat' => 33.818000, 'lon' => 134.244000, 'deleted' => false),
        '37007' => array('name' => 'Myozai', 'lat' => 33.996000, 'lon' => 134.360000, 'deleted' => false),
        '37008' => array('name' => 'Myodo', 'lat' => 33.989500, 'lon' => 134.455000, 'deleted' => false),
        '37009' => array('name' => 'Mima', 'lat' => 33.975000, 'lon' => 134.051000, 'deleted' => false),
        '37010' => array('name' => 'Miyoshi', 'lat' => 34.037000, 'lon' => 133.914000, 'deleted' => false),
        '38001' => array('name' => 'Iyo', 'lat' => 33.728056, 'lon' => 132.783056, 'deleted' => false),
        '38002' => array('name' => 'Uma', 'lat' => 33.914519, 'lon' => 133.478417, 'deleted' => true),
        '38003' => array('name' => 'Ochi', 'lat' => 34.263056, 'lon' => 133.178056, 'deleted' => false),
        '38004' => array('name' => 'Onsen', 'lat' => 33.975278, 'lon' => 132.629694, 'deleted' => true),
        '38005' => array('name' => 'Kamiukena', 'lat' => 33.653056, 'lon' => 132.960000, 'deleted' => false),
        '38006' => array('name' => 'Kita', 'lat' => 33.579000, 'lon' => 132.742000, 'deleted' => false),
        '38007' => array('name' => 'Kitauwa', 'lat' => 33.283000, 'lon' => 132.719000, 'deleted' => false),
        '38008' => array('name' => 'Shuso', 'lat' => 33.901667, 'lon' => 133.087694, 'deleted' => true),
        '38009' => array('name' => 'Nii', 'lat' => 33.922417, 'lon' => 133.305806, 'deleted' => true),
        '38010' => array('name' => 'Nishiuwa', 'lat' => 33.435000, 'lon' => 132.201111, 'deleted' => false),
        '38011' => array('name' => 'Higashiuwa', 'lat' => 33.359826, 'lon' => 132.584313, 'deleted' => true),
        '38012' => array('name' => 'Minamiuwa', 'lat' => 32.994000, 'lon' => 132.584000, 'deleted' => false),
        '39001' => array('name' => 'Agawa', 'lat' => 33.574752, 'lon' => 133.167096, 'deleted' => false),
        '39002' => array('name' => 'Aki', 'lat' => 33.537612, 'lon' => 134.091542, 'deleted' => false),
        '39003' => array('name' => 'Kami', 'lat' => 33.588837, 'lon' => 133.745545, 'deleted' => true),
        '39004' => array('name' => 'Takaoka', 'lat' => 33.337000, 'lon' => 133.080000, 'deleted' => false),
        '39005' => array('name' => 'Tosa', 'lat' => 33.764000, 'lon' => 133.462000, 'deleted' => false),
        '39006' => array('name' => 'Nagaoka', 'lat' => 33.792000, 'lon' => 133.671000, 'deleted' => false),
        '39007' => array('name' => 'Hata', 'lat' => 32.889000, 'lon' => 132.844000, 'deleted' => false),
        '40001' => array('name' => 'Asakura', 'lat' => 33.436111, 'lon' => 130.604167, 'deleted' => false),
        '40002' => array('name' => 'Itoshima', 'lat' => 33.555000, 'lon' => 130.186111, 'deleted' => true),
        '40003' => array('name' => 'Ukiha', 'lat' => 33.343500, 'lon' => 130.711131, 'deleted' => true),
        '40004' => array('name' => 'Onga', 'lat' => 33.864000, 'lon' => 130.636000, 'deleted' => false),
        '40005' => array('name' => 'Kasuya', 'lat' => 33.626180, 'lon' => 130.515120, 'deleted' => false),
        '40006' => array('name' => 'Kaho', 'lat' => 33.581200, 'lon' => 130.679300, 'deleted' => false),
        '40007' => array('name' => 'Kurate', 'lat' => 33.786900, 'lon' => 130.671000, 'deleted' => false),
        '40008' => array('name' => 'Sawara', 'lat' => 33.518806, 'lon' => 130.336000, 'deleted' => true),
        '40009' => array('name' => 'Tagawa', 'lat' => 33.627000, 'lon' => 130.858000, 'deleted' => false),
        '40010' => array('name' => 'Chikushi', 'lat' => 33.466260, 'lon' => 130.422020, 'deleted' => true),
        '40011' => array('name' => 'Chikujo', 'lat' => 33.586244, 'lon' => 131.044507, 'deleted' => false),
        '40012' => array('name' => 'Mii', 'lat' => 33.388700, 'lon' => 130.607000, 'deleted' => false),
        '40013' => array('name' => 'Miike', 'lat' => 33.105833, 'lon' => 130.493611, 'deleted' => true),
        '40014' => array('name' => 'Mizuma', 'lat' => 33.214600, 'lon' => 130.441200, 'deleted' => false),
        '40015' => array('name' => 'Miyako', 'lat' => 33.700000, 'lon' => 130.918056, 'deleted' => false),
        '40016' => array('name' => 'Munakata', 'lat' => 33.766806, 'lon' => 130.491083, 'deleted' => true),
        '40017' => array('name' => 'Yamato', 'lat' => 33.142222, 'lon' => 130.461389, 'deleted' => true),
        '40018' => array('name' => 'Yame', 'lat' => 33.251300, 'lon' => 130.561000, 'deleted' => false),
        '41001' => array('name' => 'Ogi', 'lat' => 33.260667, 'lon' => 130.206618, 'deleted' => true),
        '41002' => array('name' => 'Kanzaki', 'lat' => 33.364722, 'lon' => 130.393056, 'deleted' => false),
        '41003' => array('name' => 'Kishima', 'lat' => 33.182000, 'lon' => 130.149000, 'deleted' => false),
        '41004' => array('name' => 'Saga', 'lat' => 33.211259, 'lon' => 130.282750, 'deleted' => true),
        '41005' => array('name' => 'Nishimatsuura', 'lat' => 33.205400, 'lon' => 129.859000, 'deleted' => false),
        '41006' => array('name' => 'Higashimatsuura', 'lat' => 33.466667, 'lon' => 129.883333, 'deleted' => false),
        '41007' => array('name' => 'Fujitsu', 'lat' => 33.016667, 'lon' => 130.183333, 'deleted' => false),
        '41008' => array('name' => 'Miyaki', 'lat' => 33.365000, 'lon' => 130.393056, 'deleted' => false),
        '42001' => array('name' => 'Iki', 'lat' => 33.787160, 'lon' => 129.725208, 'deleted' => true),
        '42002' => array('name' => 'Kamiagata', 'lat' => 34.586667, 'lon' => 129.391667, 'deleted' => true),
        '42003' => array('name' => 'Kitatakaki', 'lat' => 32.862396, 'lon' => 130.120806, 'deleted' => true),
        '42004' => array('name' => 'Kitamatsuura', 'lat' => 33.232000, 'lon' => 129.660000, 'deleted' => false),
        '42005' => array('name' => 'Shimoagata', 'lat' => 34.288898, 'lon' => 129.305833, 'deleted' => true),
        '42006' => array('name' => 'Nishisonogi', 'lat' => 32.831700, 'lon' => 129.868000, 'deleted' => false),
        '42007' => array('name' => 'Higashisonogi', 'lat' => 33.083000, 'lon' => 129.879000, 'deleted' => false),
        '42008' => array('name' => 'Minamitakaki', 'lat' => 32.657823, 'lon' => 130.268837, 'deleted' => true),
        '42009' => array('name' => 'Minamimatsuura', 'lat' => 32.964722, 'lon' => 129.079167, 'deleted' => false),
        '43001' => array('name' => 'Ashikita', 'lat' => 32.283457, 'lon' => 130.531258, 'deleted' => false),
        '43002' => array('name' => 'Aso', 'lat' => 32.872801, 'lon' => 131.088483, 'deleted' => false),
        '43003' => array('name' => 'Amakusa', 'lat' => 32.508056, 'lon' => 130.047500, 'deleted' => false),
        '43004' => array('name' => 'Uto', 'lat' => 32.629708, 'lon' => 130.569444, 'deleted' => true),
        '43005' => array('name' => 'Kamimashiki', 'lat' => 32.734000, 'lon' => 130.860000, 'deleted' => false),
        '43006' => array('name' => 'Kamoto', 'lat' => 32.800000, 'lon' => 130.700000, 'deleted' => true),
        '43007' => array('name' => 'Kikuchi', 'lat' => 32.889000, 'lon' => 130.884000, 'deleted' => false),
        '43008' => array('name' => 'Kuma', 'lat' => 32.270000, 'lon' => 130.880000, 'deleted' => false),
        '43009' => array('name' => 'Shimomashiki', 'lat' => 32.615000, 'lon' => 130.869000, 'deleted' => false),
        '43010' => array('name' => 'Tamana', 'lat' => 33.028056, 'lon' => 130.590000, 'deleted' => false),
        '43011' => array('name' => 'Hotaku', 'lat' => 32.797111, 'lon' => 130.645146, 'deleted' => true),
        '43012' => array('name' => 'Yatsushiro', 'lat' => 32.584722, 'lon' => 130.673056, 'deleted' => false),
        '44001' => array('name' => 'Usa', 'lat' => 33.429750, 'lon' => 131.335417, 'deleted' => true),
        '44002' => array('name' => 'Oita', 'lat' => 33.214176, 'lon' => 131.431917, 'deleted' => true),
        '44003' => array('name' => 'Ono', 'lat' => 36.270944, 'lon' => 136.898556, 'deleted' => true),
        '44004' => array('name' => 'Kitaamabe', 'lat' => 33.247028, 'lon' => 131.876500, 'deleted' => true),
        '44005' => array('name' => 'kusu', 'lat' => 33.274000, 'lon' => 131.161000, 'deleted' => false),
        '44006' => array('name' => 'Shimoge', 'lat' => 33.477000, 'lon' => 131.131500, 'deleted' => true),
        '44007' => array('name' => 'Naoiri', 'lat' => 33.005685, 'lon' => 131.325639, 'deleted' => true),
        '44008' => array('name' => 'Nishikunisaki', 'lat' => 33.495528, 'lon' => 131.557167, 'deleted' => true),
        '44009' => array('name' => 'Hayami', 'lat' => 33.369248, 'lon' => 131.541766, 'deleted' => false),
        '44010' => array('name' => 'Higashikunisaki', 'lat' => 33.727222, 'lon' => 131.664444, 'deleted' => false),
        '44011' => array('name' => 'Hita', 'lat' => 33.190696, 'lon' => 130.963917, 'deleted' => true),
        '44012' => array('name' => 'Minamiamabe', 'lat' => 32.923889, 'lon' => 131.856781, 'deleted' => true),
        '45001' => array('name' => 'Kitamorokata', 'lat' => 31.722000, 'lon' => 131.173000, 'deleted' => false),
        '45002' => array('name' => 'Koyu', 'lat' => 32.249000, 'lon' => 131.483000, 'deleted' => false),
        '45003' => array('name' => 'Nishiusuki', 'lat' => 32.684000, 'lon' => 131.322000, 'deleted' => false),
        '45004' => array('name' => 'Nishimorokata', 'lat' => 31.925600, 'lon' => 130.990000, 'deleted' => false),
        '45005' => array('name' => 'Higashiusuki', 'lat' => 32.462000, 'lon' => 131.323000, 'deleted' => false),
        '45006' => array('name' => 'Higashimorokata', 'lat' => 31.983333, 'lon' => 131.316667, 'deleted' => false),
        '45007' => array('name' => 'Minaminaka', 'lat' => 31.601500, 'lon' => 131.379000, 'deleted' => true),
        '45008' => array('name' => 'Miyazaki', 'lat' => 31.907778, 'lon' => 131.420278, 'deleted' => true),
        '46001' => array('name' => 'Aira', 'lat' => 31.961944, 'lon' => 130.736111, 'deleted' => false),
        '46002' => array('name' => 'Isa', 'lat' => 32.057222, 'lon' => 130.613056, 'deleted' => true),
        '46003' => array('name' => 'Izumi', 'lat' => 32.179000, 'lon' => 130.152000, 'deleted' => false),
        '46004' => array('name' => 'Ibusuki', 'lat' => 31.252500, 'lon' => 130.633800, 'deleted' => true),
        '46005' => array('name' => 'Oshima', 'lat' => 27.810000, 'lon' => 128.940000, 'deleted' => false),
        '46006' => array('name' => 'Kagoshima', 'lat' => 31.583333, 'lon' => 130.550000, 'deleted' => false),
        '46007' => array('name' => 'Kawanabe', 'lat' => 31.322778, 'lon' => 130.296667, 'deleted' => true),
        '46008' => array('name' => 'Kimotsuki', 'lat' => 31.206000, 'lon' => 130.863000, 'deleted' => false),
        '46009' => array('name' => 'Kumage', 'lat' => 30.388720, 'lon' => 130.637340, 'deleted' => false),
        '46010' => array('name' => 'Satsuma', 'lat' => 31.942000, 'lon' => 130.460000, 'deleted' => false),
        '46011' => array('name' => 'Soo', 'lat' => 31.439000, 'lon' => 130.983000, 'deleted' => false),
        '46012' => array('name' => 'Hioki', 'lat' => 31.633722, 'lon' => 130.366667, 'deleted' => true),
        '47001' => array('name' => 'Kunigami', 'lat' => 26.641667, 'lon' => 128.130000, 'deleted' => false),
        '47002' => array('name' => 'Shimajiri', 'lat' => 26.176111, 'lon' => 127.722500, 'deleted' => false),
        '47003' => array('name' => 'Nakagami', 'lat' => 26.309167, 'lon' => 127.777222, 'deleted' => false),
        '47004' => array('name' => 'Miyako', 'lat' => 24.653889, 'lon' => 124.695000, 'deleted' => false),
        '47005' => array('name' => 'Yaeyama', 'lat' => 24.274444, 'lon' => 123.866111, 'deleted' => false),
    );

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
	/*
	 * Function returns all worked, but not confirmed guns.
	 * $postdata contains data from the form, e.g. LoTW/QSL confirmation filters.
	 */
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

	function getJcgBandConfirmed($location_list, $band, $postdata) {
		$bindings = array();
		$sql = "select adif as waja, name from dxcc_entities
			join (
				select col_dxcc from " . $this->config->item('table_name') . " thcv
				where station_id in (" . $location_list . ") and col_dxcc > 0";
		$sql .= $this->genfunctions->addBandToQuery($band, $bindings);

		if ($postdata['mode'] != 'All') {
			$sql .= " and (col_mode = ? or col_submode = ?)";
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= $this->genfunctions->addQslToQuery($postdata);
		$sql .= " group by col_dxcc
				) x on dxcc_entities.adif = x.col_dxcc";

		if ($postdata['includedeleted'] == NULL) {
			$sql .= " and dxcc_entities.end is null";
		}

		$query = $this->db->query($sql, $bindings);
		return $query->result();
	}

	function getJcgBandWorked($location_list, $band, $postdata) {
		$bindings = array();
		$sql = "select adif as waja, name from dxcc_entities
			join (
				select col_dxcc from " . $this->config->item('table_name') . " thcv
				where station_id in (" . $location_list . ") and col_dxcc > 0";

		$sql .= $this->genfunctions->addBandToQuery($band, $bindings);

		if ($postdata['mode'] != 'All') {
			$sql .= " and (col_mode = ? or col_submode = ?)";
			$bindings[] = $postdata['mode'];
			$bindings[] = $postdata['mode'];
		}

		$sql .= " group by col_dxcc
				) x on dxcc_entities.adif = x.col_dxcc";

		if ($postdata['includedeleted'] == NULL) {
			$sql .= " and dxcc_entities.end is null";
		}

		$query = $this->db->query($sql, $bindings);
		return $query->result();
	}

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

	function jcgGuns() {
		return $this->jaGuns;
	}

}
?>
