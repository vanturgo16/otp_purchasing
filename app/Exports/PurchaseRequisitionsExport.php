<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Log;

class PurchaseRequisitionsExport implements FromView, WithStyles, ShouldAutoSize
{
    protected $datas;

    public function __construct($datas)
    {
        // ✅ Sanitize data to prevent Excel corruption
        $this->datas = $this->sanitizeData($datas);
    }

    public function view(): View
    {
        return view('exports.purchase_requisitions', ['datas' => $this->datas]);
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $totalRows = $sheet->getHighestRow();

        Log::info("DEBUG: Highest Column = {$lastColumn}, Total Rows = {$totalRows}");

        // 1️⃣ Header Style (Bold, Centered, Gray Background)
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D3D3D3'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray($headerStyle);

        // 2️⃣ Apply Borders to All Data
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle("A1:{$lastColumn}{$totalRows}")->applyFromArray($borderStyle);

        // 3️⃣ Merge Duplicate Columns While Keeping Items Visible
        foreach ($this->datas->groupBy('id') as $groupedData) {
            if ($groupedData->isEmpty()) {
                continue;
            }

            $keys = array_values($groupedData->keys()->toArray());
            $firstRow = $keys[0] + 2; // ✅ FIXED: Ensuring correct base row
            $lastRow = $firstRow + count($groupedData) - 1;

            if ($firstRow < $lastRow) { // ✅ FIXED: Ensuring merge range is valid
                $columnsToMerge = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
                foreach ($columnsToMerge as $col) {
                    $range = "{$col}{$firstRow}:{$col}{$lastRow}";

                    Log::info("Merging cells: {$range}");

                    $sheet->mergeCells($range);
                    $sheet->getStyle($range)->getAlignment()
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP)
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                }
            } else {
                Log::warning("Skipping merge: Invalid range (First Row: {$firstRow}, Last Row: {$lastRow})");
            }
        }
    }


    // ✅ 4️⃣ Sanitize Data (Fix Hidden Characters That Break Excel)
    private function sanitizeData($data)
    {
        foreach ($data as &$row) {
            foreach ($row as &$value) {
                $value = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', trim($value));
            }
        }
        unset($value);
        return $data;
    }
}
