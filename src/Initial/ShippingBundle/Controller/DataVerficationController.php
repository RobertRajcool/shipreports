<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\CommonFunctions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Initial\ShippingBundle\Entity\ReadingKpiValues;
use Initial\ShippingBundle\Entity\RankingMonthlyData;
use Symfony\Component\HttpFoundation\Session\Session;
use Initial\ShippingBundle\Entity\Excel_file_details;
use Initial\ShippingBundle\Entity\PHPNodeJs;
use Initial\ShippingBundle\Entity\Ranking_LookupStatus;
use Initial\ShippingBundle\Entity\Scorecard_LookupStatus;
use Initial\ShippingBundle\Entity\Ranking_LookupData;
use Initial\ShippingBundle\Form\AddExcelFileType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Reader_Excel2007;
use PHPExcel_Shared_Date;
use PHPExcel_Cell;


/**
 * DataVerficationController.
 *
 * @Route("/dataverfication")
 */
class DataVerficationController extends Controller
{
    /**
     * adddata.
     *
     * @Route("/add_data", name="adddata_scorecard")
     */
    public function findnumofshipsAction(Request $request,$mode = '')
    {
        $em = $this->getDoctrine()->getManager();
        //Finding Company for Login user Starts Here//
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $userId = $user->getId();
            $username = $user->getUsername();
            $role = $user->getRoles();
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


            $listallshipforcompany = $query->getResult();
            if($mode=='nextshipajaxcall')
            {
                return $listallshipforcompany;
            }
            $statusforship = $this->findshipstatusmonth($dataofmonth = '', $listallshipforcompany, $role[0]);
            $finddatawithstatus=array();
            $shipid=0;
            $shipname='';
            $templatechoosen='base.html.twig';

            if ($role[0] == 'ROLE_ADMIN')
            {
                $status=2;
                $index = array_search(0, $statusforship);
                $shipid=$listallshipforcompany[$index]['id'];
                $shipname=$listallshipforcompany[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus($status,$shipid);
            }
            if ($role[0] == 'ROLE_MANAGER')
            {
                $status=1;
                $index = array_search(0, $statusforship);
                $shipid=$listallshipforcompany[$index]['id'];
                $shipname=$listallshipforcompany[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus($status,$shipid);
            }
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER')
            {
                $templatechoosen='v-ships_layout.html.twig';
                $status=0;
                $index = array_search(0, $statusforship);
                $shipid=$listallshipforcompany[$index]['id'];
                $shipname=$listallshipforcompany[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus($status,$shipid);

            }
            if(count($finddatawithstatus)==4)
            {
                return $this->render('InitialShippingBundle:DataVerficationScoreCorad:home.html.twig',
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray'=>$finddatawithstatus['elementnamekpiname'],'elementcount'=>$finddatawithstatus['maxelementcount'],
                        'elementvalues'=>$finddatawithstatus['elementvalues'],
                        'elementweightage'=>$finddatawithstatus['elementweightage'],
                        'currentshipid'=>$shipid,'currentshipname'=>$shipname,'templatechoosen'=>$templatechoosen
                    ));
            }
            else
            {
                return $this->render('InitialShippingBundle:DataVerficationScoreCorad:home.html.twig',
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray'=>array(),'elementcount'=>0,
                        'elementvalues'=>array(),
                        'currentshipid'=>$shipid,'currentshipname'=>$shipname,'templatechoosen'=>$templatechoosen
                    ));
            }

        }

    }
    //Finding Status For monthdata

    private function findshipstatusmonth($dataofmonth = '', $shipdetialsarray, $role)
    {
        $session = new Session();
        if ($dataofmonth == '') {
            $stringdataofmonth = date('Y-m-d');
        }
        if ($dataofmonth != '') {
            $mydate = '01-' . $dataofmonth;
            $time = strtotime($mydate);
            $stringdataofmonth = date('Y-m-d', $time);

        }
        $status = 0;
        $new_date = new \DateTime($stringdataofmonth);
        $new_date->modify('last day of this month');
        $em = $this->getDoctrine()->getManager();
        $statusarray = array();

        if ($role == 'ROLE_ADMIN')
        {
            $status = 3;


            for ($ship = 0; $ship < count($shipdetialsarray); $ship++)
            {
                $statusfromresult = $em->createQueryBuilder()
                    ->select('b.status')
                    ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.status = :status')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $shipdetialsarray[$ship]['id'])
                    ->setParameter('dataofmonth', $new_date)
                    ->setParameter('status', 3)
                    ->getQuery()
                    ->getResult();
                if (count($statusfromresult) == 0)
                {
                    array_push($statusarray, 0);

                }
                if (count($statusfromresult) > 0)
                {
                    array_push($statusarray, 3);

                }

            }

            return $statusarray;
        }
        if ($role == 'ROLE_MANAGER')
        {
            $status = 2;


            for ($ship = 0; $ship < count($shipdetialsarray); $ship++)
            {
                $statusfromresult = $em->createQueryBuilder()
                    ->select('b.status')
                    ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.status = 2 OR b.status  = 3')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $shipdetialsarray[$ship]['id'])
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery()
                    ->getResult();
                if (count($statusfromresult) == 0)
                {
                    array_push($statusarray, 0);
                }
                if (count($statusfromresult) > 0)
                {
                    array_push($statusarray, 2);

                }

            }
            return $statusarray;




        }
        if ($role == 'ROLE_KPI_INFO_PROVIDER')
        {
            $status = 1;
            $tempvarable=0;
            $elementtempvariable=0;

            for ($ship = 0; $ship < count($shipdetialsarray); $ship++)
            {
                $statusfromresult = $em->createQueryBuilder()
                    ->select('b.status')
                    ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->andWhere('b.status = 1 OR b.status  = 2 OR b.status  = 3')
                    ->setParameter('shipdetailsid', $shipdetialsarray[$ship]['id'])
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery()
                    ->getResult();
                if (count($statusfromresult) == 0)
                {
                    array_push($statusarray, 0);
                }
                if (count($statusfromresult) > 0)
                {
                    array_push($statusarray,1);

                }

            }


            return $statusarray;

        }
        else
        {
            return $statusarray;
        }
    }
    private  function findelementkpiid($shipid)
    {
        $em = $this->getDoctrine()->getManager();
        $returnarray = array();
        $sessionkpielementid=array();
        $currentselectionid=0;
        $currentselectionname='';
        $maxelementcount=0;
        $query = $em->createQueryBuilder()
            ->select('b.kpiName', 'b.id')
            ->from('InitialShippingBundle:KpiDetails', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->setParameter('shipdetailsid', $shipid)
            ->add('orderBy', 'b.id  ASC ')
            ->getQuery();

        $ids = $query->getResult();
        $maxelementcount = 0;


        $sessionkpielementid = array();
        $k = 0;
        for ($i = 0; $i < count($ids); $i++) {
            $kpiid = $ids[$i]['id'];
            $kpiname = $ids[$i]['kpiName'];

            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id')
                ->from('InitialShippingBundle:ElementDetails', 'b')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();
            $elementids = $query->getResult();
            if (count($elementids) == 0) {
                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:KpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();

                $ids1 = $query1->getResult();
                $newkpiid = $ids1[0]['id'];
                $newkpiname = $ids1[0]['kpiName'];
                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id')
                    ->from('InitialShippingBundle:ElementDetails', 'b')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    $sessionkpielementid[$newkpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];


                }
            }
            else
            {
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }

                for ($j = 0; $j < count($elementids); $j++)
                {
                    $sessionkpielementid[$kpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];

                }
            }


        }
        return array('elementids'=>$sessionkpielementid);
    }

    private function finddatawithstatus($status,$shipid,$dataofmonth = '')
    {


        if ($dataofmonth == '') {
            $stringdataofmonth = date('Y-m-d');
        }
        if ($dataofmonth != '') {
            $mydate = '01-' . $dataofmonth;
            $time = strtotime($mydate);
            $stringdataofmonth = date('Y-m-d', $time);

        }

        $new_date = new \DateTime($stringdataofmonth);
        $new_date->modify('last day of this month');
        $em = $this->getDoctrine()->getManager();

        $elementvalues=array();
        $returnarray=array();
        $elementweightage=array();

        $resularray = $em->createQueryBuilder()
            ->select('b.value')
            ->from('InitialShippingBundle:ReadingKpiValues', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->andWhere('b.monthdetail =:dataofmonth')
            ->andWhere('b.status = :status')
            ->setParameter('shipdetailsid', $shipid)
            ->setParameter('dataofmonth', $new_date)
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();
        $query = $em->createQueryBuilder()
            ->select('b.kpiName', 'b.id')
            ->from('InitialShippingBundle:KpiDetails', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->setParameter('shipdetailsid', $shipid)
            ->add('orderBy', 'b.id  ASC ')
            ->getQuery();
        $ids = $query->getResult();
        $maxelementcount = 0;
        $k = 0;
        for ($i = 0; $i < count($ids); $i++)
        {
            $kpiid = $ids[$i]['id'];
            $kpiname = $ids[$i]['kpiName'];
            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id','b.weightage')
                ->from('InitialShippingBundle:ElementDetails', 'b')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();
            $elementids = $query->getResult();
            if (count($elementids) == 0) {
                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:KpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();
                $ids1 = $query1->getResult();
                $newkpiid = $ids1[0]['id'];
                $newkpiname = $ids1[0]['kpiName'];
                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id','b.weightage')
                    ->from('InitialShippingBundle:ElementDetails', 'b')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    // $sessionkpielementid[$newkpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage,$elementids[$j]['weightage']);
                }
            }
            else {
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    // $sessionkpielementid[$kpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage,$elementids[$j]['weightage']);
                }
            }
        }
        for ($kkk = 0; $kkk < count($resularray); $kkk++)
        {
            array_push($elementvalues, $resularray[$kkk]['value']);
        }

        return array('elementvalues'=>$elementvalues,'elementweightage'=>$elementweightage,'elementnamekpiname'=>$returnarray,'maxelementcount'=>$maxelementcount);
    }
    private function finddatawithstatus_MonthChange($status,$status1,$shipid,$dataofmonth = '')
    {


        if ($dataofmonth == '') {
            $stringdataofmonth = date('Y-m-d');
        }
        if ($dataofmonth != '') {
            $mydate = '01-' . $dataofmonth;
            $time = strtotime($mydate);
            $stringdataofmonth = date('Y-m-d', $time);

        }

        $new_date = new \DateTime($stringdataofmonth);
        $new_date->modify('last day of this month');
        $em = $this->getDoctrine()->getManager();

        $elementvalues=array();
        $returnarray=array();
        $elementweightage=array();

        $resularray = $em->createQueryBuilder()
            ->select('b.value')
            ->from('InitialShippingBundle:ReadingKpiValues', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->andWhere('b.monthdetail =:dataofmonth')
            ->andWhere('b.status = '.$status.' OR b.status  = '.$status1.'')
            ->setParameter('shipdetailsid', $shipid)
            ->setParameter('dataofmonth', $new_date)
            ->getQuery()
            ->getResult();
        $query = $em->createQueryBuilder()
            ->select('b.kpiName', 'b.id')
            ->from('InitialShippingBundle:KpiDetails', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->setParameter('shipdetailsid', $shipid)
            ->add('orderBy', 'b.id  ASC ')
            ->getQuery();
        $ids = $query->getResult();
        $maxelementcount = 0;
        $k = 0;
        for ($i = 0; $i < count($ids); $i++)
        {
            $kpiid = $ids[$i]['id'];
            $kpiname = $ids[$i]['kpiName'];
            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id','b.weightage')
                ->from('InitialShippingBundle:ElementDetails', 'b')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();
            $elementids = $query->getResult();
            if (count($elementids) == 0) {
                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:KpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();
                $ids1 = $query1->getResult();
                $newkpiid = $ids1[0]['id'];
                $newkpiname = $ids1[0]['kpiName'];
                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id','b.weightage')
                    ->from('InitialShippingBundle:ElementDetails', 'b')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    // $sessionkpielementid[$newkpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage,$elementids[$j]['weightage']);
                }
            }
            else {
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    // $sessionkpielementid[$kpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage,$elementids[$j]['weightage']);
                }
            }
        }
        for ($kkk = 0; $kkk < count($resularray); $kkk++)
        {
            array_push($elementvalues, $resularray[$kkk]['value']);
        }

        return array('elementvalues'=>$elementvalues,'elementweightage'=>$elementweightage,'elementnamekpiname'=>$returnarray,'maxelementcount'=>$maxelementcount);
    }





    /**
     * Adding Kpi Values.
     *
     * @Route("/{buttonid}/addkpivalues", name="addkpivaluesname")
     * @Method("Post")
     */
    public function addkpivaluesAction(Request $request, $buttonid)
    {
        $em = $this->getDoctrine()->getManager();
        //Finding Company for Login user Starts Here//
        $user = $this->getUser();
        $userid=$user->getId();
        $shipid = $request->request->get('shipid');
        $returnfromcontroller = $this->findelementkpiid($shipid);
        $kpiandelementids = $returnfromcontroller['elementids'];
        $elementvalues = $request->request->get('newelemetvalues');
        $dataofmonth = $request->request->get('dataofmonth');
        $mydate = '01-' . $dataofmonth;
        $time = strtotime($mydate);
        $newformat = date('Y-m-d', $time);
        $em = $this->getDoctrine()->getManager();
        $new_date = new \DateTime($newformat);
        $new_date->modify('last day of this month');
        $k = 0;
        $returnmsg = '';
        $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));

        if ($buttonid == 'updatebuttonid' || $buttonid == 'adminbuttonid' || $buttonid == 'verfiybuttonid') {

            $returnarrayids = $em->createQueryBuilder()
                ->select('b.id')
                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                ->where('b.shipDetailsId = :shipdetailsid')
                ->andWhere('b.monthdetail =:dataofmonth')
                ->setParameter('shipdetailsid', $shipid)
                ->setParameter('dataofmonth', $new_date)
                ->getQuery()
                ->getResult();
            for ($kkk = 0; $kkk < count($returnarrayids); $kkk++) {
                $entityobject = $em->getRepository('InitialShippingBundle:ReadingKpiValues')->find($returnarrayids[$kkk]['id']);
                if ($buttonid == 'adminbuttonid') {
                    $entityobject->setValue($elementvalues[$kkk]);
                    $entityobject->setStatus(3);
                }
                if ($buttonid == 'verfiybuttonid') {
                    $entityobject->setValue($elementvalues[$kkk]);
                    $entityobject->setStatus(2);
                }
                if ($buttonid == 'updatebuttonid') {
                    $entityobject->setValue($elementvalues[$kkk]);
                    $entityobject->setStatus(1);
                }
                //$entityobject->setFilename($pdffilenamearray[0].'.pdf');
                $em->flush();

            }
            $returnmsg = ' Data Updated...';


            if($buttonid == 'adminbuttonid')
            {
                $rankinglookuptable=array('shipid'=>$shipid,'dataofmonth'=>$mydate,'userid'=>$userid,'status'=>3,'datetime'=>date('Y-m-d H:i:s'));
                // $lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('shipid' => $newshipid,'dataofmonth'=>$new_date));
                $lookstatus = $em->getRepository('InitialShippingBundle:Scorecard_LookupStatus')->findBy(array('dataofmonth'=>$new_date));
                if(count($lookstatus)!=0)
                {
                    $newlookupstatus=$lookstatus[0];
                }
                $TotalShipsInserted=$em->createQueryBuilder()
                    ->select('identity(a.shipDetailsId)')
                    ->from('InitialShippingBundle:ReadingKpiValues', 'a')
                    ->where('a.monthdetail = :dateOfMonth and a.status=:statusValue' )
                    ->setParameter('dateOfMonth', $new_date)
                    ->groupby('a.shipDetailsId')
                    ->setParameter('statusValue', 3)
                    ->getQuery()
                    ->getResult();
                //print_r($TotalShipsInserted);


                if(count($TotalShipsInserted)!=0)
                {
                    $shipids=array();
                    for($findshipidcount=0;$findshipidcount<count($TotalShipsInserted);$findshipidcount++)
                    {
                        array_push($shipids,$TotalShipsInserted[$findshipidcount][1]);
                    }
                    $shipids=implode(',',$shipids);
                }
                else
                {
                    $shipids=$shipid;
                }
                $newlookupstatus->setStatus(3);
                $newlookupstatus->setShipid($shipids);
                $newlookupstatus->setDatetime(new \DateTime());
                $em->flush();
                $gearman = $this->get('gearman');
                $gearman->doBackgroundJob('InitialShippingBundleserviceReadExcelWorker~addscorecardlookupdataupdate', json_encode($rankinglookuptable));
            }
            if($buttonid =='verfiybuttonid')
            {
                //$lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('shipid' => $newshipid,'dataofmonth'=>$new_date));
                $lookstatus = $em->getRepository('InitialShippingBundle:Scorecard_LookupStatus')->findBy(array('dataofmonth'=>$new_date));
                if(count($lookstatus)!=0)
                {
                    $newlookupstatus=$lookstatus[0];
                }
                $TotalShipsInserted=$em->createQueryBuilder()
                    ->select('identity(a.shipDetailsId)')
                    ->from('InitialShippingBundle:ReadingKpiValues', 'a')
                    ->where('a.monthdetail = :dateOfMonth and a.status=:statusValue' )
                    ->setParameter('dateOfMonth', $new_date)
                    ->groupby('a.shipDetailsId')
                    ->setParameter('statusValue', 2)
                    ->getQuery()
                    ->getResult();
                //print_r($TotalShipsInserted);


                if(count($TotalShipsInserted)!=0)
                {
                    $shipids=array();
                    for($findshipidcount=0;$findshipidcount<count($TotalShipsInserted);$findshipidcount++)
                    {
                        array_push($shipids,$TotalShipsInserted[$findshipidcount][1]);
                    }
                    $shipids=implode(',',$shipids);
                }
                else
                {
                    $shipids=$shipid;
                }
                $newlookupstatus->setStatus(2);
                $newlookupstatus->setShipid($shipids);
                $newlookupstatus->setDatetime(new \DateTime());
                $em->flush();
            }
            if($buttonid == 'updatebuttonid')
            {
                //$lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('shipid' => $newshipid,'dataofmonth'=>$new_date));
                $lookstatus = $em->getRepository('InitialShippingBundle:Scorecard_LookupStatus')->findBy(array('dataofmonth'=>$new_date));
                if(count($lookstatus)!=0)
                {
                    $newlookupstatus=$lookstatus[0];
                }
                $TotalShipsInserted=$em->createQueryBuilder()
                    ->select('identity(a.shipDetailsId)')
                    ->from('InitialShippingBundle:ReadingKpiValues', 'a')
                    ->where('a.monthdetail = :dateOfMonth and a.status=:statusValue' )
                    ->setParameter('dateOfMonth', $new_date)
                    ->groupby('a.shipDetailsId')
                    ->setParameter('statusValue', 1)
                    ->getQuery()
                    ->getResult();
                //print_r($TotalShipsInserted);


                if(count($TotalShipsInserted)!=0)
                {
                    $shipids=array();
                    for($findshipidcount=0;$findshipidcount<count($TotalShipsInserted);$findshipidcount++)
                    {
                        array_push($shipids,$TotalShipsInserted[$findshipidcount][1]);
                    }
                    $shipids=implode(',',$shipids);
                }
                else
                {
                    $shipids=$shipid;
                }
                $newlookupstatus->setStatus(1);
                $newlookupstatus->setShipid($shipids);
                $newlookupstatus->setDatetime(new \DateTime());
                $em->flush();
            }

        }
        if ($buttonid == 'savebuttonid') {
            foreach ($kpiandelementids as $kpikey => $kpipvalue) {


                $newkpiid = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $kpikey));
                foreach ($kpipvalue as $elementkey => $elementvalue) {
                    $newelementid = $em->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id' => $elementvalue));

                    $readingkpivalue = new ReadingKpiValues();
                    $readingkpivalue->setKpiDetailsId($newkpiid);
                    $readingkpivalue->setElementDetailsId($newelementid);
                    $readingkpivalue->setShipDetailsId($newshipid);
                    $readingkpivalue->setMonthdetail($new_date);
                    $readingkpivalue->setValue($elementvalues[$k]);
                    $readingkpivalue->setStatus(1);
                    $em->persist($readingkpivalue);
                    $em->flush();
                    $k++;

                }
            }
            $returnmsg = ' Data Saved...';
            $fullurl = "http://shipreports/login";
            $mailer = $this->container->get('mailer');
            $message = \Swift_Message::newInstance()
                ->setFrom('lawrance@commusoft.co.uk')
                ->setTo("doss.cclawranc226@gmail.com")
                ->setSubject($newshipid->getShipName() . ' Data Added By V-Ship Team')
                ->setBody("This Web Url:" . $fullurl);
            $mailer->send($message);
            $TotalShipsInserted=$em->createQueryBuilder()
                ->select('identity(a.shipDetailsId)')
                ->from('InitialShippingBundle:ReadingKpiValues', 'a')
                ->where('a.monthdetail = :dateOfMonth and a.status=:statusValue' )
                ->setParameter('dateOfMonth', $new_date)
                ->groupby('a.shipDetailsId')
                ->setParameter('statusValue', 1)
                ->getQuery()
                ->getResult();
            //print_r($TotalShipsInserted);


            if(count($TotalShipsInserted)!=0)
            {
                $shipids=array();
                for($findshipidcount=0;$findshipidcount<count($TotalShipsInserted);$findshipidcount++)
                {
                    array_push($shipids,$TotalShipsInserted[$findshipidcount][1]);
                }
                $shipids=implode(',',$shipids);
            }
            else
            {
                $shipids=$shipid;
            }

            $lookstatus = $em->getRepository('InitialShippingBundle:Scorecard_LookupStatus')->findBy(array('dataofmonth'=>$new_date));
            if(count($lookstatus)!=0)
            {
                $newlookupstatus=$lookstatus[0];
                $newlookupstatus->setShipid($shipids);
                $newlookupstatus->setDatetime(new \DateTime());
                $em->flush();
            }
            else
            {
                $lookupstatusobject = new Scorecard_LookupStatus();
                $lookupstatusobject->setShipid($shipid);
                $lookupstatusobject->setStatus(1);
                $lookupstatusobject->setDataofmonth($new_date);
                $lookupstatusobject->setDatetime(new \DateTime());
                $lookupstatusobject->setUserid($userid);
                $em->persist($lookupstatusobject);
                $em->flush();
            }

        }
        //$newlookstatus = $em->getRepository('InitialShippingBundle:Scorecard_LookupData')->findBy(array('monthdetail'=>$new_date));

        $shipname = $newshipid->getShipName();
        $nextshipid = 0;
        $nextshipname = '';
        $user = $this->getUser();
        $role = $user->getRoles();
        $kpielementarray = $this->findnumofshipsAction($request, 'nextshipajaxcall');
        $statusforship = $this->findshipstatusmonth($dataofmonth, $kpielementarray, $role[0]);
        $finddatawithstatus = array();


        if ($role[0] == 'ROLE_ADMIN') {
            $status = 2;
            $index = array_search(0, $statusforship);
            $nextshipid = $kpielementarray[$index]['id'];
            $nextshipname = $kpielementarray[$index]['shipName'];
            $finddatawithstatus = $this->finddatawithstatus($status, $nextshipid, $dataofmonth);
        }
        if ($role[0] == 'ROLE_MANAGER') {
            $status = 1;
            $index = array_search(0, $statusforship);
            $nextshipid = $kpielementarray[$index]['id'];
            $nextshipname = $kpielementarray[$index]['shipName'];
            $finddatawithstatus = $this->finddatawithstatus($status, $nextshipid, $dataofmonth);
        }
        if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
            $status = 0;
            $index = array_search(0, $statusforship);
            $nextshipid = $kpielementarray[$index]['id'];
            $nextshipname = $kpielementarray[$index]['shipName'];
            $finddatawithstatus = $this->finddatawithstatus($status, $nextshipid, $dataofmonth);

        }

        $response = new JsonResponse();
        if (count($finddatawithstatus) == 4) {


            $response->setData(array('returnmsg' => $shipname . $returnmsg,
                'shipname' => $nextshipname,
                'shipid' => $nextshipid,
                'kpiNameArray' => $finddatawithstatus['elementnamekpiname'],
                'elementcount' => $finddatawithstatus['maxelementcount'],
                'elementweightage' => $finddatawithstatus['elementweightage'],
                'elementvalues' => $finddatawithstatus['elementvalues']));
            return $response;
        }
        else {

            $response->setData(array('returnmsg' => $shipname . $returnmsg,
                'shipname' => $nextshipname,
                'shipid' => $nextshipid,
                'kpiNameArray' => array(),
                'elementcount' => 0,
                'elementvalues' => array()));
            return $response;
        }


    }

    /**
     * Element and Kpi for Particular Ships.
     *
     * @Route("/{shipid}/{monthdetail}/shipskpielment", name="shipskpielment")
     */
    public function shipskpielmentAction(Request $request, $shipid, $monthdetail, $mode = '')
    {
        $em = $this->getDoctrine()->getManager();
        $mydate = '01-' . $monthdetail;
        $time = strtotime($mydate);
        $newformat = date('Y-m-d', $time);
        $new_date = new \DateTime($newformat);
        $new_date->modify('last day of this month');
        $user = $this->getUser();
        $role = $user->getRoles();
        $status=0;
        $resularray=array();
        if($role[0] == 'ROLE_ADMIN')
        {
            $query=$em->createQueryBuilder()
                ->select('b.value')
                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                ->where('b.shipDetailsId = :shipdetailsid')
                ->andWhere('b.monthdetail =:dataofmonth')
                ->andWhere('b.status = 2 OR b.status  = 3')
                ->setParameter('shipdetailsid', $shipid)
                ->setParameter('dataofmonth', $new_date)
                ->getQuery();
        }
        if($role[0] == 'ROLE_MANAGER')
        {
            $query=$em->createQueryBuilder()
                ->select('b.value')
                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                ->where('b.shipDetailsId = :shipdetailsid')
                ->andWhere('b.monthdetail =:dataofmonth')
                ->andWhere('b.status = 1 OR b.status  = 2')
                ->setParameter('shipdetailsid', $shipid)
                ->setParameter('dataofmonth', $new_date)
                ->getQuery();
        }
        if($role[0] == 'ROLE_KPI_INFO_PROVIDER')
        {
            $query=$em->createQueryBuilder()
                ->select('b.value')
                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                ->where('b.shipDetailsId = :shipdetailsid')
                ->andWhere('b.status = 0 OR b.status  = 1')
                ->andWhere('b.monthdetail =:dataofmonth')
                ->setParameter('shipdetailsid', $shipid)
                ->setParameter('dataofmonth', $new_date)
                ->getQuery();
        }
        $resularray = $query->getResult();


        $query = $em->createQueryBuilder()
            ->select('b.kpiName', 'b.id')
            ->from('InitialShippingBundle:KpiDetails', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->setParameter('shipdetailsid', $shipid)
            ->add('orderBy', 'b.id  ASC ')
            ->getQuery();

        $ids = $query->getResult();
        $maxelementcount = 0;

        $returnarray = array();
        $elementweightage=array();
        $sessionkpielementid = array();
        $k = 0;
        for ($i = 0; $i < count($ids); $i++) {
            $kpiid = $ids[$i]['id'];
            $kpiname = $ids[$i]['kpiName'];

            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id','b.weightage')
                ->from('InitialShippingBundle:ElementDetails','b')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();
            $elementids = $query->getResult();
            if (count($elementids) == 0) {
                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:KpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();

                $ids1 = $query1->getResult();
                $newkpiid = $ids1[0]['id'];
                $newkpiname = $ids1[0]['kpiName'];
                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id','b.weightage')
                    ->from('InitialShippingBundle:ElementDetails', 'b')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    $sessionkpielementid[$newkpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage,$elementids[$j]['weightage']);


                }
            } else {
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }

                for ($j = 0; $j < count($elementids); $j++) {
                    $sessionkpielementid[$kpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage,$elementids[$j]['weightage']);

                }
            }


        }
        $elementvalues = array();
        for ($kkk = 0; $kkk < count($resularray); $kkk++) {
            array_push($elementvalues, $resularray[$kkk]['value']);
        }
        if ($mode == 'listkpielement') {
            return array('returnarray' => $returnarray,'elementweightage'=>$elementweightage, 'elementcount' => $maxelementcount, 'elementvalues' => $elementvalues);
        }
        $response = new JsonResponse();
        $response->setData(array('kpiNameArray' => $returnarray,'elementweightage'=>$elementweightage, 'elementcount' => $maxelementcount, 'elementvalues' => $elementvalues));
        return $response;
    }


    /**
     * adddata Ranking.
     *
     * @Route("/add_data_forranking", name="adddata_scorecard_forranking")
     */
    public function findnumofshipsforrankingAction(Request $request,$mode = '')
    {
        $em = $this->getDoctrine()->getManager();
        //Finding Company for Login user Starts Here//
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $userId = $user->getId();
            $username = $user->getUsername();
            $role = $user->getRoles();
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


            $listallshipforcompany = $query->getResult();
            if($mode=='nextshipajaxcall')
            {
                return $listallshipforcompany;
            }
            $statusforship = $this->findshipstatus_ranking($dataofmonth = '', $listallshipforcompany, $role[0]);
            $finddatawithstatus=array();
            $shipid=0;
            $shipname='';
            $templatechoosen='base.html.twig';

            if ($role[0] == 'ROLE_ADMIN')
            {
                $status=2;
                $index = array_search(0, $statusforship);
                $shipid=$listallshipforcompany[$index]['id'];
                $shipname=$listallshipforcompany[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus_ranking($status,$shipid);
            }
            if ($role[0] == 'ROLE_MANAGER')
            {
                $status=1;
                $index = array_search(0, $statusforship);
                $shipid=$listallshipforcompany[$index]['id'];
                $shipname=$listallshipforcompany[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus_ranking($status,$shipid);
            }
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER')
            {
                $status=0;
                $templatechoosen='v-ships_layout.html.twig';
                $index = array_search(0, $statusforship);
                $shipid=$listallshipforcompany[$index]['id'];
                $shipname=$listallshipforcompany[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus_ranking($status,$shipid);

            }
            if(count($finddatawithstatus)==4)
            {

                return $this->render('InitialShippingBundle:DataVerficationRanking:home.html.twig',
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray'=>$finddatawithstatus['elementnamekpiname'],'elementcount'=>$finddatawithstatus['maxelementcount'],
                        'elementvalues'=>$finddatawithstatus['elementvalues'],
                        'elementweightage'=>$finddatawithstatus['elementweightage'],
                        'currentshipid'=>$shipid,'currentshipname'=>$shipname,'templatechoosen'=>$templatechoosen
                    ));
            }
            else
            {

                return $this->render('InitialShippingBundle:DataVerficationRanking:home.html.twig',
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray'=>array(),'elementcount'=>0,
                        'elementvalues'=>array(),
                        'currentshipid'=>$shipid,'currentshipname'=>$shipname,'templatechoosen'=>$templatechoosen
                    ));
            }

        }
    }

    //Finding Status For monthdata While after add,save,verify,upload

    private function findshipstatus_ranking($dataofmonth = '', $shipdetialsarray, $role)
    {
        $session = new Session();
        if ($dataofmonth == '') {
            $stringdataofmonth = date('Y-m-d');
        }
        if ($dataofmonth != '') {
            $mydate = '01-' . $dataofmonth;
            $time = strtotime($mydate);
            $stringdataofmonth = date('Y-m-d', $time);

        }
        $status = 0;
        $new_date = new \DateTime($stringdataofmonth);
        $new_date->modify('last day of this month');
        $em = $this->getDoctrine()->getManager();
        $statusarray = array();

        if ($role == 'ROLE_ADMIN')
        {
            $status = 3;


            for ($ship = 0; $ship < count($shipdetialsarray); $ship++)
            {
                $statusfromresult = $em->createQueryBuilder()
                    ->select('b.status')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.status = :status')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $shipdetialsarray[$ship]['id'])
                    ->setParameter('dataofmonth', $new_date)
                    ->setParameter('status', 3)
                    ->getQuery()
                    ->getResult();
                if (count($statusfromresult) == 0)
                {
                    array_push($statusarray, 0);

                }
                if (count($statusfromresult) > 0)
                {
                    array_push($statusarray, $statusfromresult[0]['status']);

                }

            }

            return $statusarray;
        }
        if ($role == 'ROLE_MANAGER')
        {
            $status = 2;


            for ($ship = 0; $ship < count($shipdetialsarray); $ship++)
            {
                $statusfromresult = $em->createQueryBuilder()
                    ->select('b.status')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.status  = 2 OR b.status  = 3')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $shipdetialsarray[$ship]['id'])
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery()
                    ->getResult();
                if (count($statusfromresult) == 0)
                {
                    array_push($statusarray, 0);
                }
                if (count($statusfromresult) > 0)
                {
                    array_push($statusarray, $statusfromresult[0]['status']);

                }

            }
            return $statusarray;




        }
        if ($role == 'ROLE_KPI_INFO_PROVIDER')
        {
            $status = 1;
            $tempvarable=0;
            $elementtempvariable=0;

            for ($ship = 0; $ship < count($shipdetialsarray); $ship++)
            {
                $statusfromresult = $em->createQueryBuilder()
                    ->select('b.status')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.status = 1 OR b.status  = 2 OR b.status  = 3')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $shipdetialsarray[$ship]['id'])
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery()
                    ->getResult();
                if (count($statusfromresult) == 0)
                {
                    array_push($statusarray, 0);
                }
                if (count($statusfromresult) > 0)
                {
                    array_push($statusarray, $statusfromresult[0]['status']);

                }

            }


            return $statusarray;

        }
        else
        {
            return $statusarray;
        }
    }



    private  function findelementkpiid_ranking($shipid)
    {
        $em = $this->getDoctrine()->getManager();
        $returnarray = array();
        $sessionkpielementid=array();
        $currentselectionid=0;
        $currentselectionname='';
        $maxelementcount=0;
        $query = $em->createQueryBuilder()
            ->select('b.kpiName', 'b.id')
            ->from('InitialShippingBundle:RankingKpiDetails', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->setParameter('shipdetailsid', $shipid)
            ->add('orderBy', 'b.id  ASC ')
            ->getQuery();

        $ids = $query->getResult();
        $maxelementcount = 0;


        $sessionkpielementid = array();
        $k = 0;
        for ($i = 0; $i < count($ids); $i++) {
            $kpiid = $ids[$i]['id'];
            $kpiname = $ids[$i]['kpiName'];

            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id')
                ->from('InitialShippingBundle:RankingElementDetails', 'b')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();
            $elementids = $query->getResult();
            if (count($elementids) == 0) {
                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();

                $ids1 = $query1->getResult();
                $newkpiid = $ids1[0]['id'];
                $newkpiname = $ids1[0]['kpiName'];
                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id')
                    ->from('InitialShippingBundle:RankingElementDetails', 'b')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++) {
                    $sessionkpielementid[$newkpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];


                }
            }
            else
            {
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }

                for ($j = 0; $j < count($elementids); $j++)
                {
                    $sessionkpielementid[$kpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];

                }
            }


        }
        return array('elementids'=>$sessionkpielementid);
    }

    private function finddatawithstatus_ranking($status,$shipid,$dataofmonth = '')
    {


        if ($dataofmonth == '') {
            $stringdataofmonth = date('Y-m-d');
        }
        if ($dataofmonth != '') {
            $mydate = '01-' . $dataofmonth;
            $time = strtotime($mydate);
            $stringdataofmonth = date('Y-m-d', $time);

        }

        $new_date = new \DateTime($stringdataofmonth);
        $new_date->modify('last day of this month');
        $em = $this->getDoctrine()->getManager();

        $elementvalues=array();
        $elementweightage=array();
        $returnarray=array();

        $resularray = $em->createQueryBuilder()
            ->select('b.value')
            ->from('InitialShippingBundle:RankingMonthlyData', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->andWhere('b.monthdetail =:dataofmonth')
            ->andWhere('b.status = :status')
            ->setParameter('shipdetailsid', $shipid)
            ->setParameter('dataofmonth', $new_date)
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();
        $query = $em->createQueryBuilder()
            ->select('b.kpiName', 'b.id')
            ->from('InitialShippingBundle:RankingKpiDetails', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->setParameter('shipdetailsid', $shipid)
            ->add('orderBy', 'b.id  ASC ')
            ->getQuery();
        $ids = $query->getResult();
        $maxelementcount = 0;
        $k = 0;
        for ($i = 0; $i < count($ids); $i++)
        {
            $kpiid = $ids[$i]['id'];
            $kpiname = $ids[$i]['kpiName'];
            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id','b.weightage')
                ->from('InitialShippingBundle:RankingElementDetails', 'b')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();
            $elementids = $query->getResult();
            if (count($elementids) == 0)
            {
                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();
                $ids1 = $query1->getResult();
                $newkpiid = $ids1[0]['id'];
                $newkpiname = $ids1[0]['kpiName'];
                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id','b.weightage')
                    ->from('InitialShippingBundle:RankingElementDetails', 'b')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();

                if ($maxelementcount < count($elementids))
                {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++)
                {
                    // $sessionkpielementid[$newkpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage,$elementids[$j]['weightage']);
                }
            }
            else {
                if ($maxelementcount < count($elementids)) {
                    $maxelementcount = count($elementids);
                }
                for ($j = 0; $j < count($elementids); $j++)
                {
                    // $sessionkpielementid[$kpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];
                    array_push($elementweightage,$elementids[$j]['weightage']);
                }
            }
        }
        for ($kkk = 0; $kkk < count($resularray); $kkk++)
        {
            array_push($elementvalues, $resularray[$kkk]['value']);
        }

        return array('elementvalues'=>$elementvalues,'elementweightage'=>$elementweightage,'elementnamekpiname'=>$returnarray,'maxelementcount'=>$maxelementcount);
    }


    /**
     * Adding Kpi Values Ranking.
     *
     * @Route("/{buttonid}/addkpivalues_forranking", name="addkpivaluesname_forranking")
     * @Method("Post")
     */
    public function addkpivaluesforrankingAction(Request $request, $buttonid)
    {

        //Finding Company for Login user Starts Here//
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        }
        else
        {
            $userid=$user->getId();

            $shipid = $request->request->get('shipid');
            $returnfromcontroller = $this->findelementkpiid_ranking($shipid);
            $kpiandelementids=$returnfromcontroller['elementids'];
            $elementvalues = $request->request->get('newelemetvalues');
            $dataofmonth = $request->request->get('dataofmonth');
            $mydate = '01-' . $dataofmonth;
            $time = strtotime($mydate);
            $newformat = date('Y-m-d', $time);
            $em = $this->getDoctrine()->getManager();
            $new_date = new \DateTime($newformat);
            $new_date->modify('last day of this month');
            $k = 0;
            $returnmsg = '';
            $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
            if ($buttonid == 'updatebuttonid' || $buttonid == 'adminbuttonid' || $buttonid == 'verfiybuttonid') {

                $returnarrayids = $em->createQueryBuilder()
                    ->select('b.id')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $shipid)
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery()
                    ->getResult();
                for ($kkk = 0; $kkk < count($returnarrayids); $kkk++)
                {
                    $entityobject = $em->getRepository('InitialShippingBundle:RankingMonthlyData')->find($returnarrayids[$kkk]['id']);

                    if($buttonid == 'adminbuttonid')
                    {
                        $entityobject->setValue($elementvalues[$kkk]);
                        $entityobject->setStatus(3);
                    }
                    if($buttonid == 'verfiybuttonid')
                    {
                        $entityobject->setValue($elementvalues[$kkk]);
                        $entityobject->setStatus(2);
                    }
                    if($buttonid == 'updatebuttonid')
                    {
                        $entityobject->setValue($elementvalues[$kkk]);
                        $entityobject->setStatus(1);
                    }
                    //$entityobject->setFilename($pdffilenamearray[0].'.pdf');
                    $em->flush();

                }
                $returnmsg = ' Data Updated...';

                $lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('shipid' => $newshipid,'dataofmonth'=>$new_date));
                $newlookupstatus=$lookstatus[0];
                if($buttonid == 'adminbuttonid')
                {

                    $rankinglookuptable=array('shipid'=>$shipid,'dataofmonth'=>$mydate,'userid'=>$userid,'status'=>3,'datetime'=>date('Y-m-d H:i:s'));
                    // $lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('shipid' => $newshipid,'dataofmonth'=>$new_date));
                    $newlookupstatus->setStatus(3);
                    $newlookupstatus->setDatetime(new \DateTime());
                    $em->flush();
                    $gearman = $this->get('gearman');
                    $gearman->doBackgroundJob('InitialShippingBundleserviceReadExcelWorker~addrankinglookupdataupdate', json_encode($rankinglookuptable));
                }
                if($buttonid == 'verfiybuttonid')
                {
                    //$lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('shipid' => $newshipid,'dataofmonth'=>$new_date));
                    $newlookupstatus->setStatus(2);
                    $newlookupstatus->setDatetime(new \DateTime());
                    $em->flush();
                }
                if($buttonid == 'updatebuttonid')
                {
                    //$lookstatus = $em->getRepository('InitialShippingBundle:Ranking_LookupStatus')->findBy(array('shipid' => $newshipid,'dataofmonth'=>$new_date));
                    $newlookupstatus->setStatus(1);
                    $newlookupstatus->setDatetime(new \DateTime());
                    $em->flush();
                }


            }
            if ($buttonid == 'savebuttonid') {
                foreach ($kpiandelementids as $kpikey => $kpipvalue) {


                    $newkpiid = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id' => $kpikey));
                    foreach ($kpipvalue as $elementkey => $elementvalue)
                    {
                        $newelementid = $em->getRepository('InitialShippingBundle:RankingElementDetails')->findOneBy(array('id' => $elementvalue));

                        $readingkpivalue = new RankingMonthlyData();
                        $readingkpivalue->setKpiDetailsId($newkpiid);
                        $readingkpivalue->setElementDetailsId($newelementid);
                        $readingkpivalue->setShipDetailsId($newshipid);
                        $readingkpivalue->setMonthdetail($new_date);
                        $readingkpivalue->setValue($elementvalues[$k]);
                        $readingkpivalue->setStatus(1);
                        $em->persist($readingkpivalue);
                        $em->flush();
                        $k++;

                    }
                }
                $returnmsg = ' Data Saved...';
                /* $query = $em->createQueryBuilder()
                     ->select('a.shipName', 'a.id')
                     ->from('InitialShippingBundle:ShipDetails', 'a')
                     ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                     ->where('b.id = :userId')
                     ->setParameter('userId', $userId)
                     ->getQuery();*/
                $fullurl="http://shipreports/login";
                $mailer = $this->container->get('mailer');
                $message = \Swift_Message::newInstance()
                    ->setFrom('lawrance@commusoft.co.uk')
                    ->setTo("doss.cclawranc226@gmail.com")
                    ->setSubject($newshipid->getShipName().' Data Added By V-Ship Team')
                    ->setBody("This Web Url:".$fullurl);

                $mailer->send($message);
                $lookupstatusobject=new Ranking_LookupStatus();
                $lookupstatusobject->setShipid($newshipid);
                $lookupstatusobject->setStatus(1);
                $lookupstatusobject->setDataofmonth($new_date);
                $lookupstatusobject->setDatetime(new \DateTime());
                $lookupstatusobject->setUserid($userid);
                $em->persist($lookupstatusobject);
                $em->flush();

            }


            $shipname = $newshipid->getShipName();
            $nextshipid = 0;
            $nextshipname = '';
            $user = $this->getUser();
            $role = $user->getRoles();
            $kpielementarray = $this->findnumofshipsforrankingAction($request,'nextshipajaxcall');
            $statusforship = $this->findshipstatus_ranking($dataofmonth, $kpielementarray, $role[0]);
            $finddatawithstatus=array();


            if ($role[0] == 'ROLE_ADMIN')
            {
                $status=2;
                $index = array_search(0, $statusforship);
                $nextshipid=$kpielementarray[$index]['id'];
                $nextshipname=$kpielementarray[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus_ranking($status,$nextshipid,$dataofmonth);
            }
            if ($role[0] == 'ROLE_MANAGER')
            {
                $status=1;
                $index = array_search(0, $statusforship);
                $nextshipid=$kpielementarray[$index]['id'];
                $nextshipname=$kpielementarray[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus_ranking($status,$nextshipid,$dataofmonth);
            }
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER')
            {
                $status=0;
                $index = array_search(0, $statusforship);
                $nextshipid=$kpielementarray[$index]['id'];
                $nextshipname=$kpielementarray[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus_ranking($status,$nextshipid,$dataofmonth);

            }
            $response = new JsonResponse();
            if(count($finddatawithstatus)==4)
            {


                $response->setData(array('returnmsg' => $shipname . $returnmsg,
                    'shipname' =>$nextshipname,
                    'shipid' => $nextshipid,
                    'kpiNameArray' =>$finddatawithstatus['elementnamekpiname'],
                    'elementcount' => $finddatawithstatus['maxelementcount'],
                    'elementweightage'=>$finddatawithstatus['elementweightage'],
                    'elementvalues' => $finddatawithstatus['elementvalues']));
                return $response;
            }
            else
            {

                $response->setData(array('returnmsg' => $shipname . $returnmsg,
                    'shipname' =>$nextshipname,
                    'shipid' => $nextshipid,
                    'kpiNameArray' =>array(),
                    'elementcount' => 0,
                    'elementvalues' => array()));
                return $response;
            }
        }

    }


    /**
     * Element and Kpi for Particular Ships Ranking.
     *
     * @Route("/{shipid}/{monthdetail}/shipskpielment_forranking", name="shipskpielment_forranking")
     */
    public function shipskpielmentforrankingAction(Request $request, $shipid, $monthdetail, $mode = '')
    {
        $em = $this->getDoctrine()->getManager();
        //Finding Company for Login user Starts Here//
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {

            $em = $this->getDoctrine()->getManager();
            $mydate = '01-' . $monthdetail;
            $time = strtotime($mydate);
            $newformat = date('Y-m-d', $time);
            $new_date = new \DateTime($newformat);
            $new_date->modify('last day of this month');
            $user = $this->getUser();
            $role = $user->getRoles();
            $status=0;
            $resularray=array();
            if($role[0] == 'ROLE_ADMIN')
            {
                $query=$em->createQueryBuilder()
                    ->select('b.value')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->andWhere('b.status = 2 OR b.status  = 3')
                    ->setParameter('shipdetailsid', $shipid)
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery();
            }
            if($role[0] == 'ROLE_MANAGER')
            {
                $query=$em->createQueryBuilder()
                    ->select('b.value')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->andWhere('b.status = 1 OR b.status  = 2')
                    ->setParameter('shipdetailsid', $shipid)
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery();
            }
            if($role[0] == 'ROLE_KPI_INFO_PROVIDER')
            {
                $query=$em->createQueryBuilder()
                    ->select('b.value')
                    ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                    ->where('b.shipDetailsId = :shipdetailsid')
                    ->andWhere('b.status = 0 OR b.status  = 1')
                    ->andWhere('b.monthdetail =:dataofmonth')
                    ->setParameter('shipdetailsid', $shipid)
                    ->setParameter('dataofmonth', $new_date)
                    ->getQuery();
            }
            $resularray = $query->getResult();

            /* $resularray = $em->createQueryBuilder()
                 ->select('b.value')
                 ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                 ->where('b.shipDetailsId = :shipdetailsid')
                 ->andWhere('b.monthdetail =:dataofmonth')
                 ->setParameter('shipdetailsid', $shipid)
                 ->setParameter('dataofmonth', $new_date)
                 ->getQuery()
                 ->getResult();*/


            $query = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.shipDetailsId = :shipdetailsid')
                ->setParameter('shipdetailsid', $shipid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();

            $ids = $query->getResult();
            $maxelementcount = 0;

            $returnarray = array();
            $elementweightage=array();
            $sessionkpielementid_ranking = array();
            $k = 0;
            for ($i = 0; $i < count($ids); $i++) {
                $kpiid = $ids[$i]['id'];
                $kpiname = $ids[$i]['kpiName'];

                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id','b.weightage')
                    ->from('InitialShippingBundle:RankingElementDetails', 'b')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $kpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();
                if (count($elementids) == 0) {
                    $query1 = $em->createQueryBuilder()
                        ->select('b.kpiName', 'b.id')
                        ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                        ->where('b.kpiName = :kpiName')
                        ->setParameter('kpiName', $kpiname)
                        ->add('orderBy', 'b.id  ASC ')
                        ->groupby('b.kpiName')
                        ->getQuery();

                    $ids1 = $query1->getResult();
                    $newkpiid = $ids1[0]['id'];
                    $newkpiname = $ids1[0]['kpiName'];
                    $query = $em->createQueryBuilder()
                        ->select('b.elementName', 'b.id','b.weightage')
                        ->from('InitialShippingBundle:RankingElementDetails', 'b')
                        ->where('b.kpiDetailsId = :kpidetailsid')
                        ->setParameter('kpidetailsid', $newkpiid)
                        ->add('orderBy', 'b.id  ASC ')
                        ->getQuery();
                    $elementids = $query->getResult();
                    if ($maxelementcount < count($elementids)) {
                        $maxelementcount = count($elementids);
                    }
                    for ($j = 0; $j < count($elementids); $j++)
                    {
                        $sessionkpielementid_ranking[$newkpiid][$j] = $elementids[$j]['id'];
                        $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];
                        array_push($elementweightage,$elementids[$j]['weightage']);


                    }
                }
                else
                {
                    if ($maxelementcount < count($elementids))
                    {
                        $maxelementcount = count($elementids);
                    }

                    for ($j = 0; $j < count($elementids); $j++)
                    {
                        $sessionkpielementid_ranking[$kpiid][$j] = $elementids[$j]['id'];
                        $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];
                        array_push($elementweightage,$elementids[$j]['weightage']);

                    }
                }


            }
            $elementvalues = array();
            for ($kkk = 0; $kkk < count($resularray); $kkk++)
            {
                array_push($elementvalues, $resularray[$kkk]['value']);
            }
            if ($mode == 'listkpielement')
            {
                return array('returnarray' => $returnarray,'elementweightage'=>$elementweightage, 'elementcount' => $maxelementcount, 'elementvalues' => $elementvalues);
            }
            $response = new JsonResponse();
            $response->setData(array('kpiNameArray' => $returnarray,'elementweightage'=>$elementweightage, 'elementcount' => $maxelementcount, 'elementvalues' => $elementvalues));
            return $response;
        }
    }

    /**
     * Upload Index For Ranking.
     *
     * @Route("/uploadfile_ranking", name="uploadfile_ranking")
     */
    public function upAction(Request $request)
    {
        $excelobj = new Excel_file_details();

        $form = $this->createCreateForm($excelobj);


        return $this->render('InitialShippingBundle:DataImportRanking:excelfile.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Read the File Using Gearman For Ranking.
     *
     * @Route("/readfile_gearman_ranking", name="readfile_gearman_ranking")
     */
    public function newuploadAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $excelobj = new Excel_file_details();
            //$uploadsucess=false;
            $form = $this->createCreateForm($excelobj);


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
                    $userquery = $em->createQueryBuilder()
                        ->select('a.emailId')
                        ->from('InitialShippingBundle:CompanyDetails', 'a')
                        ->where('a.adminName = :userId')
                        ->setParameter('userId', $username)
                        ->getQuery();
                    $useremailid = $userquery->getSingleScalarResult();
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
                        $user = $this->getUser();
                        $userId = $user->getId();
                        $dataofmonthstring = $mydatevalue->format('Y-m-d');
                        $gearmandataarray = array('filename' => $fileName, 'dataofmonth' => $dataofmonthstring, 'userid' => $userId, 'filetype' => $inputFileType);
                        $gearman = $this->get('gearman');       //$datafromuser=array();
                        $gearman->doBackgroundJob('InitialShippingBundleserviceReadExcelWorker~readexcelsheet', json_encode($gearmandataarray));
                        $msg = "Your Document Has Been verfication.After Verfication Your File Data Has been Reading.";


                        return $this->render(
                            'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                            array('creator' => $cre, 'msg' => $msg)
                        );

                    }
                    if ($sheetCount > 1) {

                        $message = \Swift_Message::newInstance()
                            ->setFrom('lawrance@commusoft.co.uk')
                            ->setTo($useremailid)
                            ->setSubject("Your Document having more than One Sheets!!!!")
                            ->setBody("Your Document having more than One Sheets!!!!");
                        $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                        $mailer->send($message);
                        $loadedSheetNames = $objPHPExcel->getSheetNames();
                        $excelobj->removeUpload($input);

                        $this->addFlash(
                            'notice',
                            'Your Document having more than One Sheets.so document resend to Your Mail. Check Your Mail!!!'
                        );

                        return $this->render(
                            'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                            array('creator' => $cre, 'msg' => 'Number of Sheets: ' . $sheetCount)
                        );
                    }

                }


            }

            $cre = "Not Valid Document";
            return $this->render(
                'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                array('creator' => $cre, 'msg' => '')
            );
        }
    }

    /**
     * Read the File For Ranking.
     *
     * @Route("/readfile_ranking", name="readfile_ranking")
     */
    public function uploadAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $excelobj = new Excel_file_details();
            //$uploadsucess=false;
            $form = $this->createCreateForm($excelobj);


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
                    $userquery = $em->createQueryBuilder()
                        ->select('a.emailId', 'a.id')
                        ->from('InitialShippingBundle:CompanyDetails', 'a')
                        ->where('a.adminName = :userId')
                        ->setParameter('userId', $username)
                        ->getQuery();
                    $useremailid = $userquery->getResult();
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
                                                    ->setTo($useremailid[0]['emailId'])
                                                    ->setSubject("Your Document Has Mismatch Values!!!!")
                                                    ->setBody($msg);
                                                $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                                                $mailer->send($message);
                                                $excelobj->removeUpload($input);

                                                $this->addFlash(
                                                    'notice',
                                                    'Your File not Readed. Because, Ship Names are Mismatch !!!. Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                                                );

                                                return $this->render(
                                                    'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                                                    array('creator' => $cre, 'msg' => '')
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

                                        $this->addFlash(
                                            'notice',
                                            'Your File not Readed. Because, Ship Names are Mismatch !!!. Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                                        );

                                        return $this->render(
                                            'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                                            array('creator' => $cre, 'msg' => '')
                                        );
                                    }


                                }

                            }
                        }


                        //Validation For Kpi Details
                        if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                            $kpiquery = $em->createQueryBuilder()
                                ->select('a.cellName', 'a.kpiName', 'a.id', 'a.endDate')
                                ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                                ->leftjoin('InitialShippingBundle:ShipDetails', 'e', 'WITH', 'e.id = a.shipDetailsId')
                                ->leftjoin('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = e.companyDetailsId')
                                ->leftjoin('InitialShippingBundle:User', 'c', 'WITH', 'c.username = b.adminName')
                                ->where('c.id = :userId')
                                ->groupby('a.kpiName')
                                ->setParameter('userId', $userId)
                                ->getQuery();
                        } else {
                            $kpiquery = $em->createQueryBuilder()
                                ->select('a.cellName', 'a.kpiName', 'a.id', 'a.endDate')
                                ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                                ->leftjoin('InitialShippingBundle:ShipDetails', 'e', 'WITH', 'e.id = a.shipDetailsId')
                                ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = e.companyDetailsId')
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
                                    ->from('InitialShippingBundle:RankingElementDetails', 'b')
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

                                        $this->addFlash(
                                            'notice',
                                            'Your File not Readed!!!.Because, Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                                        );

                                        return $this->render(
                                            'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                                            array('creator' => $cre, 'msg' => $msg)
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

                                    $this->addFlash(
                                        'notice',
                                        'Your File not Readed!!!.Because, Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                                    );

                                    return $this->render(
                                        'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                                        array('creator' => $cre, 'msg' => '')
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

                                $this->addFlash(
                                    'notice',
                                    'Your File not Readed!!! Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                                );

                                return $this->render(
                                    'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                                    array('creator' => $cre, 'msg' => $msg)
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
                                        ->from('InitialShippingBundle:RankingElementDetails', 'b')
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
                                                    ->from('InitialShippingBundle:RankingElementRules', 'b')
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
                                                            ->from('InitialShippingBundle:RankingElementDetails', 'a')
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

                                                        $this->addFlash(
                                                            'notice',
                                                            'Your File not Readed!!! Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                                                        );

                                                        return $this->render(
                                                            'InitialShippingBundle:ExcelFileviews:showmessage.html.twig',
                                                            array('creator' => $cre, 'msg' => $msg)
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
                                            $newkpiid = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id' => $kpikey));
                                            $newelementid = $em->getRepository('InitialShippingBundle:RankingElementDetails')->findOneBy(array('id' => $elementkey));
                                            $readingkpivalue = new RankingMonthlyData();
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
                            return $this->redirectToRoute('showfile_ranking');

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

                            $this->addFlash(
                                'notice',
                                'Your File not Readed!!! Your Document Has Mismatch Value.so document resend to Your Mail. Check Your Mail!!!'
                            );

                            return $this->render(
                                'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                                array('creator' => $cre, 'msg' => $msg)
                            );
                        }

                    }

                    if ($sheetCount > 1) {

                        $message = \Swift_Message::newInstance()
                            ->setFrom('lawrance@commusoft.co.uk')
                            ->setTo($useremailid[0]['emailId'])
                            ->setSubject("Your Document having more than One Sheets!!!!")
                            ->setBody("Your Document having more than One Sheets!!!!");
                        $message->attach(\Swift_Attachment::fromPath($input)->setFilename($excelobj->getFilename()));
                        $mailer->send($message);
                        $loadedSheetNames = $objPHPExcel->getSheetNames();
                        $excelobj->removeUpload($input);

                        $this->addFlash(
                            'notice',
                            'Your Document having more than One Sheets.so document resend to Your Mail. Check Your Mail!!!'
                        );

                        return $this->render(
                            'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                            array('creator' => $cre, 'msg' => 'Number of Sheets: ' . $sheetCount)
                        );
                    }

                }


            }

            $cre = "Not Valid Document";
            return $this->render(
                'InitialShippingBundle:DataImportRanking:showmessage.html.twig',
                array('creator' => $cre, 'msg' => '')
            );
        }
    }

    /**
     * Show File For Ranking.
     *
     * @Route("/showfile_ranking", name="showfile_ranking")
     */
    public function showfileAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $username = $user->getUsername();


            $userdetails = $em->getRepository('InitialShippingBundle:Excel_file_details')->findBy(array('userid' => $username));

            return $this->render('InitialShippingBundle:DataImportRanking:listall.html.twig', array(
                'userdetails' => $userdetails,
            ));
        }

    }

    /**
     * Download File For Ranking.
     *
     * @Route("/downfile_ranking", name="downfile_ranking")
     */
    public function downloadexcelAction($filename, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $uploaddir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/excelfiles/' . $filename;
        $content = file_get_contents($uploaddir);

        $response = new Response();

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename);

        $response->setContent($content);
        return $response;
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

    /**
     * Ajax Call For change of monthdata of Scorecard
     *
     * @Route("/monthchangescorecard", name="monthchangescorecard")
     */
    public function monthchangedataverfication_scorecardAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null)
        {
            $dataofmonth=$request->request->get('dataofmonth');
            // $time = strtotime($mydate);
            // $newformat = date('Y-m-d', $time);
            // $new_date = new \DateTime($newformat);
            // $new_date->modify('last day of this month');
            $userId = $user->getId();
            $username = $user->getUsername();
            $role = $user->getRoles();
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


            $listallshipforcompany = $query->getResult();
            $statusforship = $this->findshipstatusmonth($dataofmonth, $listallshipforcompany, $role[0]);
            $finddatawithstatus=array();
            $shipid=0;
            $shipname='';

            if ($role[0] == 'ROLE_ADMIN')
            {
                $status=2;
                $index = array_search(0, $statusforship);
                $shipid=$listallshipforcompany[$index]['id'];
                $shipname=$listallshipforcompany[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus($status,$shipid,$dataofmonth);
            }
            if ($role[0] == 'ROLE_MANAGER')
            {
                $status=1;
                $index = array_search(0, $statusforship);
                $shipid=$listallshipforcompany[$index]['id'];
                $shipname=$listallshipforcompany[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus($status,$shipid,$dataofmonth);
            }
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER')
            {
                $status=0;
                $index = array_search(0, $statusforship);
                $shipid=$listallshipforcompany[$index]['id'];
                $shipname=$listallshipforcompany[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus($status,$shipid,$dataofmonth);

            }

            $response = new JsonResponse();
            if(count($finddatawithstatus)==4)
            {
                $response->setData(
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray'=>$finddatawithstatus['elementnamekpiname'],'elementcount'=>$finddatawithstatus['maxelementcount'],
                        'elementvalues'=>$finddatawithstatus['elementvalues'],
                        'elementweightage'=>$finddatawithstatus['elementweightage'],
                        'currentshipid'=>$shipid,'currentshipname'=>$shipname
                    ));
                return $response;
            }
            else
            {
                $response->setData(
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray'=>array(),'elementcount'=>0,
                        'elementvalues'=>array(),
                        'currentshipid'=>$shipid,'currentshipname'=>$shipname
                    ));
                return $response;
            }

        }
        else
        {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }

    /**
     * Ajax Call For change of monthdata of Ranking
     *
     * @Route("/monthchangescorecard_ranking", name="monthchangescorecard_ranking")
     */
    public function monthchangedataverfication_rankingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null)
        {
            $dataofmonth=$request->request->get('dataofmonth');
            // $time = strtotime($mydate);
            // $newformat = date('Y-m-d', $time);
            // $new_date = new \DateTime($newformat);
            // $new_date->modify('last day of this month');
            $userId = $user->getId();
            $username = $user->getUsername();
            $role = $user->getRoles();
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


            $listallshipforcompany = $query->getResult();
            $statusforship = $this->findshipstatus_ranking($dataofmonth, $listallshipforcompany, $role[0]);
            $finddatawithstatus=array();
            $shipid=0;
            $shipname='';

            if ($role[0] == 'ROLE_ADMIN')
            {
                $status=2;
                $index = array_search(0, $statusforship);
                /*if($index!=false)
                {
                 */   $shipid=$listallshipforcompany[$index]['id'];
                $shipname=$listallshipforcompany[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus_ranking($status,$shipid,$dataofmonth);
                /*}*/

            }
            if ($role[0] == 'ROLE_MANAGER')
            {
                $status=1;
                $index = array_search(0, $statusforship);
                if($index!=false)
                {

                }
                $shipid=$listallshipforcompany[$index]['id'];
                $shipname=$listallshipforcompany[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus_ranking($status,$shipid,$dataofmonth);

            }
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER')
            {
                $status=0;
                $index = array_search(0, $statusforship);
                if($index!=false)
                {

                }
                $shipid=$listallshipforcompany[$index]['id'];
                $shipname=$listallshipforcompany[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus_ranking($status,$shipid,$dataofmonth);


            }

            $response = new JsonResponse();
            if(count($finddatawithstatus)==4)
            {
                $response->setData(
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray'=>$finddatawithstatus['elementnamekpiname'],'elementcount'=>$finddatawithstatus['maxelementcount'],
                        'elementvalues'=>$finddatawithstatus['elementvalues'],
                        'elementweightage'=>$finddatawithstatus['elementweightage'],
                        'currentshipid'=>$shipid,'currentshipname'=>$shipname
                    ));
                return $response;
            }
            else
            {
                $response->setData(
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray'=>array(),'elementcount'=>0,
                        'elementvalues'=>array(),
                        'currentshipid'=>$shipid,'currentshipname'=>$shipname
                    ));
                return $response;
            }

        }
        else
        {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }

    /**
     * Ajax Call For change of previous monthdata of Ranking
     *
     * @Route("/ranking_previousmonth", name="ranking_previousmonth")
     */
    public function monthdata_previous_rankingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $dataofmonth=$request->request->get('dataofmonth');
        // $time = strtotime($mydate);
        // $newformat = date('Y-m-d', $time);
        // $new_date = new \DateTime($newformat);
        // $new_date->modify('last day of this month');
        $userId = $user->getId();
        $username = $user->getUsername();
        $role = $user->getRoles();
        if ($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $query = $em->createQueryBuilder()
                ->select('a.shipName', 'a.id')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                ->where('b.adminName = :username')
                ->setParameter('username', $username)
                ->getQuery();
        }
        else
        {
            $query = $em->createQueryBuilder()
                ->select('a.shipName', 'a.id')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                ->where('b.id = :userId')
                ->setParameter('userId', $userId)
                ->getQuery();
        }
        $listallshipforcompany = $query->getResult();
        $statusforship = $this->findshipstatus_ranking($dataofmonth, $listallshipforcompany, $role[0]);
        $counts = array_count_values($statusforship);
        if($role[0]=='ROLE_ADMIN')
        {
            if (array_key_exists(3, $counts))
            {
                $ship_status_done_count= $counts[3];
            }
            else
            {
                $ship_status_done_count=0;
            }


        }
        else if($role[0]=='ROLE_MANAGER')
        {
            if (array_key_exists(2, $counts))
            {
                $ship_status_done_count= $counts[2];
            }
            else
            {
                $ship_status_done_count=0;
            }
        }
        else if($role[0]=='ROLE_KPI_INFO_PROVIDER')
        {
            if (array_key_exists(1, $counts))
            {
                $ship_status_done_count= $counts[1];
            }
            else
            {
                $ship_status_done_count=0;
            }
        }



        if($ship_status_done_count!=count($listallshipforcompany))
        {
            $finddatawithstatus=array();
            $shipid=0;
            $shipname='';

            if ($role[0] == 'ROLE_ADMIN')
            {
                $status=2;
                $index = array_search(0, $statusforship);
                /*if($index!=false)
                {
                 */   $shipid=$listallshipforcompany[$index]['id'];
                $shipname=$listallshipforcompany[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus_ranking($status,$shipid,$dataofmonth);
                /*}*/

            }
            if ($role[0] == 'ROLE_MANAGER')
            {
                $status=1;
                $index = array_search(0, $statusforship);
                if($index!=false)
                {

                }
                $shipid=$listallshipforcompany[$index]['id'];
                $shipname=$listallshipforcompany[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus_ranking($status,$shipid,$dataofmonth);

            }
            if ($role[0] == 'ROLE_KPI_INFO_PROVIDER')
            {
                $status=0;
                $index = array_search(0, $statusforship);
                if($index!=false)
                {

                }
                $shipid=$listallshipforcompany[$index]['id'];
                $shipname=$listallshipforcompany[$index]['shipName'];
                $finddatawithstatus=$this->finddatawithstatus_ranking($status,$shipid,$dataofmonth);


            }

            $response = new JsonResponse();
            if(count($finddatawithstatus)==4)
            {
                $response->setData(
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray'=>$finddatawithstatus['elementnamekpiname'],'elementcount'=>$finddatawithstatus['maxelementcount'],
                        'elementvalues'=>$finddatawithstatus['elementvalues'],
                        'elementweightage'=>$finddatawithstatus['elementweightage'],
                        'currentshipid'=>$shipid,'currentshipname'=>$shipname,'commontext'=>false
                    ));
                return $response;
            }
            else
            {
                $response->setData(
                    array('listofships' => $listallshipforcompany,
                        'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                        'elementkpiarray'=>array(),'elementcount'=>0,
                        'elementvalues'=>array(),
                        'currentshipid'=>$shipid,'currentshipname'=>$shipname,'commontext'=>false
                    ));
                return $response;
            }
        }
        else
        {
            if($dataofmonth=='')
            {
                $monthInString=date('Y-m-d');
                $lastMonthDetail = new \DateTime($monthInString);
                $lastMonthDetail->modify('last day of this month');
            }
            if($dataofmonth!='')
            {
                $monthInString='01-'.$dataofmonth;
                $lastMonthDetail = new \DateTime($monthInString);
                $lastMonthDetail->modify('last day of this month');
            }

            $overallShipDetailArray = array();
            $Ships_Element_Value=array();
            $ElmentNameArray=array();
            $ElementWeighate_Array=array();
            $Element_status_count = 0;
            $common_RankingKpiList = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id', 'b.weightage')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.shipDetailsId = :shipid')
                ->setParameter('shipid',$listallshipforcompany[0]['id'])
                ->getQuery()
                ->getResult();
            for($shipCount=0;$shipCount<count($listallshipforcompany);$shipCount++)
            {
                $rankingKpiValueCountArray = array();
                $rankingShipName = $listallshipforcompany[$shipCount]['shipName'];
                $rankingShipId = $listallshipforcompany[$shipCount]['id'];

                $rankingKpiList = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id', 'b.weightage')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                    ->where('b.shipDetailsId = :shipid')
                    ->setParameter('shipid', $rankingShipId)
                    ->getQuery()
                    ->getResult();
                $ElementValuesfor_Ships=array();

                for ($rankingKpiCount = 0; $rankingKpiCount < count($rankingKpiList); $rankingKpiCount++)
                {

                    $kpiid = $rankingKpiList[$rankingKpiCount]['id'];
                    $kpiname = $rankingKpiList[$rankingKpiCount]['kpiName'];
                    $ElementName_KPI=array();
                    $ElementWeightage_KPI=array();
                    $query = $em->createQueryBuilder()
                        ->select('b.elementName', 'b.id','b.weightage')
                        ->from('InitialShippingBundle:RankingElementDetails', 'b')
                        ->where('b.kpiDetailsId = :kpidetailsid')
                        ->setParameter('kpidetailsid', $kpiid)
                        ->add('orderBy', 'b.id  ASC ')
                        ->getQuery();
                    $elementids = $query->getResult();
                    if (count($elementids) == 0)
                    {
                        $query1 = $em->createQueryBuilder()
                            ->select('b.kpiName', 'b.id')
                            ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                            ->where('b.kpiName = :kpiName')
                            ->setParameter('kpiName', $kpiname)
                            ->add('orderBy', 'b.id  ASC ')
                            ->groupby('b.kpiName')
                            ->getQuery();

                        $ids1 = $query1->getResult();
                        $newkpiid = $ids1[0]['id'];
                        $newkpiname = $ids1[0]['kpiName'];
                        $query = $em->createQueryBuilder()
                            ->select('b.elementName', 'b.id','b.weightage')
                            ->from('InitialShippingBundle:RankingElementDetails', 'b')
                            ->where('b.kpiDetailsId = :kpidetailsid')
                            ->setParameter('kpidetailsid', $newkpiid)
                            ->add('orderBy', 'b.id  ASC ')
                            ->getQuery();
                        $elementids = $query->getResult();
                        for ($j = 0; $j < count($elementids); $j++)
                        {
                            $ElementId = $elementids[$j]['id'];

                            $ElementValue_Result=$em->createQueryBuilder()
                                ->select('b.value')
                                ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                                ->where('b.shipDetailsId = :shipdetailsid')
                                ->andWhere('b.monthdetail =:dataofmonth')
                                ->andWhere('b.kpiDetailsId =:kpiDetailsId')
                                ->andWhere('b.elementDetailsId =:elementDetailsId')
                                ->setParameter('shipdetailsid', $rankingShipId)
                                ->setParameter('dataofmonth', $lastMonthDetail)
                                ->setParameter('kpiDetailsId', $newkpiid)
                                ->setParameter('elementDetailsId', $ElementId)
                                ->getQuery()
                                ->getResult();
                            if(count($ElementValue_Result)>0)
                            {
                                array_push($ElementValuesfor_Ships,$ElementValue_Result[0]['value']);
                            }
                            else
                            {
                                array_push($ElementValuesfor_Ships,null);
                            }



                        }
                    }
                    else
                    {

                        for ($j = 0; $j < count($elementids); $j++)
                        {
                            $ElementId = $elementids[$j]['id'];
                            array_push($ElementName_KPI,$elementids[$j]['weightage']);
                            array_push($ElementWeightage_KPI,$elementids[$j]['elementName']);
                            $ElementValue_Result=$em->createQueryBuilder()
                                ->select('b.value')
                                ->from('InitialShippingBundle:RankingMonthlyData', 'b')
                                ->where('b.shipDetailsId = :shipdetailsid')
                                ->andWhere('b.monthdetail =:dataofmonth')
                                ->andWhere('b.kpiDetailsId =:kpiDetailsId')
                                ->andWhere('b.elementDetailsId =:elementDetailsId')
                                ->setParameter('shipdetailsid', $rankingShipId)
                                ->setParameter('dataofmonth', $lastMonthDetail)
                                ->setParameter('kpiDetailsId', $kpiid)
                                ->setParameter('elementDetailsId', $ElementId)
                                ->getQuery()
                                ->getResult();
                            if(count($ElementValue_Result)>0)
                            {
                                array_push($ElementValuesfor_Ships,$ElementValue_Result[0]['value']);
                            }
                            else
                            {
                                array_push($ElementValuesfor_Ships,null);
                            }
                        }
                        $ElmentNameArray[$kpiid]=$ElementName_KPI;
                        $ElementWeighate_Array[$kpiid]=$ElementWeightage_KPI;
                    }

                }
                $Ships_Element_Value[$rankingShipId]=$ElementValuesfor_Ships;
            }


            $response = new JsonResponse();

            $response->setData(
                array('listofships' => $listallshipforcompany,
                    'shipcount' => count($listallshipforcompany),
                    'elementvalues_ship'=>$Ships_Element_Value,
                    'rankingKpiList'=>$common_RankingKpiList,
                    'elementname'=>$ElmentNameArray,
                    'elementweightage'=>$ElementWeighate_Array,
                    'commontext'=>true
                ));
            return $response;
        }
    }
    /**
     * Ajax Call For change of Prev monthdata of Scorecard
     *
     * @Route("/prev_month_change_scorecard", name="prev_month_change_scorecard")
     */
    public function prevmonthchangedataverfication_scorecardAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
            $dataofmonth = $request->request->get('dataofmonth');
            // $time = strtotime($mydate);
            // $newformat = date('Y-m-d', $time);
            // $new_date = new \DateTime($newformat);
            // $new_date->modify('last day of this month');
            $userId = $user->getId();
            $username = $user->getUsername();
            $role = $user->getRoles();
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


            $listallshipforcompany = $query->getResult();
            $statusforship = $this->findshipstatusmonth($dataofmonth, $listallshipforcompany, $role[0]);
            $counts = array_count_values($statusforship);
            if ($role[0] == 'ROLE_ADMIN') {
                if (array_key_exists(3, $counts)) {
                    $ship_status_done_count = $counts[3];
                } else {
                    $ship_status_done_count = 0;
                }


            } else if ($role[0] == 'ROLE_MANAGER') {
                if (array_key_exists(2, $counts)) {
                    $ship_status_done_count = $counts[2];
                } else {
                    $ship_status_done_count = 0;
                }
            } else if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                if (array_key_exists(1, $counts)) {
                    $ship_status_done_count = $counts[1];
                } else {
                    $ship_status_done_count = 0;
                }
            }


            if ($ship_status_done_count != count($listallshipforcompany)) {
                $finddatawithstatus = array();
                $shipid = 0;
                $shipname = '';

                if ($role[0] == 'ROLE_ADMIN') {
                    $status = 2;
                    $index = array_search(0, $statusforship);
                    $shipid = $listallshipforcompany[$index]['id'];
                    $shipname = $listallshipforcompany[$index]['shipName'];
                    $finddatawithstatus = $this->finddatawithstatus($status, $shipid, $dataofmonth);
                }
                if ($role[0] == 'ROLE_MANAGER') {
                    $status = 1;
                    $index = array_search(0, $statusforship);
                    $shipid = $listallshipforcompany[$index]['id'];
                    $shipname = $listallshipforcompany[$index]['shipName'];
                    $finddatawithstatus = $this->finddatawithstatus($status, $shipid, $dataofmonth);
                }
                if ($role[0] == 'ROLE_KPI_INFO_PROVIDER') {
                    $status = 0;
                    $index = array_search(0, $statusforship);
                    $shipid = $listallshipforcompany[$index]['id'];
                    $shipname = $listallshipforcompany[$index]['shipName'];
                    $finddatawithstatus = $this->finddatawithstatus($status, $shipid, $dataofmonth);

                }

                $response = new JsonResponse();
                if (count($finddatawithstatus) == 4) {
                    $response->setData(
                        array('listofships' => $listallshipforcompany,
                            'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                            'elementkpiarray' => $finddatawithstatus['elementnamekpiname'], 'elementcount' => $finddatawithstatus['maxelementcount'],
                            'elementvalues' => $finddatawithstatus['elementvalues'],
                            'elementweightage' => $finddatawithstatus['elementweightage'],
                            'currentshipid' => $shipid, 'currentshipname' => $shipname,'commontext'=>false
                        ));
                    return $response;
                } else {
                    $response->setData(
                        array('listofships' => $listallshipforcompany,
                            'shipcount' => count($listallshipforcompany), 'status_ship' => $statusforship,
                            'elementkpiarray' => array(), 'elementcount' => 0,
                            'elementvalues' => array(),
                            'currentshipid' => $shipid, 'currentshipname' => $shipname,'commontext'=>false
                        ));
                    return $response;
                }

            }
            else
        {
            if($dataofmonth=='')
            {
                $monthInString=date('Y-m-d');
                $lastMonthDetail = new \DateTime($monthInString);
                $lastMonthDetail->modify('last day of this month');
            }
            if($dataofmonth!='')
            {
                $monthInString='01-'.$dataofmonth;
                $lastMonthDetail = new \DateTime($monthInString);
                $lastMonthDetail->modify('last day of this month');
            }

            $overallShipDetailArray = array();
            $Ships_Element_Value=array();
            $ElmentNameArray=array();
            $ElementWeighate_Array=array();
            $Element_status_count = 0;
            $common_RankingKpiList = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id', 'b.weightage')
                ->from('InitialShippingBundle:KpiDetails', 'b')
                ->where('b.shipDetailsId = :shipid')
                ->setParameter('shipid',$listallshipforcompany[0]['id'])
                ->getQuery()
                ->getResult();
            for($shipCount=0;$shipCount<count($listallshipforcompany);$shipCount++)
            {
                $rankingKpiValueCountArray = array();
                $rankingShipName = $listallshipforcompany[$shipCount]['shipName'];
                $rankingShipId = $listallshipforcompany[$shipCount]['id'];

                $rankingKpiList = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id', 'b.weightage')
                    ->from('InitialShippingBundle:KpiDetails', 'b')
                    ->where('b.shipDetailsId = :shipid')
                    ->setParameter('shipid', $rankingShipId)
                    ->getQuery()
                    ->getResult();
                $ElementValuesfor_Ships=array();

                for ($rankingKpiCount = 0; $rankingKpiCount < count($rankingKpiList); $rankingKpiCount++)
                {

                    $kpiid = $rankingKpiList[$rankingKpiCount]['id'];
                    $kpiname = $rankingKpiList[$rankingKpiCount]['kpiName'];
                    $ElementName_KPI=array();
                    $ElementWeightage_KPI=array();
                    $query = $em->createQueryBuilder()
                        ->select('b.elementName', 'b.id','b.weightage')
                        ->from('InitialShippingBundle:ElementDetails', 'b')
                        ->where('b.kpiDetailsId = :kpidetailsid')
                        ->setParameter('kpidetailsid', $kpiid)
                        ->add('orderBy', 'b.id  ASC ')
                        ->getQuery();
                    $elementids = $query->getResult();
                    if (count($elementids) == 0)
                    {
                        $query1 = $em->createQueryBuilder()
                            ->select('b.kpiName', 'b.id')
                            ->from('InitialShippingBundle:KpiDetails', 'b')
                            ->where('b.kpiName = :kpiName')
                            ->setParameter('kpiName', $kpiname)
                            ->add('orderBy', 'b.id  ASC ')
                            ->groupby('b.kpiName')
                            ->getQuery();

                        $ids1 = $query1->getResult();
                        $newkpiid = $ids1[0]['id'];
                        $newkpiname = $ids1[0]['kpiName'];
                        $query = $em->createQueryBuilder()
                            ->select('b.elementName', 'b.id','b.weightage')
                            ->from('InitialShippingBundle:ElementDetails', 'b')
                            ->where('b.kpiDetailsId = :kpidetailsid')
                            ->setParameter('kpidetailsid', $newkpiid)
                            ->add('orderBy', 'b.id  ASC ')
                            ->getQuery();
                        $elementids = $query->getResult();
                        for ($j = 0; $j < count($elementids); $j++)
                        {
                            $ElementId = $elementids[$j]['id'];

                            $ElementValue_Result=$em->createQueryBuilder()
                                ->select('b.value')
                                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                                ->where('b.shipDetailsId = :shipdetailsid')
                                ->andWhere('b.monthdetail =:dataofmonth')
                                ->andWhere('b.kpiDetailsId =:kpiDetailsId')
                                ->andWhere('b.elementDetailsId =:elementDetailsId')
                                ->setParameter('shipdetailsid', $rankingShipId)
                                ->setParameter('dataofmonth', $lastMonthDetail)
                                ->setParameter('kpiDetailsId', $newkpiid)
                                ->setParameter('elementDetailsId', $ElementId)
                                ->getQuery()
                                ->getResult();
                            if(count($ElementValue_Result)>0)
                            {
                                array_push($ElementValuesfor_Ships,$ElementValue_Result[0]['value']);
                            }
                            else
                            {
                                array_push($ElementValuesfor_Ships,null);
                            }



                        }
                    }
                    else
                    {

                        for ($j = 0; $j < count($elementids); $j++)
                        {
                            $ElementId = $elementids[$j]['id'];
                            array_push($ElementName_KPI,$elementids[$j]['weightage']);
                            array_push($ElementWeightage_KPI,$elementids[$j]['elementName']);
                            $ElementValue_Result=$em->createQueryBuilder()
                                ->select('b.value')
                                ->from('InitialShippingBundle:ReadingKpiValues', 'b')
                                ->where('b.shipDetailsId = :shipdetailsid')
                                ->andWhere('b.monthdetail =:dataofmonth')
                                ->andWhere('b.kpiDetailsId =:kpiDetailsId')
                                ->andWhere('b.elementDetailsId =:elementDetailsId')
                                ->setParameter('shipdetailsid', $rankingShipId)
                                ->setParameter('dataofmonth', $lastMonthDetail)
                                ->setParameter('kpiDetailsId', $kpiid)
                                ->setParameter('elementDetailsId', $ElementId)
                                ->getQuery()
                                ->getResult();
                            if(count($ElementValue_Result)>0)
                            {
                                array_push($ElementValuesfor_Ships,$ElementValue_Result[0]['value']);
                            }
                            else
                            {
                                array_push($ElementValuesfor_Ships,null);
                            }
                        }
                        $ElmentNameArray[$kpiid]=$ElementName_KPI;
                        $ElementWeighate_Array[$kpiid]=$ElementWeightage_KPI;
                    }

                }
                $Ships_Element_Value[$rankingShipId]=$ElementValuesfor_Ships;
            }


            $response = new JsonResponse();

            $response->setData(
                array('listofships' => $listallshipforcompany,
                    'shipcount' => count($listallshipforcompany),
                    'elementvalues_ship'=>$Ships_Element_Value,
                    'rankingKpiList'=>$common_RankingKpiList,
                    'elementname'=>$ElmentNameArray,
                    'elementweightage'=>$ElementWeighate_Array,
                    'commontext'=>true
                ));
            return $response;
        }

    }
    /**
     * Ajax Call For change of Prev monthdata of Scorecard
     *
     * @Route("/db_backup", name="database_export")
     */
    public function dbBackupAction(Request $request)
    {
        $object=new CommonFunctions();
        return $object->export_Database("localhost","root","Star_123","shipping_development");

    }

}



