<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;

class PdfController extends Controller
{
    public function download()
    {
        $pdf = PDF::loadView('pdf.example', ['dummy_key' => 'dummy_value'])->setPaper([0, 0, 594, 841], 'portrait');

        return $pdf->download('pdf_file.pdf');
    }
}
