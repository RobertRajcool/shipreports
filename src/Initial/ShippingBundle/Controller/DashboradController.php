<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\Excel_file_details;
use Initial\ShippingBundle\Entity\ShipDetails;
use Initial\ShippingBundle\Entity\SendCommandRanking;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * DashboradController.
 *
 * @Route("/dashboard")
 */
class DashboradController extends Controller
{
    /**
     * Dashborad Home.
     *
     * @Route("/", name="dashboardhome")
     */
    public function indexAction(Request $request,$mode='',$dataofmonth='')
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if($user!=null)
        {
        $userId = $user->getId();
        $username = $user->getUsername();
        //Finding Number of Ships For particular company starts Here//

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
        //Finding Number of Ships For particular company Ends Here//

        //Finding Lastmonthdetail date Starts Here//

            if($dataofmonth=='')
            {
                $monthinstring=date('Y-m-d');
                $lastmonthdetail = new \DateTime($monthinstring);
            $lastmonthdetail->modify('last day of this month');
            }
            if($dataofmonth!='')
            {
                $monthinstring='01-'.$dataofmonth;
                $lastmonthdetail = new \DateTime($monthinstring);
                $lastmonthdetail->modify('last day of this month');
            }

        //Finding Lastmonthdetail date Ends Here//
        //Finding details for series and drildown starts Here//

        $mykpivaluearray = array();
       /* $drilldownarray=array();*/
        for($kj=0;$kj<count($listallshipforcompany);$kj++)
        {

            $findkpilist=$em -> createQueryBuilder()
                ->select('b.kpiName','b.id','b.weightage')
                ->from('InitialShippingBundle:RankingKpiDetails','b')
                ->where('b.shipDetailsId = :shipid')
                ->setParameter('shipid',$listallshipforcompany[$kj]['id'])
                ->getQuery()
                ->getResult();
            $drildowndataarray=array();
            $finalkpielementvalue = 0;
           /* $alterdrildownarray = array();
            $elementleveldrildown=array();
            $elementleveldrildownkpi=array()*/;


            for ($element = 0; $element < count($findkpilist); $element++)
            {

                $kpiidvalue = $findkpilist[$element]['id'];
                $kpiweightage = $findkpilist[$element]['weightage'];
                $kpiname = $findkpilist[$element]['kpiName'];
                $findelementidarray = $em->createQueryBuilder()
                    ->select('c.id','c.elementName', 'c.weightage')
                    ->from('InitialShippingBundle:RankingElementDetails', 'c')
                    ->where('c.kpiDetailsId = :kpiid')
                    ->setParameter('kpiid', $kpiidvalue)
                    ->getQuery()
                    ->getResult();


                $finalkpivalue = 0;
                if (count($findelementidarray) == 0)
                {
                    $newkpiid = $em->createQueryBuilder()
                        ->select('b.id')
                        ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                        ->where('b.kpiName = :kpiName')
                        ->setParameter('kpiName', $kpiname)
                        ->groupby('b.kpiName')
                        ->getQuery()
                        ->getResult();
                    $findelementidarray = $em->createQueryBuilder()
                        ->select('a.elementName', 'a.id', 'a.weightage')
                        ->from('InitialShippingBundle:RankingElementDetails', 'a')
                        ->where('a.kpiDetailsId = :kpiid')
                        ->setParameter('kpiid', $newkpiid[0]['id'])
                        ->getQuery()
                        ->getResult();

                    for ($jk = 0; $jk < count($findelementidarray); $jk++)
                    {

                        $weightage = $findelementidarray[$jk]['weightage'];
                        $elementname=$findelementidarray[$jk]['elementName'];
                        //Finding value based on element id and dates from user//
                        $dbvalueforelement = $em->createQueryBuilder()
                            ->select('a.value')
                            ->from('InitialShippingBundle:RankingMonthlyData', 'a')
                            ->where('a.shipDetailsId = :shipid')
                            ->andwhere('a.kpiDetailsId = :kpiDetailsId')
                            ->andWhere('a.elementDetailsId = :Elementid')
                            ->andWhere('a.monthdetail =:dataofmonth')
                            ->setParameter('shipid', $listallshipforcompany[$kj]['id'])
                            ->setParameter('kpiDetailsId', $newkpiid[0]['id'])
                            ->setParameter('Elementid', $findelementidarray[$jk]['id'])
                            ->setParameter('dataofmonth', $lastmonthdetail)
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
                       // $elementleveldrildown[$jk]['name']=$elementname;//assign drill down name for element
                       // $elementleveldrildown[$jk]['y']=$finalkpivalue;//assign drill down value for element
                       // $elementleveldrildown[$jk]['drilldown']=null;//assign drill down for element


                    }
                   // $elementleveldrildownkpi[$kpiname]=$elementleveldrildown;//assign values to elementleveldrildownkpi array

                }
                if (count($findelementidarray) > 0)
                {

                    for ($kk = 0; $kk < count($findelementidarray); $kk++)
                    {
                        $elementname=$findelementidarray[$kk]['elementName'];

                        $weightage = $findelementidarray[$kk]['weightage'];
                        //Finding value based on element id and dates from user//
                        $dbvalueforelement = $em->createQueryBuilder()
                            ->select('a.value')
                            ->from('InitialShippingBundle:RankingMonthlyData', 'a')
                            ->where('a.shipDetailsId = :shipid')
                            ->andwhere('a.kpiDetailsId = :kpiDetailsId')
                            ->andWhere('a.elementDetailsId = :Elementid')
                            ->andWhere('a.monthdetail =:dataofmonth')
                            ->setParameter('shipid', $listallshipforcompany[$kj]['id'])
                            ->setParameter('kpiDetailsId', $kpiidvalue)
                            ->setParameter('Elementid', $findelementidarray[$kk]['id'])
                            ->setParameter('dataofmonth', $lastmonthdetail)
                            ->getQuery()
                            ->getResult();

                        if (count($dbvalueforelement) == 0) {
                            $finddbvaluefomula = 0 * (((int)$weightage) / 100);
                            $finalkpivalue += $finddbvaluefomula;
                        }
                        else
                        {
                            $finddbvaluefomula = ((float)($dbvalueforelement[0]['value'])) * (((int)$weightage) / 100);
                            $finalkpivalue += $finddbvaluefomula;
                        }


                       // $elementleveldrildown[$kk]['name']=$elementname;//assign drill down name for element
                       // $elementleveldrildown[$kk]['y']=$finalkpivalue;//assign drill down value for element
                       // $elementleveldrildown[$kk]['drilldown']=null;//assign drill down for element


                    }


                   // $elementleveldrildownkpi[$kpiname]=$elementleveldrildown;//assign values to elementleveldrildownkpi array
                }


                $findkpivalue = $finalkpivalue * (((int)$kpiweightage) / 100);
                $finalkpielementvalue += $findkpivalue;




               // $alterdrildownarray[$element]['name']=$kpiname;//assign drill down name for kpi
               // $alterdrildownarray[$element]['y']=$finalkpielementvalue;//assign drill down value for kpi
               // $alterdrildownarray[$element]['drilldown']=$kpiname;//assign drill down  for kpi

                array_push($drildowndataarray,$finalkpielementvalue);//assign values to shipdetails  array value



            }

           // $drilldownarray[$kj]['name']=$listallshipforcompany[$kj]['shipName'];//assign drill down name for ship
           // $drilldownarray[$kj]['id']=$listallshipforcompany[$kj]['shipName'];//assign drill down id for ship
          //  $mykpivaluearray[$kj]['drilldown']=$listallshipforcompany[$kj]['shipName'];//assign shipdrilldown from kpivalues
          //  $drilldownarray[$kj]['data']=$alterdrildownarray;//assign drilldown array values  for shipdetails drill down array
            $mykpivaluearray[$kj]['name']=$listallshipforcompany[$kj]['shipName'];//assign shipdrilldown(name) from kpivalues
            $mykpivaluearray[$kj]['y'] = array_sum($drildowndataarray);//assign shipdrilldown(values) from kpivalues
            $mykpivaluearray[$kj]['url'] = '/dashboard/'.$listallshipforcompany[$kj]['id'].'/listallkpiforship_ranking';//assign shipdrilldown(values) from kpivalues


        }
        // Assign values of element drilldown to graph drill down array starts Here
        //$temp=count($drilldownarray);

       /* foreach($elementleveldrildownkpi as $kpikey => $kpipvalue)
        {
            $drilldownarray[$temp]['name']=$kpikey;
            $drilldownarray[$temp]['id']=$kpikey;

            $drilldownarray[$temp]['data']=$kpipvalue;


            $temp++;

        }*/
        // Assign values of element drilldown to graph drill down array Ends Here

        //Finding details for series and drildown Ends Here//
            if($mode=='getnextmonthchart')
            {
                return array("data" =>$mykpivaluearray);
            }

        $monthinletter = $lastmonthdetail->format('M-Y');
        // Adding data to javascript chart function starts Here.. //
        $ob = new Highchart();
        $ob->chart->renderTo('area');
        $ob->chart->type('column');
        $ob->chart->hieght(250);
        $ob->title->text('Star Systems Reporting Tool ',array('style'=>array('color' => 'red')));
        $ob->xAxis->type('category');
        $ob->yAxis->title(array('text'=>'Values'));
        $ob->legend->enabled(false);
        $ob->plotOptions->series(array('borderWidth'=>0,'dataLabels'=>array('enabled'=>false),
            'point'=>array('events'=>array('click'=>new \Zend\Json\Expr('function () { location.href = this.options.url; }')))));

        $ob->series(array( array( 'showInLegend'=> false,'colorByPoint'=> true,  'name' => $monthinletter, 'color' => 'rgb(124, 181, 236)',   "data" =>$mykpivaluearray)));

       /* $ob->drilldown->series($drilldownarray);*/
        $ob->exporting->enabled(false);

        // Adding data to javascript chart function  Ends Here.. //




        // Scorecard or Traffic light  Color coding starts here

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

        //Find Last three Months Starts Here //
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
        for($i=0;$i<2;$i++)
        {
            $mydatevalue=new \DateTime($mystringvaluedate);

            $mydatevalue->modify("last day of previous month");
            $myvalue=$mydatevalue->format("Y-m-d");
            array_push($lastfivedatearray,$myvalue);

            $mystringvaluedate=$myvalue;

        }
        //Find Last three Months Ends Here//

        // Finding kpiName to display

        $listallkpi = $em->createQueryBuilder()
            ->select('a.kpiName','a.id','a.weightage')
            ->from('InitialShippingBundle:KpiDetails','a')
            ->groupby('a.kpiName')
            ->getQuery()
            ->getResult();

        $newcategories=array();
        $finalkpielementvaluearray=array();
        $datescolorarray=array();
        $kpiweightagearray=array();


        //loop for sending dates//
        for ($d = 0; $d < count($lastfivedatearray); $d++) {
            $time2 = strtotime($lastfivedatearray[$d]);
            $monthinletter = date('F', $time2);
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
                            //->where('a.shipDetailsId = :shipid')
                            ->andwhere('a.kpiDetailsId = :kpiDetailsId')
                            ->andWhere('a.elementDetailsId = :Elementid')
                            ->andWhere('a.monthdetail =:dataofmonth')
                            //->setParameter('shipid', $shipid)
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
                            //->where('a.shipDetailsId = :shipid')
                            ->andwhere('a.kpiDetailsId = :kpiDetailsId')
                            ->andWhere('a.elementDetailsId = :Elementid')
                            ->andWhere('a.monthdetail =:dataofmonth')
                            //->setParameter('shipid', $shipid)
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


        $datescolorarray1 = array_reverse($datescolorarray);
        $newcategories1 = array_reverse($newcategories);
        $finalkpielementvaluearray1 = array_reverse($finalkpielementvaluearray);



        return $this->render(
            'InitialShippingBundle:DashBorad:home.html.twig',
            array('allships'=>$listallshipforcompany,
                'chart'=>$ob,
                'currentmonth'=>$monthinletter,
                'ship_count'=>count($listallshipforcompany),
                'kpi_count'=>count($findkpilist),
                'kpi_list' => $listallkpi,
                'month_name' => $newcategories1,
                'kpicolorarray' => $datescolorarray1,
                'avgscore' => $finalkpielementvaluearray1

            )
        );
        }
        else
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }
    /**
     * Ajax Call For change of monthdata of Rankinng Chart
     *
     * @Route("/{dataofmonth}/monthchangeofrankingkpi", name="monthchangeofrankingkpi")
     */
    public function monthchangeofrankingkpiAction(Request $request,$dataofmonth)
    {
        $chartobject = $this->indexAction( $request, 'getnextmonthchart',$dataofmonth);
        $response = new JsonResponse();

        $response->setData(array('changechartdata' => $chartobject['data']));
        return $response;

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
        if($user==null)
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
        else
        {
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
        for($i=0;$i<2;$i++)
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
            $monthinletter = date('M-Y', $time2);
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
        $ob->exporting->enabled(false);


        $listofcomment = $em->createQueryBuilder()
            ->select('a.comment')
            ->from('InitialShippingBundle:SendCommand','a')
            ->join('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.emailId = a.clientemail')
            ->where('a.shipid = :shipid')
            ->andwhere('b.emailId = :username')
            ->setParameter('username',$loginuseremail)
            ->setParameter('shipid',$shipid)
            ->getQuery()
            ->getResult();

        if($mode=='kpi_id')
        {
            return $datescolorarray;
        }
        if($mode=='pdftemplate_shiplevel')
        {
            return array(
                'kpicolorarray'=>$datescolorarray,
                'listofkpi'=>$listallkpi,
                'kpiweightage'=>$kpiweightagearray,
                'montharray'=>$newcategories,
                'avgscore'=>$finalkpielementvaluearray,
                'commentarray'=>$listofcomment,
            );
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

    }

    /**
     * List all element for kpi
     *
     * @Route("/{kpiid}/listelementforkpi", name="listelementforkpi")
     */
    public function listallelementforkpiAction($kpiid,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if($user==null)
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
        else {
            $username = $user->getUsername();
            $email = $user->getEmail();
            $firstnewkpiid = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $kpiid));
            $kpiname=$firstnewkpiid->getKpiName();
//Find Last Five Months Starts Here //
            $comanyiddetailarray = $em->createQueryBuilder()
                ->select('b.id')
                ->from('InitialShippingBundle:CompanyDetails', 'b')
                ->where('b.adminName = :username')
                ->setParameter('username', $username)
                ->getQuery()
                ->getResult();
            $lastdate = $em->createQueryBuilder()
                ->select('a.dataOfMonth')
                ->from('InitialShippingBundle:Excel_file_details', 'a')
                ->where('a.company_id = :company_id')
                ->setParameter('company_id', $comanyiddetailarray[0]['id'])
                ->addOrderBy('a.id', 'DESC')
                ->getQuery()
                ->getResult();

            $lastmonthdetail = $lastdate[0]['dataOfMonth'];
            $lastfivedatearray = array();
            $mystringvaluedate = $lastmonthdetail->format('Y-m-d');
            array_push($lastfivedatearray, $mystringvaluedate);
            for ($i = 0; $i < 2; $i++)
            {
                $mydatevalue = new \DateTime($mystringvaluedate);

                $mydatevalue->modify("last day of previous month");
                $myvalue = $mydatevalue->format("Y-m-d");
                array_push($lastfivedatearray, $myvalue);

                $mystringvaluedate = $myvalue;

            }
//Find Last Five Months Ends Here//
            $newcategories = array();

            $listelement = $em->createQueryBuilder()
                ->select('a.elementName', 'a.id', 'a.weightage')
                ->from('InitialShippingBundle:ElementDetails', 'a')
                ->where('a.kpiDetailsId = :kpiid')
                ->setParameter('kpiid', $kpiid)
                ->getQuery()
                ->getResult();

            $shipidarray = $em->createQueryBuilder()
                ->select('identity(b.shipDetailsId)')
                ->from('InitialShippingBundle:KpiDetails', 'b')
                ->where('b.id = :kpiid')
                ->setParameter('kpiid', $kpiid)
                ->getQuery()
                ->getResult();
            $shipid = $shipidarray[0][1];
            $elementdetailvaluearray = array();
            $elementweightagearray = array();
            $findelementcolorarray = array();

            // Getting kpi_color value from ship_kpi_listAction function/controller

            $kpi_color_array = $this->listallkpiforshipAction($shipid, $request, 'kpi_id');

            // Finding index of the kpi from $kpi_color_array

            $find_kpi_id_index = $em->createQueryBuilder()
                ->select('a.id')
                ->from('InitialShippingBundle:KpiDetails', 'a')
                ->where('a.shipDetailsId = :ship_id')
                ->setParameter('ship_id', $shipid)
                ->getQuery()
                ->getResult();
            for ($find_kpi_id_index_count = 0; $find_kpi_id_index_count < count($find_kpi_id_index); $find_kpi_id_index_count++) {
                if ($find_kpi_id_index[$find_kpi_id_index_count]['id'] == $kpiid) {
                    $index_value = $find_kpi_id_index_count;
                }
            }

            $kpi_rule_color_array = array();

            for ($kpi_color_array_count = 0; $kpi_color_array_count < count($kpi_color_array); $kpi_color_array_count++) {
                array_push($kpi_rule_color_array, $kpi_color_array[$kpi_color_array_count][$index_value]);
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
            for($elementCount=0;$elementCount<count($listelement);$elementCount++)
            {
                $element_rule1 = $em->createQueryBuilder()
                    ->select('a.rules','identity(a.elementDetailsId)')
                    ->from('InitialShippingBundle:Rules', 'a')
                    ->where('a.elementDetailsId = :element_id')
                    ->setParameter('element_id', $listelement[$elementCount]['id'])
                    ->getQuery()
                    ->getResult();
                $element_rule[$elementCount]=$element_rule1;
            }


            if (count($listelement) == 0) {

                $newkpiid = $em->createQueryBuilder()
                    ->select('b.id')
                    ->from('InitialShippingBundle:KpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->groupby('b.kpiName')
                    ->getQuery()
                    ->getResult();
                $listelement = $em->createQueryBuilder()
                    ->select('a.elementName', 'a.id', 'a.weightage')
                    ->from('InitialShippingBundle:ElementDetails', 'a')
                    ->where('a.kpiDetailsId = :kpiid')
                    ->setParameter('kpiid', $newkpiid[0]['id'])
                    ->getQuery()
                    ->getResult();
                for ($d = 0; $d < count($lastfivedatearray); $d++) {
                    $time2 = strtotime($lastfivedatearray[$d]);
                    $monthinletter = date('M', $time2);
                    array_push($newcategories, $monthinletter);
                    $new_monthdetail_date = new \DateTime($lastfivedatearray[$d]);
                    $finalkpivalue = 0;
                    $findingcolorarray = array();

                    for ($jk = 0; $jk < count($listelement); $jk++) {

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
                            ->setParameter('kpiDetailsId', $newkpiid[0]['id'])
                            ->setParameter('Elementid', $listelement[$jk]['id'])
                            ->setParameter('dataofmonth', $new_monthdetail_date)
                            ->getQuery()
                            ->getResult();

                        array_push($elementweightagearray, $weightage);
                        $kpi_rules = $em->createQueryBuilder()
                            ->select('a.rules')
                            ->from('InitialShippingBundle:Rules', 'a')
                            ->where('a.elementDetailsId = :elementid')
                            ->andwhere('a.kpiDetailsId = :kpiid')
                            ->setParameter('elementid', $listelement[$jk]['id'])
                            ->setParameter('kpiid', $kpiid)
                            ->getQuery()
                            ->getResult();
                        $read1 = "";

                        //Find the color based on kpi rules


                        if (count($dbvalueforelement) == 0) {
                            $finddbvaluefomula = 0 * (((int)$weightage) / 100);
                            $finalkpivalue += $finddbvaluefomula;
                        } else {
                            $finddbvaluefomula = ((float)($dbvalueforelement[0]['value'])) * (((int)$weightage) / 100);
                            $finalkpivalue += $finddbvaluefomula;
                        }

                        for ($kpi_rules_count = 0; $kpi_rules_count < count($kpi_rules); $kpi_rules_count++) {
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
                        array_push($findingcolorarray, $read1);

                    }

                    array_push($findelementcolorarray, $findingcolorarray);


                    array_push($elementdetailvaluearray, $finalkpivalue);
                }

                $shipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
                $shipname = $shipid->getShipName();
                $series = array
                (
                    array("name" => "$kpiname",'showInLegend'=> false, 'color' => 'blue', "data" => $elementdetailvaluearray),

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
                $ob->exporting->enabled(false);

                //$ob->plotOptions->area(array('pointStart'=>0,'marker'=>array('enabled'=>false,'symbol'=>'circle','radius'=>2,'states'=>array('hover'=>array('enabled'=>false)))));

                $listofcomment = $em->createQueryBuilder()
                    ->select('a.comment')
                    ->from('InitialShippingBundle:SendCommand', 'a')
                    ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.emailId = a.clientemail')
                    ->where('a.kpiid = :kpiid')
                    ->andwhere('b.emailId = :username')
                    ->setParameter('username', $email)
                    ->setParameter('kpiid', $kpiid)
                    ->getQuery()
                    ->getResult();

                $newcategories1 = array_reverse($newcategories);
                $kpi_rule_color_array_new = array();
                array_push($kpi_rule_color_array_new,$kpi_rule_color_array);

                return $this->render(
                    'InitialShippingBundle:DashBorad:elementforkpi.html.twig',
                    array(
                        'listofelement' => $listelement,
                        'kpiname' => $kpiname,
                        'chart' => $ob,
                        'shipname' => $shipname,
                        'elementweightage' => $elementweightagearray,
                        'montharray' => $newcategories1,
                        'elementcolorarray' => $findelementcolorarray,
                        'countmonth' => count($findelementcolorarray),
                        'avgscore' => $elementdetailvaluearray,
                        'kpiid' => $kpiid,
                        'commentarray' => $listofcomment,
                        'kpi_color' => $kpi_rule_color_array_new,
                        'kpi_rule' => $rule_for_kpi_id,
                        'elementRule' => $element_rule
                    )
                );

            } else {


                for ($d = 0; $d < count($lastfivedatearray); $d++) {
                    $time2 = strtotime($lastfivedatearray[$d]);
                    $monthinletter = date('M', $time2);
                    array_push($newcategories, $monthinletter);
                    $new_monthdetail_date = new \DateTime($lastfivedatearray[$d]);
                    $finalkpivalue = 0;
                    $findingcolorarray = array();

                    for ($jk = 0; $jk < count($listelement); $jk++) {

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

                        array_push($elementweightagearray, $weightage);
                        $kpi_rules = $em->createQueryBuilder()
                            ->select('a.rules')
                            ->from('InitialShippingBundle:Rules', 'a')
                            ->where('a.elementDetailsId = :elementid')
                            ->andwhere('a.kpiDetailsId = :kpiid')
                            ->setParameter('elementid', $listelement[$jk]['id'])
                            ->setParameter('kpiid', $kpiid)
                            ->getQuery()
                            ->getResult();
                        $read1 = "";

                        //Find the color based on kpi rules


                        if (count($dbvalueforelement) == 0) {
                            $finddbvaluefomula = 0 * (((int)$weightage) / 100);
                            $finalkpivalue += $finddbvaluefomula;
                        } else {
                            $finddbvaluefomula = ((float)($dbvalueforelement[0]['value'])) * (((int)$weightage) / 100);
                            $finalkpivalue += $finddbvaluefomula;
                        }

                        for ($kpi_rules_count = 0; $kpi_rules_count < count($kpi_rules); $kpi_rules_count++) {
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
                        array_push($findingcolorarray, $read1);

                    }

                    array_push($findelementcolorarray, $findingcolorarray);


                    array_push($elementdetailvaluearray, $finalkpivalue);
                }

                $shipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
                $shipname = $shipid->getShipName();
                $series = array
                (
                    array("name" => "$kpiname",'showInLegend'=> false, 'color' => 'blue', "data" => $elementdetailvaluearray),

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
                $ob->exporting->enabled(false);
                //$ob->plotOptions->area(array('pointStart'=>0,'marker'=>array('enabled'=>false,'symbol'=>'circle','radius'=>2,'states'=>array('hover'=>array('enabled'=>false)))));
                //find the comments for particular user//
                $listofcomment = $em->createQueryBuilder()
                    ->select('a.comment')
                    ->from('InitialShippingBundle:SendCommand', 'a')
                    ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.emailId = a.clientemail')
                    ->where('a.kpiid = :kpiid')
                    ->andwhere('b.emailId = :username')
                    ->setParameter('username', $email)
                    ->setParameter('kpiid', $kpiid)
                    ->getQuery()
                    ->getResult();

                $newcategories1 = array_reverse($newcategories);
                $kpi_rule_color_array_new = array();
                array_push($kpi_rule_color_array_new,$kpi_rule_color_array);

                return $this->render(
                    'InitialShippingBundle:DashBorad:elementforkpi.html.twig',
                    array(
                        'listofelement' => $listelement,
                        'kpiname' => $kpiname,
                        'chart' => $ob,
                        'shipname' => $shipname,
                        'elementweightage' => $elementweightagearray,
                        'montharray' => $newcategories1,
                        'elementcolorarray' => $findelementcolorarray,
                        'countmonth' => count($findelementcolorarray),
                        'avgscore' => $elementdetailvaluearray,
                        'kpiid' => $kpiid,
                        'commentarray' => $listofcomment,
                        'kpi_color' => $kpi_rule_color_array_new,
                        'kpi_rule' => $rule_for_kpi_id,
                        'elementRule' => $element_rule
                    )
                );
            }
        }
    }

    /**
     * Auto Complete for Mailing
     *
     * @Route("/autocompeltegroup", name="autocompleteformailing")
     */
    public function autocompleteformailingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        //Finding Company for Login user Starts Here//
        $user = $this->getUser();
        if($user==null)
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
        else
        {
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
                ->select('a.groupname','b.useremailid')
                ->from('InitialShippingBundle:EmailGroup','a')
                ->join('InitialShippingBundle:EmailUsers','b', 'WITH', 'b.groupid = a.id')
                ->where('a.companyid = :companyid')
                ->andwhere('a.groupname LIKE :sreachstring')
                ->orwhere('b.useremailid LIKE :sreachstring')
                ->setParameter('companyid',$newcompanyid)
                ->setParameter('sreachstring','%'.$searchstring.'%');
            $result=$qb->getQuery()->getResult();
            $response = new JsonResponse();

            $response->setData(array('returnresult' => $result));
            return $response;

        }
    }
    /**
     * Auto comment for shipreports
     *
     * @Route("/addcomment_ranking_kpi", name="addcomment_ranking_kpi")
     */
    public function runtimecommentAction(Request $request)
    {
        $em=$this->getDoctrine()->getManager();
        $session=new Session();
        //get client Email Id
        $user = $this->getUser();
        $username = $user->getUsername();
        $emailid=$user->getEmail();
        //get Informaton From User
        $kpiid = $request->request->get('kpiid');

        $newkpiid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $kpiid));
        $comment =  $request->request->get('comment');
        $today = date("Y-m-d H:i:s");
        $datetime = new \DateTime();
        $sendcommand=new SendCommandRanking();
        $sendcommand->setClientemail($emailid);
        $sendcommand->setComment($comment);
        $sendcommand->setDatetime($datetime);
        $sendcommand->setShipid($kpiid);
        $em->persist($sendcommand);
        $em->flush();
        $lastid= $sendcommand->getId();
        $lastarray=array('id'=>$lastid);
        $session->set('commandid', $lastid);

        $listofcomment = $em->createQueryBuilder()
            ->select('a.comment')
            ->from('InitialShippingBundle:SendCommandRanking','a')
            ->join('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.emailId = a.clientemail')
            ->where('a.shipid = :shipid')
            ->andwhere('b.emailId = :username')
            ->setParameter('username',$emailid)
            ->setParameter('shipid',$kpiid)
            ->getQuery()
            ->getResult();
        $response = new JsonResponse();
        $response->setData(array('resultarray' => $listofcomment,'lastinsertid'=>$lastid));
        return $response;


    }
    /**
     * Add commen kpi reports
     *
     * @Route("/addcomment_ranking_ship", name="addcomment_ranking_ship")
     */
    public function runtimekpicommentAction(Request $request)
    {
        $em=$this->getDoctrine()->getManager();
        $session=new Session();
        //get client Email Id
        $user = $this->getUser();
        $username = $user->getUsername();
        $emailid=$user->getEmail();
        //get Informaton From User
        $kpiid = $request->request->get('kpiid');

        $newkpiid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $kpiid));
        $comment =  $request->request->get('comment');
        $today = date("Y-m-d H:i:s");
        $datetime = new \DateTime();
        $sendcommand=new SendCommandRanking();
        $sendcommand->setClientemail($emailid);
        $sendcommand->setComment($comment);
        $sendcommand->setDatetime($datetime);
        $sendcommand->setKpiid($kpiid);
        $em->persist($sendcommand);
        $em->flush();
        $lastid= $sendcommand->getId();
        $lastarray=array('id'=>$lastid);
        $session->set('commandid', $lastid);

        $listofcomment = $em->createQueryBuilder()
            ->select('a.comment')
            ->from('InitialShippingBundle:SendCommandRanking','a')
            ->join('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.emailId = a.clientemail')
            ->where('a.kpiid = :kpiid')
            ->andwhere('b.emailId = :username')
            ->setParameter('username',$emailid)
            ->setParameter('kpiid',$kpiid)
            ->getQuery()
            ->getResult();
        $response = new JsonResponse();
        $response->setData(array('resultarray' => $listofcomment,'lastinsertid'=>$lastid));
        return $response;


    }

    /**
     * List all kpi for ship
     *
     * @Route("/{shipid}/listallkpiforship_ranking", name="listallkpiforship_ranking")
     */
    public function listallkpiforship_rankingAction($shipid,Request $request,$mode='')
    {
        $newshipid=$shipid;

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if($user==null)
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
        else
        {
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
            //$monthinstring=date('Y-M-d');
            //$lastmonthdetail = new \DateTime($monthinstring);
            //$lastmonthdetail->modify('last day of this month');
            $lastfivedatearray=array();
            for ($m=1; $m<=12; $m++) {
                $month = date('Y-m-d', mktime(0,0,0,$m, 1, date('Y')));
               array_push($lastfivedatearray,$month);
            }
//Find Last Five Months Ends Here//

            $listallkpi = $em->createQueryBuilder()
                ->select('a.kpiName','a.id','a.weightage')
                ->from('InitialShippingBundle:RankingKpiDetails','a')
                ->where('a.shipDetailsId = :shipid')
                ->setParameter('shipid',$shipid)
                ->getQuery()
                ->getResult();
            $newcategories=array();
            $finalkpielementvaluearray=array();
            $datescolorarray=array();
            $overalkpivaluesmontyly=array();

            $kpiweightagearray=array();
            //loop for sending dates//
            for ($d = 0; $d < count($lastfivedatearray); $d++) {
                $time2 = strtotime($lastfivedatearray[$d]);
                $monthinletter = date('M', $time2);
                array_push($newcategories, $monthinletter);
                $new_monthdetail_date = new \DateTime($lastfivedatearray[$d]);
                $new_monthdetail_date->modify('last day of this month');
                $finalkpielementvalue = 0;
                $findingcolorarray = array();
                $kpivaluesmontyly=array();

                for ($element = 0; $element < count($listallkpi); $element++)
                {

                    $kpiidvalue = $listallkpi[$element]['id'];
                    $kpiweightage = $listallkpi[$element]['weightage'];
                    $kpiname = $listallkpi[$element]['kpiName'];
                    $findelementidarray = $em->createQueryBuilder()
                        ->select('c.id', 'c.weightage')
                        ->from('InitialShippingBundle:RankingElementDetails', 'c')
                        ->where('c.kpiDetailsId = :kpiid')
                        ->setParameter('kpiid', $kpiidvalue)
                        ->getQuery()
                        ->getResult();

                    $finalkpivalue = 0;
                    if (count($findelementidarray) == 0)
                    {
                        $newkpiid = $em->createQueryBuilder()
                            ->select('b.id')
                            ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                            ->where('b.kpiName = :kpiName')
                            ->setParameter('kpiName', $kpiname)
                            ->groupby('b.kpiName')
                            ->getQuery()
                            ->getResult();
                        $findelementidarray = $em->createQueryBuilder()
                            ->select('a.elementName', 'a.id', 'a.weightage')
                            ->from('InitialShippingBundle:RankingElementDetails', 'a')
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
                                ->from('InitialShippingBundle:RankingMonthlyData', 'a')
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
                            ->from('InitialShippingBundle:RankingKpiRules', 'a')
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
                                ->from('InitialShippingBundle:RankingMonthlyData', 'a')
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
                            ->from('InitialShippingBundle:RankingKpiRules', 'a')
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
                    array_push($kpivaluesmontyly,$finalkpivalue);
                    // Kpi color Finding Ends Here//
                    $findkpivalue = $finalkpivalue * (((int)$kpiweightage) / 100);
                    $finalkpielementvalue += $findkpivalue;
                }
                array_push($overalkpivaluesmontyly,$kpivaluesmontyly);
                array_push($datescolorarray, $findingcolorarray);
                array_push($finalkpielementvaluearray, $finalkpielementvalue);

            }
            $shipobject = new ShipDetails();
            $shipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
            $shipname = $shipid->getShipName();

            $series = array
            (
                array("name" => "$shipname",'showInLegend'=> false, 'color' => 'blue', "data" => $finalkpielementvaluearray),

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
            $ob->exporting->enabled(false);


            $listofcomment = $em->createQueryBuilder()
                ->select('a.comment')
                ->from('InitialShippingBundle:SendCommand','a')
                ->join('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.emailId = a.clientemail')
                ->where('a.shipid = :shipid')
                ->andwhere('b.emailId = :username')
                ->setParameter('username',$loginuseremail)
                ->setParameter('shipid',$shipid)
                ->getQuery()
                ->getResult();

            if($mode=='kpi_id')
            {
                return $datescolorarray;
            }
            if($mode=='pdftemplate_shiplevel')
            {
                return array(
                    'kpicolorarray'=>$datescolorarray,
                    'listofkpi'=>$listallkpi,
                    'kpiweightage'=>$kpiweightagearray,
                    'montharray'=>$newcategories,
                    'avgscore'=>$finalkpielementvaluearray,
                    'commentarray'=>$listofcomment,
                    'kpimonthdata'=>$overalkpivaluesmontyly,
                    'currentyear'=>date('Y')
                );
            }


            return $this->render(
                'InitialShippingBundle:DashBorad:listallkpiforship_ranking.html.twig',
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
                    'shipid'=>$newshipid,
                    'kpimonthdata'=>$overalkpivaluesmontyly,
                    'currentyear'=>date('Y')
                )
            );
        }

    }
    /**
     * List all element for kpi
     *
     * @Route("/{kpiid}/listelementforkpi_ranking", name="listelementforkpi_ranking")
     */
    public function listallelementforkpi_rankingAction($kpiid,Request $request,$mode='')
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if($user==null)
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
        else {
            $username = $user->getUsername();
            $email = $user->getEmail();
//Find Last Five Months Starts Here //
            $firstnewkpiid = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id' => $kpiid));
            $kpiname=$firstnewkpiid->getKpiName();
            $comanyiddetailarray = $em->createQueryBuilder()
                ->select('b.id')
                ->from('InitialShippingBundle:CompanyDetails', 'b')
                ->where('b.adminName = :username')
                ->setParameter('username', $username)
                ->getQuery()
                ->getResult();
            /* $monthinstring=date('Y-m-d');
             $lastmonthdetail = new \DateTime($monthinstring);
             $lastmonthdetail->modify('last day of this month');
             $lastfivedatearray = array();
             $mystringvaluedate = $lastmonthdetail->format('Y-M-d');
             array_push($lastfivedatearray, $mystringvaluedate);
             for ($i = 0; $i < 11; $i++) {
                 $mydatevalue = new \DateTime($mystringvaluedate);

                 $mydatevalue->modify("last day of previous month");
                 $myvalue = $mydatevalue->format("Y-M-d");
                 array_push($lastfivedatearray, $myvalue);

                 $mystringvaluedate = $myvalue;

             }*/
            $lastfivedatearray=array();
            for ($m=1; $m<=12; $m++) {
                $month = date('Y-m-d', mktime(0,0,0,$m, 1, date('Y')));
                array_push($lastfivedatearray,$month);
            }
//Find Last Five Months Ends Here//
            $newcategories = array();

            $listelement = $em->createQueryBuilder()
                ->select('a.elementName', 'a.id', 'a.weightage')
                ->from('InitialShippingBundle:RankingElementDetails', 'a')
                ->where('a.kpiDetailsId = :kpiid')
                ->setParameter('kpiid', $kpiid)
                ->getQuery()
                ->getResult();

            $shipidarray = $em->createQueryBuilder()
                ->select('identity(b.shipDetailsId)')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.id = :kpiid')
                ->setParameter('kpiid', $kpiid)
                ->getQuery()
                ->getResult();
            $shipid = $shipidarray[0][1];
            $elementdetailvaluearray = array();
            $elementweightagearray = array();
            $findelementcolorarray = array();
            $findoverallelementvalue=array();

            // Getting kpi_color value from ship_kpi_listAction function/controller

            $kpi_color_array = $this->listallkpiforship_rankingAction($shipid, $request, 'kpi_id');

            // Finding index of the kpi from $kpi_color_array

            $find_kpi_id_index = $em->createQueryBuilder()
                ->select('a.id')
                ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                ->where('a.shipDetailsId = :ship_id')
                ->setParameter('ship_id', $shipid)
                ->getQuery()
                ->getResult();
            for ($find_kpi_id_index_count = 0; $find_kpi_id_index_count < count($find_kpi_id_index); $find_kpi_id_index_count++) {
                if ($find_kpi_id_index[$find_kpi_id_index_count]['id'] == $kpiid) {
                    $index_value = $find_kpi_id_index_count;
                }
            }

            $kpi_rule_color_array = array();

            for ($kpi_color_array_count = 0; $kpi_color_array_count < count($kpi_color_array); $kpi_color_array_count++) {
                array_push($kpi_rule_color_array, $kpi_color_array[$kpi_color_array_count][$index_value]);
            }

            //finding kpi rule to display in the web page

            $kpi_name = $em->createQueryBuilder()
                ->select('a.kpiName')
                ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                ->where('a.id = :kpi_id')
                ->setParameter('kpi_id', $kpiid)
                ->getQuery()
                ->getResult();

            $kpi_id_array = $em->createQueryBuilder()
                ->select('a.id')
                ->from('InitialShippingBundle:RankingKpiDetails', 'a')
                ->where('a.kpiName = :kpi_name')
                ->setParameter('kpi_name', $kpi_name[0]['kpiName'])
                ->getQuery()
                ->getResult();

            $rule_for_kpi_id = $em->createQueryBuilder()
                ->select('a.rules')
                ->from('InitialShippingBundle:RankingKpiRules', 'a')
                ->where('a.kpiDetailsId = :kpi_id')
                ->setParameter('kpi_id', $kpi_id_array[0]['id'])
                ->getQuery()
                ->getResult();





            if (count($listelement) == 0) {

                $newkpiid = $em->createQueryBuilder()
                    ->select('b.id')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->groupby('b.kpiName')
                    ->getQuery()
                    ->getResult();
                $listelement = $em->createQueryBuilder()
                    ->select('a.elementName', 'a.id', 'a.weightage')
                    ->from('InitialShippingBundle:RankingElementDetails', 'a')
                    ->where('a.kpiDetailsId = :kpiid')
                    ->setParameter('kpiid', $newkpiid[0]['id'])
                    ->getQuery()
                    ->getResult();
                for ($d = 0; $d < count($lastfivedatearray); $d++) {
                    $time2 = strtotime($lastfivedatearray[$d]);
                    $monthinletter = date('M', $time2);
                    array_push($newcategories, $monthinletter);
                    $new_monthdetail_date = new \DateTime($lastfivedatearray[$d]);
                    $new_monthdetail_date->modify("last day of this month");
                    $finalkpivalue = 0;
                    $findingcolorarray = array();
                    $kpielementvalue=array();

                    for ($jk = 0; $jk < count($listelement); $jk++) {

                        $weightage = $listelement[$jk]['weightage'];
                        //Finding value based on element id and dates from user//
                        $dbvalueforelement = $em->createQueryBuilder()
                            ->select('a.value')
                            ->from('InitialShippingBundle:RankingMonthlyData', 'a')
                            ->where('a.shipDetailsId = :shipid')
                            ->andwhere('a.kpiDetailsId = :kpiDetailsId')
                            ->andWhere('a.elementDetailsId = :Elementid')
                            ->andWhere('a.monthdetail =:dataofmonth')
                            ->setParameter('shipid', $shipid)
                            ->setParameter('kpiDetailsId', $newkpiid[0]['id'])
                            ->setParameter('Elementid', $listelement[$jk]['id'])
                            ->setParameter('dataofmonth', $new_monthdetail_date)
                            ->getQuery()
                            ->getResult();

                        array_push($elementweightagearray, $weightage);
                        $kpi_rules = $em->createQueryBuilder()
                            ->select('a.rules')
                            ->from('InitialShippingBundle:RankingRules', 'a')
                            ->where('a.elementDetailsId = :elementid')
                            ->andwhere('a.kpiDetailsId = :kpiid')
                            ->setParameter('elementid', $listelement[$jk]['id'])
                            ->setParameter('kpiid', $kpiid)
                            ->getQuery()
                            ->getResult();
                        $read1 = "";

                        //Find the color based on kpi rules


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
                        array_push($findingcolorarray, $read1);
                        array_push($kpielementvalue,$finalkpivalue);

                    }

                    array_push($findelementcolorarray, $findingcolorarray);
                    array_push($findoverallelementvalue,$kpielementvalue);

                    array_push($elementdetailvaluearray, $finalkpivalue);
                }

                $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
                $shipname = $newshipid->getShipName();
                $series = array
                (
                    array("name" => "$kpiname",'showInLegend'=> false, 'color' => 'blue', "data" => $elementdetailvaluearray),

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
                $ob->exporting->enabled(false);

                //$ob->plotOptions->area(array('pointStart'=>0,'marker'=>array('enabled'=>false,'symbol'=>'circle','radius'=>2,'states'=>array('hover'=>array('enabled'=>false)))));

                for($elementCount=0;$elementCount<count($listelement);$elementCount++)
                {
                    $element_rule1 = $em->createQueryBuilder()
                        ->select('a.rules','identity(a.elementDetailsId)')
                        ->from('InitialShippingBundle:RankingRules', 'a')
                        ->where('a.elementDetailsId = :element_id')
                        ->setParameter('element_id', $listelement[$elementCount]['id'])
                        ->getQuery()
                        ->getResult();
                    $element_rule[$elementCount]=$element_rule1;
                }
                $listofcomment = $em->createQueryBuilder()
                    ->select('a.comment')
                    ->from('InitialShippingBundle:SendCommandRanking', 'a')
                    ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.emailId = a.clientemail')
                    ->where('a.kpiid = :kpiid')
                    ->andwhere('b.emailId = :username')
                    ->setParameter('username', $email)
                    ->setParameter('kpiid', $kpiid)
                    ->getQuery()
                    ->getResult();
                if($mode=='pdftemplate_kpilevel')
                {
                    return array(
                        'elementcolorarray'=>$findelementcolorarray,
                        'listofelement'=>$listelement,
                        'elementweightage'=>$elementweightagearray,
                        'montharray'=>$newcategories,
                        'avgscore'=>$elementdetailvaluearray,
                        'commentarray'=>$listofcomment,
                        'monthlydata'=>$findoverallelementvalue,
                        'elementRule' => $element_rule
                    );
                }

                $newcategories1 = array_reverse($newcategories);

                return $this->render(
                    'InitialShippingBundle:DashBorad:elementforkpi_ranking.html.twig',
                    array(
                        'listofelement' => $listelement,
                        'kpiname' => $kpiname,
                        'chart' => $ob,
                        'shipname' => $shipname,
                        'elementweightage' => $elementweightagearray,
                        'monthdetails' => $newcategories1,
                        'elementcolorarray' => $findelementcolorarray,
                        'countmonth' => count($findelementcolorarray),
                        'avgscore' => $elementdetailvaluearray,
                        'kpiid' => $kpiid,
                        'commentarray' => $listofcomment,
                        'kpi_color' => $kpi_rule_color_array,
                        'kpi_rule' => $rule_for_kpi_id,
                        'shipid'=>$shipid,
                        'monthlydata'=>$findoverallelementvalue,
                        'elementRule' => $element_rule
                    )
                );

            }
            else {


                for ($d = 0; $d < count($lastfivedatearray); $d++)
                {
                    $time2 = strtotime($lastfivedatearray[$d]);
                    $monthinletter = date('M', $time2);
                    array_push($newcategories, $monthinletter);
                    $new_monthdetail_date = new \DateTime($lastfivedatearray[$d]);
                    $new_monthdetail_date->modify('last day of this month');
                    $finalkpivalue = 0;
                    $findingcolorarray = array();
                    $findlvaluemonth=array();

                    for ($jk = 0; $jk < count($listelement); $jk++) {

                        $weightage = $listelement[$jk]['weightage'];
                        //Finding value based on element id and dates from user//
                        $dbvalueforelement = $em->createQueryBuilder()
                            ->select('a.value')
                            ->from('InitialShippingBundle:RankingMonthlyData', 'a')
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

                        array_push($elementweightagearray, $weightage);
                        $kpi_rules = $em->createQueryBuilder()
                            ->select('a.rules')
                            ->from('InitialShippingBundle:RankingRules', 'a')
                            ->where('a.elementDetailsId = :elementid')
                            ->andwhere('a.kpiDetailsId = :kpiid')
                            ->setParameter('elementid', $listelement[$jk]['id'])
                            ->setParameter('kpiid', $kpiid)
                            ->getQuery()
                            ->getResult();
                        $read1 = "";

                        //Find the color based on kpi rules


                        if (count($dbvalueforelement) == 0) {
                            $finddbvaluefomula = 0 * (((int)$weightage) / 100);
                            $finalkpivalue += $finddbvaluefomula;
                        } else {
                            $finddbvaluefomula = ((float)($dbvalueforelement[0]['value'])) * (((int)$weightage) / 100);
                            $finalkpivalue += $finddbvaluefomula;
                        }
                        array_push($findlvaluemonth,$finalkpivalue);

                        for ($kpi_rules_count = 0; $kpi_rules_count < count($kpi_rules); $kpi_rules_count++) {
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
                        array_push($findingcolorarray, $read1);


                    }

                    array_push($findelementcolorarray, $findingcolorarray);


                    array_push($elementdetailvaluearray, array_sum($findlvaluemonth));
                    array_push($findoverallelementvalue, $findlvaluemonth);
                }

                $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
                $shipname = $newshipid->getShipName();

                $series = array
                (
                    array("name" => "$kpiname",'showInLegend'=> false, 'color' => 'blue', "data" => $elementdetailvaluearray),

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
                $ob->exporting->enabled(false);
                //$ob->plotOptions->area(array('pointStart'=>0,'marker'=>array('enabled'=>false,'symbol'=>'circle','radius'=>2,'states'=>array('hover'=>array('enabled'=>false)))));
                //find the comments for particular user//
                $listofcomment = $em->createQueryBuilder()
                    ->select('a.comment')
                    ->from('InitialShippingBundle:SendCommandRanking', 'a')
                    ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.emailId = a.clientemail')
                    ->where('a.kpiid = :kpiid')
                    ->andwhere('b.emailId = :username')
                    ->setParameter('username', $email)
                    ->setParameter('kpiid', $kpiid)
                    ->getQuery()
                    ->getResult();
                for($elementCount=0;$elementCount<count($listelement);$elementCount++)
                {
                    $element_rule1 = $em->createQueryBuilder()
                        ->select('a.rules','identity(a.elementDetailsId)')
                        ->from('InitialShippingBundle:RankingRules', 'a')
                        ->where('a.elementDetailsId = :element_id')
                        ->setParameter('element_id', $listelement[$elementCount]['id'])
                        ->getQuery()
                        ->getResult();
                    $element_rule[$elementCount]=$element_rule1;
                }
                if($mode=='pdftemplate_kpilevel')
                {
                    return array(
                        'elementcolorarray'=>$findelementcolorarray,
                        'listofelement'=>$listelement,
                        'elementweightage'=>$elementweightagearray,
                        'montharray'=>$newcategories,
                        'avgscore'=>$elementdetailvaluearray,
                        'commentarray'=>$listofcomment,
                        'monthlydata'=>$findoverallelementvalue,
                        'elementRule' => $element_rule

                    );
                }


                $newcategories1 = array_reverse($newcategories);

                return $this->render(
                    'InitialShippingBundle:DashBorad:elementforkpi_ranking.html.twig',
                    array(
                        'listofelement' => $listelement,
                        'kpiname' => $kpiname,
                        'chart' => $ob,
                        'shipname' => $shipname,
                        'elementweightage' => $elementweightagearray,
                        'monthdetails' => $newcategories1,
                        'elementcolorarray' => $findelementcolorarray,
                        'countmonth' => count($findelementcolorarray),
                        'avgscore' => $elementdetailvaluearray,
                        'kpiid' => $kpiid,
                        'commentarray' => $listofcomment,
                        'kpi_color' => $kpi_rule_color_array,
                        'kpi_rule' => $rule_for_kpi_id,
                        'shipid'=>$shipid,
                        'monthlydata'=>$findoverallelementvalue,
                        'elementRule' => $element_rule
                    )
                );
            }
        }
    }

    /**
     * Add comment for kpireports
     *
     * @Route("/addcommentkpi", name="addcommentkpi")
     */
    public function addcommentAction(Request $request)
    {
        $em=$this->getDoctrine()->getManager();
        //get client Email Id
        $user = $this->getUser();
        $username = $user->getUsername();
        $useremailaddres=$user->getEmail();

        $userquery = $em->createQueryBuilder()
            ->select('a.emailId')
            ->from('InitialShippingBundle:CompanyDetails','a')
            ->where('a.adminName = :userId')
            ->setParameter('userId',$username)
            ->getQuery();
        $clientemailid=$userquery->getSingleScalarResult();

        //get Informaton From User

        $params = $request->request->get('send_command');
        $kpiid=$params['kpiid'];
        $newkpiid = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id' => $kpiid));
        $kpiname=$newkpiid->getKpiName();
        $returnvaluefrommonth = $this->listallelementforkpi_rankingAction($kpiid,$request,'pdftemplate_kpilevel');


        $filename = $params['filename'];
        $pdffilenamearray=explode(".",$filename);

        $kpiid=$params['kpiid'];

        $comment = $params['comment'];
        $checkboxvalue='';
        if(count($params)<6)
        {
            $checkboxvalue='No';
            $listofcommentarray=array();

        }
        else
        {
            $checkboxvalue = $params['addcomment'];
            $listofcommentarray=$returnvaluefrommonth['commentarray'];
        }
        $idforrecord = $params['lastid'];

        $today = date("Y-m-d H:i:s");
        $pageName = $request->query->get('page');
        $screenName = $this->get('translator')->trans($pageName);
        $date = date('l jS F Y h:i', time());
        $route = $request->attributes->get('_route');

        $customerListDesign = $this->renderView('InitialShippingBundle:DashBorad:pdfreporttemplate.html.twig', array(
            'link' => $filename,
            'screenName' => $screenName,
            'userName' => '',
            'date' => $date,
            'listofelement'=>$returnvaluefrommonth['listofelement'],
            'kpiname'=>$kpiname,
            'elementweightage'=>$returnvaluefrommonth['elementweightage'],
            'montharray'=>$returnvaluefrommonth['montharray'],
            'elementcolorarray'=>$returnvaluefrommonth['elementcolorarray'],
            'countmonth'=>count($returnvaluefrommonth['elementcolorarray']),
            'avgscore'=> $returnvaluefrommonth['avgscore'],
            'monthlydata'=>$returnvaluefrommonth['monthlydata'],
            'commentarray'=>$listofcommentarray,
            'datetime'=>$today,
            'elementRule'=>$returnvaluefrommonth['elementRule']
        ));
        $client = new HighchartController();
        $client->setContainer($this->container);
        $printPdf = $client->createPdf($customerListDesign, $screenName);

        $pdffilenamefullpath= $this->container->getParameter('kernel.root_dir').'/../web/uploads/brochures/'.$pdffilenamearray[0].'.pdf';
        file_put_contents($pdffilenamefullpath, $printPdf);



        //$sendcommand=new SendCommand();
        //assign file attachement for mail and Mailing Starts Here...u
        $useremaildid=$params['clientemail'];

        if (filter_var($useremaildid, FILTER_VALIDATE_EMAIL))
        {
            $mailer = $this->container->get('mailer');
            $message = \Swift_Message::newInstance()
                ->setFrom($clientemailid)
                ->setTo($useremaildid)
                ->setSubject($kpiname)
                ->setBody($comment);
            $message->attach(\Swift_Attachment::fromPath($pdffilenamefullpath)->setFilename($pdffilenamearray[0] . '.pdf'));
            $mailer->send($message);
        }
        else
        {
            $findsemail=$em->createQueryBuilder()
                ->select('a.useremailid')
                ->from('InitialShippingBundle:EmailUsers','a')
                ->join('InitialShippingBundle:EmailGroup','b', 'WITH', 'b.id = a.groupid')
                ->where('b.groupname = :sq')
                ->ORwhere('a.useremailid = :sb')
                ->setParameter('sq',$useremaildid)
                ->setParameter('sb',$useremaildid)
                ->getQuery()
                ->getResult();


            //assign file attachement for mail and Mailing Starts Here...u
            for($ma=0;$ma<count($findsemail);$ma++)
            {
                $mailer = $this->container->get('mailer');
                $message = \Swift_Message::newInstance()
                    ->setFrom($clientemailid)
                    ->setTo($findsemail[$ma]['emailid'])
                    ->setSubject($kpiname)
                    ->setBody($comment);
                $message->attach(\Swift_Attachment::fromPath($pdffilenamefullpath)->setFilename($pdffilenamearray[0] . '.pdf'));
                $mailer->send($message);
            }
        }
        //Mailing Ends....
        //Update Process Starts Here...
        $session=new Session();
        $kpiandelementids= $session->get('commandid');
        if($kpiandelementids!=null)
        {
            $entityobject = $em->getRepository('InitialShippingBundle:SendCommandRanking')->find($kpiandelementids);
            $entityobject->setClientemail($clientemailid);
            $entityobject->setFilename($pdffilenamearray[0].'.pdf');
            $em->flush();
        }

        $response = new JsonResponse();
        $response->setData(array('updatemsg'=>"Report Has Been Send"));
        return $response;
    }
    /**
     * Add comment for shipreports
     *
     * @Route("/addcomment_ship", name="addcomment_ship")
     */
    public function addcommentforshipreportsAction(Request $request)
    {
        $em=$this->getDoctrine()->getManager();
        //get client Email Id
        $user = $this->getUser();
        $username = $user->getUsername();
        $useremailaddres=$user->getEmail();

        $userquery = $em->createQueryBuilder()
            ->select('a.emailId')
            ->from('InitialShippingBundle:CompanyDetails','a')
            ->where('a.adminName = :userId')
            ->setParameter('userId',$username)
            ->getQuery();
        $clientemailid=$userquery->getSingleScalarResult();
        $params = $request->request->get('send_command');
        $kpiid=$params['kpiid'];
        $returnvaluefrommonth = $this->listallkpiforship_rankingAction($kpiid,$request,'pdftemplate_shiplevel');
        $filename = $params['filename'];
        $pdffilenamearray=explode(".",$filename);
        $newkpiid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $kpiid));
        $kpiname=$newkpiid->getShipName();
        $comment = $params['comment'];
        $checkboxvalue='';
        if(count($params)<6)
        {
            $checkboxvalue='No';
            $listofcommentarray=array();

        }
        else
        {
            $checkboxvalue = $params['addcomment'];
            $listofcommentarray=$returnvaluefrommonth['commentarray'];
        }


       /* if($checkboxvalue=="Yes")
        {

        }
        else
        {

        }*/
        $idforrecord = $params['lastid'];

        $today = date("Y-m-d H:i:s");
        $pageName = $request->query->get('page');
        $screenName = $this->get('translator')->trans($pageName);
        $date = date('l jS F Y h:i', time());
        $route = $request->attributes->get('_route');

        $customerListDesign= $this->renderView('InitialShippingBundle:DashBorad:pdfreporttemplateforship.html.twig', array(
                'link' => $filename,
                'screenName' => $screenName,
                'userName' => '',
                'date' => $date,
                'listofkpi' => $returnvaluefrommonth['listofkpi'],
                'kpicolorarray' => $returnvaluefrommonth['kpicolorarray'],
                'kpiweightage' => $returnvaluefrommonth['kpiweightage'],
                'montharray' => $returnvaluefrommonth['montharray'],
                'shipname' => $kpiname,
                'countmonth' => count($returnvaluefrommonth['kpicolorarray']),
                'avgscore' => $returnvaluefrommonth['avgscore'],
                'commentarray'=>$listofcommentarray,
                 'kpimonthdata'=>$returnvaluefrommonth['kpimonthdata'],
            'currentyear'=>date('Y')
        ));

        $client = new HighchartController();
        $client->setContainer($this->container);
        $printPdf = $client->createPdf($customerListDesign, $screenName);

        $pdffilenamefullpath= $this->container->getParameter('kernel.root_dir').'/../web/uploads/brochures/'.$pdffilenamearray[0].'.pdf';
        file_put_contents($pdffilenamefullpath, $printPdf);

        $useremaildid=$params['clientemail'];
        if (filter_var($useremaildid, FILTER_VALIDATE_EMAIL))
        {
            $mailer = $this->container->get('mailer');
            $message = \Swift_Message::newInstance()
                ->setFrom($clientemailid)
                ->setTo($useremaildid)
                ->setSubject($kpiname)
                ->setBody($comment);
            $message->attach(\Swift_Attachment::fromPath($pdffilenamefullpath)->setFilename($pdffilenamearray[0] . '.pdf'));
            $mailer->send($message);
        }
        else
        {
            $findsemail=$em->createQueryBuilder()
                ->select('a.useremailid')
                ->from('InitialShippingBundle:EmailUsers','a')
                ->join('InitialShippingBundle:EmailGroup','b', 'WITH', 'b.id = a.groupid')
                ->where('b.groupname = :sq')
                ->ORwhere('a.useremailid = :sb')
                ->setParameter('sq',$useremaildid)
                ->setParameter('sb',$useremaildid)
                ->getQuery()
                ->getResult();


            //assign file attachement for mail and Mailing Starts Here...u
            for($ma=0;$ma<count($findsemail);$ma++)
            {
                $mailer = $this->container->get('mailer');
                $message = \Swift_Message::newInstance()
                    ->setFrom($clientemailid)
                    ->setTo($findsemail[$ma]['emailid'])
                    ->setSubject($kpiname)
                    ->setBody($comment);
                $message->attach(\Swift_Attachment::fromPath($pdffilenamefullpath)->setFilename($pdffilenamearray[0] . '.pdf'));
                $mailer->send($message);
            }
        }


        //Mailing Ends....
        //Update Process Starts Here...

        $session=new Session();
        $kpiandelementids= $session->get('commandid');
        if($kpiandelementids!=null)
        {
            $entityobject = $em->getRepository('InitialShippingBundle:SendCommandRanking')->find($kpiandelementids);
            $entityobject->setClientemail($clientemailid);
            $entityobject->setFilename($pdffilenamearray[0].'.pdf');
            $em->flush();
        }

        $response = new JsonResponse();
        $response->setData(array('updatemsg'=>"Report Has Been Send"));
        return $response;
    }


}