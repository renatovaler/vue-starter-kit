<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class HistoryExport implements FromCollection, WithHeadings
{
    protected $data;
    protected $headings;

    public function __construct(array $data)
    {
        $this->data = collect($data);

        if (!empty($data)) {
            $this->headings = array_keys($data[0]);
        } else {
            $this->headings = [];
        }
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
