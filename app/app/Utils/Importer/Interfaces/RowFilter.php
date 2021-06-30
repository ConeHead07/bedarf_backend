<?php

namespace App\Utils\Importer\Interfaces;

interface RowFilter {
    public function rowFilter(array $row): bool;
}
