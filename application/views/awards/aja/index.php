<script>
	let tileUrl="<?php echo $this->optionslib->get_option('option_map_tile_server');?>"
	let user_map_custom = JSON.parse('<?php echo $user_map_custom; ?>');
</script>
<style>
    #ajamap {
       height: calc(100vh - 480px) !important;
       max-height: 900px !important;
    }
</style>
<div class="container">
        <!-- Award Info Box -->
        <br>
        <div id="awardInfoButton">
            <script>
            var lang_awards_info_button = "<?= __("Award Info"); ?>";
            var lang_award_info_ln1 = "<?= __("AJA - All Japan Award"); ?>";
            var lang_award_info_ln2 = "<?= __("May be claimed for having contacted (heard) and received a QSL card from an amateur station located in each of at least 200 different cities, guns and kus (wards) of Japan."); ?>";
            var lang_award_info_ln3 = "<?= __("AJA-300, 400, 500, 600, 700, 800, 900 and 1000 will be issued as separate awards."); ?>";
            var lang_award_info_ln4 = "<?= sprintf(__("For more information, please visit: %s."), "<a href='https://www.jarl.org/English/4_Library/A-4-2_Awards/Award_Main.htm' target='_blank'>https://www.jarl.org/English/4_Library/A-4-2_Awards/Award_Main.htm</a>"); ?>";
			var lang_award_info_ln5 = "<?= __("Fields taken for this Award: DXCC (Japan) and County (Must contain a valid reference!)"); ?>";
            </script>
            <h2><?php echo $page_title; ?></h2>
            <button type="button" class="btn btn-sm btn-primary me-1" id="displayAwardInfo"><?= __("Award Info"); ?></button>
        </div>
        <!-- End of Award Info Box -->

    <form class="form" action="<?php echo site_url('awards/aja'); ?>" method="post" enctype="multipart/form-data">
        <fieldset>

            <div class="mb-3 row">
                <div class="col-md-2" for="checkboxes"><?= __("Worked / Confirmed"); ?></div>
                <div class="col-md-10">
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="worked" id="worked" value="1" <?php if ($this->input->post('worked') || $this->input->method() !== 'post') echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="worked"><?= __("Show worked"); ?></label>
                    </div>
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="confirmed" id="confirmed" value="1" <?php if ($this->input->post('confirmed') || $this->input->method() !== 'post') echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="confirmed"><?= __("Show confirmed"); ?></label>
                    </div>
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="notworked" id="notworked" value="1" <?php if ($this->input->post('notworked')) echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="notworked"><?= __("Show not worked"); ?></label>
                    </div>
                </div>
            </div>

            <div class="mb-3 row">
                <div class="col-md-2"><?= __("Show QSO with QSL Type"); ?></div>
                <div class="col-md-10">
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="qsl" value="1" id="qsl" <?php if ($this->input->post('qsl') || $this->input->method() !== 'post') echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="qsl"><?= __("QSL"); ?></label>
                    </div>
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="lotw" value="1" id="lotw" <?php if ($this->input->post('lotw') || $this->input->method() !== 'post') echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="lotw"><?= __("LoTW"); ?></label>
                    </div>
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="eqsl" value="1" id="eqsl" <?php if ($this->input->post('eqsl') || $this->input->method() !== 'post') echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="eqsl"><?= __("eQSL"); ?></label>
                    </div>
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="qrz" value="1" id="qrz" <?php if ($this->input->post('qrz')) echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="qrz"><?= __("QRZ.com"); ?></label>
                    </div>
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="clublog" value="1" id="clublog" <?php if ($this->input->post('clublog')) echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="clublog"><?= __("Clublog"); ?></label>
                    </div>
                </div>
            </div>

            <div class="mb-3 row">
                <div class="col-md-2"><?= __("Deleted entities"); ?></div>
                <div class="col-md-10">
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="includedeleted" value="1" id="includedeleted" <?php if ($this->input->post('includedeleted')) echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="includedeleted"><?= __("Include deleted"); ?></label>
                    </div>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-md-2 control-label" for="band2"><?= __("Band"); ?></label>
                <div class="col-md-2">
                    <select id="band2" name="band" class="form-select form-select-sm">
                        <option value="All" <?php if ($this->input->post('band') == "All" || $this->input->method() !== 'post') echo ' selected'; ?> ><?= __("Every band"); ?></option>
                        <?php foreach($worked_bands as $band) {
                            echo '<option value="' . $band . '"';
                            if ($this->input->post('band') == $band) echo ' selected';
                            echo '>' . $band . '</option>'."\n";
                        } ?>
                    </select>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-md-2 control-label" for="mode"><?= __("Mode"); ?></label>
                <div class="col-md-2">
                <select id="mode" name="mode" class="form-select form-select-sm">
                    <option value="All" <?php if ($this->input->post('mode') == "All" || $this->input->method() !== 'post') echo ' selected'; ?>><?= __("All"); ?></option>
                    <?php
                    foreach($modes->result() as $mode){
                        if ($mode->submode == null) {
                            echo '<option value="' . $mode->mode . '"';
                            if ($this->input->post('mode') == $mode->mode) echo ' selected';
                            echo '>'. $mode->mode . '</option>'."\n";
                        } else {
                            echo '<option value="' . $mode->submode . '"';
                            if ($this->input->post('mode') == $mode->submode) echo ' selected';
                            echo '>' . $mode->submode . '</option>'."\n";
                        }
                    }
                    ?>
                </select>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-md-2 control-label" for="button1id"></label>
                <div class="col-md-10">
                    <button id="button2id" type="reset" name="button2id" class="btn btn-sm btn-warning"><?= __("Reset"); ?></button>
                    <button id="button1id" type="submit" name="button1id" class="btn btn-sm btn-primary"><?= __("Show"); ?></button>
                    <?php if ($aja_array) {?>
                    <button type="button" onclick="load_aja_map();" class="btn btn-info btn-sm"><i class="fas fa-globe-asia"></i> <?= __("Show AJA Map"); ?></button>
					<button id="button3id" type="button" onclick="export_qsos();" name="button3id" class="btn btn-sm btn-info"><?= __("Export confirmed QSOs"); ?></button>
                    <?php } ?>
                </div>
            </div>

        </fieldset>
    </form>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="table-tab" data-bs-toggle="tab" href="#table" role="tab" aria-controls="table" aria-selected="true"><?= __("Results"); ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" onclick="load_aja_map();" id="map-tab" data-bs-toggle="tab" href="#ajamaptab" role="tab" aria-controls="home" aria-selected="false"><?= __("Map"); ?></a>
        </li>
    </ul>
    <br />

    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade" id="ajamaptab" role="tabpanel" aria-labelledby="home-tab">
    <br />

    <div id="ajamap" class="map-leaflet" ></div>

    </div>

        <div class="tab-pane fade show active" id="table" role="tabpanel" aria-labelledby="table-tab">

    <?php
    if ($aja_array) {
        echo '
                <table id="ajaTable" style="width:100%" class="table-sm table table-bordered table-hover table-striped table-condensed text-center">
                    <thead>
                    <tr>
						<td>' . __("Number") . '</td>
						<td>' . __("Name") . '</td>
						<td>' . __("Type") . '</td>';

        foreach($bands as $band) {
            echo '<td>' . $band . '</td>';
        }
        echo '</tr>
                    </thead>
                    <tbody>';
        foreach ($aja_array as $entity => $value) {
            echo '<tr>';
            echo '<td style="text-align: center">' . $value['Number'] . '</td>';
            echo '<td style="text-align: center">' . $value['Name'] . '</td>';
            echo '<td style="text-align: center">' . $value['Type'] . '</td>';
            foreach ($bands as $band) {
				echo '<td style="text-align: center">' . awards_render_jcc_cell($entity, $band, $value[$band], $postdata) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';

        $summary_labels = array(
            'city' => 'JCC count',
            'gun' => 'JCG count',
            'ku' => 'Ku count',
            'total' => __("Total"),
        );
        $summary_bands = array_values(array_filter($bands, function ($band) {
            return $band !== 'SAT';
        }));
        $show_sat = in_array('SAT', $bands);

        $render_summary_table = function ($summary_key, $title) use ($aja_summary, $summary_labels, $summary_bands, $show_sat) {
            echo '<h2>' . $title . '</h2>';
            echo '<table class="table-sm tablesummary table table-bordered table-hover table-striped table-condensed text-center">';
            echo '<thead><tr>';
            echo '<td></td>';
            foreach ($summary_bands as $band) {
                echo '<td>' . $band . '</td>';
            }
            echo '<td><b>' . __("Total") . '</b></td>';
            if ($show_sat) {
                echo '<td>' . __("SAT") . '</td>';
            }
            echo '</tr></thead>';
            echo '<tbody>';
            foreach ($summary_labels as $type => $label) {
                echo '<tr>';
                echo '<td>' . $label . '</td>';
                foreach ($summary_bands as $band) {
                    echo '<td style="text-align: center">' . ($aja_summary[$type][$summary_key][$band] ?? 0) . '</td>';
                }
                echo '<td style="text-align: center"><b>' . ($aja_summary[$type][$summary_key]['Total'] ?? 0) . '</b></td>';
                if ($show_sat) {
                    echo '<td style="text-align: center">' . ($aja_summary[$type][$summary_key]['SAT'] ?? 0) . '</td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table>';
        };

        $render_summary_table('worked', __("Summary") . ' - ' . __("Worked"));
        $render_summary_table('confirmed', __("Summary") . ' - ' . __("Confirmed"));

        echo '</div>';
    }
    else {
        echo '<div class="alert alert-danger" role="alert">' . __("Nothing found!") . '</div>';
    }
    ?>
                </div>
        </div>
</div>
