<?php

namespace App\Utils\Importer\Interfaces;

interface RowNameMapper {
    public function colMap(array $row): array;
}
