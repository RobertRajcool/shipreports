<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\Excel_file_details;
use Initial\ShippingBundle\Entity\ShipDetails;
use Initial\ShippingBundle\Entity\KpiRules;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $userId = $user->getId();
        $username = $user->getUsername();

        if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $query = $em->createQueryBuilder()
                ->select('a.shipName','a.id')
                ->from('InitialShippingBundle:ShipDetails','a')
                ->join('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = a.companyDetailsId')
                ->where('b.adminName = :username')
                ->setParameter('username',$username)
                ->getQuery();
        }
        else
        {
            $query = $em->createQueryBuilder()
                ->select('a.shipName','a.id')
                ->from('InitialShippingBundle:ShipDetails','a')
                ->leftjoin('InitialShippingBundle:User','b', 'WITH', 'b.companyid = a.companyDetailsId')
                ->where('b.id = :userId')
                ->setParameter('userId',$userId)
                ->getQuery();
        }


        $listallshipforcompany = $query->getResult();

        if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $query = $em->createQueryBuilder()
                ->select('a.id')
                ->from('InitialShippingBundle:CompanyDetails','a')
                ->join('InitialShippingBundle:User','b', 'WITH', 'b.username = a.adminName')
                ->where('b.username = :username')
                ->setParameter('username',$username)
                ->getQuery();
        }
        else
        {
            $query = $em->createQueryBuilder()
                ->select('a.companyid')
                ->from('InitialShippingBundle:User','a')
                ->where('a.id = :userId')
                ->setParameter('userId',$userId)
                ->getQuery();
        }
        $companyid=$query->getSingleScalarResult();


        $lastdate = $em->createQueryBuilder()
            ->select('a.dataOfMonth')
            ->from('InitialShippingBundle:Excel_file_details','a')
            ->where('a.company_id = :company_id')
            ->setParameter('company_id',$companyid)
            ->addOrderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();

        $lastmonthdetail=$lastdate[0]['dataOfMonth'];


        $shipid=$listallshipforcompany[0]['id'];
        $findkpilist=$em -> createQueryBuilder()
            ->select('b.kpiName','b.id','b.weightage')
            ->from('InitialShippingBundle:KpiDetails','b')
            ->where('b.shipDetailsId = :shipid')
            ->setParameter('shipid',$shipid)
            ->getQuery()
            ->getResult();
        // Finding Elements and Element Weightage Based on Kpi Id //
        $findelementidarray=$em -> createQueryBuilder()
            ->select('c.id','c.weightage')
            ->from('InitialShippingBundle:ElementDetails','c')
            ->where('c.kpiDetailsId = :kpiid')
            ->setParameter('kpiid',$findkpilist[0]['id'])
            ->getQuery()
            ->getResult();
        $mykpivaluearray = array();
        $finalkpivalue = 0;
        $categories=array();
        for($kj=0;$kj<count($listallshipforcompany);$kj++)
        {
            array_push($categories,$listallshipforcompany[$kj]['shipName']);


            //loop for sending calculating weightage value for partucular Kpi     //
            for ($jk = 0; $jk < count($findelementidarray); $jk++)
            {

                $weightage = $findelementidarray[$jk]['weightage'];
                //Finding value based on element id and dates from user//
                $dbvalueforelement = $em->createQueryBuilder()
                    ->select('a.value')
                    ->from('InitialShippingBundle:ReadingKpiValues', 'a')
                    ->where('a.shipDetailsId = :shipid')
                    ->andwhere('a.kpiDetailsId = :kpiDetailsId')
                    ->andWhere('a.elementDetailsId = :Elementid')
                    ->andWhere('a.monthdetail =:dataofmonth')
                    ->setParameter('shipid', $listallshipforcompany[$kj]['id'])
                    ->setParameter('kpiDetailsId', $findkpilist[0]['id'])
                    ->setParameter('Elementid', $findelementidarray[$jk]['id'])
                    ->setParameter('dataofmonth', $lastmonthdetail)
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
            //Push the kpivalue values from weightage value//

            $mykpivaluearray[$kj] = $finalkpivalue;

        }

        for ($l = 0; $l < count($mykpivaluearray); $l++)
        {
            $newseries[$l]['name'] = $listallshipforcompany[$l]['shipName'];
            $newseries[$l]['data'] = array($mykpivaluearray[$l]);


        }




        //loop for assign name for series Ends Here //

        // Adding data to javascript chart function starts Here.. //
        /* $time2 = strtotime();
         $monthinletter = date('F-Y', $time2);*/
        $monthinletter = $lastmonthdetail->format('F-Y');

        $formatter = new \Zend\Json\Expr('function () {
                 var unit = {
                     "Rainfall": "mm",
                     "Temperature": "degrees C"
                 }[this.series.name];
                 return this.x + ": <b>" + this.y + "</b> " + unit;
             }');
        $ob = new Highchart();

        $linksarray= array( 'Foo'=>'http://www.java2s.com','Bar'=> 'http://www.java2s.com');
        $ob->chart->renderTo('area');
        $ob->chart->type('column');
        $ob->title->text('Star Systems Reporting Tool ',array('style'=>array('color' => 'red')));
        $ob->subtitle->text($formatter);
        $ob->subtitle->style(array('color'=>'#0000f0','fontWeight'=>'bold'));
        $ob->xAxis->categories($categories);
        //$ob->xAxis->labels($formatter);
        $ob->yAxis->min(0);
        $ob->yAxis->title(array('text'=>'Values'));
        $ob->yAxis->stackLabels(array('enabled'=>true,'style'=>array( 'fontWeight'=> 'bold','color'=>  'gray')));
        $ob->legend->align( 'right');
        $ob->legend->x( -30);
        $ob->legend->verticalAlign('top');
        $ob->legend->y(25);
        $ob->legend->floating(true);
        $ob->legend->backgroundColor('white');
        $ob->legend->borderColor('#CCC');
        $ob->legend->borderWidth(1);
        $ob->legend->shadow(1);

        $ob->series(array( array( 'showInLegend'=> false,  'name' => $monthinletter, 'color' => 'rgb(124, 181, 236)',   "data" =>$mykpivaluearray)));
        $ob->plotOptions->column(array('stacking'=> 'normal','allowPointSelect'=>true,
            'dataLabels'=>array('enabled'=>true,'style'=> array('textShadow'=>'0 0 3px black'))));
        // Adding data to javascript chart function  Ends Here.. //




        return $this->render(
            'InitialShippingBundle:DashBorad:home.html.twig',
            array('allships'=>$listallshipforcompany,'chart'=>$ob,
                'currentmonth'=>$monthinletter,
                'kpiname'=>$findkpilist[0]['kpiName'])
        );
    }
    /**
     * List all kpi for ship
     *
     * @Route("/{shipid}/listallkpiforship", name="listallkpiforship")
     */
    public function listallkpiforshipAction($shipid,Request $request,$mode='')
    {
        $newshipid=$shipid;

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $userId=$user->getId();
        $username = $user->getUsername();
        $loginuseremail=$user->getEmail();
        if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $company_details_id_query = $em->createQueryBuilder()
                ->select('b.id')
                ->from('InitialShippingBundle:CompanyDetails','b')
                ->where('b.adminName = :username')
                ->setParameter('username',$username)
                ->getQuery()
                ->getResult();
            $company_details_id = $company_details_id_query[0]['id'];
        }
        else
        {
            $company_details_id_query = $em->createQueryBuilder()
                ->select('identity(a.companyid)')
                ->from('InitialShippingBundle:User','a')
                ->where('a.id = :user_id')
                ->setParameter('user_id',$userId)
                ->getQuery()
                ->getResult();
            $company_details_id = $company_details_id_query[0][1];
        }
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
            ->setParameter('company_id',$company_details_id)
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
        $datescolorarray=array();

        $kpiweightagearray=array();
        //loop for sending dates//
        for ($d = 0; $d < count($lastfivedatearray); $d++) {
            $time2 = strtotime($lastfivedatearray[$d]);
            $monthinletter = date('F-Y', $time2);
            array_push($newcategories, $monthinletter);
            $new_monthdetail_date = new \DateTime($lastfivedatearray[$d]);

            $finalkpielementvalue = 0;
            $findingcolorarray = array();

            for ($element = 0; $element < count($listallkpi); $element++)
            {
                $kpiidvalue = $listallkpi[$element]['id'];
                $kpiweightage = $listallkpi[$element]['weightage'];
                $kpiname = $listallkpi[$element]['kpiName'];
                $findelementidarray = $em->createQueryBuilder()
                    ->select('c.id', 'c.weightage')
                    ->from('InitialShippingBundle:ElementDetails', 'c')
                    ->where('c.kpiDetailsId = :kpiid')
                    ->setParameter('kpiid', $kpiidvalue)
                    ->getQuery()
                    ->getResult();

                $finalkpivalue = 0;
                if (count($findelementidarray) == 0)
                {
                    $newkpiid = $em->createQueryBuilder()
                        ->select('b.id')
                        ->from('InitialShippingBundle:KpiDetails', 'b')
                        ->where('b.kpiName = :kpiName')
                        ->setParameter('kpiName', $kpiname)
                        ->groupby('b.kpiName')
                        ->getQuery()
                        ->getResult();
                    $findelementidarray = $em->createQueryBuilder()
                        ->select('a.elementName', 'a.id', 'a.weightage')
                        ->from('InitialShippingBundle:ElementDetails', 'a')
                        ->where('a.kpiDetailsId = :kpiid')
                        ->setParameter('kpiid', $newkpiid[0]['id'])
                        ->getQuery()
                        ->getResult();
                    for ($jk = 0; $jk < count($findelementidarray); $jk++)
                    {

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
                            ->setParameter('kpiDetailsId', $newkpiid[0]['id'])
                            ->setParameter('Elementid', $findelementidarray[$jk]['id'])
                            ->setParameter('dataofmonth', $new_monthdetail_date)
                            ->getQuery()
                            ->getResult();

                        if (count($dbvalueforelement) == 0) {
                            $finddbvaluefomula = 0 * (((int)$weightage) / 100);
                            $finalkpivalue += $finddbvaluefomula;
                        }
                        else {
                            $finddbvaluefomula = ((float)($dbvalueforelement[0]['value'])) * (((int)$weightage) / 100);
                            $finalkpivalue += $finddbvaluefomula;
                        }


                    }
                    // Kpi color Finding starts Here//

                    $kpi_rules = $em->createQueryBuilder()
                        ->select('a.rules')
                        ->from('InitialShippingBundle:KpiRules', 'a')
                        ->where('a.kpiDetailsId = :kpi_id')
                        ->setParameter('kpi_id', $newkpiid[0]['id'])
                        ->getQuery()
                        ->getResult();
                    $read1 = "";

                    //Find the color based on kpi rules
                    for ($kpi_rules_count = 0; $kpi_rules_count < count($kpi_rules); $kpi_rules_count++)
                    {
                        $rule = $kpi_rules[$kpi_rules_count];
                        /*
                                            $rule_obj = json_encode($rule);*/
                        $jsfiledirectry = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_findcolornode_3.js \'' . $rule['rules'] . ' \' ' . $finalkpivalue;
                        $jsfilename = 'node ' . $jsfiledirectry;
                        $handle = popen($jsfilename, 'r');
                        $read = fread($handle, 2096);
                        $read1 = str_replace("\n", '', $read);

                        if ($read1 != "false") {
                            break;
                        }

                    }

                }
                if (count($findelementidarray) > 0)
                {
                    for ($jk = 0; $jk < count($findelementidarray); $jk++)
                    {

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

                        if (count($dbvalueforelement) == 0) {
                            $finddbvaluefomula = 0 * (((int)$weightage) / 100);
                            $finalkpivalue += $finddbvaluefomula;
                        }
                        else {
                            $finddbvaluefomula = ((float)($dbvalueforelement[0]['value'])) * (((int)$weightage) / 100);
                            $finalkpivalue += $finddbvaluefomula;
                        }


                    }
                    // Kpi color Finding starts Here//

                    $kpi_rules = $em->createQueryBuilder()
                        ->select('a.rules')
                        ->from('InitialShippingBundle:KpiRules', 'a')
                        ->where('a.kpiDetailsId = :kpi_id')
                        ->setParameter('kpi_id', $kpiidvalue)
                        ->getQuery()
                        ->getResult();
                    $read1 = "";

                    //Find the color based on kpi rules
                    for ($kpi_rules_count = 0; $kpi_rules_count < count($kpi_rules); $kpi_rules_count++)
                    {
                        $rule = $kpi_rules[$kpi_rules_count];
                        /*
                                            $rule_obj = json_encode($rule);*/
                        $jsfiledirectry = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_findcolornode_3.js \'' . $rule['rules'] . ' \' ' . $finalkpivalue;
                        $jsfilename = 'node ' . $jsfiledirectry;
                        $handle = popen($jsfilename, 'r');
                        $read = fread($handle, 2096);
                        $read1 = str_replace("\n", '', $read);

                        if ($read1 != "false") {
                            break;
                        }

                    }


                }


                array_push($findingcolorarray, $read1);
                array_push($kpiweightagearray, $kpiweightage);
                // Kpi color Finding Ends Here//
                $findkpivalue = $finalkpivalue * (((int)$kpiweightage) / 100);
                $finalkpielementvalue += $findkpivalue;
            }
            array_push($datescolorarray, $findingcolorarray);
            array_push($finalkpielementvaluearray, $finalkpielementvalue);

        }
        $shipobject = new ShipDetails();
        $shipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
        $shipname = $shipid->getShipName();

        $series = array
        (
            array("name" => "$shipname", 'color' => 'blue', "data" => $finalkpielementvaluearray),

        );


        $ob = new Highchart();
        $ob->chart->renderTo('area');
        $ob->chart->type('line');
        $ob->title->text('Star Systems Reporting Tool ', array('style' => array('color' => 'red')));
        $ob->subtitle->text($shipname);
        $ob->subtitle->style(array('color' => '#0000f0', 'fontWeight' => 'bold'));
        $ob->xAxis->categories($newcategories);
        $ob->xAxis->labels(array('style' => array('color' => '#0000F0')));
        $ob->series($series);
        $ob->plotOptions->series(array('allowPointSelect' => true, 'dataLabels' => array('enabled' => true)));

        $listofcomment = $em->createQueryBuilder()
            ->select('a.comment')
            ->from('InitialShippingBundle:SendCommand','a')
            ->join('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.emailId = a.clientemail')
            ->where('a.kpiid = :kpiid')
            ->andwhere('b.emailId = :username')
            ->setParameter('username',$loginuseremail)
            ->setParameter('kpiid',$shipid)
            ->getQuery()
            ->getResult();

        if($mode=='kpi_id')
        {
            return $datescolorarray;
        }


        return $this->render(
            'InitialShippingBundle:DashBorad:listallkpiforship.html.twig',
            array(
                'listofkpi' => $listallkpi,
                'kpicolorarray' => $datescolorarray,
                'kpiweightage' => $kpiweightagearray,
                'montharray' => $newcategories,
                'shipname' => $shipid,
                'chart' => $ob,
                'countmonth' => count($datescolorarray),
                'avgscore' => $finalkpielementvaluearray,
                'commentarray'=>$listofcomment,
                'shipid'=>$newshipid
            )
        );


    }
    /**
     * List all element for kpi
     *
     * @Route("/{kpiid}/listelementforkpi?{kpiname}", name="listelementforkpi")
     */
    public function listallelementforkpiAction($kpiid,$kpiname,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $username = $user->getUsername();
        $email= $user->getEmail();
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
        $elementweightagearray=array();
        $findelementcolorarray=array();

        // Getting kpi_color value from ship_kpi_listAction function/controller

        $kpi_color_array = $this->listallkpiforshipAction($shipid,$request,'kpi_id');

        // Finding index of the kpi from $kpi_color_array

        $find_kpi_id_index = $em->createQueryBuilder()
            ->select('a.id')
            ->from('InitialShippingBundle:KpiDetails', 'a')
            ->where('a.shipDetailsId = :ship_id')
            ->setParameter('ship_id', $shipid)
            ->getQuery()
            ->getResult();
        for ($find_kpi_id_index_count = 0;$find_kpi_id_index_count<count($find_kpi_id_index);$find_kpi_id_index_count++)
        {
            if($find_kpi_id_index[$find_kpi_id_index_count]['id']==$kpiid)
            {
                $index_value = $find_kpi_id_index_count;
            }
        }

        $kpi_rule_color_array = array();

        for($kpi_color_array_count=0;$kpi_color_array_count<count($kpi_color_array);$kpi_color_array_count++)
        {
            array_push($kpi_rule_color_array,$kpi_color_array[$kpi_color_array_count][$index_value]);
        }

        //finding kpi rule to display in the web page

        $kpi_name = $em->createQueryBuilder()
            ->select('a.kpiName')
            ->from('InitialShippingBundle:KpiDetails', 'a')
            ->where('a.id = :kpi_id')
            ->setParameter('kpi_id', $kpiid)
            ->getQuery()
            ->getResult();

        $kpi_id_array = $em->createQueryBuilder()
            ->select('a.id')
            ->from('InitialShippingBundle:KpiDetails', 'a')
            ->where('a.kpiName = :kpi_name')
            ->setParameter('kpi_name', $kpi_name[0]['kpiName'])
            ->getQuery()
            ->getResult();

        $rule_for_kpi_id = $em->createQueryBuilder()
            ->select('a.rules')
            ->from('InitialShippingBundle:KpiRules', 'a')
            ->where('a.kpiDetailsId = :kpi_id')
            ->setParameter('kpi_id', $kpi_id_array[0]['id'])
            ->getQuery()
            ->getResult();


        if(count($listelement)==0)
        {

            $newkpiid = $em->createQueryBuilder()
                ->select('b.id')
                ->from('InitialShippingBundle:KpiDetails', 'b')
                ->where('b.kpiName = :kpiName')
                ->setParameter('kpiName', $kpiname)
                ->groupby('b.kpiName')
                ->getQuery()
                ->getResult();
            $listelement = $em->createQueryBuilder()
                ->select('a.elementName','a.id','a.weightage')
                ->from('InitialShippingBundle:ElementDetails','a')
                ->where('a.kpiDetailsId = :kpiid')
                ->setParameter('kpiid',$newkpiid[0]['id'])
                ->getQuery()
                ->getResult();
            for ($d = 0; $d < count($lastfivedatearray); $d++)
            {
                $time2 = strtotime($lastfivedatearray[$d]);
                $monthinletter = date('F-Y', $time2);
                array_push($newcategories, $monthinletter);
                $new_monthdetail_date = new \DateTime($lastfivedatearray[$d]);
                $finalkpivalue = 0;
                $findingcolorarray=array();

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

                    array_push($elementweightagearray,$weightage);
                    $kpi_rules = $em->createQueryBuilder()
                        ->select('a.rules')
                        ->from('InitialShippingBundle:Rules','a')
                        ->where('a.elementDetailsId = :elementid')
                        ->andwhere('a.kpiDetailsId = :kpiid')
                        ->setParameter('elementid',$listelement[$jk]['id'])
                        ->setParameter('kpiid',$kpiid)
                        ->getQuery()
                        ->getResult();
                    $read1="";

                    //Find the color based on kpi rules



                    if (count($dbvalueforelement) == 0)
                    {
                        $finddbvaluefomula = 0 * (((int)$weightage) / 100);
                        $finalkpivalue += $finddbvaluefomula;
                    } else
                    {
                        $finddbvaluefomula = ((float)($dbvalueforelement[0]['value'])) * (((int)$weightage) / 100);
                        $finalkpivalue += $finddbvaluefomula;
                    }

                    for($kpi_rules_count=0;$kpi_rules_count<count($kpi_rules);$kpi_rules_count++)
                    {
                        $rule = $kpi_rules[$kpi_rules_count];
                        /*
                                            $rule_obj = json_encode($rule);*/
                        $jsfiledirectry = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_findcolornode_3.js \'' . $rule['rules'] . ' \' ' . $finalkpivalue;
                        $jsfilename = 'node ' . $jsfiledirectry;
                        $handle = popen($jsfilename, 'r');
                        $read = fread($handle, 2096);
                        $read1 = str_replace("\n", '', $read);

                        if ($read1 != "false")
                        {
                            break;
                        }

                    }
                    array_push($findingcolorarray,$read1);

                }

                array_push($findelementcolorarray,$findingcolorarray);


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

            $listofcomment = $em->createQueryBuilder()
                ->select('a.comment')
                ->from('InitialShippingBundle:SendCommand','a')
                ->join('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.emailId = a.clientemail')
                ->where('a.kpiid = :kpiid')
                ->andwhere('b.emailId = :username')
                ->setParameter('username',$email)
                ->setParameter('kpiid',$kpiid)
                ->getQuery()
                ->getResult();

            return $this->render(
                'InitialShippingBundle:DashBorad:elementforkpi.html.twig',
                array(
                    'listofelement'=>$listelement,
                    'kpiname'=>$kpiname,
                    'chart'=>$ob,
                    'shipname'=>$shipname,
                    'elementweightage'=>$elementweightagearray,
                    'montharray'=>$newcategories,
                    'elementcolorarray'=>$findelementcolorarray,
                    'countmonth'=>count($findelementcolorarray),
                    'avgscore'=>$elementdetailvaluearray,
                    'kpiid'=>$kpiid,
                    'commentarray'=>$listofcomment,
                    'kpi_color' => $kpi_rule_color_array,
                    'kpi_rule'=>$rule_for_kpi_id,
                )
            );

        }
        else
        {



            for ($d = 0; $d < count($lastfivedatearray); $d++)
            {
                $time2 = strtotime($lastfivedatearray[$d]);
                $monthinletter = date('F-Y', $time2);
                array_push($newcategories, $monthinletter);
                $new_monthdetail_date = new \DateTime($lastfivedatearray[$d]);
                $finalkpivalue = 0;
                $findingcolorarray=array();

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

                    array_push($elementweightagearray,$weightage);
                    $kpi_rules = $em->createQueryBuilder()
                        ->select('a.rules')
                        ->from('InitialShippingBundle:Rules','a')
                        ->where('a.elementDetailsId = :elementid')
                        ->andwhere('a.kpiDetailsId = :kpiid')
                        ->setParameter('elementid',$listelement[$jk]['id'])
                        ->setParameter('kpiid',$kpiid)
                        ->getQuery()
                        ->getResult();
                    $read1="";

                    //Find the color based on kpi rules



                    if (count($dbvalueforelement) == 0)
                    {
                        $finddbvaluefomula = 0 * (((int)$weightage) / 100);
                        $finalkpivalue += $finddbvaluefomula;
                    } else
                    {
                        $finddbvaluefomula = ((float)($dbvalueforelement[0]['value'])) * (((int)$weightage) / 100);
                        $finalkpivalue += $finddbvaluefomula;
                    }

                    for($kpi_rules_count=0;$kpi_rules_count<count($kpi_rules);$kpi_rules_count++)
                    {
                        $rule = $kpi_rules[$kpi_rules_count];
                        /*
                                            $rule_obj = json_encode($rule);*/
                        $jsfiledirectry = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_findcolornode_3.js \'' . $rule['rules'] . ' \' ' . $finalkpivalue;
                        $jsfilename = 'node ' . $jsfiledirectry;
                        $handle = popen($jsfilename, 'r');
                        $read = fread($handle, 2096);
                        $read1 = str_replace("\n", '', $read);

                        if ($read1 != "false")
                        {
                            break;
                        }

                    }
                    array_push($findingcolorarray,$read1);

                }

                array_push($findelementcolorarray,$findingcolorarray);


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
            //find the comments for particular user//
            $listofcomment = $em->createQueryBuilder()
                ->select('a.comment')
                ->from('InitialShippingBundle:SendCommand','a')
                ->join('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.emailId = a.clientemail')
                ->where('a.kpiid = :kpiid')
                ->andwhere('b.emailId = :username')
                ->setParameter('username',$email)
                ->setParameter('kpiid',$kpiid)
                ->getQuery()
                ->getResult();


            return $this->render(
                'InitialShippingBundle:DashBorad:elementforkpi.html.twig',
                array(
                    'listofelement'=>$listelement,
                    'kpiname'=>$kpiname,
                    'chart'=>$ob,
                    'shipname'=>$shipname,
                    'elementweightage'=>$elementweightagearray,
                    'montharray'=>$newcategories,
                    'elementcolorarray'=>$findelementcolorarray,
                    'countmonth'=>count($findelementcolorarray),
                    'avgscore'=>$elementdetailvaluearray,
                    'kpiid'=>$kpiid,
                    'commentarray'=>$listofcomment,
                    'kpi_color' => $kpi_rule_color_array,
                    'kpi_rule'=>$rule_for_kpi_id,
                )
            );
        }
    }
    /**
     * List all element for kpi
     *
     * @Route("/bootid", name="bootid")
     */
    public function bootAction(Request $request)
    {
        return $this->render(
            'InitialShippingBundle:DashBorad:elementforkpi.html.twig');

    }

    /**
     * Auto Complete for Mailing
     *
     * @Route("/sutocompeltegroup", name="autocompleteformailing")
     */
    public function autocompleteformailingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        //Finding Company for Login user Starts Here//
        $user = $this->getUser();
        $userId = $user->getId();
        $username = $user->getUsername();

        if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $query = $em->createQueryBuilder()
                ->select('a.id')
                ->from('InitialShippingBundle:CompanyDetails','a')
                ->join('InitialShippingBundle:User','b', 'WITH', 'b.username = a.adminName')
                ->where('b.username = :username')
                ->setParameter('username',$username)
                ->getQuery();
        }
        else
        {
            $query = $em->createQueryBuilder()
                ->select('a.companyid')
                ->from('InitialShippingBundle:User','a')
                ->where('a.id = :userId')
                ->setParameter('userId',$userId)
                ->getQuery();
        }
        $companyid=$query->getSingleScalarResult();
        $searchstring=$request->request->get('searchstring');
        $newcompanyid = $em->getRepository('InitialShippingBundle:CompanyDetails')->findOneBy(array('id'=>$companyid));
        $qb=$em->createQueryBuilder();
        $qb
            ->select('a.emailid','b.groupname')
            ->from('InitialShippingBundle:Mailing','a')
            ->join('InitialShippingBundle:MailingGroup','b', 'WITH', 'b.emailreferenceid = a.id')
            ->where('a.companyid = :companyid')
            ->andwhere('b.groupname LIKE :sreachstring')
            ->orwhere('a.emailid LIKE :sreachstring')
            ->setParameter('companyid',$newcompanyid)
            ->setParameter('sreachstring','%'.$searchstring.'%');
        $result=$qb->getQuery()->getResult();
        $response = new JsonResponse();


        if(count($result)==0)
        {
            $response->setData(array('returnresult'=>0));
        }
        if(count($result)==1)
        {
            $response->setData(array('returnresult' => $result[0]['emailid']));
        }
        if(count($result)>1)
        {
            $response->setData(array('returnresult' => $result));
        }
        return $response;

    }

}