<?php

namespace Initial\ShippingBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\ScorecardDataImport;
use Symfony\Component\HttpFoundation\Request;
use Initial\ShippingBundle\Form\ScorecardDataImportType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use PHPExcel_Cell;
use PHPExcel_IOFactory;

/**
 * ScorecardDataImport controller.
 *
 * @Route("/scorecarddataimport")
 */
class ScorecardDataImportController extends Controller
{
    /**
     * Lists all ScorecardDataImport entities.
     *
     * @Route("/", name="scorecarddataimport_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $scorecardDataImports = $em->getRepository('InitialShippingBundle:ScorecardDataImport')->findAll();

        return $this->render('scorecarddataimport/index.html.twig', array(
            'scorecardDataImports' => $scorecardDataImports,
        ));
    }

    /**
     * Lists all ScorecardDataImport entities.
     *
     * @Route("/data_import", name="scorecarddataimport_data_import")
     */
    public function dataImportAction(Request $request)
    {
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $role = $user->getRoles();
            if ($role[0] != 'ROLE_KPI_INFO_PROVIDER') {
                return $this->redirectToRoute('scorecarddataimport_files_show');
            } else {
                $dataImportObj = new ScorecardDataImport();
                $form = $this->createCreateForm($dataImportObj);
                $template = 'base.html.twig';
                if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                    $template = 'v-ships_layout.html.twig';
                }

                return $this->render('scorecarddataimport/index.html.twig', array(
                    'form' => $form->createView(),'template'=>$template
                ));
            }
        }
    }

    private function createCreateForm(ScorecardDataImport $dataImportObj)
    {
        $form = $this->createForm(new ScorecardDataImportType(), $dataImportObj, array(
            'action' => $this->generateUrl('scorecarddataimport_new'),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * Lists all ScorecardDataImport entities.
     *
     * @Route("/scorecarddataimport_new", name="scorecarddataimport_new")
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $em = $this->getDoctrine()->getManager();
            $userId=$user->getId();
            $dataImportObj = new ScorecardDataImport();
            $form = $this->createCreateForm($dataImportObj);
            $form->handleRequest($request);

            if ($form->isValid()) {
                $importDirectory = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/excelfiles';
                $file = $dataImportObj->getFilename();
                $fileName = $file->getClientOriginalName();
                $fileType = pathinfo($importDirectory . $fileName, PATHINFO_EXTENSION);
                $fileName_withoutExtension = substr($fileName, 0, -(strlen($fileType) + 1));
                $importFileName = $fileName_withoutExtension .'('. date('Y-m-d H-i-s') .')'. '.' . $fileType;

                if(!file_exists($importDirectory)) {
                    mkdir($importDirectory);
                }

                if ($file->move($importDirectory, $importFileName)) {
                    $monthDetail = $dataImportObj->getMonthDetail();
                    $lastDayOfMonth = $monthDetail->modify('last day of this month');
                    $dateTime = date("Y-m-d H:i:s");
                    $dateTimeObj = new \DateTime($dateTime);

                    $dataImportObj->setUserId($em->getRepository('InitialShippingBundle:User')->findOneBy(array('id' => $userId)));
                    $dataImportObj->setFileName($importFileName);
                    $dataImportObj->setMonthDetail($lastDayOfMonth);
                    $dataImportObj->setDateTime($dateTimeObj);
                    $em->persist($dataImportObj);
                    $em->flush();
                }
            }
            return $this->redirect('scorecarddataimport_files_show');
        }
    }

    /**
     * Lists all ScorecardDataImport entities.
     *
     * @Route("/scorecarddataimport_files_show", name="scorecarddataimport_files_show")
     */
    public function filesShowAction(Request $request)
    {
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $role=$user->getRoles();
            $em = $this->getDoctrine()->getManager();
            $fileDetails = $em->createQueryBuilder()
                ->select('a.id,a.fileName,a.monthDetail,a.dateTime,identity(a.userId)')
                ->from('InitialShippingBundle:ScorecardDataImport', 'a')
                ->getQuery()
                ->getResult();
            $userDetailsArray = array();
            $originalFileNameArray = array();
            for($fileCount=0;$fileCount<count($fileDetails);$fileCount++) {
                $userDetails = $em->createQueryBuilder()
                    ->select('a.username, a.email, a.fullname, a.imagepath')
                    ->from('InitialShippingBundle:User', 'a')
                    ->where('a.id = :userId')
                    ->setParameter('userId',$fileDetails[$fileCount]['1'])
                    ->getQuery()
                    ->getResult();
                array_push($userDetailsArray,$userDetails);
                $fileName_fromDb = $fileDetails[$fileCount]['fileName'];
                $fileType = pathinfo($fileName_fromDb, PATHINFO_EXTENSION);
                $fileName_withoutExtension = substr($fileName_fromDb, 0, -(strlen($fileType) + 1));
                $fileName_withoutDateTime = explode('(',$fileName_withoutExtension);
                $originalFileName = $fileName_withoutDateTime[0] . '.' . $fileType;
                array_push($originalFileNameArray,$originalFileName);
            }
            $template = 'base.html.twig';
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                $template = 'v-ships_layout.html.twig';
            }
            return $this->render('scorecarddataimport/show.html.twig',
                array(
                    'userDetails' => $userDetailsArray,
                    'fileDetails' => $fileDetails,
                    'fileName' => $originalFileNameArray,
                    'template' => $template
            ));
        }
    }

    /**
     * Lists all ScorecardDataImport entities.
     *
     * @Route("/scorecarddataimport_file_download/{id}", name="scorecarddataimport_file_download")
     * @Method("GET")
     */
    public function fileDownloadAction(Request $request,ScorecardDataImport $scorecardDataImport)
    {
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $fileName_fromDb = $scorecardDataImport->getFileName();
            $directoryLocation = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/excelfiles';
            $filePath = $directoryLocation . '/' . $fileName_fromDb;
            $content = file_get_contents($filePath);
            $fileType = pathinfo($fileName_fromDb, PATHINFO_EXTENSION);
            $fileName_withoutExtension = substr($fileName_fromDb, 0, -(strlen($fileType) + 1));
            $fileName_withoutDateTime = explode('(',$fileName_withoutExtension);
            $originalFileName = $fileName_withoutDateTime[0] . '.' . $fileType;
            $response = new Response();
            $response->setContent($content);
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . $originalFileName . "\"");
            return $response;
        }
    }


    /**
     * Finds and displays a ScorecardDataImport entity.
     *
     * @Route("/{id}", name="scorecarddataimport_show")
     * @Method("GET")
     */
    public function showAction(ScorecardDataImport $scorecardDataImport)
    {

        return $this->render('scorecarddataimport/show.html.twig', array(
            'scorecardDataImport' => $scorecardDataImport,
        ));
    }
}
