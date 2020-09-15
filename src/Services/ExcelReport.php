<?php


namespace App\Services;



use App\Http\HttpResponseTrait;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExcelReport
{
    //estilo al excel
    use HttpResponseTrait;

    /**
     * @var Worksheet
     */
    private $activeWorksheet;

    /**
     * @var int
     */
    private $firstRow = 1;
    private $firstCol = 0;


    /**
     * @var Spreadsheet
     */
    private $spreadsheet;
    /**
     * @var ParameterBagInterface
     */
    private $pro;
    /**
     * @var Creator
     */
    private $creator;


    public function __construct(Spreadsheet $spreadsheet, ParameterBagInterface  $pro, Creator  $creator)
    {
        $this->spreadsheet = $spreadsheet;
        $this->pro = $pro->get('export_file_properties');
        $this->creator = $creator;
    }

    /**
     * @param $data
     * @param $headers
     * @param $name
     * @return Xlsx
     * @throws Exception
     */
    public function create($data, $headers, $name): Xlsx
    {
        $this->spreadsheet->setActiveSheetIndex(0);
        $this->activeWorksheet = $this->spreadsheet->getActiveSheet();
        $this->spreadsheet->getProperties()
            ->setCreator('grupo UNIN')
        ->setTitle('pruebas!');

        $this->createHeaderStyle($headers, $name);

        return $this->setData($data, $headers, $name);

    }

    /**
     * @param $headers
     * @param $name
     * @throws Exception
     */
    public function createHeaderStyle($headers, $name)
    {

        //GENERAL STYLES HEADER AND ROWS
        $lightGrey = [
            'font' => [
                'bold' => true,
                'color' => [
                    'rgb' => '9D9D9D'
                ]
            ]
        ];

        $tableHeading = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FF8300'
                ]
            ],
            'font' => [
                'bold' => true,
                'color' => [
                    'argb' => Color::COLOR_WHITE]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => [
                        'rgb' => 000]
                ]
            ]
        ];


        $this->activeWorksheet->getParent()->getDefaultStyle()->applyFromArray(['font' => ['name' => 'arial', 'size' => 10]]);
        $this->activeWorksheet->getStyle('B2')->applyFromArray(['font' => ['bold' => true, 'color' => ['rgb' => 'FF8300']]]);
        $this->activeWorksheet->getStyle('B3')->applyFromArray(['font' => ['bold' => true]]);
        $this->activeWorksheet->getStyle('B4')->applyFromArray($lightGrey);
        $this->activeWorksheet->getStyle('C6')->applyFromArray($lightGrey);

        //SHEET HEADINGS
        $date = new \DateTime('now');
        $this->activeWorksheet->setCellValue('B2', 'Prueba de generos ');
        $this->activeWorksheet->setCellValue('B3', $name);
        $this->activeWorksheet->setCellValue('B4',  sprintf('%s: %s %s: %s',
            'fecha',
            $date->format('d-m-Y'),
            'hora',
            $date->format('H:m:s')
        ));
        $this->firstRow = 6;
        $this->firstCol = 2;

        //TABLE HEADINGS
        $i = $this->firstCol;
        $this->activeWorksheet->getRowDimension($this->firstRow)->setRowHeight(30);
        $this->activeWorksheet->setShowGridlines(false);

        foreach ($headers as $key => $value) {

            //Dimensions
            $this->activeWorksheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
            $this->activeWorksheet->getStyle(Coordinate::stringFromColumnIndex($i))->getAlignment()->setHorizontal('center');
            $this->activeWorksheet->getStyle(Coordinate::stringFromColumnIndex($i) . $this->firstRow)->applyFromArray($tableHeading);
            $this->activeWorksheet->setCellValueByColumnAndRow($i++, $this->firstRow, $value);
        }
    }

    /**
     * @param $data
     * @param $headers
     * @param $name
     * @return Xlsx
     * @throws Exception
     */
    public function setData($data, $headers, $name)
    {
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => [
                        'argb' => '404040']
                ]
            ]
        ];

        //TABLE DATA
        $i = $this->firstRow + 1;
        foreach ($data as $dataItem) {
            $j = $this->firstCol;
            foreach ($headers as $key => $value) {
                $this->activeWorksheet->setCellValueByColumnAndRow($j, $i, $dataItem["$key"]);
                $this->activeWorksheet->getStyle(Coordinate::stringFromColumnIndex($j++) . $i)->applyFromArray($borderStyle);
            }
            $i++;
        }

        $this->spreadsheet->getActiveSheet()->setTitle($name);
        $this->spreadsheet->setActiveSheetIndex(0);
        ob_end_clean();
        return new Xlsx($this->spreadsheet);

    }

    public function generateExcel($data,$temp_file)
    {

        $this->creator->create($data, $temp_file);
    }



}