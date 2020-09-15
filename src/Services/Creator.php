<?php


namespace App\Services;


use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Contracts\Translation\TranslatorInterface;

class Creator
{ /**
 * @var Spreadsheet
 */
    private $spreadsheet;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(Spreadsheet $spreadsheet, TranslatorInterface $translator)
    {
        $this->spreadsheet = $spreadsheet;
        $this->translator = $translator;
    }

    /**
     * @param string $temp_file
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function create(array $purchases, string $temp_file)
    {
        $sheet = $this->spreadsheet->setActiveSheetIndex(0);

        $this->configSheet($sheet);

        $this->setTableHeader($sheet);
        $this->addPurchaseDetails($purchases, $sheet);

        $writer = new Xlsx($this->spreadsheet);
        ob_end_clean();
        $writer->save($temp_file);
    }

    private function setTableHeader(Worksheet $sheet)
    {
        $sheet->setTitle(ucwords("Access Point"));

        $sheet->setCellValue('A1', $this->translator->trans('app.aruba_program'));
        $sheet->setCellValue('A2', $this->translator->trans('app.ap.my_registered_aps'));
        $date = new \DateTime('now');
        $sheet->setCellValue('A3', sprintf('%s: %s %s: %s',
            $this->getTranslation('app.date'),
            $date->format('d-m-Y'),
            $this->getTranslation('app.time'),
            $date->format('H:m:s')
        ));

        $sheet->getStyle('A4:D4')
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF8300');
        $sheet->getStyle('A4:D4')->getFont()->setColor(new Color(Color::COLOR_WHITE));
        $sheet->setCellValue('A4', ucwords($this->getTranslation('app.ap.invoiceNumber')));
        $sheet->setCellValue('B4', ucwords($this->getTranslation('app.ap.submitted_day')));
        $sheet->setCellValue('C4', ucwords($this->getTranslation('app.ap.serial_number')));
        $sheet->setCellValue('D4', ucwords($this->getTranslation('app.ap.status')));
    }

    /**
     * @param array $purchases
     * @param Worksheet $sheet
     * @throws Exception
     */
    private function addPurchaseDetails(array $data, Worksheet $sheet): void
    {
        $row = 5;
        foreach ($data as $purchase) {
                $sheet->getCell('A' . $row)->setValue($purchase->getNames());
                $sheet->getCell('B' . $row)->setValue($purchase->getGender());
                $row++;

        }
        $this->setTableBorders($sheet, 'A4:D' . --$row);
    }

    /**
     * @param Worksheet $sheet
     * @throws Exception
     */
    private function configSheet(Worksheet $sheet): void
    {
        $sheet->setShowGridlines(false);
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('A3:D3');
        $sheet->getStyle('A:D')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal('left');
        $this->setColumnsRangeAutoSize($sheet, 'A', 'D');
    }

    private function setColumnsRangeAutoSize(Worksheet $sheet, $start, $end)
    {
        foreach (range($start, $end) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    /**
     * @param Worksheet $sheet
     * @param $pCellCoordinate
     */
    private function setTableBorders(Worksheet $sheet, $pCellCoordinate): void
    {
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '404040'],
                ],
            ],
        ];

        $sheet->getStyle($pCellCoordinate)->applyFromArray($styleArray);
    }

    /**
     * @param string $id
     * @return string
     */
    private function getTranslation(string $id): string
    {
        return $this->translator->trans($id);
    }

}