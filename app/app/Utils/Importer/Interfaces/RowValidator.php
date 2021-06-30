<?php

namespace App\Utils\Importer\Interfaces;

interface RowValidator {
    public function rowValidate(array $row): bool;
    public function getValidationMessage(): string;
}
