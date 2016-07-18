<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\Excel_file_details;
use Initial\ShippingBundle\Entity\ReadingKpiValues;
use Initial\ShippingBundle\Entity\PHPNodeJs;
use Initial\ShippingBundle\Form\AddExcelFileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Reader_Excel2007;
use PHPExcel_Shared_Date;
use PHPExcel_Cell;
class ReadExcelSheetController extends Controller
{
    public function indexAction()
    {
        return new Response("Hi new Action");
    }

    private function createCreateForm(Excel_file_details $excelobj)
    {
        $form = $this->createForm(new AddExcelFileType(), $excelobj, array(
            'action' => $this->generateUrl('upload'),
            'method' => 'POST',
        ));

       /* $form->add('submit', 'submit', array('label' => 'Upload'));
        $form->add('add', 'submit', array('label' => 'Reading.....'));*/

        return $form;
    }

    public function upAction(Request $request)
    {
        $user = $this->getUser();
        if ($user == null)
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
        else
        {
            $userId = $user->getId();
            $username = $user->getUsername();
            $role = $user->getRoles();
            $excelobj = new Excel_file_details();

            $form = $this->createCreateForm($excelobj);
            $templatechoosen = 'base.html.twig';
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                $templatechoosen = 'v-ships_layout.html.twig';
            }


            return $this->render('InitialShippingBundle:ExcelFileviews:excelfile.html.twig', array(
                'form' => $form->createView(),'template'=>$templatechoosen,
            ));
        }
    }
    public function newuploadAction(Request $request)
    {
        $excelobj = new Excel_file_details();
        //$uploadsucess=false;
        $form = $this->createCreateForm($excelobj);
        $em = $this->getDoctrine()->getManager();


        $form->handleRequest($request);

        if ($form->isValid())
        {
            $uploaddir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/excelfiles';
            $file= $excelobj->getFilename();

            $fileName = $excelobj->getFilename()->getClientOriginalName();

            $ext= pathinfo($uploaddir.$fileName, PATHINFO_EXTENSION);

            $name = substr($fileName, 0, - (strlen($ext)+1));
            //  echo $name.'<br>';
            $i = 1;

            $fileName = $name . date('Y-m-d H-i-s') .'.'. $ext;
            if ( $file->move($uploaddir, $fileName))
            {


                $user = $this->getUser();
                $username = $user->getUsername();
                $userquery = $em->createQueryBuilder()
                    ->select('a.emailId')
                    ->from('InitialShippingBundle:CompanyDetails','a')
                    ->where('a.adminName = :userId')
                    ->setParameter('userId',$username)
                    ->getQuery();
                $useremailid=$userquery->getSingleScalarResult();
                $excelobj->setFilename($fileName);
                $mailer = $this->container->get('mailer');
                $input=$uploaddir.'/'.$excelobj->getFilename();

                $mydatevalue= $excelobj->getDataofmonth();



                $inputFileType="";

                switch ($ext) {
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




                if ($sheetCount == 1)
                {
                    $user = $this->getUser();
                    $userId = $user->getId();
                    $dataofmonthstring= $mydatevalue->format('Y-m-d');
                    $gearmandataarray=array('filename'=>$fileName,'dataofmonth'=>$dataofmonthstring,'userid'=>$userId,'filetype'=>$inputFileType);
                    $gearman = $this->get('gearman');       //$datafromuser=array();
                    $gearman->doBackgroundJob('InitialShippingBundleserviceReadExcelWorker~readexcelsheet', json_encode($gearmandataarray));
                    $msg="Your Document Has Been verfication.After Verfication Your File Data Has been Reading.";


                    return $this->render(
                        'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                        array('creator' => $cre,'msg'=>$msg)
                    );

                }
                if ($sheetCount > 1)
                {

                    $message = \Swift_Message::newInstance()
                        ->setFrom('lawrance@commusoft.co.uk')
                        ->setTo($useremailid)
                        ->setSubject("Your Document having more than One Sheets!!!!")
                        ->setBody("Your Document having more than One Sheets!!!!")
                    ;
                    $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                    $mailer->send($message);
                    $loadedSheetNames = $objPHPExcel->getSheetNames();
                    $excelobj->removeUpload($input);

                    $this->addFlash(
                        'notice',
                        'Your Document having more than One Sheets.so document resend to Your Mail. Check Your Mail!!!'
                    );

                    return $this->render(
                        'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                        array('creator' => $cre,'msg'=>'Number of Sheets: '.$sheetCount)
                    );
                }

            }


        }

        $cre="Not Valid Document";
        return $this->render(
            'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
            array('creator' => $cre,'msg'=>'')
        );
    }

    public function uploadAction(Request $request)
    {
        $user = $this->getUser();
        if ($user == null)
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
        else {
            $excelobj = new Excel_file_details();
            $templatechoosen = 'base.html.twig';
            //$uploadsucess=false;
            $form = $this->createCreateForm($excelobj);
            $em = $this->getDoctrine()->getManager();


            $form->handleRequest($request);

            if ($form->isValid()) {
                $uploaddir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/excelfiles';
                $file = $excelobj->getFilename();

                $fileName = $excelobj->getFilename()->getClientOriginalName();

                $ext = pathinfo($uploaddir . $fileName, PATHINFO_EXTENSION);

                $name = substr($fileName, 0, -(strlen($ext) + 1));
                //  echo $name.'<br>';
                $i = 1;

                $fileName = $name . date('Y-m-d H-i-s') . '.' . $ext;


                if ($file->move($uploaddir, $fileName)) {
                    $username = $user->getUsername();
                    $role = $user->getRoles();
                    if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                        $templatechoosen = 'v-ships_layout.html.twig';
                    }
                    /*$userquery = $em->createQueryBuilder()
                        ->select('a.emailId','a.id')
                        ->from('InitialShippingBundle:CompanyDetails','a')
                         ->where('a.adminName = :userId')
                        ->setParameter('userId',$username)
                        ->getQuery();
                    $useremailid=$userquery->getResult();*/
                    $useremailid = $em->createQueryBuilder()
                        ->select('IDENTITY(a.companyid)', 'a.email')
                        ->from('InitialShippingBundle:User', 'a')
                        ->where('a.username = :username')
                        ->setParameter('username', $username)
                        ->getQuery()
                        ->getResult();
                    $excelobj->setFilename($fileName);
                    $mailer = $this->container->get('mailer');
                    $input = $uploaddir . '/' . $excelobj->getFilename();

                    $mydatevalue = $excelobj->getDataofmonth();


                    $inputFileType = "";

                    switch ($ext) {
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
                                                    ->setFrom('lawrance@commusoft.co.uk')
                                                    ->setTo($useremailid[0]['email'])
                                                    ->setSubject("Your Document Has Mismatch Values!!!!")
                                                    ->setBody($msg);
                                                $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                                                $mailer->send($message);
                                                $excelobj->removeUpload($input);
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
                                            ->setFrom('lawrance@commusoft.co.uk')
                                            ->setTo($useremailid[0]['emailId'])
                                            ->setSubject("Your Document Has Mismatch Values!!!!")
                                            ->setBody($msg);
                                        $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                                        $mailer->send($message);
                                        $excelobj->removeUpload($input);
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
                                            ->setFrom('lawrance@commusoft.co.uk')
                                            ->setTo($useremailid[0]['emailId'])
                                            ->setSubject("Your Document Has Mismatch Values!!!!")
                                            ->setBody($msg);
                                        $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                                        $mailer->send($message);
                                        $excelobj->removeUpload($input);
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
                                        ->setFrom('lawrance@commusoft.co.uk')
                                        ->setTo($useremailid[0]['emailId'])
                                        ->setSubject("Your Document Has Mismatch Values!!!!")
                                        ->setBody($msg);
                                    $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                                    $mailer->send($message);
                                    $excelobj->removeUpload($input);
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
                                    ->setFrom('lawrance@commusoft.co.uk')
                                    ->setTo($useremailid[0]['emailId'])
                                    ->setSubject("Your Document Has Mismatch Values!!!!")
                                    ->setBody($msg);
                                $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                                $mailer->send($message);

                                $excelobj->removeUpload($input);
                                $erromsg='Your File not Readed!!! Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!';
                                return $this->render(
                                    'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                                    array('erromsg' => $erromsg, 'msg' => $msg, 'template' => $templatechoosen)
                                );


                            }


                        }


                        if ($j == $mycount) {


                            /* $highestRow = $objWorksheet->getHighestRow();
                             $highestColumn = $objWorksheet->getHighestColumn();*/

                            $excelsheet_data_array = array();
                            /*
                                                        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
                                                        $nrColumns = ord($highestColumn) - 64;
                                                        $worksheetTitle = $objWorksheet->getTitle();*/
                            $kpielementvaluearray = array();
                            $newkpielementvaluearray = array();

                            $usergivendata = date_format($excelobj->getDataofmonth(), "-m-Y");
                            $elementid = 0;

                            for ($d = 0; $d < count($newkpidetailsarray); $d++) {


                                $cellname = $newkpidetailsarray[$d]['cellName'];
                                $kpiid = $newkpidetailsarray[$d]['id'];
                                $cellvalue = $newkpidetailsarray[$d]['kpiName'];
                                $cellenddate = $newkpidetailsarray[$d]['endDate'];
                                $databasedate = date_format($cellenddate, "m-Y");

                                /* if ($usergivendata <= $databasedate) { */

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
                                                            ->setFrom('lawrance@commusoft.co.uk')
                                                            ->setTo($useremailid[0]['emailId'])
                                                            ->setSubject("Your Document Has Mismatch Values!!!!")
                                                            ->setBody($msg);
                                                        $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                                                        $mailer->send($message);

                                                        $excelobj->removeUpload($input);
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
                                            $exceldataofmonth = $excelobj->getDataOfMonth();
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

                            $exceldataofmonth = $excelobj->getDataOfMonth();
                            $myexcelnewdatevalue = $exceldataofmonth->modify('last day of this month');
                            $excelobj->setUserid($username);
                            $excelobj->setCompanyId($useremailid[0]['id']);
                            $nowdate1 = date("Y-m-d H:i:s");
                            $nowdatetime = new \DateTime($nowdate1);
                            $excelobj->setDatetime($nowdatetime);
                            $excelobj->setDataOfMonth($myexcelnewdatevalue);

                            $em->persist($excelobj);
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
                                ->setFrom('lawrance@commusoft.co.uk')
                                ->setTo($useremailid[0]['emailId'])
                                ->setSubject("Your Document Has Mismatch Values!!!!")
                                ->setBody($msg);
                            $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                            $mailer->send($message);
                            $excelobj->removeUpload($input);
                            $erromsg='Your File not Readed!!! Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!';
                            return $this->render(
                                'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                                array('erromsg' => $erromsg, 'msg' => $msg, 'template' => $templatechoosen)
                            );
                        }

                    }

                    if ($sheetCount > 1) {

                        $message = \Swift_Message::newInstance()
                            ->setFrom('lawrance@commusoft.co.uk')
                            ->setTo($useremailid[0]['email'])
                            ->setSubject("Your Document having more than One Sheets!!!!")
                            ->setBody("Your Document having more than One Sheets!!!!");
                        $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                        $mailer->send($message);
                        $loadedSheetNames = $objPHPExcel->getSheetNames();
                        $excelobj->removeUpload($input);


                        $erromsg = 'Your Document having more than One Sheets.so document resend to Your Mail. Check Your Mail!!!';

                        return $this->render(
                            'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                            array('erromsg' => $erromsg, 'msg' => 'Number of Sheets: ' . $sheetCount, 'template' => $templatechoosen)
                        );
                    }

                }


            }

            $erromsg = "Not Valid Document";
            return $this->render(
                'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                array('erromsg' => $erromsg, 'msg' => '', 'template' => $templatechoosen)
            );
        }
    }

    public function showfileAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $username = $user->getUsername();


        $userdetails= $em->getRepository('InitialShippingBundle:Excel_file_details')->findBy(array('userid' => $username));

        return $this->render('InitialShippingBundle:ExcelFileviews:listall.html.twig', array(
            'userdetails' => $userdetails,
        ));

    }
    public function downloadexcelAction($filename,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $uploaddir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/excelfiles/'.$filename;
        $content = file_get_contents($uploaddir);

        $response = new Response();

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$filename);

        $response->setContent($content);
        return $response;
    }


    private function readExcelForm(Excel $excelobj)
    {
        $input = $excelobj->getPath();
        $inputFileType = 'Excel2007';
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        /**  Load $inputFileName to a PHPExcel Object  **/
        $objPHPExcel = $objReader->load($input);


        echo '<hr />';

        /**  Read the document's creator property  **/
        $creator = $objPHPExcel->getProperties()->getCreator();
        echo '<b>Document Creator: </b>', $creator, '<br />';

    }

    /*public function addAction(Request $request)
    {
        $excelobj = new Excel();


        $validator = $this->get('validator');
        $errors = $validator->validate($excelobj);
        $form = $this->createFormBuilder($excelobj)
            ->add('image', FileType::class)
            ->add('save', SubmitType::class, array('label' => 'Upload File'))
            ->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->addFlash('info', 'The item was created successfully.');
            $em = $this->getDoctrine()->getManager();


        }
        return $this->render('ExcelFileViews/excelfile.html.twig', array(
            'form' => $form->createView(),
        ));

    }*/

}
