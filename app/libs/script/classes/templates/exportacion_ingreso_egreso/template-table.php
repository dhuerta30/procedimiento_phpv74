<div class="table-responsive">
    <table class="table pdocrud-table table-bordered table-striped table-sm <?php if (isset($settings["tableCellEdit"]) && $settings["tableCellEdit"]) echo "pdocrud-excel-table" ?>" data-obj-key="<?php echo $objKey; ?>">
        <?php if ($settings["headerRow"]) { ?>
            <thead>
                <tr class="pdocrud-header-row">
                    <?php if ($settings["numberCol"]) { ?>
                        <th class="w1">
                            #
                        </th>
                    <?php }
                    if ($settings["checkboxCol"]) { ?>
                        <th class="w1 text-center">
                            <input type="checkbox" value="select-all" name="pdocrud_select_all" class="pdocrud-select-all" />
                        </th>
                        <?php }
                    if ($columns) foreach ($columns as $colkey => $column) {
                        if (!in_array($column["col"], $colsRemove)) {
                        ?>

                            <?php if ($settings["sortable"]): ?>
                            <th <?php echo $column["attr"]; ?> data-action="<?php echo $column["sort"]; ?>" data-sortkey="<?php echo $colkey; ?>" class="pdocrud-actions-sorting pdocrud-<?php echo $column["sort"]; ?>">
                                <span> <?php echo $column["colname"];
                                        echo $column["tooltip"];
                                        ?>
                                </span>
                            </th>
                            <?php else: ?>
                                <th <?php echo $column["attr"]; ?>>
                                <span> <?php echo $column["colname"];
                                        echo $column["tooltip"];
                                        ?>
                                </span>
                            </th>
                            <?php endif; ?>
                        <?php }
                    }
                    if ($settings["actionbtn"]) {
                        ?>
                        <th>
                            <?php echo $lang["actions"] ?>
                        </th>
                    <?php } ?>
                </tr>
            </thead>
        <?php } ?>
        <tbody>
            <input type="hidden" value="<?php echo $objKey; ?>" class="d-none pdoobj" />
            <?php
            $rowcount = $settings["row_no"];
            if ($data)
                foreach ($data as $rows) {
                    $sumrow = false;
            ?>
                <tr data-id="<?php if (isset($rows[$pk])) echo $rows[$pk]; ?>" id="pdocrud-row-<?php echo $rowcount; ?>" class="pdocrud-data-row <?php if (isset($rows[0]["class"])) echo $rows[0]["class"]; ?>" <?php if (isset($rows[0]["style"])) echo $rows[0]["style"]; ?>>
                    <?php if ($settings["numberCol"]) { ?>
                        <td class="pdocrud-row-count">
                            <?php echo $rowcount + 1; ?>
                        </td>
                    <?php }
                    if ($settings["checkboxCol"]) { ?>
                        <td class="pdocrud-row-checkbox-actions">
                            <input type="checkbox" class="pdocrud-select-cb" value="<?php echo $rows[$pk]; ?>" />
                        </td>
                        <?php }
                    foreach ($rows as $col => $row) {
                        if (!in_array($col, $colsRemove)) {
                            if (is_array($row)) {
                        ?>
                                <td class="pdocrud-row-cols <?php if (isset($row["class"])) echo $row["class"]; ?>" <?php if (isset($row["style"])) echo $row["style"]; ?>>
                                    <?php if (isset($row["sum_type"])) {
                                        echo $lang[$row["sum_type"]];
                                        $sumrow = true;
                                    } ?>
                                    <?php echo $row["content"]; ?>
                                </td>
                            <?php
                            } else {
                            ?>
                                <td class="pdocrud-row-cols">
                                    <?php echo $row; ?>
                                </td>
                        <?php
                            }
                        }
                    }
                    if ($sumrow) {
                        ?>
                        <td class="pdocrud-row-actions"></td>
                    <?php continue;
                    }
                    if (is_array($btnActions) && count($btnActions)) {
                    ?>
                        <td class="pdocrud-row-actions">
                            <?php 
                               $fechacorte = $rows["fecha_corte"];
                            ?>
                            <a href="javascript:;" class="btn btn-warning btn-sm descargar_excel" title="Descargar Excel" data-fechacorte="<?=$fechacorte?>"  data-estado="<?=strip_tags($rows["tipo_exportacion"])?>" data-id="<?=trim(str_replace("#", '', $rows[$pk]));?>"><i class="fa fa-file-excel-o text-white"></i> <i class="fas fa-arrow-left text-white"></i> </a>
                        </td>
                    <?php } ?>
                </tr>
            <?php
                    $rowcount++;
                }
            else {
            ?>
                <tr class="pdocrud-data-row">
                    <td class="pdocrud-row-count text-center" colspan="100%">
                        <?php echo $lang["no_data"] ?>
                    </td>
                </tr>
            <?php
            }
            ?>
        </tbody>
        <?php if ($settings["footerRow"]) { ?>
            <tfoot>
                <tr class="pdocrud-header-row">
                    <?php if ($settings["numberCol"]) { ?>
                        <th class="w1">
                            #
                        </th>
                    <?php }
                    if ($settings["checkboxCol"]) { ?>
                        <th class="w1">
                            <input type="checkbox" value="select-all" name="pdocrud_select_all" class="pdocrud-select-all" />
                        </th>
                    <?php } ?>
                    <?php if ($columns) foreach ($columns as $colkey => $column) {
                        if (!in_array($column["col"], $colsRemove)) {
                    ?>
                            <th <?php echo $column["attr"]; ?> data-action="<?php echo $column["sort"]; ?>" data-sortkey="<?php echo $colkey; ?>" class="pdocrud-actions-sorting pdocrud-<?php echo $column["sort"]; ?>">
                                <?php echo $column["colname"];
                                echo $column["tooltip"];
                                ?>
                            </th>
                        <?php }
                    }
                    if ($settings["actionbtn"]) {
                        ?>
                        <th>
                            <?php echo $lang["actions"] ?>
                        </th>
                    <?php } ?>
                </tr>
            </tfoot>
        <?php } ?>
    </table>
</div>