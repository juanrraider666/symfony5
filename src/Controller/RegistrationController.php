<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UploadFormType;
use App\Security\FormLoginAuthenticator;
use App\Services\ExcelReport;
use App\Services\UploadManager;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, FormLoginAuthenticator $authenticator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("subir_archivo", name="upload_file")
     * @param Request $request
     * @param UploadManager $manager
     */
    public function uploadCSV(Request $request, UploadManager $manager)
    {

        $form = $this->createForm(UploadFormType::class, null, []);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $formData = $form->getData();
            $file = $formData['file'];

            $upload = $manager->processUpload($file);
            if ($upload) {
                $this->addFlash('success', 'subido con exito!');
            }

        }

        return $this->render('registration/upload.html.twig', [
            'uploadForm' => $form->createView(),
        ]);

    }

    /**
     * @Route("download", name="download")
     * @param ExcelReport $excelReport
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function download(ExcelReport  $excelReport)
    {
        $data = $this->getDoctrine()->getRepository(User::class)->findAll();

        if(count($data) != 0){
            $excel = [];
            $contador =0;
            foreach($data as $row){

                /** @var $row User */
                $excel[$contador]['b1'] = $row->getNames();
                $excel[$contador]['c1'] = $row->getGender();

                $contador++;
            }
            $keys = [
                'b1'=> 'nombres',
                'c1'=> 'genero',
            ];

            $response = [];
            $response['data']=$excel;
            $response['key']=$keys;


//        $fileName = sprintf('%s %s.xlsx', 'My APs Status', date('dmy'));
//        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
//
//        $excelReport->generateExcel($data, $temp_file);
//
//        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);

            try{
                $writer = $excelReport->create($response['data'], $response['key'],'Prueba de generos');

            } catch (Exception $e) {
            } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
            $this->addFlash('success', 'subido con exito!');

            // return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
            return $excelReport->createResponseFromWriter($writer, 'Prueba de generos');
        }

        $this->addFlash('error', 'no hay datos a descargar!');
        return  $this->redirectToRoute('upload_file');

    }

}
