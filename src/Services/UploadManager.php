<?php


namespace App\Services;


use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;

class UploadManager
{

    /**
     * @var UploadHandler
     */
    private $handler;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(UploadHandler $handler, EntityManagerInterface  $entityManager)
    {
        $this->handler = $handler;
        $this->entityManager = $entityManager;
    }

    /**
     * @param $file
     * @return bool
     */
    public function processUpload($file)
    {

        return $this->importFile($file);

    }

    private function importFile($file)
    {
        $read = new Xlsx();

        $read->setReadDataOnly(true);
        $read->setReadEmptyCells(false);
        $spreadSheet = $read->load($file);

        $spreadSheet->getActiveSheet()->setAutoFilter('A2:B2');

        foreach ($spreadSheet->getActiveSheet()->getRowIterator(2) as $datum) {

            if ($spreadSheet->getActiveSheet()
                ->getRowDimension($datum->getRowIndex())->getVisible()) {

                $names = $this->getFormattedValueByCell($spreadSheet, 'A', $datum);
                $gender = $this->getFormattedValueByCell($spreadSheet, 'B', $datum);

                $data[] = [
                    'nombre' => $names,
                    'genero' => $gender,
                ];
            }
        }


        foreach ($data as $item) {
            $this->handler->saveData($item['genero'], $item['nombre']);

        }

        $this->entityManager->flush();

        return true;

    }

    /**
     * @param Spreadsheet $spreadSheet
     * @param string $letterCol
     * @param Row $datum
     * @return string
     * @throws Exception
     */
    private function getFormattedValueByCell(
        Spreadsheet $spreadSheet,
        string $letterCol,
        Row $datum
    ): string
    {
        return $spreadSheet->getActiveSheet()
            ->getCell(
                $letterCol . $datum->getRowIndex()
            )->getFormattedValue();
    }

}