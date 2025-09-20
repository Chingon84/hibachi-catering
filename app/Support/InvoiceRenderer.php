<?php

namespace App\Support;

use Illuminate\Contracts\View\Factory as ViewFactory;

class InvoiceRenderer
{
    public static function renderHtml($reservation): string
    {
        /** @var ViewFactory $view */
        $view = app('view');
        return $view->make('invoices.pdf', ['reservation' => $reservation])->render();
    }

    public static function renderPdf($reservation): ?string
    {
        $html = self::renderHtml($reservation);
        // If Dompdf is available, use it; otherwise return null
        if (class_exists('Dompdf\\Dompdf')) {
            try {
                $dompdf = new \Dompdf\Dompdf([ 'isRemoteEnabled' => true ]);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                return $dompdf->output();
            } catch (\Throwable $e) {
                // fall through to null
            }
        }
        return null;
    }
}

