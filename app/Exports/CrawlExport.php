<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CrawlExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        $headings = ['Zu crawlende URL', 'Verfügbarkeit', 'Fahrzeugbeschreibung laut Anbieter'];
        return $headings;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 100,
            'B' => 45,
            'C' => 250,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
            'A'  => ['alignment' => ['wrapText' => true]],
            'B'  => ['alignment' => ['wrapText' => true]],
            'C'  => ['alignment' => ['wrapText' => true]],
        ];
    }

    public function array(): array
    {
        return $this->data;
    }
}
