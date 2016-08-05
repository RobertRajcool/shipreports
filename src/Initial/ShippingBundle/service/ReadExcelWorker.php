<?php

/**
 * Created by PhpStorm.
 * User: lawrance
 * Date: 17/2/16
 * Time: 2:40 PM
 */
namespace Initial\ShippingBundle\service;


use Initial\ShippingBundle\Entity\CommonFunctions;
use Symfony\Component\Console\Output\NullOutput;
use Mmoreram\GearmanBundle\Command\Util\GearmanOutputAwareInterface;
use Mmoreram\GearmanBundle\Driver\Gearman;
use Initial\ShippingBundle\Entity\ReadingKpiValues;
use Initial\ShippingBundle\Entity\Ranking_LookupStatus;
use Initial\ShippingBundle\Entity\Ranking_LookupData;
use Initial\ShippingBundle\Entity\Scorecard_LookupData;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Reader_Excel2007;
use PHPExcel_Shared_Date;
use PHPExcel_Cell;
use Initial\ShippingBundle\Entity\Excel_file_details;



/**
 * @Gearman\Work(
 *     defaultMethod = "doBackground",
 *     service = "readexcel.worker"
 * )
 *
 * Gearman worker for readexcelsheet
 *
 * Class ReadExcelWorker
 * @package Initial\ShippingBundle\service
 */
class ReadExcelWorker
{

    private $doctrine;
    private $container;



    /**
     * Constructor
     */
    public function __construct($doctrine,$container)
    {

        $this->doctrine = $doctrine;
        $this->container=$container;



    }
    /**
     * Insert after reading kpi values
     *
     * @param \GearmanJob $job Insert after reading kpi values
     *
     * @return boolean
     *
     * @Gearman\Job(
     *     iterations = 1,
     *     name = "readexcelsheet"
     * )
     */
    public function readExcelSheet(\GearmanJob $job)
    {
        $uploaddir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/excelfiles/';
        $excelsheetvalues = json_decode($job->workload());
        $filename = $excelsheetvalues->{'filename'};
        $dataofmonth = $excelsheetvalues->{'dataofmonth'};
        $exclefilename = $uploaddir.$filename;
        $userId = $excelsheetvalues->{'userid'};
        $filetype = $excelsheetvalues->{'filetype'};
        $excelobj = new Excel_file_details();
        $emClient = $this->doctrine->getManager();
        $mailer = $this->container->get('mailer');

        $objReader = PHPExcel_IOFactory::createReader($filetype);
        $objReader->setLoadAllSheets();
        $objPHPExcel = $objReader->load($exclefilename);

        $objWorksheet = $objPHPExcel->getActiveSheet();
        $sheetCount = $objPHPExcel->getSheetCount();
        $userquery = $emClient->createQueryBuilder()
            ->select('a.email')
            ->from('InitialShippingBundle:User','a')
            ->where('a.id = :userId')
            ->setParameter('userId',$userId)
            ->getQuery();
        $useremailid=$userquery->getSingleScalarResult();


        $databaseshipsname=array();


        $query = $emClient->createQueryBuilder()
            ->select('a.id','a.shipName')
            ->from('InitialShippingBundle:ShipDetails','a')
            ->leftjoin('InitialShippingBundle:CompanyDetails','b','WITH','b.id = a.companyDetailsId')
            ->leftjoin('InitialShippingBundle:CompanyUsers','c','WITH','b.id = c.companyName')
            ->leftjoin('InitialShippingBundle:User','d','WITH','d.username = b.adminName or d.username = c.userName')
            ->where('d.id = :userId')
            ->groupby('a.id')
            ->setParameter('userId',$userId)
            ->getQuery();


        $shipdetailsarray = $query->getResult();



        for ($k = 0; $k < count($shipdetailsarray); $k++)
        {
            $shipnamename = $shipdetailsarray[$k]['shipName'];
            if($shipnamename!=" " && $shipnamename!=null)
            {

                array_push($databaseshipsname, $shipnamename);

            }


        }

        // print_r($databaseshipsname);die;

        $sheetshipsname=array();

        $arrayLabel = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "w", "X", "Y", "z");
        //$myArray=array();
        $shipnameflag=true;
        for ($x = 1; $x <= 10; $x++)
        {

            for ($y = 1; $y < count($arrayLabel); $y++)
            {
                //== display each cell value
                $pcellvale = $objWorksheet->getCell($arrayLabel[$y] . $x)->getValue();
                $mystaticvalue=25;
                $mystartvalue = "KPI";


                if ($pcellvale == $mystartvalue)

                {

                    $excelArray = $objWorksheet->rangeToArray($arrayLabel[$y] . $x .':'.$arrayLabel[$mystaticvalue]. $x);

                    foreach($excelArray as $key=>$value){

                        for ($m = 4; $m < count($value); $m++)
                        {
                            if($value[$m]!=" " && $value[$m]!=null)
                            {
                                array_push($sheetshipsname, $value[$m]);
                            }
                        }

                    }


                    if(!(count($sheetshipsname)>count($databaseshipsname)))
                    {

                        for ($b = 0; $b < count($sheetshipsname); $b++)
                        {
                            if (!(in_array($sheetshipsname[$b], $databaseshipsname))) {
                                $shipnameflag = false;
                                $cre = "";
                                $msg="Ships Names Are Mismatch.Mismatch Shipnae: ".$sheetshipsname[$b];
                                $message = \Swift_Message::newInstance()
                                    ->setFrom('lawrance@commusoft.co.uk')
                                    ->setTo($useremailid)
                                    ->setSubject("Your Document Has Mismatch Values!!!!")
                                    ->setBody($msg)
                                ;
                                $message->attach(\Swift_Attachment::fromPath($exclefilename)->setFilename($filename));
                                $mailer->send($message);
                                $excelobj->removeUpload($exclefilename);
                                return false;

                                /* $this->addFlash(
                                     'notice',
                                     'Your File not Readed. Because, Ship Names are Mismatch !!!. Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                                 );

                                 return $this->render(
                                     'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                                     array('creator' => $cre,'msg'=>'')
                                 );*/
                            }
                        }

                    }
                    if((count($sheetshipsname)>count($databaseshipsname)))
                    {
                        $shipnameflag = false;
                    }

                    if($shipnameflag==false)
                    {
                        $cre = "";
                        $msg="Ships Are Mismatch";
                        $message = \Swift_Message::newInstance()
                            ->setFrom('lawrance@commusoft.co.uk')
                            ->setTo($useremailid)
                            ->setSubject("Your Document Has Mismatch Values!!!!")
                            ->setBody($msg)
                        ;
                        $message->attach(\Swift_Attachment::fromPath($exclefilename)->setFilename($filename));
                        $mailer->send($message);
                        $excelobj->removeUpload($exclefilename);
                        return false;
                        /*$this->addFlash(
                            'notice',
                            'Your File not Readed. Because, Ship Names are Mismatch !!!. Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                        );

                        return $this->render(
                            'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                            array('creator' => $cre,'msg'=>'')
                        );*/
                    }




                }

            }
        }






        //Validation For Kpi Details

        $kpiquery= $emClient -> createQueryBuilder()
            ->select('a.cellName', 'a.kpiName', 'a.id', 'a.endDate')
            ->from('InitialShippingBundle:KpiDetails', 'a')
            ->leftjoin('InitialShippingBundle:ShipDetails','e','WITH','e.id = a.shipDetailsId')
            ->leftjoin('InitialShippingBundle:CompanyDetails','b','WITH','b.id = e.companyDetailsId')
            ->leftjoin('InitialShippingBundle:CompanyUsers','c','WITH','b.id = c.companyName')
            ->leftjoin('InitialShippingBundle:User','d','WITH','d.username = b.adminName or d.username = c.userName')
            ->where('d.id = :userId')
            ->groupby('a.kpiName')
            ->setParameter('userId',$userId)
            ->getQuery();
        $newkpidetailsarray=$kpiquery->getResult();

        $mycount = 0;
        //$flag=true;
        $j = count($newkpidetailsarray);
        for ($i = 0; $i < $j; $i++) {


            $cellname = $newkpidetailsarray[$i]['cellName'];
            $cellvalue = $newkpidetailsarray[$i]['kpiName'];

            $columnvalue1 = $objPHPExcel->getActiveSheet()->getCell($cellname)->getValue();
            // echo 'The Column value'.$columnvalue1;die;
            if ($cellvalue == $columnvalue1)
            {
                $mycount++;

                //Validation For Elements Starts Here...//

                $elementid = $newkpidetailsarray[$i]['id'];
                $query = $emClient->createQueryBuilder()
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
                            ->setTo($useremailid)
                            ->setSubject("Your Document Has Mismatch Values!!!!")
                            ->setBody($msg)
                        ;
                        $message->attach(\Swift_Attachment::fromPath($exclefilename)->setFilename($filename));
                        $mailer->send($message);
                        $excelobj->removeUpload($emClient);
                        return false;
                        /*$this->addFlash(
                            'notice',
                            'Your File not Readed!!!.Because, Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                        );

                        return $this->render(
                            'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                            array('creator' => $cre, 'msg' => $msg)
                        );*/

                    }
                }


                if ($c != $elementcount)
                {
                    $msg = 'In Cell ' . $elementcell . ' having that value:' . $elementname . ' Thats Mismatch Value So Correct!!!..';
                    $cre = "";
                    $message = \Swift_Message::newInstance()
                        ->setFrom('lawrance@commusoft.co.uk')
                        ->setTo($useremailid)
                        ->setSubject("Your Document Has Mismatch Values!!!!")
                        ->setBody($msg)
                    ;
                    $message->attach(\Swift_Attachment::fromPath($exclefilename)->setFilename($filename));
                    $mailer->send($message);
                    $excelobj->removeUpload($exclefilename);
                    return false;

                    /*  $this->addFlash(
                          'notice',
                          'Your File not Readed!!!.Because, Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                      );

                      return $this->render(
                          'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                          array('creator' => $cre, 'msg' => '')
                      );*/
                }


            }
            if ($cellvalue != $columnvalue1)
            {
                $mycount--;
                $msg = 'In Cell ' . $cellname . ' having that value:' . $cellvalue . ' Thats Mismatch Value So Correct!!!..';
                $cre = "";
                $message = \Swift_Message::newInstance()
                    ->setFrom('lawrance@commusoft.co.uk')
                    ->setTo($useremailid)
                    ->setSubject("Your Document Has Mismatch Values!!!!")
                    ->setBody($msg)
                ;
                $message->attach(\Swift_Attachment::fromPath($exclefilename)->setFilename($filename));
                $mailer->send($message);

                $excelobj->removeUpload($exclefilename);
                return false;
                /* $this->addFlash(
                     'notice',
                     'Your File not Readed!!! Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                 );

                 return $this->render(
                     'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                     array('creator' => $cre, 'msg' => $msg)
                 );*/


            }


        }



        if ($j == $mycount)
        {



            $excelsheet_data_array = array();

            $kpielementvaluearray=array();
            $newkpielementvaluearray=array();

            // $usergivendata = date_format($excelobj->getDataofmonth(), "-m-Y");
            $elementid=0;

            for ($d = 0; $d < count($newkpidetailsarray); $d++)
            {


                $cellname = $newkpidetailsarray[$d]['cellName'];
                $kpiid = $newkpidetailsarray[$d]['id'];
                $cellvalue = $newkpidetailsarray[$d]['kpiName'];
                $cellenddate = $newkpidetailsarray[$d]['endDate'];
                //  $databasedate = date_format($cellenddate, "m-Y");

                /* if ($usergivendata <= $databasedate) { */

                $columnvalue1 = $objPHPExcel->getActiveSheet()->getCell($cellname)->getValue();
                if ($cellvalue == $columnvalue1)
                {
                    $elementid = $newkpidetailsarray[$d]['id'];
                    $query = $emClient->createQueryBuilder()
                        ->select('b.cellName', 'b.elementName', 'b.id')
                        ->from('InitialShippingBundle:ElementDetails', 'b')
                        ->where('b.kpiDetailsId = :kpidetailsid')
                        ->setParameter('kpidetailsid', $elementid)
                        ->getQuery();
                    $elementarray = $query->getResult();
                    $o = count($elementarray);
                    for ($p = 0; $p < $o; $p++)
                    {


                        $elementcell = $elementarray[$p]['cellName'];
                        $elementname = $elementarray[$p]['elementName'];
                        $elementid = $elementarray[$p]['id'];
                        $mysheetelementvalues = array();
                        $numbers_array = $excelobj->extract_numbers($elementcell);
                        $totalshipcount = count($sheetshipsname) + 3;
                        $columnLetter = PHPExcel_Cell::stringFromColumnIndex($totalshipcount+1);
                        $elementexcesheetvalue = $objWorksheet->rangeToArray($elementcell . ':' . $columnLetter . $numbers_array[0]);

                        foreach ($elementexcesheetvalue as $key1 => $value1) {



                            for ($mb = 3; $mb < $totalshipcount; $mb++) {

                                $rulesresultarray = array();
                                $read1 = "";
                                //Finding rulues for Element
                                $rulesarray = $emClient->createQueryBuilder()
                                    ->select('b.rules')
                                    ->from('InitialShippingBundle:ElementRules', 'b')
                                    ->where('b.elementDetailsId = :elementDetailsId')
                                    ->setParameter('elementDetailsId', $elementid)
                                    ->getQuery()
                                    ->getResult();


                                $totalcountofrulesarry = count($rulesarray);
                                //If element for rule is zero thats going take excel sheeet value that validation goes Starts Here..//
                                if ($totalcountofrulesarry > 0)
                                {

                                    for ($aaa = 0; $aaa < count($rulesarray); $aaa++)
                                    {
                                        $argu = $value1[$mb];
                                        $jsfiledirectry = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_nodejs_3.js \'' . $rulesarray[$aaa]['rules'] . ' \' ' . $argu;
                                        $jsfilename = 'node ' . $jsfiledirectry;
                                        $handle = popen($jsfilename, 'r');
                                        $read = fread($handle, 2096);
                                        $read1 = str_replace("\n", '', $read);
                                        if ($read1 != "false")
                                        {
                                            break;
                                        }

                                    }
                                    //If Element rule return null answer that shows error message starts Here//
                                    if ($read1 == "false")
                                    {
                                        $elementnameforfule = $emClient->createQueryBuilder()
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
                                            ->setTo($useremailid)
                                            ->setSubject("Your Document Has Mismatch Values!!!!")
                                            ->setBody($msg);
                                        $message->attach(\Swift_Attachment::fromPath($exclefilename)->setFilename($filename));
                                        $mailer->send($message);

                                        $excelobj->removeUpload($exclefilename);
                                        return false;

                                        /* $this->addFlash(
                                             'notice',
                                             'Your File not Readed!!! Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                                         );

                                         return $this->render(
                                             'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                                             array('creator' => $cre, 'msg' => $msg)
                                         );*/

                                    }
                                    //If Element rule return null answer that shows error message Ends Here//
                                    else
                                    {
                                        $kpielementvaluearray[$kpiid][$elementid][$mb - 3] = $read1;

                                    }
                                }
                                else
                                {
                                    $kpielementvaluearray[$kpiid][$elementid][$mb - 3] = $value1[$mb];
                                }
                                //If element for rule is zero thats going take excel sheeet value that validation goes Ends  Here..//
                            }
                        }
                    }
                }
            }
            // Insertion process Starts Here //

            if(count($shipdetailsarray)==count($databaseshipsname))
            {

                // $arrayexcelsheetvalues=array('shipideatilsarray'=>$shipdetailsarray,'kpielementvaluearray'=>$kpielementvaluearray,'dataofmonth'=>$excelobj->getDataOfMonth());


                $abc = 0;
                foreach ($kpielementvaluearray as $kpikey => $kpipvalue)
                {


                    foreach($kpipvalue as $elementkey=>$elementvalue)
                    {

                        foreach($elementvalue as $valuekey=>$finalvalue)
                        {

                            $shipid=$shipdetailsarray[$abc]['id'];

                            $newshipid = $emClient->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
                            $newkpiid = $emClient->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $kpikey));
                            $newelementid = $emClient->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id' => $elementkey));
                            $readingkpivalue = new ReadingKpiValues();
                            $readingkpivalue->setElementDetailsId($newelementid);
                            $exceldataofmonth=new \DateTime($dataofmonth);
                            $myexcelnewdatevalue= $exceldataofmonth->modify('last day of this month');
                            $readingkpivalue->setMonthdetail($myexcelnewdatevalue);
                            $readingkpivalue->setShipDetailsId($newshipid);
                            $readingkpivalue->setKpiDetailsId($newkpiid);
                            $readingkpivalue->setValue($finalvalue);
                            $emClient->persist($readingkpivalue);
                            $emClient->flush();
                            $abc++;
                        }
                        $abc=0;
                    }

                }
            }

            $exceldataofmonth=new \DateTime($dataofmonth);
            $myexcelnewdatevalue= $exceldataofmonth->modify('last day of this month');
            $excelobj->setUserid($useremailid)  ;
            $nowdate1 = date("Y-m-d H:i:s");
            $nowdatetime=new \DateTime($nowdate1);
            $excelobj->setDatetime($nowdatetime);
            $excelobj->setDataOfMonth($myexcelnewdatevalue);
            $excelobj->setFilename($filename);

            $emClient->persist($excelobj);
            $emClient->flush();

            return true;
            // Insertion process Starts Ends Here ///*

            /*    $cre = "Your File Readed!!!";

                $this->addFlash(
                    'notice',
                    'Your Document Has Been Added!!!!'
                );
                return $this->redirectToRoute('showfile');*/

        }

        if ($j != $mycount)
        {
            $msg='In Cell '.$cellname.' having that value:'.$cellvalue.' Thats Mismatch Value So Correct!!!1';
            $cre = "";
            $message = \Swift_Message::newInstance()
                ->setFrom('lawrance@commusoft.co.uk')
                ->setTo($useremailid)
                ->setSubject("Your Document Has Mismatch Values!!!!")
                ->setBody($msg)
            ;
            $message->attach(\Swift_Attachment::fromPath($exclefilename)->setFilename($filename));
            $mailer->send($message);

            $excelobj->removeUpload($exclefilename);
            return false;

            /*   $this->addFlash(
                   'notice',
                   'Your File not Readed!!! Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
               );

               return $this->render(
                   'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                   array('creator' => $cre,'msg'=>$msg)
               );*/
        }
        echo 'Welcome';
        return true;


    }
    /**
     * Insert after reading kpi values
     *
     * @param \GearmanJob $job Insert after reading kpi values
     *
     * @return boolean
     *
     * @Gearman\Job(
     *     iterations = 1,
     *     name = "kpivalues"
     * )
     */
    public function readkpivalues(\GearmanJob $job)
    {


        $emClient = $this->doctrine->getManager();
        $parametervalues = json_decode($job->workload());

        $shipid = $parametervalues->{'shipid'};
        $kpiid = $parametervalues->{'kpiid'};
        $elementId = $parametervalues->{'elementId'};
        $value = $parametervalues->{'value'};
        $dataofmonth= $parametervalues->{'dataofmonth'};
        $new_date=new \DateTime($dataofmonth);
        $new_date->modify('first day of this month');
        $newkpiid = $emClient->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$kpiid));
        $newshipid = $emClient->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id'=>$shipid));
        $newelementid = $emClient->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id'=>$elementId));

        $readingkpivalue=new ReadingKpiValues();
        $readingkpivalue->setKpiDetailsId($newkpiid);
        $readingkpivalue->setElementDetailsId($newelementid);
        $readingkpivalue->setShipDetailsId($newshipid);
        $readingkpivalue->setMonthdetail($new_date);
        $readingkpivalue->setValue($value);


        $emClient->persist($readingkpivalue);
        $emClient->flush();
        return true;


    }

    /**
     * Scorecard Lookup Data Update
     *
     * @param \GearmanJob $job Insert after finding kpi colors and data
     *
     * @return boolean
     *
     * @Gearman\Job(
     *     iterations = 1,
     *     name = "addscorecardlookupdataupdate"
     * )
     */
    public function scorecardLookupDataUpdate(\GearmanJob $job)
    {
        $em= $this->doctrine->getManager();
        $commonfunction_object=new CommonFunctions();
        $parametervalues = json_decode($job->workload());
        $shipid = $parametervalues->{'shipid'};
        $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
        $dataofmonth = $parametervalues->{'dataofmonth'};
        $time = strtotime($dataofmonth);
        $newformat = date('Y-m-d', $time);
        $new_date = new \DateTime($newformat);
        $new_date->modify('last day of this month');
        $TotalShipsInserted=$em->createQueryBuilder()
            ->select('identity(a.shipDetailsId)')
            ->from('InitialShippingBundle:ReadingKpiValues', 'a')
            ->where('a.monthdetail = :dateOfMonth and a.status=:statusValue' )
            ->setParameter('dateOfMonth', $new_date)
            ->groupby('a.shipDetailsId')
            ->setParameter('statusValue', 3)
            ->getQuery()
            ->getResult();
        // print_r($TotalShipsInserted);
        $total_numberofShipsInserted_count=count($TotalShipsInserted);


        if(count($TotalShipsInserted)!=0)
        {
            $shipids=array();
            for($findshipidcount=0;$findshipidcount<$total_numberofShipsInserted_count;$findshipidcount++)
            {
                array_push($shipids,$TotalShipsInserted[$findshipidcount][1]);
            }
            $shipids=implode(',',$shipids);
        }
        else
        {
            $shipids=$shipid;
        }

        //print_r($new_date) ;
        $newlookstatus = $em->getRepository('InitialShippingBundle:Scorecard_LookupData')->findBy(array('monthdetail'=>$new_date));
        //echo count($newlookstatus);
        for($count=0;$count<count($newlookstatus);$count++)
        {
            $mylookstatus=$newlookstatus[$count];
            $id=$mylookstatus->getId();
            $qb = $em->createQueryBuilder()
                ->delete('InitialShippingBundle:Scorecard_LookupData', 'd')
                ->where('d.id = :useremailid')
                ->setParameter(':useremailid', $id)
                ->getQuery()
                ->getResult();
        }
        $userid = $parametervalues->{'userid'};
        $status = $parametervalues->{'status'};
        $datetime=$parametervalues->{'datetime'};
        $currenttime = strtotime($datetime);
        $currentnewformat = date('Y-m-d H:i:s', $currenttime);
        $current_new_date = new \DateTime($currentnewformat);
        //print_r($parametervalues);
        if($status==3)
        {
            $monthlyScorecardKpiColorArray = array();
            $ElementColorforKpi = array();
            $scorecardKpiColorArray = array();
            $scorecardKpiList = $em->createQueryBuilder()
                ->select('a.kpiName','a.id','a.weightage')
                ->from('InitialShippingBundle:KpiDetails','a')
                ->groupby('a.kpiName')
                ->getQuery()
                ->getResult();
            //print_r($scorecardKpiList);
            $monthlyScorecardKpiWeightAverageValueTotal = 0;

            for($kpiCount=0;$kpiCount<count($scorecardKpiList);$kpiCount++)
            {
                // echo "Forloop Scorecard";
                $scorecardKpiId = $scorecardKpiList[0]['id'];
                $scorecardAllKpiId = $scorecardKpiList[$kpiCount]['id'];
                // echo "Forloop Scorecard after";
                $scorecardKpiWeight = $scorecardKpiList[$kpiCount]['weightage'];
                $scorecardKpiName = $scorecardKpiList[$kpiCount]['kpiName'];
                $kpiSumValue=0;
                $ElementColor=array();
                $ElementIds=array();

                $scorecardElementArray = $em->createQueryBuilder()
                    ->select('c.id, c.weightage, sum(a.value) as value','c.vesselWiseTotal','c.comparisonStatus')
                    ->from('InitialShippingBundle:ElementDetails', 'c')
                    ->leftjoin('InitialShippingBundle:ReadingKpiValues', 'a', 'WITH', 'c.id = a.elementDetailsId and a.monthdetail = :dateOfMonth')
                    ->where('c.kpiDetailsId = :kpiId and a.status=:statusValue' )
                    ->setParameter('kpiId', $scorecardAllKpiId)
                    ->setParameter('dateOfMonth', $new_date)
                    ->setParameter('statusValue', 3)
                    ->groupBy('c.id, c.weightage')
                    ->orderBy('c.id')
                    ->getQuery()
                    ->getResult();

                if(count($scorecardElementArray)>0)
                {
                    //echo "if condition";
                    for($elementCount=0;$elementCount<count($scorecardElementArray);$elementCount++)
                    {
                        // print_r($scorecardElementArray);
                        //echo "forloop";
                        $scorecardElementId = $scorecardElementArray[$elementCount]['id'];
                        array_push($ElementIds,$scorecardElementId);
                        $scorecardElementWeight = $scorecardElementArray[$elementCount]['weightage'];
                        $scorecardElementSumValue = $scorecardElementArray[$elementCount]['value'];
                        $vesselWiseTotalStatus = $scorecardElementArray[$elementCount]['vesselWiseTotal'];
                        $comparisonStatus=$scorecardElementArray[$elementCount]['comparisonStatus'];
                        //echo  "after for loop";
                        /*if($vesselWiseTotalStatus == "Average") {
                            $averageElementValue = $scorecardElementSumValue / count($TotalShipsInserted);
                        } else if($vesselWiseTotalStatus == "Sum") {
                            $averageElementValue = $scorecardElementSumValue;
                        }
                        else {
                            $averageElementValue = $scorecardElementSumValue / count($TotalShipsInserted);
                        }*/
                        //Find element average value
                        if(int($comparisonStatus)==1)
                        {
                            $scorecardElementRulesArray = $em->createQueryBuilder()
                                ->select('a.rules')
                                ->from('InitialShippingBundle:ElementComparisonRules', 'a')
                                ->where('a.elementDetailsId = :elementId')
                                ->setParameter('elementId', $scorecardElementId)
                                ->getQuery()
                                ->getResult();
                            $elementResultColor = "";
                            $elementColorValue=0;
                            for($elementRulesCount=0;$elementRulesCount<count($scorecardElementRulesArray);$elementRulesCount++)
                            {
                                $elementRule='\'' . $scorecardElementRulesArray[$elementRulesCount]['rules'] . ' \'';
                                $newjson_decodevalue=json_decode($elementRule);
                                $first_ElementId=$newjson_decodevalue->first->id;
                                $second_ElementId=$newjson_decodevalue->second->id;

                                $first_ElementValue= $em->createQueryBuilder()
                                    ->select('sum(a.value) as value')
                                    ->from('InitialShippingBundle:ReadingKpiValues', 'a')
                                    ->where('a.elementDetailsId = :elementId and a.monthdetail = :monthName and a.kpiDetailsId = :kpiId and a.status = :statusvalue')
                                    ->setParameter('elementId', $first_ElementId)
                                    ->setParameter('monthName',$new_date)
                                    ->setParameter('statusvalue',3)
                                    ->setParameter('kpiId',$scorecardAllKpiId)
                                    ->getQuery()
                                    ->getResult();
                                $second_ElementValue=$em->createQueryBuilder()
                                    ->select('sum(a.value) as value')
                                    ->from('InitialShippingBundle:ReadingKpiValues', 'a')
                                    ->where('a.elementDetailsId = :elementId and a.monthdetail = :monthName and a.kpiDetailsId = :kpiId and a.status = :statusvalue')
                                    ->setParameter('elementId', $second_ElementId)
                                    ->setParameter('monthName',$new_date)
                                    ->setParameter('statusvalue',3)
                                    ->setParameter('kpiId',$scorecardAllKpiId)
                                    ->getQuery()
                                    ->getResult();
                                $average_FirstElementValue=$commonfunction_object->find_Avg_Sum_Calculation($vesselWiseTotalStatus,$first_ElementValue[0]['value'],$total_numberofShipsInserted_count);
                                $average_SecondElementValue=$commonfunction_object->find_Avg_Sum_Calculation($vesselWiseTotalStatus,$second_ElementValue[0]['value'],$total_numberofShipsInserted_count);
                                $first_ElementResult=$commonfunction_object->find_options_ComparsionRule($newjson_decodevalue->first->option,$average_FirstElementValue,(int)$newjson_decodevalue->first->value);
                                $second_ElementResult=$commonfunction_object->find_options_ComparsionRule($newjson_decodevalue->second->option,$average_SecondElementValue,(int)$newjson_decodevalue->second->value);
                                if($first_ElementResult==true &&$second_ElementResult==true)
                                {
                                    $elementResultColor=$newjson_decodevalue->action->color;
                                    if ($elementResultColor == "Green") {
                                        $elementColorValue = 3;
                                    } else if ($elementResultColor == "Yellow") {
                                        $elementColorValue = 2;
                                    } else if ($elementResultColor == "Red") {
                                        $elementColorValue = 1;
                                    }
                                    break;

                                }
                                else
                                {
                                    continue;
                                }


                                /* $elementJsFileDirectory = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_findcolornode_3.js \'' . $elementRule['rules'] . ' \' ' . ((float)$averageElementValue);
                                 $elementJsFileName = 'node ' . $elementJsFileDirectory;
                                 $handle = popen($elementJsFileName, 'r');
                                 $elementColor = fread($handle, 2096);
                                 $elementResultColor = str_replace("\n", '', $elementColor);

                                 if ($elementResultColor == "false") {
                                     continue;
                                 }

                                 if ($elementResultColor == "Green") {
                                     $elementColorValue = 3;
                                     break;
                                 } else if ($elementResultColor == "Yellow") {
                                     $elementColorValue = 2;
                                     break;
                                 } else if ($elementResultColor == "Red") {
                                     $elementColorValue = 1;
                                     break;
                                 }*/
                            }

                        }
                        else
                        {
                            $averageElementValue=$commonfunction_object->find_Avg_Sum_Calculation($vesselWiseTotalStatus,$scorecardElementSumValue,$total_numberofShipsInserted_count);

                            $scorecardElementRulesArray = $em->createQueryBuilder()
                                ->select('a.rules')
                                ->from('InitialShippingBundle:Rules', 'a')
                                ->where('a.elementDetailsId = :elementId')
                                ->setParameter('elementId', $scorecardElementId)
                                ->getQuery()
                                ->getResult();

                            $elementResultColor = "";
                            $elementColorValue=0;

                            for($elementRulesCount=0;$elementRulesCount<count($scorecardElementRulesArray);$elementRulesCount++)
                            {
                                $elementRule = $scorecardElementRulesArray[$elementRulesCount];
                                $elementJsFileDirectory = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_findcolornode_3.js \'' . $elementRule['rules'] . ' \' ' . ((float)$averageElementValue);
                                $elementJsFileName = 'node ' . $elementJsFileDirectory;
                                $handle = popen($elementJsFileName, 'r');
                                $elementColor = fread($handle, 2096);
                                $elementResultColor = str_replace("\n", '', $elementColor);

                                if ($elementResultColor == "false") {
                                    continue;
                                }

                                if ($elementResultColor == "Green") {
                                    $elementColorValue = 3;
                                    break;
                                } else if ($elementResultColor == "Yellow") {
                                    $elementColorValue = 2;
                                    break;
                                } else if ($elementResultColor == "Red") {
                                    $elementColorValue = 1;
                                    break;
                                }
                            }

                        }

                        $elementValueWithWeight = $elementColorValue * (((int)$scorecardElementWeight) / 100);
                        $kpiSumValue+=$elementValueWithWeight;
                        array_push($ElementColor,$elementResultColor);

                    }

                }
                $scorecardKpiRulesArray = $em->createQueryBuilder()
                    ->select('a.rules')
                    ->from('InitialShippingBundle:KpiRules', 'a')
                    ->where('a.kpiDetailsId = :kpiId')
                    ->setParameter('kpiId', $scorecardKpiId)
                    ->getQuery()
                    ->getResult();
                $kpiResultColor='';
                for ($kpiRulesCount = 0; $kpiRulesCount < count($scorecardKpiRulesArray); $kpiRulesCount++)
                {
                    $kpiRule = $scorecardKpiRulesArray[$kpiRulesCount];
                    $kpiJsFileDirectory = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_findcolornode_3.js \'' . $kpiRule['rules'] . ' \' ' . $kpiSumValue;
                    $kpiJsFileName = 'node ' . $kpiJsFileDirectory;
                    $handle = popen($kpiJsFileName, 'r');
                    $kpiColor = fread($handle, 2096);
                    $kpiResultColor = str_replace("\n", '', $kpiColor);

                    if ($kpiResultColor != "false") {
                        break;
                    }
                }


                for($Elementcolorcount=0;$Elementcolorcount<count($ElementColor);$Elementcolorcount++)
                {
                    $lookupdataobject=new Scorecard_LookupData();
                    $lookupdataobject->setShipDetailsId($shipids);
                    $lookupdataobject->setElementcolor($ElementColor[$Elementcolorcount]);
                    $lookupdataobject->setKpiColor($kpiResultColor);
                    $lookupdataobject->setMonthdetail($new_date);
                    $lookupdataobject->setIndividualKpiAverageScore($kpiSumValue);
                    $newkpiid = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' =>$scorecardAllKpiId));
                    $newelementid = $em->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id' => $ElementIds[$Elementcolorcount]));
                    $lookupdataobject->setElementDetailsId($newelementid);
                    $lookupdataobject->setKpiDetailsId($newkpiid);
                    $em->persist($lookupdataobject);
                    $em->flush();
                }

            }

        }
        // echo "Look up status";
        $lookstatus = $em->getRepository('InitialShippingBundle:Scorecard_LookupStatus')->findBy(array('dataofmonth'=>$new_date));
        if(count($lookstatus)>0) {
            $newlookupstatus=$lookstatus[0];
            //echo "after look up status";
            $newlookupstatus->setStatus(4);
            $newlookupstatus->setDatetime(new \DateTime());
            $em->flush();
        }
        echo "Data inserted";
        return true;


    }

    /**
     * Ranking Lookup Data Update
     *
     * @param \GearmanJob $job Insert after reading kpi values
     *
     * @return boolean
     *
     * @Gearman\Job(
     *     iterations = 1,
     *     name = "addrankinglookupdataupdate"
     * )
     */
    public function rankingLookupDataUpdate(\GearmanJob $job)
    {
        //echo "Hi";
        $em= $this->doctrine->getManager();
        $parametervalues = json_decode($job->workload());
        $shipid = $parametervalues->{'shipid'};
        $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
        $dataofmonth = $parametervalues->{'dataofmonth'};
        $time = strtotime($dataofmonth);
        $newformat = date('Y-m-d', $time);
        $new_date = new \DateTime($newformat);
        $new_date->modify('last day of this month');
        $newlookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupData')->findBy(array('shipDetailsId' => $newshipid,'monthdetail'=>$new_date));
        //echo count($newlookstatus);
        for($count=0;$count<count($newlookstatus);$count++)
        {

            $mylookstatus=$newlookstatus[$count];
            $id=$mylookstatus->getId();
            $qb = $em->createQueryBuilder()
                ->delete('InitialShippingBundle:Ranking_LookupData', 'd')
                ->where('d.id = :useremailid')
                ->setParameter(':useremailid', $id)
                ->getQuery()
                ->getResult();
        }
        $userid = $parametervalues->{'userid'};
        $status = $parametervalues->{'status'};
        $datetime=$parametervalues->{'datetime'};
        $currenttime = strtotime($datetime);
        $currentnewformat = date('Y-m-d H:i:s', $currenttime);
        $current_new_date = new \DateTime($currentnewformat);

        if($status==3)
        {
            $rankingKpiList = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id', 'b.weightage')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.shipDetailsId = :shipid')
                ->setParameter('shipid', $shipid)
                ->getQuery()
                ->getResult();
            for($rankingKpiCount=0;$rankingKpiCount<count($rankingKpiList);$rankingKpiCount++)
            {
                $rankingElementValueTotal = 0;
                $rankingKpiId = $rankingKpiList[$rankingKpiCount]['id'];
                $rankingKpiWeight = $rankingKpiList[$rankingKpiCount]['weightage'];
                $rankingKpiName = $rankingKpiList[$rankingKpiCount]['kpiName'];

                $elementForKpiList = $em->createQueryBuilder()
                    ->select('a.elementName', 'a.id', 'a.weightage')
                    ->from('InitialShippingBundle:RankingElementDetails', 'a')
                    ->where('a.kpiDetailsId = :kpiid')
                    ->setParameter('kpiid', $rankingKpiId)
                    ->getQuery()
                    ->getResult();

                if(count($elementForKpiList)>0)
                {
                    for($elementCount=0;$elementCount<count($elementForKpiList);$elementCount++)
                    {
                        $scorecardElementId = $elementForKpiList[$elementCount]['id'];
                        $scorecardElementWeight = $elementForKpiList[$elementCount]['weightage'];

                        $elementDbValue = $em->createQueryBuilder()
                            ->select('a.value')
                            ->from('InitialShippingBundle:RankingMonthlyData', 'a')
                            ->where('a.elementDetailsId = :elementId and a.monthdetail = :monthName and a.shipDetailsId = :shipId and a.kpiDetailsId = :kpiId and a.status = :statusvalue')
                            ->setParameter('elementId', $scorecardElementId)
                            ->setParameter('monthName',$new_date)
                            ->setParameter('shipId',$shipid)
                            ->setParameter('statusvalue',3)
                            ->setParameter('kpiId',$rankingKpiId)
                            ->getQuery()
                            ->getResult();

                        $rankingElementRulesArray = $em->createQueryBuilder()
                            ->select('a.rules')
                            ->from('InitialShippingBundle:RankingRules', 'a')
                            ->where('a.elementDetailsId = :elementId')
                            ->setParameter('elementId', $scorecardElementId)
                            ->getQuery()
                            ->getResult();
                        $elementResultColor = "";
                        $elementColorValue=0;
                        if(count($elementDbValue)!=0)
                        {
                            for($elementRulesCount=0;$elementRulesCount<count($rankingElementRulesArray);$elementRulesCount++)
                            {
                                $elementRule = $rankingElementRulesArray[$elementRulesCount];
                                $elementJsFileDirectory = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_findcolornode_3.js \'' . $elementRule['rules'] . ' \' ' . $elementDbValue[0]['value'];
                                $elementJsFileName = 'node ' . $elementJsFileDirectory;
                                $handle = popen($elementJsFileName, 'r');
                                $elementColor = fread($handle, 2096);
                                $elementResultColor = str_replace("\n", '', $elementColor);

                                if ($elementResultColor == "false") {
                                    continue;
                                }

                                if ($elementResultColor == "Green") {
                                    $elementColorValue = $scorecardElementWeight;
                                    break;
                                } else if ($elementResultColor == "Yellow") {
                                    $elementColorValue = $scorecardElementWeight/2;
                                    break;
                                } else if ($elementResultColor == "Red") {
                                    $elementColorValue = 0;
                                    break;
                                }
                            }
                        }
                        else
                        {
                            $elementDbValue[0]['value']=null;
                        }
                        //array_push($kpiElementColorArray,$elementResultColor);
                        //$elementValueWithWeight = $elementColorValue ;

                        $lookupdataobject=new Ranking_LookupData();
                        $lookupdataobject->setShipDetailsId($newshipid);
                        $lookupdataobject->setElementcolor($elementResultColor);
                        $lookupdataobject->setMonthdetail($new_date);
                        $lookupdataobject->setElementdata($elementColorValue);
                        $newkpiid = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id' =>$rankingKpiId));
                        $newelementid = $em->getRepository('InitialShippingBundle:RankingElementDetails')->findOneBy(array('id' => $scorecardElementId));
                        $lookupdataobject->setElementDetailsId($newelementid);
                        $lookupdataobject->setKpiDetailsId($newkpiid);
                        $em->persist($lookupdataobject);
                        $em->flush();

                    }

                }
                if(count($elementForKpiList)==0)
                {
                    $newkpiid = $em->createQueryBuilder()
                        ->select('b.id')
                        ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                        ->where('b.kpiName = :kpiName')
                        ->setParameter('kpiName', $rankingKpiName)
                        ->groupby('b.kpiName')
                        ->getQuery()
                        ->getSingleScalarResult();
                    $elementForKpiList = $em->createQueryBuilder()
                        ->select('a.elementName', 'a.id', 'a.weightage')
                        ->from('InitialShippingBundle:RankingElementDetails', 'a')
                        ->where('a.kpiDetailsId = :kpiid')
                        ->setParameter('kpiid', $newkpiid)
                        ->getQuery()
                        ->getResult();

                    for($elementCount=0;$elementCount<count($elementForKpiList);$elementCount++)
                    {
                        $scorecardElementId = $elementForKpiList[$elementCount]['id'];
                        $scorecardElementWeight = $elementForKpiList[$elementCount]['weightage'];
                        // $Alternewkpiid=$newkpiid[0]['id'];

                        $elementDbValue = $em->createQueryBuilder()
                            ->select('a.value')
                            ->from('InitialShippingBundle:RankingMonthlyData', 'a')
                            ->where('a.elementDetailsId = :elementId and a.monthdetail = :monthName and a.shipDetailsId = :shipId and a.kpiDetailsId = :kpiId and a.status = :statusvalue')
                            ->setParameter('elementId', $scorecardElementId)
                            ->setParameter('monthName',$new_date)
                            ->setParameter('shipId',$shipid)
                            ->setParameter('statusvalue',3)
                            ->setParameter('kpiId',$newkpiid)
                            ->getQuery()
                            ->getResult();

                        $rankingElementRulesArray = $em->createQueryBuilder()
                            ->select('a.rules')
                            ->from('InitialShippingBundle:RankingRules', 'a')
                            ->where('a.elementDetailsId = :elementId')
                            ->setParameter('elementId', $scorecardElementId)
                            ->getQuery()
                            ->getResult();

                        $elementResultColor = "";
                        $elementColorValue=0;
                        if(count($elementDbValue)!=0)
                        {
                            // echo $elementDbValue[0]['value'];
                            for($elementRulesCount=0;$elementRulesCount<count($rankingElementRulesArray);$elementRulesCount++)
                            {
                                $elementRule = $rankingElementRulesArray[$elementRulesCount];
                                $elementJsFileDirectory = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_findcolornode_3.js \'' . $elementRule['rules'] . ' \' ' . $elementDbValue[0]['value'];
                                $elementJsFileName = 'node ' . $elementJsFileDirectory;
                                $handle = popen($elementJsFileName, 'r');
                                $elementColor = fread($handle, 2096);
                                $elementResultColor = str_replace("\n", '', $elementColor);

                                if ($elementResultColor == "false") {
                                    continue;
                                }

                                if ($elementResultColor == "Green") {
                                    $elementColorValue = $scorecardElementWeight;
                                    break;
                                } else if ($elementResultColor == "Yellow") {
                                    $elementColorValue = $scorecardElementWeight/2;
                                    break;
                                } else if ($elementResultColor == "Red") {
                                    $elementColorValue = 0;
                                    break;
                                }
                            }
                        }
                        else
                        {
                            $elementDbValue[0]['value']=null;
                        }
                        // array_push($kpiElementColorArray,$elementResultColor);
                        //$elementValueWithWeight = $elementColorValue ;
                        // echo $elementColorValue;
                        $lookupdataobject=new Ranking_LookupData();
                        $lookupdataobject->setShipDetailsId($newshipid);
                        $lookupdataobject->setElementcolor($elementResultColor);
                        $lookupdataobject->setMonthdetail($new_date);
                        $lookupdataobject->setElementdata($elementColorValue);
                        $newkpiidobject = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id' => $newkpiid));
                        $newelementid = $em->getRepository('InitialShippingBundle:RankingElementDetails')->findOneBy(array('id' => $scorecardElementId));
                        $lookupdataobject->setElementDetailsId($newelementid);
                        $lookupdataobject->setKpiDetailsId($newkpiidobject);
                        $em->persist($lookupdataobject);
                        $em->flush();
                    }
                }
            }

        }

        $lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('shipid' => $newshipid,'dataofmonth'=>$new_date));

        if(count($lookstatus)>0)
        {
            $newlookupstatus=$lookstatus[0];
            $newlookupstatus->setStatus(4);
            $newlookupstatus->setDatetime(new \DateTime());
            $em->flush();
        }

        echo "Data inserted";
        return true;


    }
    /**
     * Mailing Reports
     *
     * @param \GearmanJob $job Alll Mailing  Sending
     *
     * @return boolean
     *
     * @Gearman\Job(
     *     iterations = 1,
     *     name = "common_mail_function"
     * )
     */
    public function common_mail_function(\GearmanJob $job)
    {


        $emClient = $this->doctrine->getManager();
        $mailer = $this->container->get('mailer');
        $parametervalues = json_decode($job->workload());
        $from_emailid = $parametervalues->{'from_emailid'};
        $to_emailids = $parametervalues->{'to_emailids'};
        $filename = $parametervalues->{'filename'};
        $comment = $parametervalues->{'comment'};
        $subject= $parametervalues->{'subject'};
        $pdffilenamefullpath= $this->container->getParameter('kernel.root_dir').'/../web/uploads/brochures/'.$filename;

        for($ma=0;$ma<count($to_emailids);$ma++)
        {

            $message = \Swift_Message::newInstance()
                ->setFrom($from_emailid)
                ->setTo($to_emailids[$ma])
                ->setSubject($subject)
                ->setBody($comment);
            $message->attach(\Swift_Attachment::fromPath($pdffilenamefullpath)->setFilename($filename));
            $mailer->send($message);

        }
        echo "Mail Has Been Send...";
        return true;


    }


}