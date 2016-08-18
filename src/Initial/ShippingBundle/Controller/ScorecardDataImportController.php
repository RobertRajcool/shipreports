<?php

namespace Initial\ShippingBundle\Controller;


use Initial\ShippingBundle\Entity\ScorecardFolder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\ScorecardDataImport;
use Initial\ShippingBundle\Entity\Excel_file_details;
use Initial\ShippingBundle\Entity\ReadingKpiValues;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        }
        else
        {
            $em = $this->getDoctrine()->getManager();
            $userId=$user->getId();
            $dataImportObj = new ScorecardDataImport();
            $excelobj = new Excel_file_details();
            $form = $this->createCreateForm($dataImportObj);
            $form->handleRequest($request);
            $templatechoosen = "base.html.twig";
            if ($form->isValid()) {
                $monthDetail = $dataImportObj->getMonthDetail();
                $lastDayOfMonth = $monthDetail->modify('last day of this month');
                $folderName=date('F-Y', strtotime(date_format($lastDayOfMonth,'Y-m-d')));
                $beforecrete_importDirectory = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/excelfiles/scorecard';
                if(!file_exists($beforecrete_importDirectory)) {
                    mkdir($beforecrete_importDirectory);
                }
                $importDirectory = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/excelfiles/scorecard/'.$folderName;
                $file = $dataImportObj->getFilename();
                $fileName = $file->getClientOriginalName();
                $fileType = pathinfo($importDirectory . $fileName, PATHINFO_EXTENSION);
                $fileName_withoutExtension = substr($fileName, 0, -(strlen($fileType) + 1));
                $importFileName = $fileName_withoutExtension .'('. date('Y-m-d H-i-s') .')'. '.' . $fileType;
                if(!file_exists($importDirectory)) {
                    mkdir($importDirectory);
                    $folderobject = new ScorecardFolder();
                    $folderobject->setFolderName($folderName);
                    $em->persist($folderobject);
                    $em->flush();
                }

                if ($file->move($importDirectory, $importFileName)) {
                    $dateTime = date("Y-m-d H:i:s");
                    $dateTimeObj = new \DateTime($dateTime);
                    $dataImportObj->setUserId($em->getRepository('InitialShippingBundle:User')->findOneBy(array('id' => $userId)));
                    $folderId = $em->getRepository('InitialShippingBundle:ScorecardFolder')->findBy(array('folderName' => $folderName));
                    $dataImportObj->setFileName($importFileName);
                    $dataImportObj->setMonthDetail($lastDayOfMonth);
                    $dataImportObj->setDateTime($dateTimeObj);
                    $dataImportObj->setFolderId($folderId[0]);
                    $username = $user->getUsername();
                    $role = $user->getRoles();
                    if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                        $templatechoosen = 'v-ships_layout.html.twig';
                    }
                    $em->persist($dataImportObj);
                    $em->flush();
                    /*$userquery = $em->createQueryBuilder()
                        ->select('a.emailId','a.id')
                        ->from('InitialShippingBundle:CompanyDetails','a')
                         ->where('a.adminName = :userId')
                        ->setParameter('userId',$username)
                        ->getQuery();
                    $useremailid=$userquery->getResult();*/
                    /*$useremailid = $em->createQueryBuilder()
                        ->select('IDENTITY(a.companyid)', 'a.email')
                        ->from('InitialShippingBundle:User', 'a')
                        ->where('a.username = :username')
                        ->setParameter('username', $username)
                        ->getQuery()
                        ->getResult();
                    $mailer = $this->container->get('mailer');
                    $input = $importDirectory . '/' . $dataImportObj->getFilename();

                    $mydatevalue = $dataImportObj->getMonthDetail();


                    $inputFileType = "";

                    switch ($fileType) {
                        case "xls":
                            $inputFileType = 'Excel5';
                            break;
                        case "xlsx":
                            $inputFileType = 'Excel2007';
                            break;

                    }


                    // Creating Excel Sheet Objects....//

                    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                    $objReader->setLoadAllSheets();
                    $objPHPExcel = $objReader->load($input);

                    $objWorksheet = $objPHPExcel->getActiveSheet();
                    $sheetCount = $objPHPExcel->getSheetCount();
                    $cre = "";


                    if ($sheetCount == 1) {

                        //Validation For Ship Details
                        $user = $this->getUser();
                        $userId = $user->getId();

                        $databaseshipsname = array();


                        if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                            $query = $em->createQueryBuilder()
                                ->select('a.shipName', 'a.id')
                                ->from('InitialShippingBundle:ShipDetails', 'a')
                                ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                                ->where('b.adminName = :username')
                                ->setParameter('username', $username)
                                ->getQuery();
                        } else {
                            $query = $em->createQueryBuilder()
                                ->select('a.shipName', 'a.id')
                                ->from('InitialShippingBundle:ShipDetails', 'a')
                                ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                                ->where('b.id = :userId')
                                ->setParameter('userId', $userId)
                                ->getQuery();
                        }


                        $shipdetailsarray = $query->getResult();


                        for ($k = 0; $k < count($shipdetailsarray); $k++) {
                            $shipnamename = $shipdetailsarray[$k]['shipName'];
                            if ($shipnamename != " " && $shipnamename != null) {
                                array_push($databaseshipsname, $shipnamename);
                            }
                        }

                        // print_r($databaseshipsname);die;

                        $sheetshipsname = array();

                        $arrayLabel = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "w", "X", "Y", "z");
                        //$myArray=array();
                        $shipnameflag = true;
                        for ($x = 1; $x <= 10; $x++) {

                            for ($y = 1; $y < count($arrayLabel); $y++) {
                                //== display each cell value
                                $pcellvale = $objWorksheet->getCell($arrayLabel[$y] . $x)->getValue();
                                $mystaticvalue = 25;
                                $mystartvalue = "KPI";


                                if ($pcellvale == $mystartvalue) {

                                    $excelArray = $objWorksheet->rangeToArray($arrayLabel[$y] . $x . ':' . $arrayLabel[$mystaticvalue] . $x);

                                    foreach ($excelArray as $key => $value) {

                                        for ($m = 4; $m < count($value); $m++) {
                                            if ($value[$m] != " " && $value[$m] != null) {
                                                array_push($sheetshipsname, $value[$m]);
                                            }
                                        }

                                    }


                                    if (!(count($sheetshipsname) > count($databaseshipsname))) {

                                        for ($b = 0; $b < count($sheetshipsname); $b++) {
                                            if (!(in_array($sheetshipsname[$b], $databaseshipsname))) {
                                                $shipnameflag = false;
                                                $cre = "";
                                                $msg = "Ships Names Are Mismatch.Mismatch Shipnae: " . $sheetshipsname[$b];
                                                $message = \Swift_Message::newInstance()
                                                    ->setFrom($maileruserid)
                                                    ->setTo($useremailid[0]['email'])
                                                    ->setSubject("Your Document Has Mismatch Values!!!!")
                                                    ->setBody($msg);
                                                $message->attach(\Swift_Attachment::fromPath($input)->setFilename($dataImportObj->getFilename()));
                                                $mailer->send($message);
                                                $erromsg='Your File not Readed. Because, Ship Names are Mismatch !!!. Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!';

                                                return $this->render(
                                                    'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                                                    array('erromsg' => $erromsg, 'msg' => '', 'template' => $templatechoosen)
                                                );
                                            }
                                        }

                                    }
                                    if ((count($sheetshipsname) > count($databaseshipsname))) {
                                        $shipnameflag = false;
                                    }

                                    if ($shipnameflag == false) {
                                        $cre = "";
                                        $msg = "Ships Are Mismatch";
                                        $message = \Swift_Message::newInstance()
                                            ->setFrom($maileruserid)
                                            ->setTo($useremailid[0]['email'])
                                            ->setSubject("Your Document Has Mismatch Values!!!!")
                                            ->setBody($msg);
                                        $message->attach(\Swift_Attachment::fromPath($input)->setFilename($dataImportObj->getFilename()));
                                        $mailer->send($message);
                                        $erromsg='Your File not Readed. Because, Ship Names are Mismatch !!!. Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!';


                                        return $this->render(
                                            'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                                            array('erromsg' => $erromsg, 'msg' => '', 'template' => $templatechoosen)
                                        );
                                    }


                                }

                            }
                        }
                        if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                            $kpiquery = $em->createQueryBuilder()
                                ->select('a.cellName', 'a.kpiName', 'a.id', 'a.endDate')
                                ->from('InitialShippingBundle:KpiDetails', 'a')
                                ->leftjoin('InitialShippingBundle:ShipDetails', 'd', 'WITH', 'd.id = a.shipDetailsId')
                                ->leftjoin('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = d.companyDetailsId')
                                ->leftjoin('InitialShippingBundle:User', 'c', 'WITH', 'c.username = b.adminName')
                                ->where('c.id = :userId')
                                ->groupby('a.kpiName')
                                ->setParameter('userId', $userId)
                                ->getQuery();
                        } else {
                            $kpiquery = $em->createQueryBuilder()
                                ->select('a.cellName', 'a.kpiName', 'a.id', 'a.endDate')
                                ->from('InitialShippingBundle:KpiDetails', 'a')
                                ->leftjoin('InitialShippingBundle:ShipDetails', 'c', 'WITH', 'c.id = a.shipDetailsId')
                                ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = c.companyDetailsId')
                                ->where('b.id = :userId')
                                ->groupby('a.kpiName')
                                ->setParameter('userId', $userId)
                                ->getQuery();
                        }
                        $newkpidetailsarray = $kpiquery->getResult();


                        $mycount = 0;
                        //$flag=true;
                        $j = count($newkpidetailsarray);
                        for ($i = 0; $i < $j; $i++) {
                            $cellname = $newkpidetailsarray[$i]['cellName'];
                            $cellvalue = $newkpidetailsarray[$i]['kpiName'];

                            $columnvalue1 = $objPHPExcel->getActiveSheet()->getCell($cellname)->getValue();
                            // echo 'The Column value'.$columnvalue1;die;
                            if ($cellvalue == $columnvalue1) {
                                $mycount++;

                                //Validation For Elements Starts Here...//

                                $elementid = $newkpidetailsarray[$i]['id'];
                                $query = $em->createQueryBuilder()
                                    ->select('b.cellName', 'b.elementName', 'b.id')
                                    ->from('InitialShippingBundle:ElementDetails', 'b')
                                    ->where('b.kpiDetailsId = :kpidetailsid')
                                    ->setParameter('kpidetailsid', $elementid)
                                    ->getQuery();
                                $elementarray = $query->getResult();

                                $elementcount = 0;
                                $c = count($elementarray);
                                for ($e = 0; $e < $c; $e++) {


                                    $elementcell = $elementarray[$e]['cellName'];
                                    $elementname = $elementarray[$e]['elementName'];
                                    // echo $cellvalue.'<br>';
                                    // echo $cellname.'<br>';
                                    $elementcellvalue = $objPHPExcel->getActiveSheet()->getCell($elementcell)->getValue();
                                    if ($elementcellvalue == $elementname) {
                                        $elementcount++;
                                    }
                                    if ($elementcellvalue != $elementname) {
                                        $elementcount--;

                                        $msg = 'In Cell ' . $elementcell . ' having that value:' . $elementname . ' Thats Mismatch Value So Correct!!!..';
                                        $cre = "";
                                        $message = \Swift_Message::newInstance()
                                            ->setFrom($maileruserid)
                                            ->setTo($useremailid[0]['email'])
                                            ->setSubject("Your Document Has Mismatch Values!!!!")
                                            ->setBody($msg);
                                        $message->attach(\Swift_Attachment::fromPath($input)->setFilename($dataImportObj->getFilename()));
                                        $mailer->send($message);
                                        $erromsg= 'Your File not Readed!!!.Because, Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!';


                                        return $this->render(
                                            'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                                            array('erromsg' => $erromsg, 'msg' => $msg, 'template' => $templatechoosen)
                                        );

                                    }
                                }


                                if ($c != $elementcount) {
                                    $msg = 'In Cell ' . $elementcell . ' having that value:' . $elementname . ' Thats Mismatch Value So Correct!!!..';
                                    $cre = "";
                                    $message = \Swift_Message::newInstance()
                                        ->setFrom($maileruserid)
                                        ->setTo($useremailid[0]['email'])
                                        ->setSubject("Your Document Has Mismatch Values!!!!")
                                        ->setBody($msg);
                                    $message->attach(\Swift_Attachment::fromPath($input)->setFilename($dataImportObj->getFilename()));
                                    $mailer->send($message);
                                    $erromsg='Your File not Readed!!!.Because, Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!';

                                    return $this->render(
                                        'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                                        array('erromsg' => $erromsg, 'msg' => '', 'template' => $templatechoosen)
                                    );
                                }


                            }
                            if ($cellvalue != $columnvalue1) {
                                $mycount--;
                                $msg = 'In Cell ' . $cellname . ' having that value:' . $cellvalue . ' Thats Mismatch Value So Correct!!!..';
                                $cre = "";
                                $message = \Swift_Message::newInstance()
                                    ->setFrom($maileruserid)
                                    ->setTo($useremailid[0]['email'])
                                    ->setSubject("Your Document Has Mismatch Values!!!!")
                                    ->setBody($msg);
                                $message->attach(\Swift_Attachment::fromPath($input)->setFilename($dataImportObj->getFilename()));
                                $mailer->send($message);
                                $erromsg='Your File not Readed!!! Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!';
                                return $this->render(
                                    'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                                    array('erromsg' => $erromsg, 'msg' => $msg, 'template' => $templatechoosen)
                                );


                            }


                        }


                        if ($j == $mycount) {
                            $excelsheet_data_array = array();
                            $kpielementvaluearray = array();
                            $newkpielementvaluearray = array();

                            $usergivendata = date_format($dataImportObj->getMonthDetail(), "-m-Y");
                            $elementid = 0;

                            for ($d = 0; $d < count($newkpidetailsarray); $d++) {


                                $cellname = $newkpidetailsarray[$d]['cellName'];
                                $kpiid = $newkpidetailsarray[$d]['id'];
                                $cellvalue = $newkpidetailsarray[$d]['kpiName'];
                                $cellenddate = $newkpidetailsarray[$d]['endDate'];
                                $databasedate = date_format($cellenddate, "m-Y");

                                $columnvalue1 = $objPHPExcel->getActiveSheet()->getCell($cellname)->getValue();
                                if ($cellvalue == $columnvalue1) {
                                    $elementid = $newkpidetailsarray[$d]['id'];
                                    $query = $em->createQueryBuilder()
                                        ->select('b.cellName', 'b.elementName', 'b.id')
                                        ->from('InitialShippingBundle:ElementDetails', 'b')
                                        ->where('b.kpiDetailsId = :kpidetailsid')
                                        ->setParameter('kpidetailsid', $elementid)
                                        ->getQuery();
                                    $elementarray = $query->getResult();
                                    $o = count($elementarray);
                                    for ($p = 0; $p < $o; $p++) {


                                        $elementcell = $elementarray[$p]['cellName'];
                                        $elementname = $elementarray[$p]['elementName'];
                                        $elementid = $elementarray[$p]['id'];
                                        $mysheetelementvalues = array();
                                        $numbers_array = $excelobj->extract_numbers($elementcell);
                                        $totalshipcount = count($sheetshipsname) + 3;
                                        $columnLetter = PHPExcel_Cell::stringFromColumnIndex($totalshipcount + 1);
                                        $elementexcesheetvalue = $objWorksheet->rangeToArray($elementcell . ':' . $columnLetter . $numbers_array[0]);

                                        foreach ($elementexcesheetvalue as $key1 => $value1) {


                                            for ($mb = 3; $mb < $totalshipcount; $mb++) {

                                                $rulesresultarray = array();
                                                $read1 = "";
                                                //Finding rulues for Element
                                                $rulesarray = $em->createQueryBuilder()
                                                    ->select('b.rules')
                                                    ->from('InitialShippingBundle:ElementRules', 'b')
                                                    ->where('b.elementDetailsId = :elementDetailsId')
                                                    ->setParameter('elementDetailsId', $elementid)
                                                    ->getQuery()
                                                    ->getResult();


                                                $totalcountofrulesarry = count($rulesarray);
                                                //If element for rule is zero thats going take excel sheeet value that validation goes Starts Here..//
                                                if ($totalcountofrulesarry > 0) {

                                                    for ($aaa = 0; $aaa < count($rulesarray); $aaa++) {
                                                        $argu = $value1[$mb];
                                                        $jsfiledirectry = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_nodejs_3.js \'' . $rulesarray[$aaa]['rules'] . ' \' ' . $argu;
                                                        $jsfilename = 'node ' . $jsfiledirectry;
                                                        $handle = popen($jsfilename, 'r');
                                                        $read = fread($handle, 2096);
                                                        $read1 = str_replace("\n", '', $read);
                                                        if ($read1 != "false") {
                                                            break;
                                                        }

                                                    }
                                                    //If Element rule return null answer that shows error message starts Here//
                                                    if ($read1 == "false") {
                                                        $elementnameforfule = $em->createQueryBuilder()
                                                            ->select('a.elementName')
                                                            ->from('InitialShippingBundle:ElementDetails', 'a')
                                                            ->where('a.id = :userId')
                                                            ->setParameter('userId', $elementid)
                                                            ->getQuery()
                                                            ->getSingleScalarResult();
                                                        $msg = 'In Rule for Element  ' . $elementnameforfule . ' . Thats Mismatch Value So Correct!!!';
                                                        $cre = "";
                                                        $message = \Swift_Message::newInstance()
                                                            ->setFrom($maileruserid)
                                                            ->setTo($useremailid[0]['email'])
                                                            ->setSubject("Your Document Has Mismatch Values!!!!")
                                                            ->setBody($msg);
                                                        $message->attach(\Swift_Attachment::fromPath($input)->setFilename($dataImportObj->getFilename()));
                                                        $mailer->send($message);
                                                        $erromsg= 'Your File not Readed!!! Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!';

                                                        return $this->render(
                                                            'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                                                            array('erromsg' => $erromsg, 'msg' => $msg, 'template' => $templatechoosen)
                                                        );

                                                    } //If Element rule return null answer that shows error message Ends Here//
                                                    else {
                                                        $kpielementvaluearray[$kpiid][$elementid][$mb - 3] = $read1;

                                                    }
                                                } else {
                                                    $kpielementvaluearray[$kpiid][$elementid][$mb - 3] = $value1[$mb];
                                                }
                                                //If element for rule is zero thats going take excel sheeet value that validation goes Ends  Here..//
                                            }
                                        }
                                    }
                                }
                            }
                            // Insertion process Starts Here //

                            if (count($shipdetailsarray) == count($databaseshipsname)) {

                                // $arrayexcelsheetvalues=array('shipideatilsarray'=>$shipdetailsarray,'kpielementvaluearray'=>$kpielementvaluearray,'dataofmonth'=>$excelobj->getDataOfMonth());


                                $abc = 0;
                                foreach ($kpielementvaluearray as $kpikey => $kpipvalue) {


                                    foreach ($kpipvalue as $elementkey => $elementvalue) {

                                        foreach ($elementvalue as $valuekey => $finalvalue) {

                                            $shipid = $shipdetailsarray[$abc]['id'];

                                            $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
                                            $newkpiid = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $kpikey));
                                            $newelementid = $em->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id' => $elementkey));
                                            $readingkpivalue = new ReadingKpiValues();
                                            $readingkpivalue->setElementDetailsId($newelementid);
                                            $exceldataofmonth = $dataImportObj->getMonthDetail();
                                            $myexcelnewdatevalue = $exceldataofmonth->modify('last day of this month');
                                            $readingkpivalue->setMonthdetail($myexcelnewdatevalue);
                                            $readingkpivalue->setShipDetailsId($newshipid);
                                            $readingkpivalue->setKpiDetailsId($newkpiid);
                                            $readingkpivalue->setValue($finalvalue);
                                            $em = $this->getDoctrine()->getManager();
                                            $em->persist($readingkpivalue);
                                            $em->flush();
                                            $abc++;
                                        }
                                        $abc = 0;
                                    }

                                }
                            }


                            $em->persist($dataImportObj);
                            $em->flush();
                            // Insertion process Starts Ends Here //

                            $cre = "Your File Readed!!!";

                            $this->addFlash(
                                'notice',
                                'Your Document Has Been Added!!!!'
                            );
                            return $this->redirectToRoute('showfile');

                        }

                        if ($j != $mycount) {
                            $msg = 'In Cell ' . $cellname . ' having that value:' . $cellvalue . ' Thats Mismatch Value So Correct!!!1';
                            $cre = "";
                            $message = \Swift_Message::newInstance()
                                ->setFrom($maileruserid)
                                ->setTo($useremailid[0]['email'])
                                ->setSubject("Your Document Has Mismatch Values!!!!")
                                ->setBody($msg);
                            $message->attach(\Swift_Attachment::fromPath($input)->setFilename($dataImportObj->getFilename()));
                            $mailer->send($message);
                            $erromsg='Your File not Readed!!! Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!';
                            return $this->render(
                                'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                                array('erromsg' => $erromsg, 'msg' => $msg, 'template' => $templatechoosen)
                            );
                        }

                    }

                    if ($sheetCount > 1) {

                        $message = \Swift_Message::newInstance()
                            ->setFrom($maileruserid)
                            ->setTo($useremailid[0]['email'])
                            ->setSubject("Your Document having more than One Sheets!!!!")
                            ->setBody("Your Document having more than One Sheets!!!!");
                        $message->attach(\Swift_Attachment::fromPath($input)->setFilename($dataImportObj->getFilename()));
                        $mailer->send($message);
                        $loadedSheetNames = $objPHPExcel->getSheetNames();
                        $erromsg = 'Your Document having more than One Sheets.so document resend to Your Mail. Check Your Mail!!!';

                        return $this->render(
                            'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                            array('erromsg' => $erromsg, 'msg' => 'Number of Sheets: ' . $sheetCount, 'template' => $templatechoosen)
                        );
                    }*/
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
         /*   $fileDetails = $em->createQueryBuilder()
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
            }*/
            $template = 'base.html.twig';
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                $template = 'v-ships_layout.html.twig';
            }

            $listoffiles = $em->createQueryBuilder()
                ->select('c.folderName')
                ->from('InitialShippingBundle:ScorecardFolder', 'c')
                ->getQuery()
                ->getResult();
            $filenamesarray=array();
            for($filecount=0;$filecount<count($listoffiles);$filecount++)
            {
                $foldername=$listoffiles[$filecount]['folderName'];
                $listoffiles_foldername = $em->createQueryBuilder()
                    ->select('a.id','a.monthDetail','a.dateTime','a.fileName','b.username','c.folderName')
                    ->from('InitialShippingBundle:ScorecardDataImport', 'a')
                    ->leftjoin('InitialShippingBundle:ScorecardFolder', 'c', 'WITH', 'c.id = a.folderId')
                    ->leftjoin('InitialShippingBundle:User', 'b' ,'WITH','b.id = a.userId')
                    ->where('c.folderName = :folderName')
                    ->setParameter('folderName', $foldername)
                    ->getQuery()
                    ->getResult();
                $filenamesarray[$foldername]=$listoffiles_foldername;
            }


           /* return $this->render('InitialShippingBundle:DataImportRanking:listall.html.twig', array(
                'filenamesarray' => $filenamesarray,'template'=>$templatechoosen
            ));*/
            return $this->render('scorecarddataimport/show.html.twig',
                array(
                    'filenamesarray' => $filenamesarray,'template'=>$template
                ));
        }
    }

    /**
     * Lists all ScorecardDataImport entities.
     *
     * @Route("/scorecarddataimport_file_filter", name="scorecarddataimport_file_filter")
     */
    public function fileFilterAction(Request $request)
    {
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $em = $this->getDoctrine()->getManager();
            $monthYear = $request->request->get('month-year');
            $date = '01-'.$monthYear;
            $dateObj=new \DateTime($date);
            $dateObj->modify('last day of this month');

            $fileDetails = $em->createQueryBuilder()
                ->select('a.id,a.fileName,a.monthDetail,a.dateTime,identity(a.userId)')
                ->from('InitialShippingBundle:ScorecardDataImport', 'a')
                ->where('a.monthDetail = :monthDetail')
                ->setParameter('monthDetail',$dateObj)
                ->getQuery()
                ->getResult();
            $userDetailsArray = array();
            $originalFileNameArray = array();
            for($fileCount=0;$fileCount<count($fileDetails);$fileCount++) {
                $userDetails = $em->createQueryBuilder()
                    ->select('a.username, a.email, a.fullname, a.imagepath')
                    ->from('InitialShippingBundle:User', 'a')
                    ->where('a.id = :userId')
                    ->setParameter('userId', $fileDetails[$fileCount]['1'])
                    ->getQuery()
                    ->getResult();
                array_push($userDetailsArray, $userDetails);
                $fileName_fromDb = $fileDetails[$fileCount]['fileName'];
                $fileType = pathinfo($fileName_fromDb, PATHINFO_EXTENSION);
                $fileName_withoutExtension = substr($fileName_fromDb, 0, -(strlen($fileType) + 1));
                $fileName_withoutDateTime = explode('(', $fileName_withoutExtension);
                $originalFileName = $fileName_withoutDateTime[0] . '.' . $fileType;
                array_push($originalFileNameArray, $originalFileName);
            }
            $response = new JsonResponse();
            $response->setData(array(
                'userDetails' => $userDetailsArray,
                'fileDetails' => $fileDetails,
                'fileName' => $originalFileNameArray,
            ));
            return $response;
        }
    }

    /**
     * Lists all ScorecardDataImport entities.
     *
     * @Route("/{filename}/{foldername}/scorecarddataimport_file_download", name="scorecarddataimport_file_download")
     * @Method("GET")
     */
    public function fileDownloadAction(Request $request,$filename,$foldername)
    {
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $fileName_fromDb = $filename;
            //$directoryLocation = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/excelfiles/scorecard';
            $directoryLocation = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/excelfiles/scorecard/' . $foldername.'/'.$filename;
           // $filePath = $directoryLocation . '/' . $fileName_fromDb;
            $content = file_get_contents($directoryLocation);
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
