<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\Excel_file_details;
use Initial\ShippingBundle\Entity\RankingKpiDetails;
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
    public function indexAction(Request $request,$mode='',$dataofmonth='', $year=' ')
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if($user!=null)
        {
            $userId = $user->getId();
            $userName = $user->getUsername();

            if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
            {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName','a.id', 'a.manufacturingYear')
                    ->from('InitialShippingBundle:ShipDetails','a')
                    ->join('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = a.companyDetailsId')
                    ->where('b.adminName = :username')
                    ->setParameter('username',$userName)
                    ->getQuery();
            }
            else
            {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName','a.id', 'a.manufacturingYear')
                    ->from('InitialShippingBundle:ShipDetails','a')
                    ->leftjoin('InitialShippingBundle:User','b', 'WITH', 'b.companyid = a.companyDetailsId')
                    ->where('b.id = :userId')
                    ->setParameter('userId',$userId)
                    ->getQuery();
            }

            $listAllShipForCompany = $query->getResult();

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
            for($shipCount=0;$shipCount<count($listAllShipForCompany);$shipCount++)
            {
                $rankingKpiValueCountArray = array();
                $rankingShipName = $listAllShipForCompany[$shipCount]['shipName'];
                $manufacturingYear = $listAllShipForCompany[$shipCount]['manufacturingYear'];
                $rankingShipId = $listAllShipForCompany[$shipCount]['id'];

                $monthlyShipDataStatus = $em->createQueryBuilder()
                    ->select('b.status')
                    ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                    ->where('b.shipid = :shipId and b.dataofmonth = :monthDetail')
                    ->setParameter('shipId', $rankingShipId)
                    ->setParameter('monthDetail', $lastMonthDetail)
                    ->getQuery()
                    ->getResult();
                $rankingKpiList = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id', 'b.weightage')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                    ->where('b.shipDetailsId = :shipid')
                    ->setParameter('shipid', $listAllShipForCompany[0]['id'])
                    ->getQuery()
                    ->getResult();
                $verifyField = 0;
                if(count($monthlyShipDataStatus)!=0)
                {
                    $verifyField = $monthlyShipDataStatus[0]['status'];
                }
                if($verifyField==4)
                {
                    for ($rankingKpiCount = 0; $rankingKpiCount < count($rankingKpiList); $rankingKpiCount++)
                    {
                        $rankingElementValueTotal = 0;
                        $rankingKpiId = $rankingKpiList[$rankingKpiCount]['id'];
                        $rankingKpiWeight = $rankingKpiList[$rankingKpiCount]['weightage'];
                        $rankingKpiName = $rankingKpiList[$rankingKpiCount]['kpiName'];

                        $rankingElementList = $em->createQueryBuilder()
                            ->select('c.id','c.elementName', 'c.weightage', 'a.value')
                            ->from('InitialShippingBundle:RankingElementDetails', 'c')
                            ->join('InitialShippingBundle:RankingMonthlyData', 'a', 'with',  'c.id = a.elementDetailsId')
                            ->where('c.kpiDetailsId = :kpiid and a.monthdetail = :datamonth and a.status = :rankingStatusValue and a.shipDetailsId = :shipId')
                            ->setParameter('kpiid', $rankingKpiId)
                            ->setParameter('datamonth', $lastMonthDetail )
                            ->setParameter('rankingStatusValue',3)
                            ->setParameter('shipId',$rankingShipId)
                            ->getQuery()
                            ->getResult();

                        if($rankingElementList>0)
                        {
                            for($rankingElementCount=0;$rankingElementCount<count($rankingElementList);$rankingElementCount++)
                            {
                                $rankingElementName=$rankingElementList[$rankingElementCount]['elementName'];
                                $rankingElementId=$rankingElementList[$rankingElementCount]['id'];
                                $rankingElementWeight = $rankingElementList[$rankingElementCount]['weightage'];
                                $rankingElementValue = $rankingElementList[$rankingElementCount]['value'];
                                $rankingElementResultColor = "";
                                $rankingElementColorValue = 0;

                                $rankingElementResult = $em->createQueryBuilder()
                                    ->select('b.elementdata, b.elementcolor')
                                    ->from('InitialShippingBundle:Ranking_LookupData', 'b')
                                    ->where('b.kpiDetailsId = :kpiId and b.shipDetailsId = :shipId and b.elementDetailsId = :elementId and b.monthdetail = :monthDetail')
                                    ->setParameter('kpiId', $rankingKpiId)
                                    ->setParameter('shipId', $rankingShipId)
                                    ->setParameter('elementId', $rankingElementId)
                                    ->setParameter('monthDetail', $lastMonthDetail )
                                    ->getQuery()
                                    ->getResult();
                                if(count($rankingElementResult)!=0)
                                {
                                    $rankingElementResultColor = $rankingElementResult[0]['elementcolor'];
                                }

                                if ($rankingElementResultColor == "false")
                                {
                                    $rankingElementResultColor = "";
                                }

                                if($rankingElementResultColor=='Green')
                                {
                                    $rankingElementColorValue = $rankingElementWeight;
                                }
                                else if($rankingElementResultColor == 'Yellow')
                                {
                                    $rankingElementColorValue = $rankingElementWeight/2;
                                }
                                else if($rankingElementResultColor == 'Red')
                                {
                                    $rankingElementColorValue = 0;
                                }

                                /*$rankingElementRulesList = $em->createQueryBuilder()
                                    ->select('a.rules')
                                    ->from('InitialShippingBundle:RankingRules', 'a')
                                    ->where('a.elementDetailsId = :element_id')
                                    ->setParameter('element_id', $rankingElementId)
                                    ->getQuery()
                                    ->getResult();*/
                                /*for($elementRuleCount=0;$elementRuleCount<count($rankingElementRulesList);$elementRuleCount++)
                                {
                                    $rankingElementRule = $rankingElementRulesList[$elementRuleCount];
                                    $rankingJsFileDirectory = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_findcolornode_3.js \'' . $rankingElementRule['rules'] . ' \' ' . $rankingElementValue;
                                    $rankingJsFileName = 'node ' . $rankingJsFileDirectory;
                                    $handle = popen($rankingJsFileName, 'r');
                                    $resultColor = fread($handle, 2096);
                                    $rankingElementResultColor = str_replace("\n", '', $resultColor);

                                    if ($rankingElementResultColor == "false")
                                    {
                                        continue;
                                    }

                                    if($rankingElementResultColor=='Green')
                                    {
                                        $rankingElementColorValue = $rankingElementWeight;
                                        break;
                                    }
                                    else if($rankingElementResultColor == 'Yellow')
                                    {
                                        $rankingElementColorValue = $rankingElementWeight/2;
                                        break;
                                    }
                                    else if($rankingElementResultColor == 'Red')
                                    {
                                        $rankingElementColorValue = 0;
                                        break;
                                    }
                                }*/
                                $rankingElementValueTotal+=$rankingElementColorValue;
                            }
                        }
                        array_push($rankingKpiValueCountArray,($rankingElementValueTotal*$rankingKpiWeight/100));
                    }
                    if($manufacturingYear=="")
                    {
                        $yearcount=0;
                    }
                    else
                    {
                        $currentdatestring=date('Y-01-01');
                        $d1 = new \DateTime($currentdatestring);
                        $man_datestring=$manufacturingYear.'-01-'.'01';
                        $d2=new \DateTime($man_datestring);
                        $diff = $d2->diff($d1);
                        $yearcount=$diff->y+1;
                        $vesselage=20/$yearcount;
                    }
                    $overallShipDetailArray[$shipCount]['name']=$rankingShipName;
                    $overallShipDetailArray[$shipCount]['y'] = (array_sum($rankingKpiValueCountArray));
                    $yearChange = $lastMonthDetail->format('Y');
                    $overallShipDetailArray[$shipCount]['url'] = '/dashboard/'.$rankingShipId.'/'.$yearChange.'/listallkpiforship_ranking';
                }
                else
                {
                    $overallShipDetailArray[$shipCount]['name']=$rankingShipName;
                    $overallShipDetailArray[$shipCount]['y'] = 0;
                    $yearChange = $lastMonthDetail->format('Y');
                    $overallShipDetailArray[$shipCount]['url'] = '/dashboard/'.$rankingShipId.'/'.$yearChange.'/listallkpiforship_ranking';
                }

            }

            $monthInLetter = $lastMonthDetail->format('M-Y');
            if($mode=='getnextmonthchart')
            {
                return array("data" =>$overallShipDetailArray,'currentmonth'=>$monthInLetter, 'name' => $monthInLetter,);
            }
            // Adding data to javascript chart function starts Here.. //
            $ob = new Highchart();
            $ob->chart->renderTo('area');
            $ob->chart->type('column');
            $ob->chart->hieght(250);
            $ob->title->text('',array('style'=>array('color' => 'red')));
            $ob->xAxis->type('category');
            $ob->xAxis->labels(array('style' => array('color' => '#0000F0')));
            $ob->yAxis->title(array('text'=>'Values'));
            $ob->yAxis->max(100);
            $ob->legend->enabled(false);
            $ob->plotOptions->series(array('borderWidth'=>0,'dataLabels'=>array('enabled'=>false),
                'point'=>array('events'=>array('click'=>new \Zend\Json\Expr('function () { location.href = this.options.url; }')))));

            $ob->series(array( array( 'showInLegend'=> false,'colorByPoint'=> true,  'name' => $monthInLetter, 'color' => 'rgb(124, 181, 236)',   "data" =>$overallShipDetailArray)));

            /* $ob->drilldown->series($drilldownarray);*/
            $ob->exporting->enabled(false);
            if($mode=='overallreports_ranking')
            {
                return array(
                    'ship_count'=>count($listAllShipForCompany),
                    'allships'=>$listAllShipForCompany,
                    'chart'=>$ob,
                    'rankinKpiCount' => count($rankingKpiList),
                    'currentmonth'=>$monthInLetter,
                    'currentyear'=>$yearChange,
                );
            }

            // Adding data to javascript chart function  Ends Here.. //


            $datesArray=array();

            if($year==' ')
            {
                for ($m=2; $m<=6; $m++)
                {
                    $month = date('Y-m-d', mktime(0,0,0,$m, 0, date('Y')));
                    array_push($datesArray,$month);
                }
                $currentyear=date('Y');
            }
            if($year!=' ')
            {
                for ($m=2; $m<=6; $m++)
                {
                    $month = date('Y-m-d', mktime(0,0,0,$m, 0, date($year)));
                    array_push($datesArray,$month);
                }
                $currentyear=date($year);
            }

            $scorecardKpiList = $em->createQueryBuilder()
                ->select('a.kpiName','a.id','a.weightage')
                ->from('InitialShippingBundle:KpiDetails','a')
                ->groupby('a.kpiName')
                ->getQuery()
                ->getResult();

            $monthLetterArray = array();
            $monthlyScorecardKpiColorArray = array();
            $monthlyKpiAverageValueTotal = array();
            for ($dateCount=0; $dateCount<count($datesArray);$dateCount++)
            {
                $scorecardKpiColorArray = array();
                $date = strtotime($datesArray[$dateCount]);
                $monthLetterFormat = date('M', $date);
                array_push($monthLetterArray, $monthLetterFormat);
                $monthDetail = new \DateTime($datesArray[$dateCount]);
                $monthlyScorecardKpiWeightAverageValueTotal = 0;

                for($kpiCount=0;$kpiCount<count($scorecardKpiList);$kpiCount++)
                {
                    $scorecardKpiId = $scorecardKpiList[0]['id'];
                    $scorecardAllKpiId = $scorecardKpiList[$kpiCount]['id'];
                    $scorecardKpiWeight = $scorecardKpiList[$kpiCount]['weightage'];
                    $scorecardKpiName = $scorecardKpiList[$kpiCount]['kpiName'];
                    $kpiSumValue=0;

                    $scorecardElementArray = $em->createQueryBuilder()
                        ->select('c.id, c.weightage, sum(a.value) as value')
                        ->from('InitialShippingBundle:ElementDetails', 'c')
                        ->leftjoin('InitialShippingBundle:ReadingKpiValues', 'a', 'WITH', 'c.id = a.elementDetailsId and a.monthdetail = :dateOfMonth')
                        ->where('c.kpiDetailsId = :kpiId and a.status=:statusValue' )
                        ->setParameter('kpiId', $scorecardAllKpiId)
                        ->setParameter('dateOfMonth', $monthDetail)
                        ->setParameter('statusValue', 1)
                        ->groupBy('c.id, c.weightage')
                        ->orderBy('c.id')
                        ->getQuery()
                        ->getResult();

                    if(count($scorecardElementArray)>0)
                    {
                        for($elementCount=0;$elementCount<count($scorecardElementArray);$elementCount++)
                        {
                            $scorecardElementId = $scorecardElementArray[$elementCount]['id'];
                            $scorecardElementWeight = $scorecardElementArray[$elementCount]['weightage'];
                            $scorecardElementSumValue = $scorecardElementArray[$elementCount]['value'];

                            $averageElementValue = $scorecardElementSumValue / count($listAllShipForCompany);

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
                            $elementValueWithWeight = $elementColorValue * (((int)$scorecardElementWeight) / 100);
                            $kpiSumValue+=$elementValueWithWeight;
                        }
                    }
                    $scorecardKpiRulesArray = $em->createQueryBuilder()
                        ->select('a.rules')
                        ->from('InitialShippingBundle:KpiRules', 'a')
                        ->where('a.kpiDetailsId = :kpiId')
                        ->setParameter('kpiId', $scorecardKpiId)
                        ->getQuery()
                        ->getResult();

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
                    array_push($scorecardKpiColorArray,$kpiResultColor);
                    $monthlyScorecardKpiWeightAverageValueTotal+=$kpiSumValue*($scorecardKpiWeight/100);
                }
                array_push($monthlyScorecardKpiColorArray,$scorecardKpiColorArray);
                array_push($monthlyKpiAverageValueTotal,$monthlyScorecardKpiWeightAverageValueTotal);
            }

            $currentMonthNumber = date('n');
            $num = $currentMonthNumber-3;

            $quarterMonthName = array();
            $quarterMonthColor = array();
            $quarterMonthKpiWeight = array();
            for($a=0;$a<3;$a++)
            {
                array_push($quarterMonthName,$monthLetterArray[$num]);
                array_push($quarterMonthColor,$monthlyScorecardKpiColorArray[$num]);
                array_push($quarterMonthKpiWeight,$monthlyKpiAverageValueTotal[$num]);
                $num++;
            }

            if($year != ' ')
            {
                return array(
                    'yearKpiColorArray' => $monthlyScorecardKpiColorArray,
                    'yearMonthName' => $monthLetterArray,
                    'kpi_list' => $scorecardKpiList,
                    'currentYear' => $year,
                    'kpiAverageScore' => $monthlyKpiAverageValueTotal
                );
            }

            return $this->render(
                'InitialShippingBundle:DashBorad:home.html.twig',
                array(
                    'ship_count'=>count($listAllShipForCompany),
                    'kpi_list' => $scorecardKpiList,
                    'month_name' => $quarterMonthName,
                    'kpicolorarray' => $quarterMonthColor,
                    'yearKpiColorArray' => $monthlyScorecardKpiColorArray,
                    'yearAvgScore' => $monthlyKpiAverageValueTotal,
                    'yearMonthName' => $monthLetterArray,
                    'currentYear' => $currentyear,
                    'kpiCount' => count($scorecardKpiList),
                    'kpiAverageScore' => $quarterMonthKpiWeight,
                    'allships'=>$listAllShipForCompany,
                    'chart'=>$ob,
                    'rankinKpiCount' => count($rankingKpiList),
                    'currentmonth'=>$monthInLetter,
                    'currentyear'=>$yearChange,
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
     * Ajax Call For change of monthdata of Rankinng Chart
     *
     * @Route("/previousYearChange", name="previousYearChange")
     */
    public function previousYearChangeAction(Request $request)
    {
        $year = $request->request->get('Year');
        $yearValue = $this->indexAction( $request, '', '', $year-1);
        $yy = $yearValue['currentYear'];

        $response = new JsonResponse();
        $response->setData(array(
            'yearKpiColorArray' => $yearValue['yearKpiColorArray'],
            'yearAvgScore' => $yearValue['yearAvgScore'],
            'yearMonthName' => $yearValue['yearMonthName'],
            'currentYear' => $yearValue['currentYear'],
            'kpiNameList' => $yearValue['kpi_list']
        ));
        return $response;

    }

    /**
     * Ajax Call For change of monthdata of Rankinng Chart
     *
     * @Route("/nextYearChange", name="nextYearChange")
     */
    public function nextYearChangeAction(Request $request)
    {
        $year = $request->request->get('Year');
        $yearValue = $this->indexAction( $request, '', '', $year+1);

        $response = new JsonResponse();
        $response->setData(array(
            'yearKpiColorArray' => $yearValue['yearKpiColorArray'],
            'yearAvgScore' => $yearValue['yearAvgScore'],
            'yearMonthName' => $yearValue['yearMonthName'],
            'currentYear' => $yearValue['currentYear'],
            'kpiNameList' => $yearValue['kpi_list']
        ));
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
    public function listallelementforkpiAction($kpiid,Request $request,$mode='')
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if($user!=null)
        {
            $datesArray=array();
            for ($m=2; $m<=6; $m++)
            {
                $month = date('Y-m-d', mktime(0,0,0,$m, 0, date('Y')));
                array_push($datesArray,$month);
            }
            $currentMonthNumber = date('n');
            $num = $currentMonthNumber-3;

            $quarterDatesArray=array();
            for($d=$num;$d<$currentMonthNumber;$d++)
            {
                array_push($quarterDatesArray,$datesArray[$d]);
            }

            $userId = $user->getId();
            $username = $user->getUsername();
            $email = $user->getEmail();

            if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
            {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName','a.id', 'a.manufacturingYear')
                    ->from('InitialShippingBundle:ShipDetails','a')
                    ->join('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = a.companyDetailsId')
                    ->where('b.adminName = :username')
                    ->setParameter('username',$username)
                    ->getQuery();
            }
            else
            {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName','a.id', 'a.manufacturingYear')
                    ->from('InitialShippingBundle:ShipDetails','a')
                    ->leftjoin('InitialShippingBundle:User','b', 'WITH', 'b.companyid = a.companyDetailsId')
                    ->where('b.id = :userId')
                    ->setParameter('userId',$userId)
                    ->getQuery();
            }

            $allShipsArray = $query->getResult();

            $scorecardKpiList = $em->createQueryBuilder()
                ->select('a.kpiName','a.id','a.weightage')
                ->from('InitialShippingBundle:KpiDetails','a')
                ->groupby('a.kpiName')
                ->getQuery()
                ->getResult();

            $monthLetterArray = array();
            $monthlyScorecardKpiColorArray = array();
            $monthlyKpiAverageValueTotal = array();
            $monthlyElementColorArray = array();
            $monthlyElementValueArray = array();
            for($monthCount=0;$monthCount<count($quarterDatesArray);$monthCount++)
            {
                $scorecardKpiColorArray = array();
                $date = strtotime($quarterDatesArray[$monthCount]);
                $monthLetterFormat = date('M', $date);
                array_push($monthLetterArray, $monthLetterFormat);
                $monthDetail = new \DateTime($quarterDatesArray[$monthCount]);
                $monthlyScorecardKpiWeightAverageValueTotal = 0;

                for($kpiCount=0;$kpiCount<count($scorecardKpiList);$kpiCount++)
                {
                    $scorecardAllKpiId = $scorecardKpiList[$kpiCount]['id'];
                    if($kpiid==$scorecardAllKpiId)
                    {
                        $scorecardElementRules = array();
                        $scorecardElementValueArray = array();
                        $kpiElementColorArray = array();
                        $scorecardKpiId = $scorecardKpiList[$kpiCount]['id'];
                        $scorecardKpiWeight = $scorecardKpiList[$kpiCount]['weightage'];
                        $scorecardKpiName = $scorecardKpiList[$kpiCount]['kpiName'];
                        $kpiSumValue=0;

                        $scorecardElementArray = $em->createQueryBuilder()
                            ->select('c.id, c.weightage, c.elementName, sum(a.value) as value')
                            ->from('InitialShippingBundle:ElementDetails', 'c')
                            ->leftjoin('InitialShippingBundle:ReadingKpiValues', 'a', 'WITH', 'c.id = a.elementDetailsId and a.monthdetail = :dateOfMonth')
                            ->where('c.kpiDetailsId = :kpiId and a.status=:statusValue' )
                            ->setParameter('kpiId', $scorecardKpiId)
                            ->setParameter('dateOfMonth', $monthDetail)
                            ->setParameter('statusValue',1)
                            ->groupBy('c.id, c.weightage')
                            ->orderBy('c.id')
                            ->getQuery()
                            ->getResult();

                        if(count($scorecardElementArray)>0)
                        {
                            for($elementCount=0;$elementCount<count($scorecardElementArray);$elementCount++)
                            {
                                $scorecardElementId = $scorecardElementArray[$elementCount]['id'];
                                $scorecardElementWeight = $scorecardElementArray[$elementCount]['weightage'];
                                $scorecardElementSumValue = $scorecardElementArray[$elementCount]['value'];

                                $averageElementValue = $scorecardElementSumValue / count($allShipsArray);

                                $scorecardElementRulesArray = $em->createQueryBuilder()
                                    ->select('a.rules')
                                    ->from('InitialShippingBundle:Rules', 'a')
                                    ->where('a.elementDetailsId = :elementId')
                                    ->setParameter('elementId', $scorecardElementId)
                                    ->getQuery()
                                    ->getResult();
                                $elementResultColor = "";
                                $elementColorValue=0;

                                array_push($scorecardElementRules,$scorecardElementRulesArray);
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
                                array_push($kpiElementColorArray,$elementResultColor);
                                $elementValueWithWeight = $elementColorValue * (((int)$scorecardElementWeight) / 100);
                                $kpiSumValue+=$elementValueWithWeight;
                            }
                        }
                        else{
                            array_push($kpiElementColorArray,'false');
                        }
                        array_push($scorecardElementValueArray,$kpiSumValue);
                        $scorecardKpiRulesArray = $em->createQueryBuilder()
                            ->select('a.rules')
                            ->from('InitialShippingBundle:KpiRules', 'a')
                            ->where('a.kpiDetailsId = :kpiId')
                            ->setParameter('kpiId', $scorecardKpiId)
                            ->getQuery()
                            ->getResult();

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
                        array_push($scorecardKpiColorArray,$kpiResultColor);
                        $monthlyScorecardKpiWeightAverageValueTotal+=$kpiSumValue*($scorecardKpiWeight/100);
                    }
                }
                array_push($monthlyScorecardKpiColorArray,$scorecardKpiColorArray);
                array_push($monthlyKpiAverageValueTotal,$monthlyScorecardKpiWeightAverageValueTotal);
                array_push($monthlyElementColorArray,$kpiElementColorArray);
                array_push($monthlyElementValueArray,$scorecardElementValueArray);
            }

            $series = array
            (
                array("name" => "$scorecardKpiName",'showInLegend'=> false, 'color' => 'blue', "data" => $monthlyElementValueArray),

            );

            $ob = new Highchart();
            $ob->chart->renderTo('area');
            $ob->chart->type('line');
            $ob->title->text('Star Systems Reporting Tool ', array('style' => array('color' => 'red')));
            /*$ob->subtitle->text($shipname);
            $ob->subtitle->style(array('color' => '#0000f0', 'fontWeight' => 'bold'));*/
            $ob->xAxis->categories($monthLetterArray);
            $ob->xAxis->labels(array('style' => array('color' => '#0000F0')));
            $ob->series($series);
            $ob->plotOptions->series(array('allowPointSelect' => true, 'dataLabels' => array('enabled' => true)));
            $ob->exporting->enabled(false);
            //$ob->plotOptions->area(array('pointStart'=>0,'marker'=>array('enabled'=>false,'symbol'=>'circle','radius'=>2,'states'=>array('hover'=>array('enabled'=>false)))));

            $commentForElementKpi = $em->createQueryBuilder()
                ->select('a.comment')
                ->from('InitialShippingBundle:SendCommand', 'a')
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
                    'listofelement' => $scorecardElementArray,
                    'montharray' => $monthLetterArray,
                    'elementcolorarray' => $monthlyElementColorArray,
                    'countmonth' => count($monthlyElementColorArray),
                    'kpiid' => $kpiid,
                    'kpiname'=>$scorecardKpiName,
                    'commentarray' => $commentForElementKpi,
                    'kpi_color' => $monthlyScorecardKpiColorArray,
                    'elementRule' => $scorecardElementRules

                );
            }


            return $this->render(
                'InitialShippingBundle:DashBorad:elementforkpi.html.twig',
                array(
                    'listofelement' => $scorecardElementArray,
                    'kpiname' => $scorecardKpiName,
                    'chart' => $ob,
                    'montharray' => $monthLetterArray,
                    'elementcolorarray' => $monthlyElementColorArray,
                    'countmonth' => count($monthlyElementColorArray),
                    'kpiid' => $kpiid,
                    'commentarray' => $commentForElementKpi,
                    'kpi_color' => $monthlyScorecardKpiColorArray,
                    'elementRule' => $scorecardElementRules
                )
            );

        }
        else
        {
            return $this->redirectToRoute('fos_user_security_login');
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
     * @Route("/{shipid}/{year}/listallkpiforship_ranking", name="listallkpiforship_ranking")
     */
    public function listallkpiforship_rankingAction($shipid,$year='',Request $request,$mode='')
    {
        $newShipId=$shipid;
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if($user==null)
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
        else {
            $userId = $user->getId();
            $username = $user->getUsername();
            $loginuseremail = $user->getEmail();
            $oneyear_montharray = array();
            if ($year == ' ') {
                for ($m = 1; $m <= 12; $m++) {
                    $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date('Y')));
                    array_push($oneyear_montharray, $month);
                }
                $currentyear = date('Y');
            }
            if ($year != ' ') {
                for ($m = 1; $m <= 12; $m++) {
                    $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date($year)));
                    array_push($oneyear_montharray, $month);
                }
                $currentyear = date($year);
            }
            $rankingKpiList = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id', 'b.weightage')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.shipDetailsId = :shipid')
                ->setParameter('shipid', $shipid)
                ->getQuery()
                ->getResult();
            $monthlyKpiValue = array();
            $newcategories = array();
            $monthlyKpiAverageScore = array();
            for ($d = 0; $d < count($oneyear_montharray); $d++)
            {
                $time2 = strtotime($oneyear_montharray[$d]);
                $monthinletter = date('M', $time2);
                array_push($newcategories, $monthinletter);
                $new_monthdetail_date = new \DateTime($oneyear_montharray[$d]);
                $new_monthdetail_date->modify('last day of this month');
                $rankingKpiValueCountArray = array();
                $rankingKpiWeightarray = array();
                for($rankingKpiCount=0;$rankingKpiCount<count($rankingKpiList);$rankingKpiCount++)
                {
                    $rankingElementValueTotal = 0;
                    $rankingKpiId = $rankingKpiList[$rankingKpiCount]['id'];
                    $rankingKpiWeight = $rankingKpiList[$rankingKpiCount]['weightage'];
                    $rankingKpiName = $rankingKpiList[$rankingKpiCount]['kpiName'];
                    array_push($rankingKpiWeightarray,$rankingKpiWeight);

                    $rankingElementList = $em->createQueryBuilder()
                        ->select('c.id','c.elementName', 'c.weightage', 'a.value')
                        ->from('InitialShippingBundle:RankingElementDetails', 'c')
                        ->join('InitialShippingBundle:RankingMonthlyData', 'a', 'with',  'c.id = a.elementDetailsId')
                        ->where('c.kpiDetailsId = :kpiid and a.monthdetail = :datamonth and a.status = :rankingStatusValue and a.shipDetailsId = :shipId')
                        ->setParameter('kpiid', $rankingKpiId)
                        ->setParameter('datamonth', $new_monthdetail_date )
                        ->setParameter('rankingStatusValue',3)
                        ->setParameter('shipId',$shipid)
                        ->getQuery()
                        ->getResult();

                    if(count($rankingElementList)>0)
                    {
                        for($rankingElementCount=0;$rankingElementCount<count($rankingElementList);$rankingElementCount++)
                        {
                            $rankingElementName=$rankingElementList[$rankingElementCount]['elementName'];
                            $rankingElementId=$rankingElementList[$rankingElementCount]['id'];
                            $rankingElementWeight = $rankingElementList[$rankingElementCount]['weightage'];
                            $rankingElementValue = $rankingElementList[$rankingElementCount]['value'];

                            $rankingElementRulesList = $em->createQueryBuilder()
                                ->select('a.rules')
                                ->from('InitialShippingBundle:RankingRules', 'a')
                                ->where('a.elementDetailsId = :element_id')
                                ->setParameter('element_id', $rankingElementId)
                                ->getQuery()
                                ->getResult();

                            $rankingElementResultColor = "";
                            $rankingElementColorValue = 0;

                            for($elementRuleCount=0;$elementRuleCount<count($rankingElementRulesList);$elementRuleCount++)
                            {
                                $rankingElementRule = $rankingElementRulesList[$elementRuleCount];
                                $rankingJsFileDirectory = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_findcolornode_3.js \'' . $rankingElementRule['rules'] . ' \' ' . $rankingElementValue;
                                $rankingJsFileName = 'node ' . $rankingJsFileDirectory;
                                $handle = popen($rankingJsFileName, 'r');
                                $resultColor = fread($handle, 2096);
                                $rankingElementResultColor = str_replace("\n", '', $resultColor);

                                if ($rankingElementResultColor == "false")
                                {
                                    continue;
                                }

                                if($rankingElementResultColor=='Green')
                                {
                                    $rankingElementColorValue = $rankingElementWeight;
                                    break;
                                }
                                else if($rankingElementResultColor == 'Yellow')
                                {
                                    $rankingElementColorValue = $rankingElementWeight/2;
                                    break;
                                }
                                else if($rankingElementResultColor == 'Red')
                                {
                                    $rankingElementColorValue = 0;
                                    break;
                                }
                            }
                            $rankingElementValueTotal+=$rankingElementColorValue;
                        }
                    }
                    if(count($rankingElementList)==0)
                    {
                        $newkpiid = $em->createQueryBuilder()
                            ->select('b.id')
                            ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                            ->where('b.kpiName = :kpiName')
                            ->setParameter('kpiName', $rankingKpiName)
                            ->groupby('b.kpiName')
                            ->getQuery()
                            ->getResult();
                        $rankingElementList = $em->createQueryBuilder()
                            ->select('c.id','c.elementName', 'c.weightage', 'a.value')
                            ->from('InitialShippingBundle:RankingElementDetails', 'c')
                            ->join('InitialShippingBundle:RankingMonthlyData', 'a', 'with',  'c.id = a.elementDetailsId')
                            ->where('c.kpiDetailsId = :kpiid and a.monthdetail = :datamonth and a.status = :rankingStatusValue and a.shipDetailsId = :shipId')
                            ->setParameter('kpiid', $newkpiid[0]['id'])
                            ->setParameter('datamonth', $new_monthdetail_date )
                            ->setParameter('rankingStatusValue',3)
                            ->setParameter('shipId',$shipid)
                            ->getQuery()
                            ->getResult();

                        for($rankingElementCount=0;$rankingElementCount<count($rankingElementList);$rankingElementCount++)
                        {
                            $rankingElementName=$rankingElementList[$rankingElementCount]['elementName'];
                            $rankingElementId=$rankingElementList[$rankingElementCount]['id'];
                            $rankingElementWeight = $rankingElementList[$rankingElementCount]['weightage'];
                            $rankingElementValue = $rankingElementList[$rankingElementCount]['value'];

                            $rankingElementRulesList = $em->createQueryBuilder()
                                ->select('a.rules')
                                ->from('InitialShippingBundle:RankingRules', 'a')
                                ->where('a.elementDetailsId = :element_id')
                                ->setParameter('element_id', $rankingElementId)
                                ->getQuery()
                                ->getResult();

                            $rankingElementResultColor = "";
                            $rankingElementColorValue = 0;

                            for($elementRuleCount=0;$elementRuleCount<count($rankingElementRulesList);$elementRuleCount++)
                            {
                                $rankingElementRule = $rankingElementRulesList[$elementRuleCount];
                                $rankingJsFileDirectory = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_findcolornode_3.js \'' . $rankingElementRule['rules'] . ' \' ' . $rankingElementValue;
                                $rankingJsFileName = 'node ' . $rankingJsFileDirectory;
                                $handle = popen($rankingJsFileName, 'r');
                                $resultColor = fread($handle, 2096);
                                $rankingElementResultColor = str_replace("\n", '', $resultColor);

                                if ($rankingElementResultColor == "false")
                                {
                                    continue;
                                }

                                if($rankingElementResultColor=='Green')
                                {
                                    $rankingElementColorValue = $rankingElementWeight;
                                    break;
                                }
                                else if($rankingElementResultColor == 'Yellow')
                                {
                                    $rankingElementColorValue = $rankingElementWeight/2;
                                    break;
                                }
                                else if($rankingElementResultColor == 'Red')
                                {
                                    $rankingElementColorValue = 0;
                                    break;
                                }
                            }
                            $rankingElementValueTotal+=$rankingElementColorValue;
                        }
                    }

                    array_push($rankingKpiValueCountArray,($rankingElementValueTotal*$rankingKpiWeight/100));
                }
                array_push($monthlyKpiValue,$rankingKpiValueCountArray);
                array_push($monthlyKpiAverageScore,array_sum($rankingKpiValueCountArray));
            }
            $shipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
            $shipname = $shipid->getShipName();
            $man_year= $shipid->getManufacturingYear();
            if($man_year=="")
            {
                $yearcount=0;
            }
            else
            {
                $currentdatestring=date('Y-01-01');
                $d1 = new \DateTime($currentdatestring);
                $man_datestring=$man_year.'-01-'.'01';
                $d2=new \DateTime($man_datestring);
                $diff = $d2->diff($d1);
                $yearcount=$diff->y+1;
            }

            $series = array(
                array("name" => "$shipname",'showInLegend'=> false, 'color' => 'blue', "data" => $monthlyKpiAverageScore)
            );
            $ob = new Highchart();
            $ob->chart->renderTo('area');
            $ob->chart->type('line');
            $ob->title->text($shipname, array('style' => array('color' => 'red')));
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
            if($mode=='pdftemplate_shiplevel')
            {
                return array
                (
                    'listofkpi'=>$rankingKpiList,
                    'kpiweightage'=>$rankingKpiWeightarray,
                    'montharray'=>$newcategories,
                    'avgscore'=>$monthlyKpiAverageScore,
                    'commentarray'=>$listofcomment,
                    'kpimonthdata'=>$monthlyKpiValue,
                    'currentyear'=>$currentyear,
                    'ageofvessel'=>$yearcount
                );
            }
            if($mode=='pdftemplate_rankingoverall_shiplevel')
            {
                return array
                (
                    'listofkpi'=>$rankingKpiList,
                    'kpiweightage'=>$rankingKpiWeightarray,
                    'montharray'=>$newcategories,
                    'avgscore'=>$monthlyKpiAverageScore,
                    'commentarray'=>$listofcomment,
                    'kpimonthdata'=>$monthlyKpiValue,
                    'currentyear'=>$currentyear,
                    'chart' => $ob,
                    'shipname' => $shipname,
                    'ageofvessel'=>$yearcount
                );
            }


             return $this->render(
                'InitialShippingBundle:DashBorad:listallkpiforship_ranking.html.twig',
                array(
                    'listofkpi' => $rankingKpiList,
                    'kpiweightage' => $rankingKpiWeightarray,
                    'montharray' => $newcategories,
                    'shipname' => $shipname,
                    'chart' => $ob,
                    'countmonth' => count($newcategories),
                    'avgscore' => $monthlyKpiAverageScore,
                    'commentarray'=>$listofcomment,
                    'shipid'=>$shipid->getId(),
                    'kpimonthdata'=>$monthlyKpiValue,
                    'currentyear'=>$currentyear,
                    'ageofvessel'=>$yearcount
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
        if($user!=null)
        {
            $userName = $user->getUsername();
            $email = $user->getEmail();

            $kpiDetailObject = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id' => $kpiid));
            $kpiName=$kpiDetailObject->getKpiName();
            $kpiWeight = $kpiDetailObject->getWeightage();
            $monthDetails = array();
            for ($m = 1; $m <= 12; $m++) {
                $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date('Y')));
                array_push($monthDetails, $month);
            }
            $elementForKpiList = $em->createQueryBuilder()
                ->select('a.elementName', 'a.id', 'a.weightage')
                ->from('InitialShippingBundle:RankingElementDetails', 'a')
                ->where('a.kpiDetailsId = :kpiid')
                ->setParameter('kpiid', $kpiid)
                ->getQuery()
                ->getResult();
            if(count($elementForKpiList)!=0)
            {
                $shipidarray = $em->createQueryBuilder()
                ->select('identity(b.shipDetailsId)')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.id = :kpiid')
                ->setParameter('kpiid', $kpiid)
                ->getQuery()
                ->getResult();
            $shipId = $shipidarray[0][1];

            $monthNameLetter = array();
            $monthlyKpiAverageValueTotal = array();
            $monthlyElementColorArray = array();
            $monthlyElementValueArray = array();
            for($monthCount=0;$monthCount<count($monthDetails);$monthCount++)
            {
                $scorecardElementValueArray = array();
                $kpiElementColorArray = array();
                $scorecardElementRules = array();
                $kpiSumValue =0;
                $time2 = strtotime($monthDetails[$monthCount]);
                $monthInLetter = date('M', $time2);
                array_push($monthNameLetter, $monthInLetter);
                $new_monthdetail_date = new \DateTime($monthDetails[$monthCount]);
                $new_monthdetail_date->modify('last day of this month');

                for($elementCount=0;$elementCount<count($elementForKpiList);$elementCount++)
                {
                    $scorecardElementId = $elementForKpiList[$elementCount]['id'];
                    $scorecardElementWeight = $elementForKpiList[$elementCount]['weightage'];

                    $elementDbValue = $em->createQueryBuilder()
                        ->select('a.value')
                        ->from('InitialShippingBundle:RankingMonthlyData', 'a')
                        ->where('a.elementDetailsId = :elementId and a.monthdetail = :monthName and a.shipDetailsId = :shipId and a.kpiDetailsId = :kpiId and a.status = :statusvalue')
                        ->setParameter('elementId', $scorecardElementId)
                        ->setParameter('monthName',$new_monthdetail_date)
                        ->setParameter('shipId',$shipId)
                        ->setParameter('statusvalue',3)
                        ->setParameter('kpiId',$kpiid)
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

                    array_push($scorecardElementRules,$rankingElementRulesArray);
                    array_push($scorecardElementValueArray,$elementDbValue[0]['value']);
                    array_push($kpiElementColorArray,$elementResultColor);
                    $elementValueWithWeight = $elementColorValue ;
                    $kpiSumValue+=$elementValueWithWeight;
                }
                array_push($monthlyKpiAverageValueTotal,($kpiSumValue*$kpiWeight)/100);
                array_push($monthlyElementColorArray,$kpiElementColorArray);
                array_push($monthlyElementValueArray,$scorecardElementValueArray);
            }

                $series = array
                (
                    array("name" => "$kpiName",'showInLegend'=> false, 'color' => 'blue', "data" => $monthlyKpiAverageValueTotal),

                );
                $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipId));
                $shipname = $newshipid->getShipName();

                $ob = new Highchart();
                $ob->chart->renderTo('area');
                $ob->chart->type('line');
                $ob->title->text($kpiName, array('style' => array('color' => 'red')));
                $ob->subtitle->style(array('color' => '#0000f0', 'fontWeight' => 'bold'));
                $ob->xAxis->categories($monthNameLetter);
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

                if($mode=='pdftemplate_kpilevel')
                {
                    return array(
                        'elementcolorarray'=>$monthlyElementColorArray,
                        'listofelement'=>$elementForKpiList,
                        'montharray'=>$monthNameLetter,
                        'avgscore'=>$monthlyKpiAverageValueTotal,
                        'commentarray'=>$listofcomment,
                        'monthlydata'=>$monthlyElementValueArray,
                        'elementRule' => $scorecardElementRules

                    );
                }

                return $this->render(
                    'InitialShippingBundle:DashBorad:elementforkpi_ranking.html.twig',
                    array(
                        'listofelement' => $elementForKpiList,
                        'kpiname' => $kpiName,
                        'chart' => $ob,
                        'shipname' => $shipname,
                        'monthdetails' => $monthNameLetter,
                        'elementcolorarray' => $monthlyElementColorArray,
                        'countmonth' => count($monthNameLetter),
                        'avgscore' => $monthlyKpiAverageValueTotal,
                        'kpiid' => $kpiid,
                        'commentarray' => $listofcomment,
                        'shipid'=>$shipId,
                        'monthlydata'=>$monthlyElementValueArray,
                        'elementRule' => $scorecardElementRules
                    )
                );
            }


            else
            {
                $shipidarray = $em->createQueryBuilder()
                    ->select('identity(b.shipDetailsId)')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                    ->where('b.id = :kpiid')
                    ->setParameter('kpiid', $kpiid)
                    ->getQuery()
                    ->getResult();
                $shipId = $shipidarray[0][1];
                $newkpiid = $em->createQueryBuilder()
                    ->select('b.id')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiName)
                    ->groupby('b.kpiName')
                    ->getQuery()
                    ->getResult();
                $elementForKpiList = $em->createQueryBuilder()
                    ->select('a.elementName', 'a.id', 'a.weightage')
                    ->from('InitialShippingBundle:RankingElementDetails', 'a')
                    ->where('a.kpiDetailsId = :kpiid')
                    ->setParameter('kpiid', $newkpiid[0]['id'])
                    ->getQuery()
                    ->getResult();

                $monthNameLetter = array();
                $monthlyKpiAverageValueTotal = array();
                $monthlyElementColorArray = array();
                $monthlyElementValueArray = array();
                for($monthCount=0;$monthCount<count($monthDetails);$monthCount++)
                {
                    $scorecardElementValueArray = array();
                    $kpiElementColorArray = array();
                    $scorecardElementRules = array();
                    $kpiSumValue =0;
                    $time2 = strtotime($monthDetails[$monthCount]);
                    $monthInLetter = date('M', $time2);
                    array_push($monthNameLetter, $monthInLetter);
                    $new_monthdetail_date = new \DateTime($monthDetails[$monthCount]);
                    $new_monthdetail_date->modify('last day of this month');

                    for($elementCount=0;$elementCount<count($elementForKpiList);$elementCount++)
                    {
                        $scorecardElementId = $elementForKpiList[$elementCount]['id'];
                        $scorecardElementWeight = $elementForKpiList[$elementCount]['weightage'];

                        $elementDbValue = $em->createQueryBuilder()
                            ->select('a.value')
                            ->from('InitialShippingBundle:RankingMonthlyData', 'a')
                            ->where('a.elementDetailsId = :elementId and a.monthdetail = :monthName and a.shipDetailsId = :shipId and a.kpiDetailsId = :kpiId and a.status = :statusvalue')
                            ->setParameter('elementId', $scorecardElementId)
                            ->setParameter('monthName',$new_monthdetail_date)
                            ->setParameter('shipId',$shipId)
                            ->setParameter('statusvalue',3)
                            ->setParameter('kpiId',$newkpiid[0]['id'])
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

                        array_push($scorecardElementRules,$rankingElementRulesArray);
                        array_push($scorecardElementValueArray,$elementDbValue[0]['value']);
                        array_push($kpiElementColorArray,$elementResultColor);
                        $elementValueWithWeight = $elementColorValue ;
                        $kpiSumValue+=$elementValueWithWeight;
                    }
                    array_push($monthlyKpiAverageValueTotal,($kpiSumValue*$kpiWeight)/100);
                    array_push($monthlyElementColorArray,$kpiElementColorArray);
                    array_push($monthlyElementValueArray,$scorecardElementValueArray);
                }

                $series = array
                (
                    array("name" => "$kpiName",'showInLegend'=> false, 'color' => 'blue', "data" => $monthlyKpiAverageValueTotal),

                );
                $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipId));
                $shipname = $newshipid->getShipName();

                $ob = new Highchart();
                $ob->chart->renderTo('area');
                $ob->chart->type('line');
                $ob->title->text($kpiName, array('style' => array('color' => 'red')));
                $ob->subtitle->style(array('color' => '#0000f0', 'fontWeight' => 'bold'));
                $ob->xAxis->categories($monthNameLetter);
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

                if($mode=='pdftemplate_kpilevel')
                {
                    return array(
                        'elementcolorarray'=>$monthlyElementColorArray,
                        'listofelement'=>$elementForKpiList,
                        'montharray'=>$monthNameLetter,
                        'avgscore'=>$monthlyKpiAverageValueTotal,
                        'commentarray'=>$listofcomment,
                        'monthlydata'=>$monthlyElementValueArray,
                        'elementRule' => $scorecardElementRules

                    );
                }

                return $this->render(
                    'InitialShippingBundle:DashBorad:elementforkpi_ranking.html.twig',
                    array(
                        'listofelement' => $elementForKpiList,
                        'kpiname' => $kpiName,
                        'chart' => $ob,
                        'shipname' => $shipname,
                        'monthdetails' => $monthNameLetter,
                        'elementcolorarray' => $monthlyElementColorArray,
                        'countmonth' => count($monthNameLetter),
                        'avgscore' => $monthlyKpiAverageValueTotal,
                        'kpiid' => $kpiid,
                        'commentarray' => $listofcomment,
                        'shipid'=>$shipId,
                        'monthlydata'=>$monthlyElementValueArray,
                        'elementRule' => $scorecardElementRules
                    )
                );


            }

        }
        else
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }
    /**
     * Add comment for kpireports_scorecard
     *
     * @Route("/addcommentkpi_scorecard", name="addcommentkpi_scorecard")
     */
    public function addcomment_scorecardAction(Request $request)
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
        $newkpiid = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $kpiid));
        $kpiname=$newkpiid->getKpiName();
        $returnvaluefrommonth = $this->listallelementforkpiAction($kpiid,$request,'pdftemplate_kpilevel');


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


        $customerListDesign = $this->renderView('InitialShippingBundle:DashBorad:pdfreporttemplate_scorecard_kpi.html.twig', array(
            'link' => $filename,
            'screenName' => $screenName,
            'userName' => '',
            'date' => $date,
            'listofelement'=>$returnvaluefrommonth['listofelement'],
            'kpiname'=>$kpiname,
            'montharray'=>$returnvaluefrommonth['montharray'],
            'elementcolorarray'=>$returnvaluefrommonth['elementcolorarray'],
            'countmonth'=>count($returnvaluefrommonth['elementcolorarray']),
            'commentarray'=>$listofcommentarray,
            'datetime'=>$today,
            'kpi_color'=>$returnvaluefrommonth['kpi_color'],
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
            $entityobject = $em->getRepository('InitialShippingBundle:SendCommand')->find($kpiandelementids);
            $entityobject->setClientemail($clientemailid);
            $entityobject->setFilename($pdffilenamearray[0].'.pdf');
            $em->flush();
        }

        $response = new JsonResponse();
        $response->setData(array('updatemsg'=>"Report Has Been Send"));
        return $response;
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
        $returnvaluefrommonth = $this->listallkpiforship_rankingAction($kpiid,$year=' ',$request,'pdftemplate_shiplevel');
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
            'kpiweightage' => $returnvaluefrommonth['kpiweightage'],
            'montharray' => $returnvaluefrommonth['montharray'],
            'shipname' => $kpiname,
            'countmonth' => count($returnvaluefrommonth['montharray']),
            'avgscore' => $returnvaluefrommonth['avgscore'],
            'ageofvessel'=>$returnvaluefrommonth['ageofvessel'],
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
            $entityobject = $em->getRepository('InitialShippingBundle:SendCommandRanking')->findOneBy(array('id' => $kpiandelementids));
            $entityobject->setClientemail($clientemailid);
            $entityobject->setFilename($pdffilenamearray[0].'.pdf');
            $em->flush();
        }

        $response = new JsonResponse();
        $response->setData(array('updatemsg'=>"Report Has Been Send"));
        return $response;
    }
    /**
     * Reports For Ranking
     *
     * @Route("/ranking_reports", name="ranking_reports")
     */
    public function ranking_reportsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null)
        {
            $userId = $user->getId();
            $userName = $user->getUsername();
            if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
            {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName','a.id', 'a.manufacturingYear')
                    ->from('InitialShippingBundle:ShipDetails','a')
                    ->join('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = a.companyDetailsId')
                    ->where('b.adminName = :username')
                    ->setParameter('username',$userName)
                    ->getQuery();
            }
            else
            {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName','a.id', 'a.manufacturingYear')
                    ->from('InitialShippingBundle:ShipDetails','a')
                    ->leftjoin('InitialShippingBundle:User','b', 'WITH', 'b.companyid = a.companyDetailsId')
                    ->where('b.id = :userId')
                    ->setParameter('userId',$userId)
                    ->getQuery();
            }
            $series = array(
                array("name" => "",'showInLegend'=> false, 'color' => 'blue', "data" => array())
            );
            $ob = new Highchart();
            $ob->chart->renderTo('area');
            $ob->chart->type('line');
            $ob->title->text('', array('style' => array('color' => 'red')));
            $ob->subtitle->style(array('color' => '#0000f0', 'fontWeight' => 'bold'));
            $ob->xAxis->categories(array());
            $ob->xAxis->labels(array('style' => array('color' => '#0000F0')));
            $ob->yAxis->max(100);
            $ob->yAxis->title(array('text'=>'Values','style'=>array('color'=>'#0000F0')));
            $ob->series($series);
            $ob->plotOptions->series(array('allowPointSelect' => true, 'dataLabels' => array('enabled' => true)));
            $ob->exporting->enabled(false);

            $listAllShipForCompany = $query->getResult();
            return $this->render(
                'InitialShippingBundle:DashBorad:report_ranking.html.twig',
                array('listofships'=>$listAllShipForCompany,'chart'=>$ob)
            );

        }
        else
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }
    /**
     * Reports For Ranking
     *
     * @Route("/view_ranking_reports", name="view_ranking_reports")
     */
    public function view_ranking_reportsAction(Request $request,$mode='')
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null)
        {
            $userId = $user->getId();
            $userName = $user->getUsername();
            $shipid = $request->request->get('shipid');
            $year=$request->request->get('year');
            $today = date("Y-m-d H:i:s");
            $pageName = $request->query->get('page');
            $screenName = $this->get('translator')->trans($pageName);
            $date = date('l jS F Y h:i', time());
            $route = $request->attributes->get('_route');
            $oneyear_montharray = array();
            if ($year == ' ') {
                for ($m = 1; $m <= 12; $m++) {
                    $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date('Y')));
                    array_push($oneyear_montharray, $month);
                }
                $currentyear = date('Y');
            }
            if ($year != ' ') {
                for ($m = 1; $m <= 12; $m++) {
                    $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date($year)));
                    array_push($oneyear_montharray, $month);
                }
                $currentyear = date($year);
            }
            $rankingKpiList = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id', 'b.weightage')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.shipDetailsId = :shipid')
                ->setParameter('shipid', $shipid)
                ->getQuery()
                ->getResult();
            $monthlyKpiValue = array();
            $newcategories = array();
            $monthlyKpiAverageScore = array();
            $monthlyKpiAverageValueTotal = array();
            //$monthlyElementColorArray = array();
            //$monthlyElementValueArray = array();
            $ElementName_Weightage=array();
            // $monthlykpicolorarray=array();
            $dataforgraphforship=array();
            $NewMonthlyKPIValue=array();
            $NewMonthlyAvgTotal=array();
            $NewMonthColor=array();


            for ($d = 0; $d < count($oneyear_montharray); $d++)
            {
                $time2 = strtotime($oneyear_montharray[$d]);
                $monthinletter = date('M', $time2);
                array_push($newcategories, $monthinletter);
                $new_monthdetail_date = new \DateTime($oneyear_montharray[$d]);
                $new_monthdetail_date->modify('last day of this month');
                $scorecardElementRules = array();
                $scorecardElementValueArray = array();
                $rankingKpiValueCountArray = array();
                $rankingKpiWeightarray = array();
                $Newkpivalue=array();
                $NewKpiAvg=array();
                $NewKpiColor=array();


                for($rankingKpiCount=0;$rankingKpiCount<count($rankingKpiList);$rankingKpiCount++)
                {
                    $rankingElementValueTotal = 0;
                    $rankingKpiId = $rankingKpiList[$rankingKpiCount]['id'];
                    $rankingKpiWeight = $rankingKpiList[$rankingKpiCount]['weightage'];
                    $rankingKpiName = $rankingKpiList[$rankingKpiCount]['kpiName'];
                    array_push($rankingKpiWeightarray,$rankingKpiWeight);
                    $elementForKpiList = $em->createQueryBuilder()
                        ->select('a.elementName', 'a.id', 'a.weightage')
                        ->from('InitialShippingBundle:RankingElementDetails', 'a')
                        ->where('a.kpiDetailsId = :kpiid')
                        ->setParameter('kpiid', $rankingKpiId)
                        ->getQuery()
                        ->getResult();

                    $kpiSumValue =0;
                    // $Element_Color_Array=array();
                    $NewElementColor=array();
                    $Elment_Value=array();
                    if(count($elementForKpiList)>0)
                    {
                        if($d==0)
                        {
                            $ElementName_Weightage[$rankingKpiId]=$elementForKpiList;
                        }
                        for($elementCount=0;$elementCount<count($elementForKpiList);$elementCount++)
                        {

                            $scorecardElementId = $elementForKpiList[$elementCount]['id'];
                            $scorecardElementWeight = $elementForKpiList[$elementCount]['weightage'];

                            $elementDbValue = $em->createQueryBuilder()
                                ->select('a.value')
                                ->from('InitialShippingBundle:RankingMonthlyData', 'a')
                                ->where('a.elementDetailsId = :elementId and a.monthdetail = :monthName and a.shipDetailsId = :shipId and a.kpiDetailsId = :kpiId and a.status = :statusvalue')
                                ->setParameter('elementId', $scorecardElementId)
                                ->setParameter('monthName',$new_monthdetail_date)
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
                                array_push($scorecardElementRules,$rankingElementRulesArray);
                                array_push($scorecardElementValueArray,$elementDbValue[0]['value']);
                                $elementValueWithWeight = $elementColorValue ;
                                $kpiSumValue+=$elementValueWithWeight;
                                $rankingElementValueTotal+=$elementColorValue;
                                // array_push($Element_Color_Array,$elementResultColor);
                                array_push($Elment_Value,$elementDbValue[0]['value']);
                                array_push($NewElementColor,$elementResultColor);


                            }
                            else
                            {
                                $elementDbValue[0]['value']=null;
                                array_push($scorecardElementRules,$rankingElementRulesArray);
                                array_push($scorecardElementValueArray,$elementDbValue[0]['value']);
                                $elementValueWithWeight = $elementColorValue ;
                                $kpiSumValue+=$elementValueWithWeight;
                                $rankingElementValueTotal=105;
                                //  array_push($Element_Color_Array,$elementResultColor);
                                array_push($Elment_Value,$elementDbValue[0]['value']);
                                array_push($NewElementColor,$elementResultColor);

                            }


                        }
                        array_push($monthlyKpiAverageValueTotal,($kpiSumValue*$rankingKpiWeight)/100);
                        //array_push($NewKpiAvg,($kpiSumValue*$rankingKpiWeight)/100);
                        $NewKpiAvg[$rankingKpiId]=($kpiSumValue*$rankingKpiWeight)/100;
                        //  array_push($monthlyElementValueArray,$scorecardElementValueArray);
                        if($rankingElementValueTotal !=105)
                        {
                            array_push($rankingKpiValueCountArray,($rankingElementValueTotal*$rankingKpiWeight/100));
                        }
                        else
                        {
                            array_push($rankingKpiValueCountArray,null);
                        }

                        // array_push($monthlykpicolorarray,$Element_Color_Array);
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

                        if($d==0)
                        {
                            $ElementName_Weightage[$rankingKpiId]=$elementForKpiList;
                        }


                        for($elementCount=0;$elementCount<count($elementForKpiList);$elementCount++)
                        {

                            $scorecardElementId = $elementForKpiList[$elementCount]['id'];
                            $scorecardElementWeight = $elementForKpiList[$elementCount]['weightage'];

                            $elementDbValue = $em->createQueryBuilder()
                                ->select('a.value')
                                ->from('InitialShippingBundle:RankingMonthlyData', 'a')
                                ->where('a.elementDetailsId = :elementId and a.monthdetail = :monthName and a.shipDetailsId = :shipId and a.kpiDetailsId = :kpiId and a.status = :statusvalue')
                                ->setParameter('elementId', $scorecardElementId)
                                ->setParameter('monthName',$new_monthdetail_date)
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
                                array_push($scorecardElementRules,$rankingElementRulesArray);
                                array_push($scorecardElementValueArray,$elementDbValue[0]['value']);
                                $elementValueWithWeight = $elementColorValue ;
                                $kpiSumValue+=$elementValueWithWeight;
                                $rankingElementValueTotal+=$elementColorValue;
                                // array_push($Element_Color_Array,$elementResultColor);
                                array_push($Elment_Value,$elementDbValue[0]['value']);
                                array_push($NewElementColor,$elementResultColor);


                            }
                            else
                            {
                                $elementDbValue[0]['value']=null;
                                array_push($scorecardElementRules,$rankingElementRulesArray);
                                array_push($scorecardElementValueArray,$elementDbValue[0]['value']);
                                $elementValueWithWeight = $elementColorValue ;
                                $kpiSumValue+=$elementValueWithWeight;
                                $rankingElementValueTotal=105;
                                //  array_push($Element_Color_Array,$elementResultColor);
                                array_push($Elment_Value,$elementDbValue[0]['value']);
                                array_push($NewElementColor,$elementResultColor);

                            }


                        }
                        array_push($monthlyKpiAverageValueTotal,($kpiSumValue*$rankingKpiWeight)/100);
                        //array_push($NewKpiAvg,($kpiSumValue*$rankingKpiWeight)/100);
                        $NewKpiAvg[$rankingKpiId]=($kpiSumValue*$rankingKpiWeight)/100;
                        //  array_push($monthlyElementValueArray,$scorecardElementValueArray);
                        if($rankingElementValueTotal !=105)
                        {
                            array_push($rankingKpiValueCountArray,($rankingElementValueTotal*$rankingKpiWeight/100));
                        }
                        else
                        {
                            array_push($rankingKpiValueCountArray,null);
                        }
                    }
                    // array_push($Newkpivalue,$Elment_Value);
                    $Newkpivalue[$rankingKpiId]=$Elment_Value;
                    $NewKpiColor[$rankingKpiId]=$NewElementColor;


                }


                array_push($monthlyKpiValue,$rankingKpiValueCountArray);
                if(array_sum($rankingKpiValueCountArray)!=0)
                {
                    array_push($monthlyKpiAverageScore,array_sum($rankingKpiValueCountArray));
                    array_push($dataforgraphforship,array_sum($rankingKpiValueCountArray));
                }
                else if(array_sum($rankingKpiValueCountArray)==0)
                {
                    array_push($monthlyKpiAverageScore, null);
                    //array_push($dataforgraphforship,0);
                }
                //array_push($monthlykpicolorarray,$KpiColorArray);
                array_push($NewMonthlyKPIValue,$Newkpivalue);
                array_push($NewMonthlyAvgTotal,$NewKpiAvg);
                array_push($NewMonthColor,$NewKpiColor);

            }
            $New_overallfindingelementgraph=array();
            $New_overallfindingelementvalue=array();
            $New_overallfindingelementcolor=array();

            for($SplitKpiCount=0;$SplitKpiCount<count($rankingKpiList);$SplitKpiCount++)
            {
                $rankingKpiId = $rankingKpiList[$SplitKpiCount]['id'];
                $rankingKpiName = $rankingKpiList[$SplitKpiCount]['kpiName'];
                $New_Month_Avg_Total=array();
                $New_Month_Element_Value=array();
                $New_Month_Element_Color=array();
                for($New_FindKpivalueCount=0;$New_FindKpivalueCount<count($dataforgraphforship);$New_FindKpivalueCount++)
                {
                    $New_Month_Avg_Total[$New_FindKpivalueCount]=$NewMonthlyAvgTotal[$New_FindKpivalueCount][$rankingKpiId];
                    $New_Month_Element_Value[$New_FindKpivalueCount]=$NewMonthlyKPIValue[$New_FindKpivalueCount][$rankingKpiId];
                    $New_Month_Element_Color[$New_FindKpivalueCount]=$NewMonthColor[$New_FindKpivalueCount][$rankingKpiId];
                }
                $New_overallfindingelementgraph[$rankingKpiId]=$New_Month_Avg_Total;
                $New_overallfindingelementvalue[$rankingKpiId]=$New_Month_Element_Value;
                $New_overallfindingelementcolor[$rankingKpiId]=$New_Month_Element_Color;
            }


            $shipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
            $shipname = $shipid->getShipName();
            $man_year= $shipid->getManufacturingYear();
            if($man_year=="")
            {
                $yearcount=0;
            }
            else
            {
                $currentdatestring=date('Y-01-01');
                $d1 = new \DateTime($currentdatestring);
                $man_datestring=$man_year.'-01-'.'01';
                $d2=new \DateTime($man_datestring);
                $diff = $d2->diff($d1);
                $yearcount=$diff->y+1;
            }
            //$lookstatusobject = $em->getRepository('InitialShippingBundle:ShipDetails')->findBy(array('id' => $shipid,'shipName'=>$shipname,));

            $response = new JsonResponse();
            $response->setData
            (
                array(
                    'listofkpi' => $rankingKpiList,
                    'kpiweightage' => $rankingKpiWeightarray,
                    'montharray' => $newcategories,
                    'shipname' => $shipname,
                    'countmonth' => count($dataforgraphforship),
                    'avgscore' => $monthlyKpiAverageScore,
                    'shipid'=>$shipid->getId(),
                    'chartdata'=>$dataforgraphforship,
                    'kpimonthdata'=>$monthlyKpiValue,
                    'currentyear'=>$currentyear,
                    'ageofvessel'=>$yearcount,
                    'kpigraph'=>$New_overallfindingelementgraph,
                    'elementcolorarray' => $New_overallfindingelementcolor,
                    'monthlydata'=>$New_overallfindingelementvalue,
                    'elementRule' => $scorecardElementRules,
                    'listofelement' => $ElementName_Weightage,
                )
            );
            return $response;

        }
        else
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }
    /**
     * Send Reports For Ranking
     *
     * @Route("/send_rankingreports", name="send_rankingreports")
     */
    public function sendreports_rankingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null)
        {
            $userId = $user->getId();
            $userName = $user->getUsername();
            $shipid = $request->request->get('shipid');
            $year=$request->request->get('year');
            $today = date("Y-m-d H:i:s");
            $pageName = $request->query->get('page');
            $screenName = $this->get('translator')->trans($pageName);
            $date = date('l jS F Y h:i', time());
            $route = $request->attributes->get('_route');
            $oneyear_montharray = array();
            if ($year == ' ') {
                for ($m = 1; $m <= 12; $m++) {
                    $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date('Y')));
                    array_push($oneyear_montharray, $month);
                }
                $currentyear = date('Y');
            }
            if ($year != ' ') {
                for ($m = 1; $m <= 12; $m++) {
                    $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date($year)));
                    array_push($oneyear_montharray, $month);
                }
                $currentyear = date($year);
            }
            $rankingKpiList = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id', 'b.weightage')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.shipDetailsId = :shipid')
                ->setParameter('shipid', $shipid)
                ->getQuery()
                ->getResult();
            $monthlyKpiValue = array();
            $newcategories = array();
            $monthlyKpiAverageScore = array();
            $monthlyKpiAverageValueTotal = array();
            //$monthlyElementColorArray = array();
            //$monthlyElementValueArray = array();
            $ElementName_Weightage=array();
           // $monthlykpicolorarray=array();
            $dataforgraphforship=array();
            $NewMonthlyKPIValue=array();
            $NewMonthlyAvgTotal=array();
            $NewMonthColor=array();


            for ($d = 0; $d < count($oneyear_montharray); $d++)
            {
                $time2 = strtotime($oneyear_montharray[$d]);
                $monthinletter = date('M', $time2);
                array_push($newcategories, $monthinletter);
                $new_monthdetail_date = new \DateTime($oneyear_montharray[$d]);
                $new_monthdetail_date->modify('last day of this month');
                $scorecardElementRules = array();
                $scorecardElementValueArray = array();
                $rankingKpiValueCountArray = array();
                $rankingKpiWeightarray = array();
                $Newkpivalue=array();
                $NewKpiAvg=array();
                $NewKpiColor=array();


                for($rankingKpiCount=0;$rankingKpiCount<count($rankingKpiList);$rankingKpiCount++)
                {
                    $rankingElementValueTotal = 0;
                    $rankingKpiId = $rankingKpiList[$rankingKpiCount]['id'];
                    $rankingKpiWeight = $rankingKpiList[$rankingKpiCount]['weightage'];
                    $rankingKpiName = $rankingKpiList[$rankingKpiCount]['kpiName'];
                    array_push($rankingKpiWeightarray,$rankingKpiWeight);
                    $elementForKpiList = $em->createQueryBuilder()
                        ->select('a.elementName', 'a.id', 'a.weightage')
                        ->from('InitialShippingBundle:RankingElementDetails', 'a')
                        ->where('a.kpiDetailsId = :kpiid')
                        ->setParameter('kpiid', $rankingKpiId)
                        ->getQuery()
                        ->getResult();

                    $kpiSumValue =0;
                   // $Element_Color_Array=array();
                    $NewElementColor=array();
                    $Elment_Value=array();
                    if(count($elementForKpiList)>0)
                    {
                        if($d==0)
                        {
                            $ElementName_Weightage[$rankingKpiId]=$elementForKpiList;
                        }
                        for($elementCount=0;$elementCount<count($elementForKpiList);$elementCount++)
                        {

                            $scorecardElementId = $elementForKpiList[$elementCount]['id'];
                            $scorecardElementWeight = $elementForKpiList[$elementCount]['weightage'];

                            $elementDbValue = $em->createQueryBuilder()
                                ->select('a.value')
                                ->from('InitialShippingBundle:RankingMonthlyData', 'a')
                                ->where('a.elementDetailsId = :elementId and a.monthdetail = :monthName and a.shipDetailsId = :shipId and a.kpiDetailsId = :kpiId and a.status = :statusvalue')
                                ->setParameter('elementId', $scorecardElementId)
                                ->setParameter('monthName',$new_monthdetail_date)
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
                                array_push($scorecardElementRules,$rankingElementRulesArray);
                                array_push($scorecardElementValueArray,$elementDbValue[0]['value']);
                                $elementValueWithWeight = $elementColorValue ;
                                $kpiSumValue+=$elementValueWithWeight;
                                $rankingElementValueTotal+=$elementColorValue;
                               // array_push($Element_Color_Array,$elementResultColor);
                                array_push($Elment_Value,$elementDbValue[0]['value']);
                                array_push($NewElementColor,$elementResultColor);


                            }
                            else
                            {
                                $elementDbValue[0]['value']=null;
                                array_push($scorecardElementRules,$rankingElementRulesArray);
                                array_push($scorecardElementValueArray,$elementDbValue[0]['value']);
                                $elementValueWithWeight = $elementColorValue ;
                                $kpiSumValue+=$elementValueWithWeight;
                                $rankingElementValueTotal=105;
                              //  array_push($Element_Color_Array,$elementResultColor);
                                array_push($Elment_Value,$elementDbValue[0]['value']);
                                array_push($NewElementColor,$elementResultColor);

                            }


                        }
                        array_push($monthlyKpiAverageValueTotal,($kpiSumValue*$rankingKpiWeight)/100);
                        //array_push($NewKpiAvg,($kpiSumValue*$rankingKpiWeight)/100);
                        $NewKpiAvg[$rankingKpiId]=($kpiSumValue*$rankingKpiWeight)/100;
                      //  array_push($monthlyElementValueArray,$scorecardElementValueArray);
                        if($rankingElementValueTotal !=105)
                        {
                            array_push($rankingKpiValueCountArray,($rankingElementValueTotal*$rankingKpiWeight/100));
                        }
                        else
                        {
                            array_push($rankingKpiValueCountArray,null);
                        }

                       // array_push($monthlykpicolorarray,$Element_Color_Array);
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

                        if($d==0)
                        {
                            $ElementName_Weightage[$rankingKpiId]=$elementForKpiList;
                        }


                        for($elementCount=0;$elementCount<count($elementForKpiList);$elementCount++)
                        {

                            $scorecardElementId = $elementForKpiList[$elementCount]['id'];
                            $scorecardElementWeight = $elementForKpiList[$elementCount]['weightage'];

                            $elementDbValue = $em->createQueryBuilder()
                                ->select('a.value')
                                ->from('InitialShippingBundle:RankingMonthlyData', 'a')
                                ->where('a.elementDetailsId = :elementId and a.monthdetail = :monthName and a.shipDetailsId = :shipId and a.kpiDetailsId = :kpiId and a.status = :statusvalue')
                                ->setParameter('elementId', $scorecardElementId)
                                ->setParameter('monthName',$new_monthdetail_date)
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
                                array_push($scorecardElementRules,$rankingElementRulesArray);
                                array_push($scorecardElementValueArray,$elementDbValue[0]['value']);
                                $elementValueWithWeight = $elementColorValue ;
                                $kpiSumValue+=$elementValueWithWeight;
                                $rankingElementValueTotal+=$elementColorValue;
                                // array_push($Element_Color_Array,$elementResultColor);
                                array_push($Elment_Value,$elementDbValue[0]['value']);
                                array_push($NewElementColor,$elementResultColor);


                            }
                            else
                            {
                                $elementDbValue[0]['value']=null;
                                array_push($scorecardElementRules,$rankingElementRulesArray);
                                array_push($scorecardElementValueArray,$elementDbValue[0]['value']);
                                $elementValueWithWeight = $elementColorValue ;
                                $kpiSumValue+=$elementValueWithWeight;
                                $rankingElementValueTotal=105;
                                //  array_push($Element_Color_Array,$elementResultColor);
                                array_push($Elment_Value,$elementDbValue[0]['value']);
                                array_push($NewElementColor,$elementResultColor);

                            }


                        }
                        array_push($monthlyKpiAverageValueTotal,($kpiSumValue*$rankingKpiWeight)/100);
                        //array_push($NewKpiAvg,($kpiSumValue*$rankingKpiWeight)/100);
                        $NewKpiAvg[$rankingKpiId]=($kpiSumValue*$rankingKpiWeight)/100;
                        //  array_push($monthlyElementValueArray,$scorecardElementValueArray);
                        if($rankingElementValueTotal !=105)
                        {
                            array_push($rankingKpiValueCountArray,($rankingElementValueTotal*$rankingKpiWeight/100));
                        }
                        else
                        {
                            array_push($rankingKpiValueCountArray,null);
                        }
                    }
                   // array_push($Newkpivalue,$Elment_Value);
                    $Newkpivalue[$rankingKpiId]=$Elment_Value;
                    $NewKpiColor[$rankingKpiId]=$NewElementColor;


                }


                array_push($monthlyKpiValue,$rankingKpiValueCountArray);
                if(array_sum($rankingKpiValueCountArray)!=0)
                {
                    array_push($monthlyKpiAverageScore,array_sum($rankingKpiValueCountArray));
                    array_push($dataforgraphforship,array_sum($rankingKpiValueCountArray));
                }
                if(array_sum($rankingKpiValueCountArray)==0)
                {
                    array_push($monthlyKpiAverageScore, null);
                    //array_push($dataforgraphforship,0);
                }
                //array_push($monthlykpicolorarray,$KpiColorArray);
                array_push($NewMonthlyKPIValue,$Newkpivalue);
                array_push($NewMonthlyAvgTotal,$NewKpiAvg);
                array_push($NewMonthColor,$NewKpiColor);

            }
            $New_overallfindingelementgraph=array();
            $New_overallfindingelementvalue=array();
            $New_overallfindingelementcolor=array();

            for($SplitKpiCount=0;$SplitKpiCount<count($rankingKpiList);$SplitKpiCount++)
            {
                $rankingKpiId = $rankingKpiList[$SplitKpiCount]['id'];
                $rankingKpiName = $rankingKpiList[$SplitKpiCount]['kpiName'];
                $New_Month_Avg_Total=array();
                $New_Month_Element_Value=array();
                $New_Month_Element_Color=array();
                for($New_FindKpivalueCount=0;$New_FindKpivalueCount<count($dataforgraphforship);$New_FindKpivalueCount++)
                {
                    $New_Month_Avg_Total[$New_FindKpivalueCount]=$NewMonthlyAvgTotal[$New_FindKpivalueCount][$rankingKpiId];
                    $New_Month_Element_Value[$New_FindKpivalueCount]=$NewMonthlyKPIValue[$New_FindKpivalueCount][$rankingKpiId];
                    $New_Month_Element_Color[$New_FindKpivalueCount]=$NewMonthColor[$New_FindKpivalueCount][$rankingKpiId];
                }
                $New_overallfindingelementgraph[$rankingKpiId]=$New_Month_Avg_Total;
                $New_overallfindingelementvalue[$rankingKpiId]=$New_Month_Element_Value;
                $New_overallfindingelementcolor[$rankingKpiId]=$New_Month_Element_Color;
            }
            $shipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
            $shipname = $shipid->getShipName();
            $man_year= $shipid->getManufacturingYear();
            if($man_year=="")
            {
                $yearcount=0;
            }
            else
            {
                $currentdatestring=date('Y-01-01');
                $d1 = new \DateTime($currentdatestring);
                $man_datestring=$man_year.'-01-'.'01';
                $d2=new \DateTime($man_datestring);
                $diff = $d2->diff($d1);
                $yearcount=$diff->y+1;
            }

          $mpdf = $this->container->get('tfox.mpdfport')->getMPdf();
            $mpdf->defaultheaderline = 0;
            $mpdf->defaultheaderfontstyle = 'B';
            $WateMarkImagePath= $this->container->getParameter('kernel.root_dir').'/../web/images/pioneer_logo_02.png';
            $mpdf ->SetWatermarkImage($WateMarkImagePath);
            $mpdf ->showWatermarkImage = true;
             $graphObject = array(
                  'chart'=>array('renderTo'=>'areaId','type'=>"line"),
                  'exporting'=>array('enabled'=>false),
                  'plotOptions'=>array('series'=>array(
                      "allowPointSelect"=>true,
                      "dataLabels"=>array(
                          "enabled"=>true
                      )
                  )),
                  'series'=>array(
                      array('name'=>'Series','showInLegend'=>false,'color'=>'blue','data'=>$dataforgraphforship)
                  ),
                  'subtitle'=>array('style'=>array('color'=>'#0000f0','fontWeight'=>'bold')),
                  'title'=>array('text'=>$shipname),
                  'xAxis'=>array('categories'=>$newcategories,'labels'=>array('style'=>array('color'=>'#0000F0'))),
                  'yAxis'=>array('max'=>100,'title'=>array('text'=>'Values','style'=>array('color'=>'#0000F0'))),
              );
              $jsondata=json_encode($graphObject);
              $pdffilenamefullpath= $this->container->getParameter('kernel.root_dir').'/../web/phantomjs/listofjsonfiles/ship_'.$shipid.'.json';
              file_put_contents($pdffilenamefullpath, $jsondata);
              $Highchartconvertjs = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js -infile ';
              $outfile=$this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph/shipimage_'.$shipid.'.png';
              $JsonFileDirectroy=$this->container->getParameter('kernel.root_dir').'/../web/phantomjs/listofjsonfiles/ship_'.$shipid.'.json -outfile '.$outfile.' -scale 2.5 -width 1065';
              $ImageGeneration = 'phantomjs ' . $Highchartconvertjs.$JsonFileDirectroy;
              $handle = popen($ImageGeneration, 'r');
              $charamee = fread($handle, 2096);
             $customerListDesign= $this->renderView('InitialShippingBundle:DashBorad:overallranking_report_template.html.twig', array(
                  'shipid' => $shipid,
                  'screenName' => 'Ranking Report',
                  'userName' => '',
                  'date' => date('Y-m-d'),
                  'link' => 'shipimage_'.$shipid.'.png',
                  'listofkpi' => $rankingKpiList,
                  'kpiweightage' => $rankingKpiWeightarray,
                  'montharray' => $newcategories,
                  'shipname' => $shipname,
                  'countmonth' => count($dataforgraphforship),
                  'avgscore' => $monthlyKpiAverageScore,
                  'ageofvessel'=>$yearcount,
                  'kpimonthdata'=>$monthlyKpiValue,
                  'currentyear'=>date('Y')
              ));
            $mpdf->AddPage('', 4, '', 'on');
            $mpdf->SetFooter('|Date/Time: {DATE l jS F Y h:i}| Page No: {PAGENO}');
            $mpdf->WriteHTML($customerListDesign);
            for($KpiPdfcount=0;$KpiPdfcount<count($rankingKpiList);$KpiPdfcount++)
            {
                $kpiName=$rankingKpiList[$KpiPdfcount]['kpiName'];
                $kpiid=$rankingKpiList[$KpiPdfcount]['id'];
                $weightage=$rankingKpiList[$KpiPdfcount]['weightage'];
                 $graphObject = array(
                        'chart'=>array('renderTo'=>'areaId','type'=>"line"),
                        'exporting'=>array('enabled'=>false),
                        'plotOptions'=>array('series'=>array(
                            "allowPointSelect"=>true,
                            "dataLabels"=>array(
                                "enabled"=>true
                            )
                        )),
                        'series'=>array(
                            array('name'=>'Series','showInLegend'=>false,'color'=>'blue','data'=>$New_overallfindingelementgraph[$kpiid])
                        ),
                        'subtitle'=>array('style'=>array('color'=>'#0000f0','fontWeight'=>'bold')),
                        'title'=>array('text'=>$kpiName),
                        'xAxis'=>array('categories'=>$newcategories,'labels'=>array('style'=>array('color'=>'#0000F0'))),
                    );
                    $jsondata=json_encode($graphObject);
                    $pdffilenamefullpath= $this->container->getParameter('kernel.root_dir').'/../web/phantomjs/listofjsonfiles/kpi_'.$kpiid.'.json';
                    file_put_contents($pdffilenamefullpath, $jsondata);
                    $Highchartconvertjs = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js -infile ';
                    $outfile=$this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph/kpiimage_'.$kpiid.'.png';
                    $JsonFileDirectroy=$this->container->getParameter('kernel.root_dir').'/../web/phantomjs/listofjsonfiles/kpi_'.$kpiid.'.json -outfile '.$outfile.' -scale 2.5 -width 1065';
                    $ImageGeneration = 'phantomjs ' . $Highchartconvertjs.$JsonFileDirectroy;
                    $handle = popen($ImageGeneration, 'r');
                    $charamee = fread($handle, 2096);

                    $customerListDesign = $this->renderView('InitialShippingBundle:DashBorad:overallranking_kpi_template.html.twig', array(
                        'kpiid' => $kpiid,
                        'screenName' => 'Ranking Report',
                        'userName' => '',
                        'date' => date('Y-m-d'),
                        'link' => 'kpiimage_'.$kpiid.'.png',
                        'montharray' => $newcategories,
                        'kpiname' => $kpiName,
                        'countmonth' => count($dataforgraphforship),
                        'kpigraph'=>$New_overallfindingelementgraph[$kpiid],
                        'elementcolorarray' => $New_overallfindingelementcolor[$kpiid],
                        'monthlydata'=>$New_overallfindingelementvalue[$kpiid],
                        'elementRule' => $scorecardElementRules,
                        'listofelement' => $ElementName_Weightage[$kpiid],
                        'countofelement'=>count($ElementName_Weightage[$kpiid]),
                        'currentyear'=>date('Y')
                    ));



                $mpdf->AddPage('', 4, '', 'on');
                $mpdf->SetFooter('|Date/Time: {DATE l jS F Y h:i}| Page No: {PAGENO}');
                $mpdf->WriteHTML($customerListDesign);
                //$mpdf->SetTitle($title);
            }
          $content = $mpdf->Output('', 'S');
            $response = new Response();
            $response->setContent($content);
            $response->headers->set('Content-Type', 'application/pdf');
            return $response;
        }
        else
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }
    /**
     * Reports For Ranking
     *
     * @Route("/allships_ranking_reports", name="allships_ranking_reports")
     */
    public function allships_ranking_reportsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null)
        {
            $userId = $user->getId();
            $userName = $user->getUsername();
            return $this->render(
                'InitialShippingBundle:DashBorad:rankingreport_allships.html.twig'
            );

        }
        else
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Ranking Overall Shipreports.
     *
     * @Route("/overall_ships_rankingreports", name="overall_ships_rankingreports")
     */
    public function overall_ships_rankingreportsAction(Request $request,$mode='',$dataofmonth='', $year=' ')
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null) {
            $userId = $user->getId();
            $userName = $user->getUsername();

            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName', 'a.id', 'a.manufacturingYear')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyDetailsId')
                    ->where('b.adminName = :username')
                    ->setParameter('username', $userName)
                    ->getQuery();
            } else {
                $query = $em->createQueryBuilder()
                    ->select('a.shipName', 'a.id', 'a.manufacturingYear')
                    ->from('InitialShippingBundle:ShipDetails', 'a')
                    ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                    ->where('b.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            }

            $listAllShipForCompany = $query->getResult();
            $year=$request->request->get('year');
            $oneyear_montharray = array();
            $oneChart_Data=array();
            if ($year == ' ') {
                for ($m = 1; $m <= 12; $m++) {
                    $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date('Y')));
                    array_push($oneyear_montharray, $month);
                }
                $currentyear = date('Y');
            }
            if ($year != ' ') {
                for ($m = 1; $m <= 12; $m++) {
                    $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date($year)));
                    array_push($oneyear_montharray, $month);
                }
                $currentyear = date($year);
            }
            $newcategories=array();
            $DataRankingReports=0;


            $overallShipDetailArray = array();
            for ($shipCount = 0; $shipCount < count($listAllShipForCompany); $shipCount++)
            {
                $rankingShipName = $listAllShipForCompany[$shipCount]['shipName'];
                $manufacturingYear = $listAllShipForCompany[$shipCount]['manufacturingYear'];
                $rankingShipId = $listAllShipForCompany[$shipCount]['id'];
                $ShipDetailDataarray=array();
                for ($d = 0; $d < count($oneyear_montharray); $d++)
                {
                    $monthcount=0;
                    $time2 = strtotime($oneyear_montharray[$d]);
                    $monthinletter = date('M', $time2);
                    array_push($newcategories, $monthinletter);
                    $new_monthdetail_date = new \DateTime($oneyear_montharray[$d]);
                    $new_monthdetail_date->modify('last day of this month');
                $rankingKpiValueCountArray = array();

                $rankingKpiList = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id', 'b.weightage')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                    ->where('b.shipDetailsId = :shipid')
                    ->setParameter('shipid', $listAllShipForCompany[0]['id'])
                    ->getQuery()
                    ->getResult();

                for ($rankingKpiCount = 0; $rankingKpiCount < count($rankingKpiList); $rankingKpiCount++)
                {
                    $rankingElementValueTotal = 0;
                    $rankingKpiId = $rankingKpiList[$rankingKpiCount]['id'];
                    $rankingKpiWeight = $rankingKpiList[$rankingKpiCount]['weightage'];
                    $rankingKpiName = $rankingKpiList[$rankingKpiCount]['kpiName'];

                    $rankingElementList = $em->createQueryBuilder()
                        ->select('c.id', 'c.elementName', 'c.weightage', 'a.value')
                        ->from('InitialShippingBundle:RankingElementDetails', 'c')
                        ->join('InitialShippingBundle:RankingMonthlyData', 'a', 'with', 'c.id = a.elementDetailsId')
                        ->where('c.kpiDetailsId = :kpiid and a.monthdetail = :datamonth and a.status = :rankingStatusValue and a.shipDetailsId = :shipId')
                        ->setParameter('kpiid', $rankingKpiId)
                        ->setParameter('datamonth', $new_monthdetail_date)
                        ->setParameter('rankingStatusValue', 3)
                        ->setParameter('shipId', $rankingShipId)
                        ->getQuery()
                        ->getResult();

                    if ($rankingElementList > 0)
                    {
                        if($monthcount==0)
                        {
                            $DataRankingReports++;

                        }
                        for ($rankingElementCount = 0; $rankingElementCount < count($rankingElementList); $rankingElementCount++)
                        {
                            $rankingElementName = $rankingElementList[$rankingElementCount]['elementName'];
                            $rankingElementId = $rankingElementList[$rankingElementCount]['id'];
                            $rankingElementWeight = $rankingElementList[$rankingElementCount]['weightage'];
                            $rankingElementValue = $rankingElementList[$rankingElementCount]['value'];

                            $rankingElementRulesList = $em->createQueryBuilder()
                                ->select('a.rules')
                                ->from('InitialShippingBundle:RankingRules', 'a')
                                ->where('a.elementDetailsId = :element_id')
                                ->setParameter('element_id', $rankingElementId)
                                ->getQuery()
                                ->getResult();

                            $rankingElementResultColor = "";
                            $rankingElementColorValue = 0;

                            for ($elementRuleCount = 0; $elementRuleCount < count($rankingElementRulesList); $elementRuleCount++) {
                                $rankingElementRule = $rankingElementRulesList[$elementRuleCount];
                                $rankingJsFileDirectory = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_findcolornode_3.js \'' . $rankingElementRule['rules'] . ' \' ' . $rankingElementValue;
                                $rankingJsFileName = 'node ' . $rankingJsFileDirectory;
                                $handle = popen($rankingJsFileName, 'r');
                                $resultColor = fread($handle, 2096);
                                $rankingElementResultColor = str_replace("\n", '', $resultColor);

                                if ($rankingElementResultColor == "false") {
                                    continue;
                                }

                                if ($rankingElementResultColor == 'Green') {
                                    $rankingElementColorValue = $rankingElementWeight;
                                    break;
                                } else if ($rankingElementResultColor == 'Yellow') {
                                    $rankingElementColorValue = $rankingElementWeight / 2;
                                    break;
                                } else if ($rankingElementResultColor == 'Red') {
                                    $rankingElementColorValue = 0;
                                    break;
                                }
                            }
                            $rankingElementValueTotal += $rankingElementColorValue;
                        }
                        $monthcount++;
                    }
                    array_push($rankingKpiValueCountArray, ($rankingElementValueTotal * $rankingKpiWeight / 100));
                }
                if ($manufacturingYear == "")
                {
                    $yearcount = 0;
                }
                else
                {
                    $currentdatestring = date('Y-01-01');
                    $d1 = new \DateTime($currentdatestring);
                    $man_datestring = $manufacturingYear . '-01-' . '01';
                    $d2 = new \DateTime($man_datestring);
                    $diff = $d2->diff($d1);
                    $yearcount = $diff->y + 1;
                    $vesselage = 20 / $yearcount;
                }
                    if(array_sum($rankingKpiValueCountArray)!=0)
                    {
                        array_push($ShipDetailDataarray,(array_sum($rankingKpiValueCountArray)));
                    }


            }
               // array($oneChart_Data,$overallShipDetailArray);
                $monthInLetter = $new_monthdetail_date->format('M-Y');
               // $oneChart_Data=array(array("name" => $monthInLetter,'showInLegend'=> false, 'color' => 'blue', "data" => $overallShipDetailArray));
                array_push($oneChart_Data,array("name" => $rankingShipName,'showInLegend'=> true, 'color' => 'blue', "data" => $ShipDetailDataarray));
        }
            $response = new JsonResponse();
            $response->setData
            (
                array(
                    'montharray' => $newcategories,
                    'chartdata'=>$oneChart_Data,

                )
            );
            return $response;
        }

    }



}