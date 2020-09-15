<?php

namespace App\Http;


use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HttpResponseTrait {

    /**
     * @param Xlsx $writer
     * @param $name
     * @param string $extension
     * @return StreamedResponse
     */
    public function createResponseFromWriter(Xlsx $writer, $name, $extension = 'xlsx')
    {
        // adding headers
        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );

        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $name.'_'.date("dmy").'.'.$extension
        );

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

} 