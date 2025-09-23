<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockReport;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;



class StockReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Datatables $datatables)
    {
        if ($request->ajax()) {
            $query = StockReport::select('stock_reports.*');

            return datatables()->eloquent($query)->make(true);
        }

        return view('Admin.stockreport.list');
    }
    public function StockExport()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columns = [
            'A' => 'Am_Sku_ID',
            'B' => 'Shopify_Sku_Id',
            'C' => 'product_name',
            'D' => 'shopify_available_qty',
            'E' => 'am_available_qty',
            'F' => 'shopify_barcode',
            'G' => 'upc_display'
        ];

        foreach ($columns as $col => $header) {
            $sheet->setCellValue($col . '1', $header);
        }

        $reports = StockReport::get();

        $row = 2;
        foreach ($reports as $item) {
            $sheet->setCellValueExplicit('A' . $row, $item->am_sku_id, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B' . $row, $item->shopify_sku_id ?? '', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $row, $item->produt_name ?? '', DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $row, $item->shopify_available_qty ?? '');
            $sheet->setCellValue('E' . $row, $item->am_available_qty ?? '');
            $sheet->setCellValueExplicit('F' . $row, $item->shopify_barcode ?? '', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('G' . $row, $item->upc_display ?? '', DataType::TYPE_STRING);
            $row++;
        }

        foreach (array_keys($columns) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        $filename = 'inventory_report_' . now()->format('Ymd_His') . '.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($temp_file);

        return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
