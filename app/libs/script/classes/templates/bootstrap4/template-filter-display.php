<div class="row pdocrud-filters-container" data-objkey="<?php echo $objKey; ?>">
    <div class="col-md-12">
        <?php if (isset($filters) && count($filters)) { ?>
            <div class="pdocrud-filters-options text-center">
                <div class="pdocrud-filter-selected text-center">
                    <span class="pdocrud-filter-option-remove btn btn-success mb-3"><i class="fa fa-paint-brush"></i> <?php echo $lang["clear_all"] ?></span>
                </div>
                <?php
                foreach ($filters as $filter) {
                    echo $filter;
                }
                ?>
            </div>
            <div class="row mb-2">
                <div class="col-md-12 text-center">
                    <button class="btn btn-primary" id="filter-button"><i class="fa fa-search"></i> <?php echo $lang["filter_text"] ?></button>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
    <div class="col-md-12">
        <?php echo $data ?>
    </div>
</div>