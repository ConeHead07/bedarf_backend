<?php

namespace App\Utils\Importer\Interfaces;

interface RowMapper {
    public function rowMap(array $row): array;
}
