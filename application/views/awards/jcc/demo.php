<style>
    .award-grid-legend-swatch {
        width: 1rem;
        height: 1rem;
        display: inline-block;
    }

    .award-grid-legend-swatch-deleted {
        background-image: repeating-linear-gradient(135deg, rgba(0, 0, 0, 0.18) 0, rgba(0, 0, 0, 0.18) 2px, transparent 2px, transparent 6px);
    }

    .award-grid-prefecture {
        min-width: 12rem;
    }

    .award-grid-slots {
        align-content: flex-start;
    }

    .award-grid-slot {
        width: 3rem;
        height: 2rem;
        padding: 0 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.9rem;
        line-height: 1;
        transition: transform 0.12s ease, box-shadow 0.12s ease;
    }

    .award-grid-slot:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.12);
    }

    .award-grid-slot-empty {
        color: inherit !important;
    }

    .award-grid-slot-deleted {
        background-image: repeating-linear-gradient(135deg, rgba(0, 0, 0, 0.18) 0, rgba(0, 0, 0, 0.18) 2px, transparent 2px, transparent 6px);
    }

    .award-grid-slot-designated {
        box-shadow: inset 0 0 0 2px rgba(13, 110, 253, 0.4);
    }

    .award-grid-summary {
        margin-top: 1.5rem;
    }

    @media (max-width: 991.98px) {
        .award-grid-prefecture {
            min-width: 0;
        }
    }
</style>

<div class="container">
    <br>
    <div id="awardInfoButton">
        <script>
        var lang_awards_info_button = "<?= __("Award Info"); ?>";
        var lang_award_info_ln1 = "<?= __("JCC - Japan Century Cities Award"); ?>";
        var lang_award_info_ln2 = "<?= __("May be claimed for having contacted (heard) and received a QSL card from an amateur station located in each of at least 100 different cities of Japan."); ?>";
        var lang_award_info_ln3 = "<?= __("This demo shows JCC progress as grouped slots by prefecture instead of the classic entity by band table."); ?>";
        var lang_award_info_ln4 = "<?= sprintf(__("For more information, please visit: %s."), "<a href='https://www.jarl.org/English/4_Library/A-4-2_Awards/Award_Main.htm' target='_blank'>https://www.jarl.org/English/4_Library/A-4-2_Awards/Award_Main.htm</a>"); ?>";
        var lang_award_info_ln5 = "<?= __("Fields taken for this Award: DXCC (Japan) and County (Must contain a valid reference!)"); ?>";
        </script>
        <h2><?php echo $page_title; ?></h2>
        <button type="button" class="btn btn-sm btn-primary me-1" id="displayAwardInfo"><?= __("Award Info"); ?></button>
        <a class="btn btn-sm btn-outline-secondary" href="<?php echo site_url('awards/jcc'); ?>">Classic JCC View</a>
    </div>

    <form class="form" action="<?php echo site_url('awards/jcc_demo'); ?>" method="post" enctype="multipart/form-data">
        <fieldset>
            <div class="mb-3 row">
                <div class="col-md-2"><?= __("Show QSO with QSL Type"); ?></div>
                <div class="col-md-10">
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="qsl" value="1" id="qsl" <?php if (($postdata['qsl'] ?? null) == 1) echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="qsl"><?= __("QSL"); ?></label>
                    </div>
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="lotw" value="1" id="lotw" <?php if (($postdata['lotw'] ?? null) == 1) echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="lotw"><?= __("LoTW"); ?></label>
                    </div>
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="eqsl" value="1" id="eqsl" <?php if (($postdata['eqsl'] ?? null) == 1) echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="eqsl"><?= __("eQSL"); ?></label>
                    </div>
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="qrz" value="1" id="qrz" <?php if (($postdata['qrz'] ?? null) == 1) echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="qrz"><?= __("QRZ.com"); ?></label>
                    </div>
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="clublog" value="1" id="clublog" <?php if (($postdata['clublog'] ?? null) == 1) echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="clublog"><?= __("Clublog"); ?></label>
                    </div>
                </div>
            </div>

            <div class="mb-3 row">
                <div class="col-md-2"><?= __("Deleted cities"); ?></div>
                <div class="col-md-10">
                    <div class="form-check-inline">
                        <input class="form-check-input" type="checkbox" name="includedeleted" value="1" id="includedeleted" <?php if (($postdata['includedeleted'] ?? null) == 1) echo ' checked="checked"'; ?> >
                        <label class="form-check-label" for="includedeleted"><?= __("Include deleted"); ?></label>
                    </div>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-md-2 control-label" for="band2"><?= __("Band"); ?></label>
                <div class="col-md-2">
                    <select id="band2" name="band" class="form-select form-select-sm">
                        <option value="All" <?php if (($postdata['band'] ?? 'All') == 'All') echo ' selected'; ?>><?= __("Every band"); ?></option>
                        <?php foreach ($worked_bands as $band) {
                            echo '<option value="' . $band . '"';
                            if (($postdata['band'] ?? 'All') == $band) {
                                echo ' selected';
                            }
                            echo '>' . $band . '</option>' . "\n";
                        } ?>
                    </select>
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-md-2 control-label" for="mode"><?= __("Mode"); ?></label>
                <div class="col-md-2">
                    <select id="mode" name="mode" class="form-select form-select-sm">
                        <option value="All" <?php if (($postdata['mode'] ?? 'All') == 'All') echo ' selected'; ?>><?= __("All"); ?></option>
                        <?php
                        foreach ($modes->result() as $mode) {
                            if ($mode->submode == null) {
                                echo '<option value="' . $mode->mode . '"';
                                if (($postdata['mode'] ?? 'All') == $mode->mode) {
                                    echo ' selected';
                                }
                                echo '>' . $mode->mode . '</option>' . "\n";
                            } else {
                                echo '<option value="' . $mode->submode . '"';
                                if (($postdata['mode'] ?? 'All') == $mode->submode) {
                                    echo ' selected';
                                }
                                echo '>' . $mode->submode . '</option>' . "\n";
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
                </div>
            </div>
        </fieldset>
    </form>

    <div class="mt-4">
        <div class="d-flex flex-wrap gap-3 mb-3 small text-body-secondary">
            <div class="d-inline-flex align-items-center gap-2">
                <span class="award-grid-legend-swatch rounded border border-success text-bg-success"></span>
                <span><?= __("Confirmed"); ?></span>
            </div>
            <div class="d-inline-flex align-items-center gap-2">
                <span class="award-grid-legend-swatch rounded border border-danger text-bg-danger"></span>
                <span><?= __("Worked not confirmed"); ?></span>
            </div>
            <div class="d-inline-flex align-items-center gap-2">
                <span class="award-grid-legend-swatch rounded border bg-body"></span>
                <span><?= __("Not worked"); ?></span>
            </div>
            <div class="d-inline-flex align-items-center gap-2">
                <span class="award-grid-legend-swatch award-grid-legend-swatch-deleted rounded border bg-body"></span>
                <span><?= __("Deleted"); ?></span>
            </div>
        </div>

        <?php if (!$has_active_slots) { ?>
            <div class="alert alert-info" role="alert">
                <?= __("No worked or confirmed JCC slots match the current filters."); ?>
            </div>
        <?php } ?>

        <div class="border-top">
            <?php foreach ($jcc_groups as $group) { ?>
                <section class="d-flex flex-column flex-lg-row gap-3 py-3 border-bottom">
                    <div class="award-grid-prefecture flex-shrink-0">
                        <div class="gap-2 mb-1">
                            <span class="fs-5 fw-bold"><?php echo $group['prefecture_code']; ?></span>
                            <span class="fw-bold gap-2 mb-1"><?php echo $group['prefecture_name']; ?></span>
                        </div>
                        <!-- <div class="small text-body-secondary">
                            <?php echo $group['confirmed_count']; ?> <?= __("Confirmed"); ?> /
                            <?php echo $group['worked_count']; ?> <?= __("Worked"); ?> /
                            <?php echo $group['slot_count']; ?> <?= __("Total"); ?>
                        </div> -->
                    </div>
                    <div class="award-grid-slots d-flex flex-wrap gap-2">
                        <?php foreach ($group['slots'] as $slot) {
                            echo awards_render_jcc_grid_slot($slot, $postdata);
                        } ?>
                    </div>
                </section>
            <?php } ?>
        </div>
    </div>

    <?php
    $summary_columns = array();
    if (count($bands) > 1) {
        $summary_columns = array_keys($jcc_summary['worked']);
    } elseif (count($bands) === 1) {
        $summary_columns = array($bands[0]);
    } else {
        $summary_columns = array('Total');
    }
    ?>

    <div class="award-grid-summary">
        <h3><?= __("Summary"); ?></h3>
        <table class="table-sm tablesummary table table-bordered table-hover table-striped text-center">
            <thead>
                <tr>
                    <th></th>
                    <?php foreach ($summary_columns as $column) { ?>
                        <th><?php echo $column === 'Total' ? '<b>' . __("Total") . '</b>' : $column; ?></th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= __("Total worked"); ?></td>
                    <?php foreach ($summary_columns as $column) { ?>
                        <td><?php echo $jcc_summary['worked'][$column] ?? 0; ?></td>
                    <?php } ?>
                </tr>
                <tr>
                    <td><?= __("Total confirmed"); ?></td>
                    <?php foreach ($summary_columns as $column) { ?>
                        <td><?php echo $jcc_summary['confirmed'][$column] ?? 0; ?></td>
                    <?php } ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('[data-bs-toggle="tooltip"]').tooltip({
            html: true,
            placement: 'top',
            boundary: 'window'
        });
    });
</script>