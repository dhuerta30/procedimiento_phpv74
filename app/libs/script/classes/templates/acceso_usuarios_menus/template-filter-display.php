<div class="row pdocrud-filters-container" data-objkey="<?php echo $objKey; ?>" >
    <div class="col-sm-3">
        <?php if (isset($filters) && count($filters)) { ?>
            <div class="pdocrud-filters-options">
                <div class="pdocrud-filter-selected">
                    <span class="pdocrud-filter-option-remove btn btn-warning" data-rendertype="CRUD"><i class="fa fa-paint-brush"></i> <?php echo $lang["clear_all"] ?></span>
                </div>
                <br>
                <?php
                foreach ($filters as $filter) {
                    echo $filter;
                }
                ?>
                <a href="javascript:;" class="btn btn-primary" id="filter-button" data-rendertype="CRUD"><?php echo $lang["filter_text"] ?></a>

            </div>
            <?php
        }
        ?>
    </div>
    <div class="col-sm-9">
        <?php echo $data ?>
    </div>
</div>