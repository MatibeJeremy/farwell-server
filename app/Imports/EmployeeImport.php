<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class EmployeeImport implements ToArray
{

    /**
     * @inheritDoc
     */
    public function array(array $array)
    {
        return $array;
    }
}
