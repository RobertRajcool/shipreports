<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\Excel_file_details;
use Initial\ShippingBundle\Entity\ShipDetails;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * DashboradController.
 *
 * @Route("/dashborad")
 */
class DashboradController extends Controller
{
    /**
     * Dashborad Home.
     *
     * @Route("/", name="dashboradhome")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $username = $user->getUsername();

        $listallshipforcompany = $em->createQueryBuilder()
            ->select('a.shipName','a.id')
            ->from('InitialShippingBundle:ShipDetails','a')
            ->join('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = a.companyDetailsId')
            ->where('b.adminName = :username')
            ->setParameter('username',$username)
            ->getQuery()
            ->getResult();


        return $this->render(
            'InitialShippingBundle:DashBorad:home.html.twig',
            array('allships'=>$listallshipforcompany)
        );
    }
    /**
     * List all kpi for ship
     *
     * @Route("/{shipid}/listallkpiforship", name="listallkpiforship")
     */
    public function listallkpiforshipAction($shipid,Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $username = $user->getUsername();
//Find Last Five Months Starts Here //
        $comanyiddetailarray = $em->createQueryBuilder()
            ->select('b.id')
            ->from('InitialShippingBundle:CompanyDetails','b')
            ->where('b.adminName = :username')
            ->setParameter('username',$username)
            ->getQuery()
            ->getResult();
        $lastdate = $em->createQueryBuilder()
            ->select('a.dataOfMonth')
            ->from('InitialShippingBundle:Excel_file_details','a')
            ->where('a.company_id = :company_id')
            ->setParameter('company_id',$comanyiddetailarray[0]['id'])
            ->addOrderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();

        $lastmonthdetail=$lastdate[0]['dataOfMonth'];
        $lastfivedatearray=array();
        $mystringvaluedate=$lastmonthdetail->format('Y-m-d');
        array_push($lastfivedatearray,$mystringvaluedate);
        for($i=0;$i<4;$i++)
        {
            $mydatevalue=new \DateTime($mystringvaluedate);

            $mydatevalue->modify("last day of previous month");
                $myvalue=$mydatevalue->format("Y-m-d");
                array_push($lastfivedatearray,$myvalue);

            $mystringvaluedate=$myvalue;

        }
//Find Last Five Months Ends Here//

        $listallkpi = $em->createQueryBuilder()
            ->select('a.kpiName','a.id','a.weightage')
            ->from('InitialShippingBundle:KpiDetails','a')
            ->where('a.shipDetailsId = :shipid')
            ->setParameter('shipid',$shipid)
            ->getQuery()
            ->getResult();
        $newcategories=array();
        $finalkpielementvaluearray=array();
        //loop for sending dates//
        for ($d = 0; $d < count($lastfivedatearray); $d++)
        {
            $time2 = strtotime($lastfivedatearray[$d]);
            $monthinletter = date('F-Y', $time2);
            array_push($newcategories,$monthinletter);
            $new_monthdetail_date = new \DateTime($lastfivedatearray[$d]);

            $finalkpielementvalue=0;

            for($element=0;$element<count($listallkpi);$element++)
            {
                $kpiidvalue=$listallkpi[$element]['id'];
                $kpiweightage=$listallkpi[$element]['weightage'];
                $findelementidarray=$em -> createQueryBuilder()
                    ->select('c.id','c.weightage')
                    ->from('InitialShippingBundle:ElementDetails','c')
                    ->where('c.kpiDetailsId = :kpiid')
                    ->setParameter('kpiid',$kpiidvalue)
                    ->getQuery()
                    ->getResult();

                $finalkpivalue = 0;

                for ($jk = 0; $jk < count($findelementidarray); $jk++) {

                    $weightage = $findelementidarray[$jk]['weightage'];
                    //Finding value based on element id and dates from user//
                    $dbvalueforelement = $em->createQueryBuilder()
                        ->select('a.value')
                        ->from('InitialShippingBundle:ReadingKpiValues', 'a')
                        ->where('a.shipDetailsId = :shipid')
                        ->andwhere('a.kpiDetailsId = :kpiDetailsId')
                        ->andWhere('a.elementDetailsId = :Elementid')
                        ->andWhere('a.monthdetail =:dataofmonth')
                        ->setParameter('shipid', $shipid)
                        ->setParameter('kpiDetailsId', $listallkpi[$element]['id'])
                        ->setParameter('Elementid', $findelementidarray[$jk]['id'])
                        ->setParameter('dataofmonth', $new_monthdetail_date)
                        ->getQuery()
                        ->getResult();

                    if (count($dbvalueforelement) == 0)
                    {
                        $finddbvaluefomula = 0 * (((int)$weightage) / 100);
                        $finalkpivalue += $finddbvaluefomula;
                    }
                    else
                    {
                        $finddbvaluefomula = ((float)($dbvalueforelement[0]['value'])) * (((int)$weightage) / 100);
                        $finalkpivalue += $finddbvaluefomula;
                    }


                }
                $findkpivalue = $finalkpivalue * (((int)$kpiweightage) / 100);
                $finalkpielementvalue += $findkpivalue;
            }
            array_push($finalkpielementvaluearray,$finalkpielementvalue);

        }
         $shipobject=new ShipDetails();
        $shipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id'=>$shipid));
        $shipname=$shipid->getShipName();
        $series = array
        (
            array("name" => "$shipname", 'color' => 'blue',   "data" => $finalkpielementvaluearray),

        );

       $ob = new Highchart();
        $ob->chart->renderTo('area');
        $ob->chart->type('line');
        $ob->title->text('Star Systems Reporting Tool ',array('style'=>array('color' => 'red')));
        $ob->subtitle->text($shipname);
        $ob->subtitle->style(array('color'=>'#0000f0','fontWeight'=>'bold'));
        $ob->xAxis->categories($newcategories);
        $ob->xAxis->labels(array('style'=>array('color'=>'#0000F0')));
        $ob->series($series);
        $ob->plotOptions->series(array('allowPointSelect'=>true,'dataLabels'=>array('enabled'=>true)));
        //$ob->plotOptions->area(array('pointStart'=>0,'marker'=>array('enabled'=>false,'symbol'=>'circle','radius'=>2,'states'=>array('hover'=>array('enabled'=>false)))));






        return $this->render(
            'InitialShippingBundle:DashBorad:listallkpiforship.html.twig',
            array('listofkpi'=>$listallkpi,'shipname'=>$shipid,'chart' => $ob)
        );
    }
    /**
     * List all element for kpi
     *
     * @Route("/{kpiid}/listelementforkpi?{kpiname}&&{TB_iframe}", name="listelementforkpi")
     */
    public function listallelementforkpiAction($kpiid,$kpiname,$TB_iframe,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $username = $user->getUsername();
//Find Last Five Months Starts Here //
        $comanyiddetailarray = $em->createQueryBuilder()
            ->select('b.id')
            ->from('InitialShippingBundle:CompanyDetails','b')
            ->where('b.adminName = :username')
            ->setParameter('username',$username)
            ->getQuery()
            ->getResult();
        $lastdate = $em->createQueryBuilder()
            ->select('a.dataOfMonth')
            ->from('InitialShippingBundle:Excel_file_details','a')
            ->where('a.company_id = :company_id')
            ->setParameter('company_id',$comanyiddetailarray[0]['id'])
            ->addOrderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();

        $lastmonthdetail=$lastdate[0]['dataOfMonth'];
        $lastfivedatearray=array();
        $mystringvaluedate=$lastmonthdetail->format('Y-m-d');
        array_push($lastfivedatearray,$mystringvaluedate);
        for($i=0;$i<4;$i++)
        {
            $mydatevalue=new \DateTime($mystringvaluedate);

            $mydatevalue->modify("last day of previous month");
            $myvalue=$mydatevalue->format("Y-m-d");
            array_push($lastfivedatearray,$myvalue);

            $mystringvaluedate=$myvalue;

        }
//Find Last Five Months Ends Here//
        $newcategories=array();

        $listelement = $em->createQueryBuilder()
            ->select('a.elementName','a.id','a.weightage')
            ->from('InitialShippingBundle:ElementDetails','a')
            ->where('a.kpiDetailsId = :kpiid')
            ->setParameter('kpiid',$kpiid)
            ->getQuery()
            ->getResult();

        $shipidarray = $em->createQueryBuilder()
            ->select('identity(b.shipDetailsId)')
            ->from('InitialShippingBundle:KpiDetails', 'b')
            ->where('b.id = :kpiid')
            ->setParameter('kpiid', $kpiid)
            ->getQuery()
            ->getResult();
        $shipid=$shipidarray[0][1];
        $elementdetailvaluearray=array();

        if(count($listelement)==0)
        {

        $newkpiid = $em->createQueryBuilder()
            ->select('b.id','b.shipDetailsId')
            ->from('InitialShippingBundle:KpiDetails', 'b')
            ->where('b.kpiName = :kpiName')
            ->setParameter('kpiName', $kpiname)
            ->groupby('b.kpiName')
            ->getQuery()
            ->getResult();
            $listelement = $em->createQueryBuilder()
                ->select('a.elementName','a.id')
                ->from('InitialShippingBundle:ElementDetails','a')
                ->where('a.kpiDetailsId = :kpiid')
                ->setParameter('kpiid',$newkpiid[0]['id'])
                ->getQuery()
                ->getResult();
            return $this->render(
                'InitialShippingBundle:DashBorad:elementforkpi.html.twig',
            array('listofelement'=>$listelement,'kpiname'=>$kpiname)
            );

        }



        for ($d = 0; $d < count($lastfivedatearray); $d++)
        {
            $time2 = strtotime($lastfivedatearray[$d]);
            $monthinletter = date('F-Y', $time2);
            array_push($newcategories, $monthinletter);
            $new_monthdetail_date = new \DateTime($lastfivedatearray[$d]);
            $finalkpivalue = 0;

            for ($jk = 0; $jk < count($listelement); $jk++)
            {

                $weightage = $listelement[$jk]['weightage'];
                //Finding value based on element id and dates from user//
                $dbvalueforelement = $em->createQueryBuilder()
                    ->select('a.value')
                    ->from('InitialShippingBundle:ReadingKpiValues', 'a')
                    ->where('a.shipDetailsId = :shipid')
                    ->andwhere('a.kpiDetailsId = :kpiDetailsId')
                    ->andWhere('a.elementDetailsId = :Elementid')
                    ->andWhere('a.monthdetail =:dataofmonth')
                    ->setParameter('shipid', $shipid)
                    ->setParameter('kpiDetailsId', $kpiid)
                    ->setParameter('Elementid', $listelement[$jk]['id'])
                    ->setParameter('dataofmonth', $new_monthdetail_date)
                    ->getQuery()
                    ->getResult();

                if (count($dbvalueforelement) == 0) {
                    $finddbvaluefomula = 0 * (((int)$weightage) / 100);
                    $finalkpivalue += $finddbvaluefomula;
                } else {
                    $finddbvaluefomula = ((float)($dbvalueforelement[0]['value'])) * (((int)$weightage) / 100);
                    $finalkpivalue += $finddbvaluefomula;
                }
            }
            array_push($elementdetailvaluearray,$finalkpivalue);
        }

        $shipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id'=>$shipid));
        $shipname=$shipid->getShipName();
        $series = array
        (
            array("name" => "$kpiname", 'color' => 'blue',   "data" => $elementdetailvaluearray),

        );

        $ob = new Highchart();
        $ob->chart->renderTo('area');
        $ob->chart->type('line');
        $ob->title->text('Star Systems Reporting Tool ',array('style'=>array('color' => 'red')));
        $ob->subtitle->text($shipname);
        $ob->subtitle->style(array('color'=>'#0000f0','fontWeight'=>'bold'));
        $ob->xAxis->categories($newcategories);
        $ob->xAxis->labels(array('style'=>array('color'=>'#0000F0')));
        $ob->series($series);
        $ob->plotOptions->series(array('allowPointSelect'=>true,'dataLabels'=>array('enabled'=>true)));
        //$ob->plotOptions->area(array('pointStart'=>0,'marker'=>array('enabled'=>false,'symbol'=>'circle','radius'=>2,'states'=>array('hover'=>array('enabled'=>false)))));



        return $this->render(
            'InitialShippingBundle:DashBorad:elementforkpi.html.twig',
            array('listofelement'=>$listelement,'kpiname'=>$kpiname,'chart'=>$ob,'shipname'=>$shipname)
        );
    }
}
