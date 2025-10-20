<?php

namespace App\Controllers;

use App\Controllers\CommonController;
use App\Models\Salon;
use App\Models\User;
use App\Models\Payment_mode;
use App\Models\Entry;

require_once(APPPATH . 'Views/vendor/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Export_excel extends CommonController
{
    protected $helpers = ["custom"];

    public function mode_wise_export()
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Sr. No.');
        $sheet->setCellValue('B1', 'Mode Name');
        $sheet->setCellValue('C1', 'Total');
        $total = 0;
        $db = db_connect();
        $sql = "SELECT spm.id, spm.name,SUM(se.amount) AS total FROM  salon_payment_modes spm LEFT JOIN  salon_cover_entries se ON se.payment_mode_id = spm.id WHERE  spm.is_active = 1  AND spm.deleted_at IS NULL GROUP BY spm.id, spm.name";
        $query = $db->query($sql);
        $result = $query->getResult();
        if($result) {
            $no = 2;
            foreach($result as $key => $val) {
                $sheet->setCellValue('A'.$no, $key+1);
                $sheet->setCellValue('B'.$no, $val->name);
                $sheet->setCellValue('C'.$no, $val->total);
                $total = $total + $val->total;
                $no++;
            }
        }
        $sheet->setCellValue('A'.$no, $key+1);
        $sheet->setCellValue('B'.$no, "TOTAL");
        $sheet->setCellValue('C'.$no, $total);
        
        // Write to the file (You can customize the filename as needed)
        $writer = new Xlsx($spreadsheet);

        // Set the file to be downloaded as an Excel file
        $filename = 'payment_mode_wise.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Write the file to output
        $writer->save('php://output');
        
        exit;
    }
}