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
use Luxifer\DQL\Datetime\DateFormat;

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
    public function indexAction(Request $request, $mode = '', $dataofmonth = '', $modeYear = 0)
    {
        $em = $this->getDoctrine()->getManager();
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $this->getUser();
        if ($user != null) {
            $userId = $user->getId();
            $userName = $user->getUsername();
            $role = $user->getRoles();
            if ($role[0] != 'ROLE_KPI_INFO_PROVIDER') {
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
                if ($modeYear == 0) {
                    if ($dataofmonth == '') {
                        $monthInString = '01-May-2016';
                        $lastMonthDetail = new \DateTime($monthInString);
                        $lastMonthDetail->modify('last day of this month');
                    }
                    if ($dataofmonth != '') {
                        $monthInString = '01-' . $dataofmonth;
                        $lastMonthDetail = new \DateTime($monthInString);
                        $lastMonthDetail->modify('last day of this month');
                    }

                    $overallShipDetailArray = array();
                    $redarea_vessel_shipid=array();
                    $greenarea_vessel_shipids=array();
                    $yellowarea_vessel_shipid=array();
                    $lastMonthvesselpieChart="";


                    for ($shipCount = 0; $shipCount < count($listAllShipForCompany); $shipCount++) {
                        $rankingKpiValueCountArray = array();
                        $rankingShipName = $listAllShipForCompany[$shipCount]['shipName'];
                        $manufacturingYear = $listAllShipForCompany[$shipCount]['manufacturingYear'];
                        $rankingShipId = $listAllShipForCompany[$shipCount]['id'];
                        $oneyear_montharray = array();
                        $monthlyKpiValue = array();
                        $newcategories = array();
                        $monthlyKpiAverageScore = array();
                        if ($dataofmonth == '')
                        {
                            $currentMonth=date('Y-m-d');
                            $yearChange=date('Y');
                        }
                        if ($dataofmonth != '')
                        {
                            $monthInString = '01-' . $dataofmonth;
                            $currentMonth=date($monthInString);
                            $yearChange = date('Y', strtotime($currentMonth));
                        }
                        $new_monthdetail_date = new \DateTime($currentMonth);
                        $new_monthdetail_date->modify('last day of this month');
                        $currentyear=date_format($new_monthdetail_date, 'Y');
                        $monthlyShipDataStatus = $em->createQueryBuilder()
                            ->select('b.status')
                            ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                            ->where('b.shipid = :shipId and b.dataofmonth = :monthDetail')
                            ->setParameter('shipId', $rankingShipId)
                            ->setParameter('monthDetail', $new_monthdetail_date)
                            ->getQuery()
                            ->getResult();

                        if (count($monthlyShipDataStatus) != 0 && $monthlyShipDataStatus[0]['status'] == 4)
                        {
                            $currentMonthinter = date('n');
                            $limit=(int)$currentMonthinter;
                            // $statusVerified = $currentMonth;
                            for ($m = 1; $m <= $limit; $m++) {
                                $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date($currentyear)));
                                array_push($oneyear_montharray, $month);
                            }
                        }
                        else
                        {/*
                $currentMonthinter = date('n');
                $limit=(int)$currentMonthinter;
                for ($m = 1; $m <=$limit; $m++) {
                    $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date($currentyear)));
                    array_push($oneyear_montharray, $month);
                }*/
                            $statusFieldQuery = $em->createQueryBuilder()
                                ->select('b.dataofmonth,b.status')
                                ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                                ->where('b.shipid = :shipId and b.status = :monthStatus')
                                ->setParameter('monthStatus', 4)
                                ->setParameter('shipId', $rankingShipId)
                                ->groupby('b.dataofmonth')
                                ->getQuery()
                                ->getResult();
                            if (count($statusFieldQuery) != 0 && $statusFieldQuery[count($statusFieldQuery) - 1]['status'] == 4) {
                                for ($m =0; $m < count($statusFieldQuery); $m++)
                                {
                                    $month = date_format($statusFieldQuery[$m]['dataofmonth'], 'Y-m-d');
                                    array_push($oneyear_montharray, $month);

                                }
                            }


                        }
                        $rankingKpiList = $em->createQueryBuilder()
                            ->select('b.kpiName', 'b.id', 'b.weightage')
                            ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                            ->where('b.shipDetailsId = :shipid')
                            ->setParameter('shipid', $rankingShipId)
                            ->getQuery()
                            ->getResult();
                        if(count($oneyear_montharray) > 0 ) {
                            for ($d = 0; $d < count($oneyear_montharray); $d++) {
                                $time2 = strtotime($oneyear_montharray[$d]);
                                $monthinletter = date('M', $time2);
                                array_push($newcategories, $monthinletter);
                                $new_monthdetail_date = new \DateTime($oneyear_montharray[$d]);
                                $new_monthdetail_date->modify('last day of this month');
                                $rankingKpiValueCountArray = array();
                                $rankingKpiWeightarray = array();

                                for ($rankingKpiCount = 0; $rankingKpiCount < count($rankingKpiList); $rankingKpiCount++) {
                                    $rankingElementValueTotal = 0;
                                    $rankingKpiId = $rankingKpiList[$rankingKpiCount]['id'];
                                    $rankingKpiWeight = $rankingKpiList[$rankingKpiCount]['weightage'];
                                    $rankingKpiName = $rankingKpiList[$rankingKpiCount]['kpiName'];
                                    array_push($rankingKpiWeightarray, $rankingKpiWeight);
                                    if ($rankingKpiName == 'Vessel age') {
                                        if ($manufacturingYear == "") {
                                            $yearcount = 0;
                                        } else {

                                            $man_datestring = $manufacturingYear. '-01';
                                            $temp_man_year = new \DateTime($man_datestring);
                                            $temp_man_year->modify('last day of this month');
                                            $Vessage_count = $temp_man_year->diff($lastMonthDetail)->y;
                                        }
                                        $vesselage = ($Vessage_count * $rankingKpiWeight) / 20;
                                        array_push($rankingKpiValueCountArray, $vesselage);
                                    }
                                    else
                                    {

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

                                        if (count($rankingElementList) > 0) {
                                            for ($rankingElementCount = 0; $rankingElementCount < count($rankingElementList); $rankingElementCount++) {
                                                $rankingElementName = $rankingElementList[$rankingElementCount]['elementName'];
                                                $rankingElementId = $rankingElementList[$rankingElementCount]['id'];
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
                                                    ->setParameter('monthDetail', $new_monthdetail_date)
                                                    ->getQuery()
                                                    ->getResult();
                                                if (count($rankingElementResult) != 0) {
                                                    $rankingElementResultColor = $rankingElementResult[0]['elementcolor'];
                                                }

                                                if ($rankingElementResultColor == "false") {
                                                    $rankingElementResultColor = "";
                                                }

                                                if ($rankingElementResultColor == 'Green') {
                                                    $rankingElementColorValue = $rankingElementWeight;
                                                } else if ($rankingElementResultColor == 'Yellow') {
                                                    $rankingElementColorValue = $rankingElementWeight / 2;
                                                } else if ($rankingElementResultColor == 'Red') {
                                                    $rankingElementColorValue = 0;
                                                }
                                                $rankingElementValueTotal += $rankingElementColorValue;
                                            }
                                        }
                                        if (count($rankingElementList) == 0) {
                                            $newkpiid = $em->createQueryBuilder()
                                                ->select('b.id')
                                                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                                                ->where('b.kpiName = :kpiName')
                                                ->setParameter('kpiName', $rankingKpiName)
                                                ->groupby('b.kpiName')
                                                ->getQuery()
                                                ->getResult();
                                            $rankingElementList = $em->createQueryBuilder()
                                                ->select('c.id', 'c.elementName', 'c.weightage', 'a.value')
                                                ->from('InitialShippingBundle:RankingElementDetails', 'c')
                                                ->join('InitialShippingBundle:RankingMonthlyData', 'a', 'with', 'c.id = a.elementDetailsId')
                                                ->where('c.kpiDetailsId = :kpiid and a.monthdetail = :datamonth and a.status = :rankingStatusValue and a.shipDetailsId = :shipId')
                                                ->setParameter('kpiid', $newkpiid[0]['id'])
                                                ->setParameter('datamonth', $new_monthdetail_date)
                                                ->setParameter('rankingStatusValue', 3)
                                                ->setParameter('shipId', $rankingShipId)
                                                ->getQuery()
                                                ->getResult();

                                            for ($rankingElementCount = 0; $rankingElementCount < count($rankingElementList); $rankingElementCount++) {
                                                $rankingElementName = $rankingElementList[$rankingElementCount]['elementName'];
                                                $rankingElementId = $rankingElementList[$rankingElementCount]['id'];
                                                $rankingElementWeight = $rankingElementList[$rankingElementCount]['weightage'];
                                                $rankingElementValue = $rankingElementList[$rankingElementCount]['value'];
                                                $rankingElementResultColor = "";
                                                $rankingElementColorValue = 0;
                                                $rankingElementResult = $em->createQueryBuilder()
                                                    ->select('b.elementdata, b.elementcolor')
                                                    ->from('InitialShippingBundle:Ranking_LookupData', 'b')
                                                    ->where('b.kpiDetailsId = :kpiId and b.shipDetailsId = :shipId and b.elementDetailsId = :elementId and b.monthdetail = :monthDetail')
                                                    ->setParameter('kpiId', $newkpiid[0]['id'])
                                                    ->setParameter('shipId', $rankingShipId)
                                                    ->setParameter('elementId', $rankingElementId)
                                                    ->setParameter('monthDetail', $new_monthdetail_date)
                                                    ->getQuery()
                                                    ->getResult();
                                                if (count($rankingElementResult) != 0) {
                                                    $rankingElementResultColor = $rankingElementResult[0]['elementcolor'];
                                                }

                                                if ($rankingElementResultColor == "false") {
                                                    $rankingElementResultColor = "";
                                                }

                                                if ($rankingElementResultColor == 'Green') {
                                                    $rankingElementColorValue = $rankingElementWeight;
                                                } else if ($rankingElementResultColor == 'Yellow') {
                                                    $rankingElementColorValue = $rankingElementWeight / 2;
                                                } else if ($rankingElementResultColor == 'Red') {
                                                    $rankingElementColorValue = 0;
                                                }
                                                $rankingElementValueTotal += $rankingElementColorValue;
                                            }
                                        }

                                        array_push($rankingKpiValueCountArray, ($rankingElementValueTotal * $rankingKpiWeight / 100));
                                    }
                                }
                                array_push($monthlyKpiValue, $rankingKpiValueCountArray);
                                array_push($monthlyKpiAverageScore, array_sum($rankingKpiValueCountArray));
                                //This is Vessel pie data calculating Starts Here
                                if($d==(count($oneyear_montharray)-1))
                                {
                                    $lastMonthvesselpieChart=date('M-Y', $time2);
                                    if(array_sum($rankingKpiValueCountArray)>=80)
                                    {
                                        array_push($greenarea_vessel_shipids,$rankingShipId);

                                    }
                                    else if(array_sum($rankingKpiValueCountArray)>=70)
                                    {
                                        array_push($yellowarea_vessel_shipid,$rankingShipId);
                                    }
                                    else if(array_sum($rankingKpiValueCountArray)<70)
                                    {
                                        array_push($redarea_vessel_shipid,$rankingShipId);
                                    }

                                }

                            }
                        }
                        /*     $rankingKpiList = $em->createQueryBuilder()
                                 ->select('b.kpiName', 'b.id', 'b.weightage')
                                 ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                                 ->where('b.shipDetailsId = :shipid')
                                 ->setParameter('shipid', $listAllShipForCompany[0]['id'])
                                 ->getQuery()
                                 ->getResult();
                             $verifyField = 0;
                             if (count($monthlyShipDataStatus) != 0) {
                                 $verifyField = $monthlyShipDataStatus[0]['status'];
                             }
                             if ($verifyField == 4) {
                                 for ($rankingKpiCount = 0; $rankingKpiCount < count($rankingKpiList); $rankingKpiCount++) {
                                     $rankingElementValueTotal = 0;
                                     $rankingKpiId = $rankingKpiList[$rankingKpiCount]['id'];
                                     $rankingKpiWeight = $rankingKpiList[$rankingKpiCount]['weightage'];
                                     $rankingKpiName = $rankingKpiList[$rankingKpiCount]['kpiName'];
                                     if ($rankingKpiName == 'Vessel age') {
                                         if ($manufacturingYear == "") {
                                             $yearcount = 0;
                                         } else {

                                             $man_datestring = $manufacturingYear. '-01';
                                             $temp_man_year = new \DateTime($man_datestring);
                                             $temp_man_year->modify('last day of this month');
                                             $Vessage_count = $temp_man_year->diff($lastMonthDetail)->y;
                                         }
                                         $vesselage = ($Vessage_count * $rankingKpiWeight) / 20;
                                         array_push($rankingKpiValueCountArray, $vesselage);
                                     }
                                     else
                                     {
                                     $rankingElementList = $em->createQueryBuilder()
                                         ->select('c.id', 'c.elementName', 'c.weightage', 'a.value')
                                         ->from('InitialShippingBundle:RankingElementDetails', 'c')
                                         ->join('InitialShippingBundle:RankingMonthlyData', 'a', 'with', 'c.id = a.elementDetailsId')
                                         ->where('c.kpiDetailsId = :kpiid and a.monthdetail = :datamonth and a.status = :rankingStatusValue and a.shipDetailsId = :shipId')
                                         ->setParameter('kpiid', $rankingKpiId)
                                         ->setParameter('datamonth', $lastMonthDetail)
                                         ->setParameter('rankingStatusValue', 3)
                                         ->setParameter('shipId', $rankingShipId)
                                         ->getQuery()
                                         ->getResult();

                                     if ($rankingElementList > 0) {
                                         for ($rankingElementCount = 0; $rankingElementCount < count($rankingElementList); $rankingElementCount++) {
                                             $rankingElementName = $rankingElementList[$rankingElementCount]['elementName'];
                                             $rankingElementId = $rankingElementList[$rankingElementCount]['id'];
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
                                                 ->setParameter('monthDetail', $lastMonthDetail)
                                                 ->getQuery()
                                                 ->getResult();
                                             if (count($rankingElementResult) != 0) {
                                                 $rankingElementResultColor = $rankingElementResult[0]['elementcolor'];
                                             }

                                             if ($rankingElementResultColor == "false") {
                                                 $rankingElementResultColor = "";
                                             }

                                             if ($rankingElementResultColor == 'Green') {
                                                 $rankingElementColorValue = $rankingElementWeight;
                                             } else if ($rankingElementResultColor == 'Yellow') {
                                                 $rankingElementColorValue = $rankingElementWeight / 2;
                                             } else if ($rankingElementResultColor == 'Red') {
                                                 $rankingElementColorValue = 0;
                                             }

                                             $rankingElementValueTotal += $rankingElementColorValue;
                                         }
                                     }
                                     array_push($rankingKpiValueCountArray, ($rankingElementValueTotal * $rankingKpiWeight / 100));
                                 }
                                 }
                                 if ($manufacturingYear == "") {
                                     $yearcount = 0;
                                 } else {
                                     $currentdatestring = date('Y-01-01');
                                     $d1 = new \DateTime($currentdatestring);
                                     $man_datestring = $manufacturingYear . '-01-' . '01';
                                     $d2 = new \DateTime($man_datestring);
                                     $diff = $d2->diff($d1);
                                     $yearcount = $diff->y + 1;
                                     $vesselage = 20 / $yearcount;
                                 }
                                 $overallShipDetailArray[$shipCount]['name'] = $rankingShipName;
                                 $overallShipDetailArray[$shipCount]['y'] = (array_sum($rankingKpiValueCountArray)/(count()));
                                 $yearChange = $lastMonthDetail->format('Y');
                                 $overallShipDetailArray[$shipCount]['url'] = '/dashboard/' . $rankingShipId . '/' . $yearChange . '/listallkpiforship_ranking';
                             } else {
                                 $overallShipDetailArray[$shipCount]['name'] = $rankingShipName;
                                 $overallShipDetailArray[$shipCount]['y'] = 0;
                                 $yearChange = $lastMonthDetail->format('Y');
                                 $overallShipDetailArray[$shipCount]['url'] = '/dashboard/' . $rankingShipId . '/' . $yearChange . '/listallkpiforship_ranking';
                             }*/
                        $overallShipDetailArray[$shipCount]['name'] = $rankingShipName;
                        if(count($oneyear_montharray) > 0 ) {
                            $overallShipDetailArray[$shipCount]['y'] = (array_sum($monthlyKpiAverageScore)/(count($oneyear_montharray)));
                        }
                        //$yearChange = $lastMonthDetail->format('Y');
                        $overallShipDetailArray[$shipCount]['url'] = '/dashboard/' . $rankingShipId . '/' . $yearChange . '/listallkpiforship_ranking';

                    }


                    /*$monthInLetter = $lastMonthDetail->format('M-Y');
                    if ($mode == 'getnextmonthchart') {
                        return array("data" => $overallShipDetailArray, 'currentmonth' => $monthInLetter, 'name' => $monthInLetter,);
                    }*/
                    //This Ranking Dashboard Highcharts Starts Here//

                    $ob = new Highchart();
                    $ob->chart->renderTo('area');
                    $ob->credits->enabled(false);
                    $ob->chart->type('column');
                    $ob->chart->hieght(250);
                    $ob->title->text('', array('style' => array('color' => 'red')));
                    $ob->xAxis->type('category');
                    $ob->xAxis->labels(array('style' => array('color' => '#103a71')));
                    $ob->yAxis->title(array('text' => 'Values'));
                    $ob->yAxis->max(100);
                    $ob->legend->enabled(false);
                    $ob->plotOptions->series(array('borderWidth' => 0, 'dataLabels' => array('enabled' => true),
                        'point' => array('events' => array('click' => new \Zend\Json\Expr('function () { location.href = this.options.url; }')))));
                    $ob->series(array(array('showInLegend' => false, 'colorByPoint' => true, 'name' => $yearChange, 'color' => 'rgb(124, 181, 236)', "data" => $overallShipDetailArray)));
                    $ob->exporting->enabled(false);
                    //This Ranking Dashboard Highcharts Starts Here//
                }

                if ($mode == 'overallreports_ranking') {
                    return array(
                        'ship_count' => count($listAllShipForCompany),
                        'allships' => $listAllShipForCompany,
                        'chart' => $ob,
                        'rankinKpiCount' => count($rankingKpiList),
                        'currentmonth' => '',
                        'currentyear' => $yearChange,
                    );
                }
                $redarea_kpiids=array();
                $greenarea_kpiids=array();
                $yellowarea_kpiids=array();

                $datesArray = array();
                $initial = 0;
                $statusVerified = 0;
                $currentyear = date('Y');

                if ($modeYear == 0 && count($listAllShipForCompany) != 0) {
                    $currentDate = date('Y-m-d');
                    $currentMonthObject = new \DateTime($currentDate);
                    $currentMonthObject->modify('last day of this month');
                    $monthlyShipDataStatus = $em->createQueryBuilder()
                        ->select('b.status')
                        ->from('InitialShippingBundle:Scorecard_LookupStatus', 'b')
                        ->where('b.dataofmonth = :monthDetail')
                        ->setParameter('monthDetail', $currentMonthObject)
                        ->getQuery()
                        ->getResult();
                    if (count($monthlyShipDataStatus) != 0 && $monthlyShipDataStatus[0]['status'] == 4)
                    {
                        for ($m = 0; $m <= 2; $m++) {
                            if($m ==0) {
                                $month = $currentMonthObject->format('Y-m-d');
                                array_push($datesArray, $month);
                            } else {
                                $month = date("Y-m-d", strtotime($datesArray[$m-1]."last day of previous month"));
                                array_push($datesArray, $month);
                            }
                        }
                    }
                    else
                    {
                        $statusFieldQuery = $em->createQueryBuilder()
                            ->select('b.dataofmonth,b.status')
                            ->from('InitialShippingBundle:Scorecard_LookupStatus', 'b')
                            ->where('b.status = :monthStatus')
                            ->setParameter('monthStatus', 4)
                            ->groupby('b.dataofmonth')
                            ->getQuery()
                            ->getResult();
                        if (count($statusFieldQuery) != 0 && $statusFieldQuery[count($statusFieldQuery) - 1]['status'] == 4) {
                            $dateFromDb = $statusFieldQuery[count($statusFieldQuery) - 1]['dataofmonth'];
                            for ($m = 0; $m <= 2; $m++) {
                                if($m ==0) {
                                    $month = $dateFromDb->format('Y-m-d');
                                    array_push($datesArray, $month);
                                } else {
                                    $month = date("Y-m-d", strtotime($datesArray[$m-1]."last day of previous month"));
                                    array_push($datesArray, $month);
                                }
                            }
                        }
                    }
                    $currentyear = date('Y');
                    $initial = 0;
                    $statusVerified = 3;
                }

                if ($modeYear != 0 && count($listAllShipForCompany) != 0) {
                    for ($m = 2; $m <= 13; $m++) {
                        $month = date('Y-m-d', mktime(0, 0, 0, $m, 0, date($modeYear)));
                        array_push($datesArray, $month);
                    }
                    if($modeYear == date('Y')) {
                        $statusFieldQuery = $em->createQueryBuilder()
                            ->select('b.dataofmonth,b.status')
                            ->from('InitialShippingBundle:Scorecard_LookupStatus', 'b')
                            ->where('b.status = :monthStatus')
                            ->setParameter('monthStatus', 4)
                            ->groupby('b.dataofmonth')
                            ->getQuery()
                            ->getResult();
                        if (count($statusFieldQuery) != 0 && $statusFieldQuery[count($statusFieldQuery) - 1]['status'] == 4) {
                            $dateFromDb = $statusFieldQuery[count($statusFieldQuery) - 1]['dataofmonth'];
                            $statusVerified = $dateFromDb->format('n');
                            $initial = 0;
                        }
                    }
                    if($modeYear != date('Y')) {
                        for ($yearCount = 0; $yearCount < count($datesArray); $yearCount++) {
                            $monthObject = new \DateTime($datesArray[$yearCount]);
                            $monthObject->modify('last day of this month');
                            $statusFieldQuery = $em->createQueryBuilder()
                                ->select('b.status, b.dataofmonth')
                                ->from('InitialShippingBundle:Scorecard_LookupStatus', 'b')
                                ->where('b.dataofmonth = :monthDetail')
                                ->setParameter('monthDetail', $monthObject)
                                ->getQuery()
                                ->getResult();
                            if (count($statusFieldQuery) != 0) {
                                if ($statusFieldQuery[count($statusFieldQuery)-1]['status'] == 4) {
                                    $dateFromDb = $statusFieldQuery[count($statusFieldQuery)-1]['dataofmonth'];
                                    $initial = ($dateFromDb->format('n'))-1;
                                    $statusVerified = 12;
                                }
                                break;
                            }
                        }
                    }
                    $currentyear = date($modeYear);
                }
                $scorecardKpiList = $em->createQueryBuilder()
                    ->select('a.kpiName', 'a.id', 'a.weightage')
                    ->from('InitialShippingBundle:KpiDetails', 'a')
                    ->groupby('a.kpiName')
                    ->getQuery()
                    ->getResult();
                $monthLetterArray = array();
                $monthlyScorecardKpiColorArray = array();
                $monthlyKpiAverageValueTotal = array();
                $lastMonthKpiPieChart="";
                for ($dateCount = (int)$initial; $dateCount < (int)$statusVerified; $dateCount++) {
                    $scorecardKpiColorArray = array();
                    $date = strtotime($datesArray[$dateCount]);
                    $monthLetterFormat = date('M', $date);
                    array_push($monthLetterArray, $monthLetterFormat);
                    $monthDetail = new \DateTime($datesArray[$dateCount]);
                    $monthlyScorecardKpiWeightAverageValueTotal = 0;

                    for ($kpiCount = 0; $kpiCount < count($scorecardKpiList); $kpiCount++) {
                        $scorecardAllKpiId = $scorecardKpiList[$kpiCount]['id'];
                        $scorecardKpiWeight = $scorecardKpiList[$kpiCount]['weightage'];
                        $kpiResult = $em->createQueryBuilder()
                            ->select('b.kpiColor, b.individualKpiAverageScore')
                            ->from('InitialShippingBundle:Scorecard_LookupData', 'b')
                            ->where('b.kpiDetailsId = :kpiId and b.monthdetail = :monthDetail')
                            ->setParameter('kpiId', $scorecardAllKpiId)
                            ->setParameter('monthDetail', $monthDetail)
                            ->getQuery()
                            ->getResult();
                        if (count($kpiResult) != 0) {
                            // $lastMonthKpiPieChart=
                            $kpi_Color_Result=$kpiResult[0]['kpiColor'];
                            array_push($scorecardKpiColorArray,$kpi_Color_Result );
                            if($dateCount==(int)$initial)
                            {
                                $lastMonthKpiPieChart=date('M-Y', $date);
                                if($kpi_Color_Result=='Green')
                                {
                                    array_push($greenarea_kpiids,$scorecardAllKpiId);

                                }
                                else if($kpi_Color_Result=='Red')
                                {
                                    array_push($redarea_kpiids,$scorecardAllKpiId);
                                }
                                else if($kpi_Color_Result=='Yellow')
                                {
                                    array_push($yellowarea_kpiids,$scorecardAllKpiId);
                                }
                            }

                            $monthlyScorecardKpiWeightAverageValueTotal += ($kpiResult[0]['individualKpiAverageScore'] * $scorecardKpiWeight) / 100;
                        } else {
                            array_push($scorecardKpiColorArray, "");
                            $monthlyScorecardKpiWeightAverageValueTotal += 0;
                        }
                    }
                    array_push($monthlyScorecardKpiColorArray, $scorecardKpiColorArray);
                    array_push($monthlyKpiAverageValueTotal, $monthlyScorecardKpiWeightAverageValueTotal);
                }
                if ($modeYear ==0) {
                    $quarterMonthName = array();
                    $quarterMonthColor = array();
                    $quarterMonthKpiWeight = array();
                    if (count($monthLetterArray) != 0) {
                        for ($num = count($monthLetterArray) - 3; $num < count($monthLetterArray); $num++) {
                            array_push($quarterMonthName, $monthLetterArray[$num]);
                            array_push($quarterMonthColor, $monthlyScorecardKpiColorArray[$num]);
                            array_push($quarterMonthKpiWeight, $monthlyKpiAverageValueTotal[$num]);
                        }
                    }
                }
                if ($modeYear != 0) {
                    return array(
                        'yearKpiColorArray' => $monthlyScorecardKpiColorArray,
                        'yearMonthName' => $monthLetterArray,
                        'kpi_list' => $scorecardKpiList,
                        'currentYear' => $modeYear,
                        'yearAvgScore' => $monthlyKpiAverageValueTotal
                    );
                }
                if(count($listAllShipForCompany) == 0) {
                    $rKPICount = 0;
                    $yChange = date('Y');
                } else {
                    $rKPICount = count($rankingKpiList);
                    $yChange = $yearChange;
                }
                //Vessel Pie Charts Starts Here//
                //Url 1 is Green ,2 is Yellow,3 is Red
                //$data=array(array( 'Bananas', 8),array('Kiwi', 3),array('Mixed nuts', 1));
                /*$vessel_Piechart_data=array(
                    array('name'=>'Green(Greater Than Equal 80)','y'=>count($greenarea_vessel_shipids),'url'=>'/piechart/'.$lastMonthvesselpieChart.'_'.implode('_',$greenarea_vessel_shipids).'/listall','color'=>'#1ea50b'),
                    array('name'=>'Yellow(Greater Than Equal 70)','y'=>count($yellowarea_vessel_shipid),'url'=>'/piechart/'.$lastMonthvesselpieChart.'_'.implode('_',$yellowarea_vessel_shipid).'/listall','color'=>'#feba06'),
                    array('name'=>'Red(Less Than 70)','y'=>count($redarea_vessel_shipid),'url'=>'/piechart/'.$lastMonthvesselpieChart.'_'.implode('_',$redarea_vessel_shipid).'/listall','color'=>'#b30000')
                );*/
                //Vessel Pie Charts Starts Here//
                $vessel_Piechart_data=array(
                    array('name'=>'G','y'=>count($greenarea_vessel_shipids),'url'=>'/piechart/'.$lastMonthvesselpieChart.'_'.implode('_',$greenarea_vessel_shipids).'/listall','color'=>'#1ea50b'),
                    array('name'=>'Y','y'=>count($yellowarea_vessel_shipid),'url'=>'/piechart/'.$lastMonthvesselpieChart.'_'.implode('_',$yellowarea_vessel_shipid).'/listall','color'=>'#feba06'),
                    array('name'=>'R','y'=>count($redarea_vessel_shipid),'url'=>'/piechart/'.$lastMonthvesselpieChart.'_'.implode('_',$redarea_vessel_shipid).'/listall','color'=>'#b30000')
                );
                $titlearry=array('fontSize'=>'10px');
                $formatter =new \Zend\Json\Expr('function () {return this.name + ":" + this.y + ""; }');

                $vessel_Piechart = new Highchart();
                $vessel_Piechart->chart->renderTo('vessel_piechart');
                $vessel_Piechart->chart->hieght(150);
                $vessel_Piechart->chart->plotBackgroundColor(null);
                $vessel_Piechart->chart->plotBorderWidth(0);
                $vessel_Piechart->chart->plotShadow(false);
                $vessel_Piechart->chart->marginTop(-35);
                $vessel_Piechart->chart->marginLeft(-0);
                $vessel_Piechart->chart->marginRight(-0);
                $vessel_Piechart->chart->marginBottom(-0);
                $vessel_Piechart->credits->enabled(false);
                $vessel_Piechart->title->text('');
                $vessel_Piechart->legend->enabled(true);
                $vessel_Piechart->legend->floating(true);
                $vessel_Piechart->legend->symbolHeight(4);
                $vessel_Piechart->legend->symbolWidth(4);
                $vessel_Piechart->legend->symbolRadius(2);
                $vessel_Piechart->legend->itemMarginTop(1);
                $vessel_Piechart->legend->labelFormatter($formatter);
                $vessel_Piechart->plotOptions->pie(array(
                    'dataLabels' => array('enabled' => false,'distance'=>-15,'format' => '{point.name}: {point.y}','style'=>array('fontWeight'> 'bold', 'color'=>'#333333')),
                    'startAngle'=> -90,
                    'endAngle'=> 90,
                    'center'=>array('50%', '75%'),
                ));
                $vessel_Piechart->series(array(array('type' => 'pie','showInLegend' => true,'name' => 'Vessel','innerSize'=> '50%', 'data' => $vessel_Piechart_data)));
               $vessel_Piechart->plotOptions->series(array(
                    'point' => array('events' => array('click' => new \Zend\Json\Expr('function () { location.href = this.options.url; }')))));
                $vessel_Piechart->exporting->enabled(false);

                //Vessel Pie Charts Ends Here//
                //KPI Pie Charts Starts Here//
                $kpi_alerts=new Highchart();
                $kpi_alerts->chart->renderTo('kpi_piechart');
                $kpi_alerts->chart->plotBackgroundColor(null);
                $kpi_alerts->chart->plotBorderWidth(0);
                $kpi_alerts->chart->plotShadow(false);
                $kpi_alerts->chart->marginTop(-35);
                $kpi_alerts->chart->marginLeft(-0);
                $kpi_alerts->chart->marginRight(-0);
                $kpi_alerts->chart->marginBottom(-0);
                $kpi_alerts->credits->enabled(false);
                $kpi_alerts->title->text('');
                $kpi_alerts->legend->enabled(true);
                $kpi_alerts->legend->floating(true);
                $kpi_alerts->legend->symbolHeight(4);
                $kpi_alerts->legend->symbolWidth(4);
                $kpi_alerts->legend->symbolRadius(2);
                $kpi_alerts->legend->itemMarginTop(1);
                $kpi_alerts->legend->labelFormatter($formatter);
                $kpi_alerts->plotOptions->pie(array(
                    'dataLabels' => array('enabled' => true,'distance'=>-15,'format' => '{point.name}: {point.y}','style'=>array('fontWeight'> 'bold', 'color'=>'#333333')),
                    'startAngle'=> -90,
                    'endAngle'=> 90,
                    'center'=>array('50%', '75%'),
                ));
                $kpi_Piechart_data=array(
                    array('name'=>'G','y'=>count($greenarea_kpiids),'color'=>'#1ea50b'),
                    array('name'=>'Y','y'=>count($yellowarea_kpiids),'color'=>'#feba06'),
                    array('name'=>'R','y'=>count($redarea_kpiids),'color'=>'#b30000')
                );

                //$kpi_alerts->series(array(array('type' => 'pie','name' => 'Browser share', 'data' => $kpi_Piechart_data)));
                $kpi_alerts->series(array(array('type' => 'pie','showInLegend' => true,'name' => 'KPI','innerSize'=> '50%', 'data' => $kpi_Piechart_data)));
                $kpi_alerts->plotOptions->series(array(
                    'dataLabels'=>array( 'enabled'=> false,'inside'=> true,)
                    ));
                //KPI Pie Charts Ends Here//

                return $this->render(
                    'InitialShippingBundle:DashBorad:home.html.twig',
                    array(
                        'ship_count' => count($listAllShipForCompany),
                        'kpi_list' => $scorecardKpiList,
                        'month_name' => array_reverse($quarterMonthName),
                        'kpicolorarray' => array_reverse($quarterMonthColor),
                        'yearKpiColorArray' => $monthlyScorecardKpiColorArray,
                        'yearAvgScore' => $monthlyKpiAverageValueTotal,
                        'yearMonthName' => $monthLetterArray,
                        'currentYear' => $currentyear,
                        'kpiCount' => count($scorecardKpiList),
                        'kpiAverageScore' => array_reverse($quarterMonthKpiWeight),
                        'allships' => $listAllShipForCompany,
                        'chart' => $ob,
                        'vessel_piechart'=>$vessel_Piechart,
                        'kpi_piechart'=>$kpi_alerts,
                        'rankinKpiCount' => $rKPICount,
                        'currentmonth' => '',
                        'currentyear' => $yChange,
                        'heading' => 'Management Performance'
                    )
                );
            }
            else
            {
                return $this->redirectToRoute('adddata_scorecard');
            }
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
    public function monthchangeofrankingkpiAction(Request $request, $dataofmonth)
    {
        $chartobject = $this->indexAction($request, 'getnextmonthchart', $dataofmonth);
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
        $user = $this->getUser();
        if ($user != null) {
            $year = $request->request->get('Year');
            $intYear = (int)$year - 1;
            $yearValue = $this->indexAction($request, '', '', $intYear);

            $response = new JsonResponse();
            $response->setData(array(
                'yearKpiColorArray' => $yearValue['yearKpiColorArray'],
                'yearAvgScore' => $yearValue['yearAvgScore'],
                'yearMonthName' => $yearValue['yearMonthName'],
                'currentYear' => $yearValue['currentYear'],
                'kpiNameList' => $yearValue['kpi_list'],
                'heading' => 'Management Performance'
            ));
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Ajax Call For change of monthdata of Rankinng Chart
     *
     * @Route("/nextYearChange", name="nextYearChange")
     */
    public function nextYearChangeAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $year = $request->request->get('Year');
            $intYear = (int)$year + 1;
            $yearValue = $this->indexAction($request, '', '', $intYear);

            $response = new JsonResponse();
            $response->setData(array(
                'yearKpiColorArray' => $yearValue['yearKpiColorArray'],
                'yearAvgScore' => $yearValue['yearAvgScore'],
                'yearMonthName' => $yearValue['yearMonthName'],
                'currentYear' => $yearValue['currentYear'],
                'kpiNameList' => $yearValue['kpi_list']
            ));
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    /**
     * List all kpi for ship
     *
     * @Route("/{shipid}/listallkpiforship", name="listallkpiforship")
     */
    public function listallkpiforshipAction($shipid, Request $request, $mode = '')
    {
        $newshipid = $shipid;

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $userId = $user->getId();
            $username = $user->getUsername();
            $loginuseremail = $user->getEmail();
            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $company_details_id_query = $em->createQueryBuilder()
                    ->select('b.id')
                    ->from('InitialShippingBundle:CompanyDetails', 'b')
                    ->where('b.adminName = :username')
                    ->setParameter('username', $username)
                    ->getQuery()
                    ->getResult();
                $company_details_id = $company_details_id_query[0]['id'];
            } else {
                $company_details_id_query = $em->createQueryBuilder()
                    ->select('identity(a.companyid)')
                    ->from('InitialShippingBundle:User', 'a')
                    ->where('a.id = :user_id')
                    ->setParameter('user_id', $userId)
                    ->getQuery()
                    ->getResult();
                $company_details_id = $company_details_id_query[0][1];
            }

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
                ->setParameter('company_id', $company_details_id)
                ->addOrderBy('a.id', 'DESC')
                ->getQuery()
                ->getResult();

            $lastmonthdetail = $lastdate[0]['dataOfMonth'];
            $lastfivedatearray = array();
            $mystringvaluedate = $lastmonthdetail->format('Y-m-d');
            array_push($lastfivedatearray, $mystringvaluedate);
            for ($i = 0; $i < 2; $i++) {
                $mydatevalue = new \DateTime($mystringvaluedate);

                $mydatevalue->modify("last day of previous month");
                $myvalue = $mydatevalue->format("Y-m-d");
                array_push($lastfivedatearray, $myvalue);

                $mystringvaluedate = $myvalue;

            }

            $listallkpi = $em->createQueryBuilder()
                ->select('a.kpiName', 'a.id', 'a.weightage')
                ->from('InitialShippingBundle:KpiDetails', 'a')
                ->where('a.shipDetailsId = :shipid')
                ->setParameter('shipid', $shipid)
                ->getQuery()
                ->getResult();
            $newcategories = array();
            $finalkpielementvaluearray = array();
            $datescolorarray = array();
            $kpiweightagearray = array();

            //loop for sending dates//
            for ($d = 0; $d < count($lastfivedatearray); $d++) {
                $time2 = strtotime($lastfivedatearray[$d]);
                $monthinletter = date('M-Y', $time2);
                array_push($newcategories, $monthinletter);
                $new_monthdetail_date = new \DateTime($lastfivedatearray[$d]);

                $finalkpielementvalue = 0;
                $findingcolorarray = array();

                for ($element = 0; $element < count($listallkpi); $element++) {

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
                    if (count($findelementidarray) == 0) {
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
                                ->setParameter('kpiDetailsId', $newkpiid[0]['id'])
                                ->setParameter('Elementid', $findelementidarray[$jk]['id'])
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

                        $kpi_rules = $em->createQueryBuilder()
                            ->select('a.rules')
                            ->from('InitialShippingBundle:KpiRules', 'a')
                            ->where('a.kpiDetailsId = :kpi_id')
                            ->setParameter('kpi_id', $newkpiid[0]['id'])
                            ->getQuery()
                            ->getResult();
                        $read1 = "";

                        for ($kpi_rules_count = 0; $kpi_rules_count < count($kpi_rules); $kpi_rules_count++) {
                            $rule = $kpi_rules[$kpi_rules_count];
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
                    if (count($findelementidarray) > 0) {
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

                            if (count($dbvalueforelement) == 0) {
                                $finddbvaluefomula = 0 * (((int)$weightage) / 100);
                                $finalkpivalue += $finddbvaluefomula;
                            } else {
                                $finddbvaluefomula = ((float)($dbvalueforelement[0]['value'])) * (((int)$weightage) / 100);
                                $finalkpivalue += $finddbvaluefomula;
                            }
                        }

                        $kpi_rules = $em->createQueryBuilder()
                            ->select('a.rules')
                            ->from('InitialShippingBundle:KpiRules', 'a')
                            ->where('a.kpiDetailsId = :kpi_id')
                            ->setParameter('kpi_id', $kpiidvalue)
                            ->getQuery()
                            ->getResult();
                        $read1 = "";

                        //Find the color based on kpi rules
                        for ($kpi_rules_count = 0; $kpi_rules_count < count($kpi_rules); $kpi_rules_count++) {
                            $rule = $kpi_rules[$kpi_rules_count];
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
            $ob->credits->enabled(false);
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
                ->select('a.comment', 'a.datetime', 'b.adminName')
                ->from('InitialShippingBundle:SendCommand', 'a')
                ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.emailId = a.clientemail')
                ->where('a.shipid = :shipid')
                ->andwhere('b.emailId = :username')
                ->setParameter('username', $loginuseremail)
                ->setParameter('shipid', $shipid)
                ->getQuery()
                ->getResult();

            if ($mode == 'kpi_id') {
                return $datescolorarray;
            }
            if ($mode == 'pdftemplate_shiplevel') {
                return array(
                    'kpicolorarray' => $datescolorarray,
                    'listofkpi' => $listallkpi,
                    'kpiweightage' => $kpiweightagearray,
                    'montharray' => $newcategories,
                    'avgscore' => $finalkpielementvaluearray,
                    'commentarray' => $listofcomment,
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
                    'commentarray' => $listofcomment,
                    'shipid' => $newshipid
                )
            );
        }
    }


    /**
     * Find Elements original value
     *
     * @Route("/{id}/find_element_original_value", name="find_element_original_value")
     */
    public function findElementOriginalValueAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null) {
            $monthInNumber = $request->request->get('monthNumber');
            $kpiDetailsId = $request->request->get('kpiDetailsId');
            $da = new \DateTime(date('01-'.$monthInNumber.'-Y'));
            $da->modify('last day of this month');

            $shipDetails = $em->createQueryBuilder()
                ->select('a.shipName, a.id')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->getQuery()
                ->getResult();
            $elementDetails = $em->createQueryBuilder()
                ->select('a.elementName, a.id')
                ->from('InitialShippingBundle:ElementDetails', 'a')
                ->where('a.kpiDetailsId = :kpiId')
                ->setParameter('kpiId', $kpiDetailsId)
                ->getQuery()
                ->getResult();
            $elementOriginalValuesArray = array();
            for($i=0;$i<count($shipDetails);$i++) {
                for($j=0;$j<count($elementDetails);$j++) {
                    $elementOriginalValues = $em->createQueryBuilder()
                        ->select('a.value')
                        ->from('InitialShippingBundle:ReadingKpiValues', 'a')
                        ->where('a.monthdetail = :month and a.elementDetailsId = :elementId and a.shipDetailsId = :shipId')
                        ->setParameter('month', $da)
                        ->setParameter('elementId',$elementDetails[$j]['id'])
                        ->setParameter('shipId',$shipDetails[$i]['id'])
                        ->getQuery()
                        ->getResult();
                    $elementOriginalValuesArray[$i][$j]=$elementOriginalValues[0]['value'];
                }
            }
            $response = new JsonResponse();
            $response->setData(array(
                'elementOriginalValues' => $elementOriginalValuesArray,
                'shipDetail' => $shipDetails,
                'monthName' => date("M",strtotime('01-'.$monthInNumber.'-2011')),
                'monthNumber' => $monthInNumber
            ));
            return $response;

        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Find previous month elements original value
     *
     * @Route("/{id}/previous_month_element_original_value", name="previous_month_element_original_value")
     */
    public function previousMonthElementOriginalValueAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null) {
            $monthInNumber = $request->request->get('monthNumber');
            $kpiDetailsId = $request->request->get('kpiDetailsId');
            $da = new \DateTime(date('01-'.$monthInNumber.'-Y'));
            $da->modify('last day of this month');

            $shipDetails = $em->createQueryBuilder()
                ->select('a.shipName, a.id')
                ->from('InitialShippingBundle:ShipDetails', 'a')
                ->getQuery()
                ->getResult();
            $elementDetails = $em->createQueryBuilder()
                ->select('a.elementName, a.id')
                ->from('InitialShippingBundle:ElementDetails', 'a')
                ->where('a.kpiDetailsId = :kpiId')
                ->setParameter('kpiId', $kpiDetailsId)
                ->getQuery()
                ->getResult();
            $elementOriginalValuesArray = array();
            for($i=0;$i<count($shipDetails);$i++) {
                for($j=0;$j<count($elementDetails);$j++) {
                    $elementOriginalValues = $em->createQueryBuilder()
                        ->select('a.value')
                        ->from('InitialShippingBundle:ReadingKpiValues', 'a')
                        ->where('a.monthdetail = :month and a.elementDetailsId = :elementId and a.shipDetailsId = :shipId')
                        ->setParameter('month', $da)
                        ->setParameter('elementId',$elementDetails[$j]['id'])
                        ->setParameter('shipId',$shipDetails[$i]['id'])
                        ->getQuery()
                        ->getResult();
                    $elementOriginalValuesArray[$i][$j]=$elementOriginalValues[0]['value'];
                }
            }
            $response = new JsonResponse();
            $response->setData(array(
                'elementOriginalValues' => $elementOriginalValuesArray,
                'shipDetail' => $shipDetails,
                'monthName' => date("M",strtotime('01-'.$monthInNumber.'-2011')),
                'monthNumber' => $monthInNumber
            ));
            return $response;

        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * List all element for kpi
     *
     * @Route("/{kpiid}/listelementforkpi", name="listelementforkpi")
     */
    public function listallelementforkpiAction($kpiid, Request $request, $mode = '')
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null) {
            $datesArray = array();
            for ($m = 2; $m <= 13; $m++) {
                $month = date('Y-m-d', mktime(0, 0, 0, $m, 0, date('Y')));
                array_push($datesArray, $month);
            }
            $statusVerified = 0;
            $currentMonth = date('n');
            $currentMonthObject = new \DateTime($datesArray[$currentMonth - 1]);
            $currentMonthObject->modify('last day of this month');
            $statusVerified = $currentMonth - 1;
            $monthlyShipDataStatus = $em->createQueryBuilder()
                ->select('b.status')
                ->from('InitialShippingBundle:Scorecard_LookupStatus', 'b')
                ->where('b.dataofmonth = :monthDetail')
                ->setParameter('monthDetail', $currentMonthObject)
                ->getQuery()
                ->getResult();

            if (count($monthlyShipDataStatus) != 0 && $monthlyShipDataStatus[0]['status'] == 4) {
                $statusVerified = (int)$currentMonth;
            } else {
                $statusFieldQuery = $em->createQueryBuilder()
                    ->select('b.dataofmonth,b.status')
                    ->from('InitialShippingBundle:Scorecard_LookupStatus', 'b')
                    ->where('b.status = :monthStatus')
                    ->setParameter('monthStatus', 4)
                    ->groupby('b.dataofmonth')
                    ->getQuery()
                    ->getResult();
                if (count($statusFieldQuery) != 0 && $statusFieldQuery[count($statusFieldQuery) - 1]['status'] == 4) {
                    $dateFromDb = $statusFieldQuery[count($statusFieldQuery) - 1]['dataofmonth'];
                    $statusVerified = $dateFromDb->format('n');
                }
            }

            $quarterDatesArray = array();
            if ($statusVerified <= 2) {
                $date = $datesArray[(int)$statusVerified-1];
                for ($d = 0; $d <= 2; $d++) {
                    if($d==0) {
                        array_push($quarterDatesArray, $date);
                    } else {
                        $previousMonthDate = date('Y-m-d', strtotime('last month', strtotime($quarterDatesArray[$d-1])));
                        $lastMonthDetail = new \DateTime($previousMonthDate);
                        $previousMonth = $lastMonthDetail->modify('last day of this month');
                        array_push($quarterDatesArray, $previousMonth->format('Y-m-d'));
                        $date = $quarterDatesArray[$d];
                    }
                }
                $quarterDatesArray = array_reverse($quarterDatesArray);
            } else {
                for ($d = $statusVerified - 3; $d <= $statusVerified - 1; $d++) {
                    array_push($quarterDatesArray, $datesArray[$d]);
                }
            }
            $email = $user->getEmail();
            $scorecardKpiList = $em->createQueryBuilder()
                ->select('a.kpiName', 'a.id', 'a.weightage')
                ->from('InitialShippingBundle:KpiDetails', 'a')
                ->groupby('a.kpiName')
                ->getQuery()
                ->getResult();
            $monthLetterArray = array();
            $monthlyScorecardKpiColorArray = array();
            $monthlyKpiAverageValueTotal = array();
            $monthlyElementColorArray = array();
            $monthlyElementValueArray = array();
            for ($monthCount = 0; $monthCount < count($quarterDatesArray); $monthCount++) {
                $scorecardKpiColorArray = array();
                $date = strtotime($quarterDatesArray[$monthCount]);
                $monthLetterFormat = date('M', $date);
                $monthNumberFormat = date('m', $date);
                array_push($monthLetterArray, $monthLetterFormat);
                $monthDetail = new \DateTime($quarterDatesArray[$monthCount]);
                $monthlyScorecardKpiWeightAverageValueTotal = 0;
                for ($kpiCount = 0; $kpiCount < count($scorecardKpiList); $kpiCount++) {
                    $scorecardAllKpiId = $scorecardKpiList[$kpiCount]['id'];
                    if ($kpiid == $scorecardAllKpiId) {
                        $scorecardElementRules = array();
                        $scorecardElementValueArray = array();
                        $kpiElementColorArray = array();
                        $scorecardKpiId = $scorecardKpiList[$kpiCount]['id'];
                        $scorecardKpiWeight = $scorecardKpiList[$kpiCount]['weightage'];
                        $scorecardKpiName = $scorecardKpiList[$kpiCount]['kpiName'];

                        $scorecardElementArray = $em->createQueryBuilder()
                            ->select('c.id, c.weightage, c.elementName')
                            ->from('InitialShippingBundle:ElementDetails', 'c')
                            ->where('c.kpiDetailsId = :kpiId')
                            ->setParameter('kpiId', $scorecardKpiId)
                            ->getQuery()
                            ->getResult();
                        if (count($scorecardElementArray) > 0) {
                            for ($elementCount = 0; $elementCount < count($scorecardElementArray); $elementCount++) {
                                $scorecardElementId = $scorecardElementArray[$elementCount]['id'];
                                $scorecardElementRulesArray = $em->createQueryBuilder()
                                    ->select('a.rules')
                                    ->from('InitialShippingBundle:Rules', 'a')
                                    ->where('a.elementDetailsId = :elementId')
                                    ->setParameter('elementId', $scorecardElementId)
                                    ->getQuery()
                                    ->getResult();
                                array_push($scorecardElementRules, $scorecardElementRulesArray);
                                $elementResultColor = "";
                                $elementColorValue = 0;
                                $scorecardElementResult = $em->createQueryBuilder()
                                    ->select('b.elementcolor')
                                    ->from('InitialShippingBundle:Scorecard_LookupData', 'b')
                                    ->where('b.kpiDetailsId = :kpiId and b.elementDetailsId = :elementId and b.monthdetail = :monthDetail')
                                    ->setParameter('kpiId', $scorecardAllKpiId)
                                    ->setParameter('elementId', $scorecardElementId)
                                    ->setParameter('monthDetail', $monthDetail)
                                    ->getQuery()
                                    ->getResult();
                                if (count($scorecardElementResult) != 0) {
                                    $elementResultColor = $scorecardElementResult[0]['elementcolor'];
                                }

                                array_push($kpiElementColorArray, $elementResultColor);
                            }
                        } else {
                            array_push($kpiElementColorArray, 'false');
                        }
                        $kpiResult = $em->createQueryBuilder()
                            ->select('b.kpiColor, b.individualKpiAverageScore')
                            ->from('InitialShippingBundle:Scorecard_LookupData', 'b')
                            ->where('b.kpiDetailsId = :kpiId and b.monthdetail = :monthDetail')
                            ->setParameter('kpiId', $scorecardAllKpiId)
                            ->setParameter('monthDetail', $monthDetail)
                            ->groupby('b.kpiDetailsId')
                            ->getQuery()
                            ->getResult();
                        if (count($kpiResult) != 0) {
                            array_push($scorecardKpiColorArray, $kpiResult[0]['kpiColor']);
                            $monthlyScorecardKpiWeightAverageValueTotal += ($kpiResult[0]['individualKpiAverageScore'] * $scorecardKpiWeight) / 100;
                            array_push($scorecardElementValueArray, (int)$kpiResult[0]['individualKpiAverageScore']);
                        } else {
                            array_push($scorecardKpiColorArray, "");
                            $monthlyScorecardKpiWeightAverageValueTotal += 0;
                            array_push($scorecardElementValueArray, 0);
                        }
                    }
                }
                array_push($monthlyScorecardKpiColorArray, $scorecardKpiColorArray);
                array_push($monthlyKpiAverageValueTotal, $monthlyScorecardKpiWeightAverageValueTotal);
                array_push($monthlyElementColorArray, $kpiElementColorArray);
                array_push($monthlyElementValueArray, $scorecardElementValueArray);
            }

            $graphMonthLetterArray = array();
            $graphMonthlyElementValueArray = array();
            for($num=0;$num<count($quarterDatesArray);$num++) {
                $statusFieldQuery = $em->createQueryBuilder()
                    ->select('b.dataofmonth,b.status')
                    ->from('InitialShippingBundle:Scorecard_LookupStatus', 'b')
                    ->where('b.status = :monthStatus')
                    ->andwhere ('b.dataofmonth = :pre_date')
                    ->setParameter('monthStatus', 4)
                    ->setParameter('pre_date', $quarterDatesArray[$num])
                    ->groupby('b.dataofmonth')
                    ->getQuery()
                    ->getResult();
                if(count($statusFieldQuery) != 0 && $statusFieldQuery[count($statusFieldQuery) - 1]['status'] == 4) {
                    array_push($graphMonthLetterArray,$monthLetterArray[$num]);
                    array_push($graphMonthlyElementValueArray,$monthlyElementValueArray[$num]);
                }
            }
            $series = array
            (
                array("name" => "$scorecardKpiName", 'showInLegend' => false, 'color' => '#103a71', "data" => $graphMonthlyElementValueArray),

            );

            $ob = new Highchart();
            $ob->chart->renderTo('area');
            $ob->chart->type('line');
            $ob->title->text($scorecardKpiName, array('style' => array('color' => 'red')));
            $ob->xAxis->categories($graphMonthLetterArray);
            $ob->xAxis->labels(array('style' => array('color' => '#0000F0')));
            $ob->yAxis->min(0);
            $ob->yAxis->max(3);
            $ob->series($series);
            $ob->plotOptions->series(array('allowPointSelect' => true, 'dataLabels' => array('enabled' => true)));
            $ob->exporting->enabled(false);

            $commentForElementKpi = $em->createQueryBuilder()
                ->select('a.comment', 'a.datetime', 'b.username')
                ->from('InitialShippingBundle:SendCommand', 'a')
                ->join('InitialShippingBundle:User', 'b', 'WITH', 'b.email = a.clientemail')
                ->where('a.kpiid = :kpiid')
                ->andwhere('b.email = :username')
                ->setParameter('username', $email)
                ->setParameter('kpiid', $kpiid)
                ->getQuery()
                ->getResult();
            if ($mode == 'pdftemplate_kpilevel') {
                return array(
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

                );
            }
            return $this->render(
                'InitialShippingBundle:DashBorad:elementforkpi.html.twig',
                array(
                    'listofelement' => $scorecardElementArray,
                    'kpiname' => $scorecardKpiName,
                    'chart' => $ob,
                    'montharray' => $monthLetterArray,
                    'monthInNumber' => $monthNumberFormat,
                    'elementcolorarray' => $monthlyElementColorArray,
                    'countmonth' => count($monthlyElementColorArray),
                    'kpiid' => $kpiid,
                    'commentarray' => $commentForElementKpi,
                    'kpi_color' => $monthlyScorecardKpiColorArray,
                    'elementRule' => $scorecardElementRules
                )
            );
        } else {
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
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $userId = $user->getId();
            $username = $user->getUsername();
            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $query = $em->createQueryBuilder()
                    ->select('a.id')
                    ->from('InitialShippingBundle:CompanyDetails', 'a')
                    ->join('InitialShippingBundle:User', 'b', 'WITH', 'b.username = a.adminName')
                    ->where('b.username = :username')
                    ->setParameter('username', $username)
                    ->getQuery();
            } else {
                $query = $em->createQueryBuilder()
                    ->select('identity(a.companyid)')
                    ->from('InitialShippingBundle:User', 'a')
                    ->where('a.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            }
            $companyid = $query->getSingleScalarResult();
            $searchstring = $request->request->get('searchstring');
            $newcompanyid = $em->getRepository('InitialShippingBundle:CompanyDetails')->findOneBy(array('id' => $companyid));
            $qb = $em->createQueryBuilder();
            $qb
                ->select('a.groupname', 'b.useremailid')
                ->from('InitialShippingBundle:EmailGroup', 'a')
                ->join('InitialShippingBundle:EmailUsers', 'b', 'WITH', 'b.groupid = a.id')
                ->where('a.companyid = :companyid')
                ->andwhere('a.groupname LIKE :sreachstring')
                ->orwhere('b.useremailid LIKE :sreachstring')
                ->setParameter('companyid', $newcompanyid)
                ->setParameter('sreachstring', '%' . $searchstring . '%');
            $result = $qb->getQuery()->getResult();
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
        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        //get client Email Id
        $user = $this->getUser();
        $username = $user->getUsername();
        $emailid = $user->getEmail();
        //get Informaton From User
        $kpiid = $request->request->get('kpiid');

        $newkpiid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $kpiid));
        $comment = $request->request->get('comment');
        $today = date("Y-m-d H:i:s");
        $datetime = new \DateTime();
        $sendcommand = new SendCommandRanking();
        $sendcommand->setClientemail($emailid);
        $sendcommand->setComment($comment);
        $sendcommand->setDatetime($datetime);
        $sendcommand->setShipid($kpiid);
        $em->persist($sendcommand);
        $em->flush();
        $lastid = $sendcommand->getId();
        $lastarray = array('id' => $lastid);
        $session->set('commandid', $lastid);

        $listofcomment = $em->createQueryBuilder()
            ->select('a.comment', 'a.datetime', 'b.username')
            ->from('InitialShippingBundle:SendCommandRanking', 'a')
            ->join('InitialShippingBundle:User', 'b', 'WITH', 'b.email = a.clientemail')
            ->where('a.shipid = :shipid')
            ->andwhere('b.email = :username')
            ->setParameter('username', $emailid)
            ->setParameter('shipid', $kpiid)
            ->getQuery()
            ->getResult();
        $response = new JsonResponse();
        $response->setData(array('resultarray' => $listofcomment, 'lastinsertid' => $lastid));
        return $response;

    }

    /**
     * Add commen kpi reports
     *
     * @Route("/addcomment_ranking_ship", name="addcomment_ranking_ship")
     */
    public function runtimekpicommentAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        //get client Email Id
        $user = $this->getUser();
        $username = $user->getUsername();
        $emailid = $user->getEmail();
        //get Informaton From User
        $kpiid = $request->request->get('kpiid');

        $newkpiid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $kpiid));
        $comment = $request->request->get('comment');
        $today = date("Y-m-d H:i:s");
        $datetime = new \DateTime();
        $sendcommand = new SendCommandRanking();
        $sendcommand->setClientemail($emailid);
        $sendcommand->setComment($comment);
        $sendcommand->setDatetime($datetime);
        $sendcommand->setKpiid($kpiid);
        $em->persist($sendcommand);
        $em->flush();
        $lastid = $sendcommand->getId();
        $lastarray = array('id' => $lastid);
        $session->set('commandid', $lastid);


        $listofcomment = $em->createQueryBuilder()
            ->select('a.comment', 'a.datetime', 'b.username')
            ->from('InitialShippingBundle:SendCommandRanking', 'a')
            ->join('InitialShippingBundle:User', 'b', 'WITH', 'b.email = a.clientemail')
            ->where('a.kpiid = :kpiid')
            ->andwhere('b.email = :username')
            ->setParameter('username', $emailid)
            ->setParameter('kpiid', $kpiid)
            ->getQuery()
            ->getResult();
        $response = new JsonResponse();
        $response->setData(array('resultarray' => $listofcomment, 'lastinsertid' => $lastid));
        return $response;
    }


    /**
     * List all kpi for ship
     *
     * @Route("/{shipid}/{year}/listallkpiforship_ranking", name="listallkpiforship_ranking")
     */
    public function listallkpiforship_rankingAction($shipid, $year = '', Request $request, $mode = '')
    {
        $newShipId = $shipid;
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('fos_user_security_login');
        }
        else
        {
            $userId = $user->getId();
            $username = $user->getUsername();
            $loginuseremail = $user->getEmail();
            $oneyear_montharray = array();
            if ($year == ' ')
            {
                $currentMonth=date('Y-m-d');
            }
            if ($year != ' ')
            {
                $dateformat=$year.'-m-d';
                $currentMonth=date($dateformat);
            }
            $new_monthdetail_date = new \DateTime($currentMonth);
            $new_monthdetail_date->modify('last day of this month');
            $currentyear=date_format($new_monthdetail_date, 'Y');
            $monthlyShipDataStatus = $em->createQueryBuilder()
                ->select('b.status')
                ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                ->where('b.shipid = :shipId and b.dataofmonth = :monthDetail')
                ->setParameter('shipId', $shipid)
                ->setParameter('monthDetail', $new_monthdetail_date)
                ->getQuery()
                ->getResult();

            if (count($monthlyShipDataStatus) != 0 && $monthlyShipDataStatus[0]['status'] == 4)
            {
                $currentMonthinter = date('n');
                $limit=(int)$currentMonthinter;
                // $statusVerified = $currentMonth;
                for ($m = 1; $m <= $limit; $m++) {
                    $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date($currentyear)));
                    array_push($oneyear_montharray, $month);
                }
            }
            else
            {/*
                $currentMonthinter = date('n');
                $limit=(int)$currentMonthinter;
                for ($m = 1; $m <=$limit; $m++) {
                    $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date($currentyear)));
                    array_push($oneyear_montharray, $month);
                }*/
                $statusFieldQuery = $em->createQueryBuilder()
                    ->select('b.dataofmonth,b.status')
                    ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                    ->where('b.shipid = :shipId and b.status = :monthStatus')
                    ->setParameter('monthStatus', 4)
                    ->setParameter('shipId', $shipid)
                    ->groupby('b.dataofmonth')
                    ->getQuery()
                    ->getResult();
                if (count($statusFieldQuery) != 0 && $statusFieldQuery[count($statusFieldQuery) - 1]['status'] == 4) {
                    for ($m =0; $m < count($statusFieldQuery); $m++)
                    {
                        $month = date_format($statusFieldQuery[$m]['dataofmonth'], 'Y-m-d');
                        array_push($oneyear_montharray, $month);

                    }
                }


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
            $shipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
            $shipname = $shipid->getShipName();
            $man_year = $shipid->getManufacturingYear();
            for ($d = 0; $d < count($oneyear_montharray); $d++) {
                $time2 = strtotime($oneyear_montharray[$d]);
                $monthinletter = date('M', $time2);
                array_push($newcategories, $monthinletter);
                $new_monthdetail_date = new \DateTime($oneyear_montharray[$d]);
                $new_monthdetail_date->modify('last day of this month');
                $rankingKpiValueCountArray = array();
                $rankingKpiWeightarray = array();

                for ($rankingKpiCount = 0; $rankingKpiCount < count($rankingKpiList); $rankingKpiCount++) {
                    $rankingElementValueTotal = 0;
                    $rankingKpiId = $rankingKpiList[$rankingKpiCount]['id'];
                    $rankingKpiWeight = $rankingKpiList[$rankingKpiCount]['weightage'];
                    $rankingKpiName = $rankingKpiList[$rankingKpiCount]['kpiName'];
                    array_push($rankingKpiWeightarray, $rankingKpiWeight);
                    if ($rankingKpiName=='Vessel age')
                    {
                        if ($man_year == "")
                        {
                            $yearcount = 0;
                        }
                        else
                        {

                            $man_datestring = $man_year . '-01-' . '01';
                            $temp_man_year = new \DateTime($man_datestring);
                            $temp_man_year->modify('last day of this month');
                            $Vessage_count= $temp_man_year->diff($new_monthdetail_date)->y;
                        }
                        $vesselage=($Vessage_count*$rankingKpiWeight)/20;
                        array_push($rankingKpiValueCountArray,$vesselage);

                    }
                    else
                    {

                        $rankingElementList = $em->createQueryBuilder()
                            ->select('c.id', 'c.elementName', 'c.weightage', 'a.value')
                            ->from('InitialShippingBundle:RankingElementDetails', 'c')
                            ->join('InitialShippingBundle:RankingMonthlyData', 'a', 'with', 'c.id = a.elementDetailsId')
                            ->where('c.kpiDetailsId = :kpiid and a.monthdetail = :datamonth and a.status = :rankingStatusValue and a.shipDetailsId = :shipId')
                            ->setParameter('kpiid', $rankingKpiId)
                            ->setParameter('datamonth', $new_monthdetail_date)
                            ->setParameter('rankingStatusValue', 3)
                            ->setParameter('shipId', $shipid)
                            ->getQuery()
                            ->getResult();

                        if (count($rankingElementList) > 0) {
                            for ($rankingElementCount = 0; $rankingElementCount < count($rankingElementList); $rankingElementCount++) {
                                $rankingElementName = $rankingElementList[$rankingElementCount]['elementName'];
                                $rankingElementId = $rankingElementList[$rankingElementCount]['id'];
                                $rankingElementWeight = $rankingElementList[$rankingElementCount]['weightage'];
                                $rankingElementValue = $rankingElementList[$rankingElementCount]['value'];
                                $rankingElementResultColor = "";
                                $rankingElementColorValue = 0;
                                $rankingElementResult = $em->createQueryBuilder()
                                    ->select('b.elementdata, b.elementcolor')
                                    ->from('InitialShippingBundle:Ranking_LookupData', 'b')
                                    ->where('b.kpiDetailsId = :kpiId and b.shipDetailsId = :shipId and b.elementDetailsId = :elementId and b.monthdetail = :monthDetail')
                                    ->setParameter('kpiId', $rankingKpiId)
                                    ->setParameter('shipId', $shipid)
                                    ->setParameter('elementId', $rankingElementId)
                                    ->setParameter('monthDetail', $new_monthdetail_date)
                                    ->getQuery()
                                    ->getResult();
                                if (count($rankingElementResult) != 0) {
                                    $rankingElementResultColor = $rankingElementResult[0]['elementcolor'];
                                }

                                if ($rankingElementResultColor == "false") {
                                    $rankingElementResultColor = "";
                                }

                                if ($rankingElementResultColor == 'Green') {
                                    $rankingElementColorValue = $rankingElementWeight;
                                } else if ($rankingElementResultColor == 'Yellow') {
                                    $rankingElementColorValue = $rankingElementWeight / 2;
                                } else if ($rankingElementResultColor == 'Red') {
                                    $rankingElementColorValue = 0;
                                }
                                $rankingElementValueTotal += $rankingElementColorValue;
                            }
                        }
                        if (count($rankingElementList) == 0) {
                            $newkpiid = $em->createQueryBuilder()
                                ->select('b.id')
                                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                                ->where('b.kpiName = :kpiName')
                                ->setParameter('kpiName', $rankingKpiName)
                                ->groupby('b.kpiName')
                                ->getQuery()
                                ->getResult();
                            $rankingElementList = $em->createQueryBuilder()
                                ->select('c.id', 'c.elementName', 'c.weightage', 'a.value')
                                ->from('InitialShippingBundle:RankingElementDetails', 'c')
                                ->join('InitialShippingBundle:RankingMonthlyData', 'a', 'with', 'c.id = a.elementDetailsId')
                                ->where('c.kpiDetailsId = :kpiid and a.monthdetail = :datamonth and a.status = :rankingStatusValue and a.shipDetailsId = :shipId')
                                ->setParameter('kpiid', $newkpiid[0]['id'])
                                ->setParameter('datamonth', $new_monthdetail_date)
                                ->setParameter('rankingStatusValue', 3)
                                ->setParameter('shipId', $shipid)
                                ->getQuery()
                                ->getResult();

                            for ($rankingElementCount = 0; $rankingElementCount < count($rankingElementList); $rankingElementCount++) {
                                $rankingElementName = $rankingElementList[$rankingElementCount]['elementName'];
                                $rankingElementId = $rankingElementList[$rankingElementCount]['id'];
                                $rankingElementWeight = $rankingElementList[$rankingElementCount]['weightage'];
                                $rankingElementValue = $rankingElementList[$rankingElementCount]['value'];
                                $rankingElementResultColor = "";
                                $rankingElementColorValue = 0;
                                $rankingElementResult = $em->createQueryBuilder()
                                    ->select('b.elementdata, b.elementcolor')
                                    ->from('InitialShippingBundle:Ranking_LookupData', 'b')
                                    ->where('b.kpiDetailsId = :kpiId and b.shipDetailsId = :shipId and b.elementDetailsId = :elementId and b.monthdetail = :monthDetail')
                                    ->setParameter('kpiId', $newkpiid[0]['id'])
                                    ->setParameter('shipId', $shipid)
                                    ->setParameter('elementId', $rankingElementId)
                                    ->setParameter('monthDetail', $new_monthdetail_date)
                                    ->getQuery()
                                    ->getResult();
                                if (count($rankingElementResult) != 0) {
                                    $rankingElementResultColor = $rankingElementResult[0]['elementcolor'];
                                }

                                if ($rankingElementResultColor == "false") {
                                    $rankingElementResultColor = "";
                                }

                                if ($rankingElementResultColor == 'Green') {
                                    $rankingElementColorValue = $rankingElementWeight;
                                } else if ($rankingElementResultColor == 'Yellow') {
                                    $rankingElementColorValue = $rankingElementWeight / 2;
                                } else if ($rankingElementResultColor == 'Red') {
                                    $rankingElementColorValue = 0;
                                }
                                $rankingElementValueTotal += $rankingElementColorValue;
                            }
                        }

                        array_push($rankingKpiValueCountArray, ($rankingElementValueTotal * $rankingKpiWeight / 100));
                    }
                }
                array_push($monthlyKpiValue, $rankingKpiValueCountArray);
                array_push($monthlyKpiAverageScore, array_sum($rankingKpiValueCountArray));
            }
            if ($man_year == "") {
                $yearcount = 0;
            } else {
                $currentdatestring = date('Y-m-d');
                $currentdatetime = new \DateTime($currentdatestring);
                // $man_datestring = $man_year . '-01-' . '01';
                // $d2 = new \DateTime($man_datestring);
                //$diff = $d2->diff($d1);
                //$yearcount = $diff->y;
                $man_datestring = $man_year . '-01-' . '01';
                $temp_man_year = new \DateTime($man_datestring);
                $temp_man_year->modify('last day of this month');
                $currentdatetime->modify('last day of this month');
                $yearcount= $temp_man_year->diff($currentdatetime)->y;
            }

            $series = array(
                array("name" => "$shipname", 'showInLegend' => false, 'color' => '#103a71', "data" => $monthlyKpiAverageScore)
            );
            $ob = new Highchart();
            $ob->chart->renderTo('area');
            $ob->credits->enabled(false);
            $ob->chart->type('line');
            $ob->title->text($shipname, array('style' => array('color' => 'red')));
            $ob->subtitle->style(array('color' => '#0000f0', 'fontWeight' => 'bold'));
            $ob->xAxis->categories($newcategories);
            $ob->xAxis->labels(array('style' => array('color' => '#0000F0')));
            $ob->series($series);
            $ob->plotOptions->series(array('allowPointSelect' => true, 'dataLabels' => array('enabled' => true)));
            $ob->exporting->enabled(false);
            $listofcomment = $em->createQueryBuilder()
                ->select('a.comment', 'a.datetime', 'b.username')
                ->from('InitialShippingBundle:SendCommandRanking', 'a')
                ->join('InitialShippingBundle:User', 'b', 'WITH', 'b.email = a.clientemail')
                ->where('a.shipid = :shipid')
                ->andwhere('b.email = :username')
                ->setParameter('username', $loginuseremail)
                ->setParameter('shipid', $shipid)
                ->getQuery()
                ->getResult();
            if ($mode == 'pdftemplate_shiplevel') {
                return array
                (
                    'listofkpi' => $rankingKpiList,
                    'kpiweightage' => $rankingKpiWeightarray,
                    'montharray' => $newcategories,
                    'avgscore' => $monthlyKpiAverageScore,
                    'commentarray' => $listofcomment,
                    'kpimonthdata' => $monthlyKpiValue,
                    'currentyear' => $currentyear,
                    'ageofvessel' => $yearcount
                );
            }
            if ($mode == 'pdftemplate_rankingoverall_shiplevel') {
                return array
                (
                    'listofkpi' => $rankingKpiList,
                    'kpiweightage' => $rankingKpiWeightarray,
                    'montharray' => $newcategories,
                    'avgscore' => $monthlyKpiAverageScore,
                    'commentarray' => $listofcomment,
                    'kpimonthdata' => $monthlyKpiValue,
                    'currentyear' => $currentyear,
                    'chart' => $ob,
                    'shipname' => $shipname,
                    'ageofvessel' => $yearcount
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
                    'commentarray' => $listofcomment,
                    'shipid' => $shipid->getId(),
                    'kpimonthdata' => $monthlyKpiValue,
                    'currentyear' => $currentyear,
                    'ageofvessel' => $yearcount
                )
            );
        }
    }

    /**
     * List all element for kpi
     *
     * @Route("/{kpiid}/listelementforkpi_ranking", name="listelementforkpi_ranking")
     */
    public function listallelementforkpi_rankingAction($kpiid, Request $request, $mode = '')
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null) {
            $userName = $user->getUsername();
            $email = $user->getEmail();

            $kpiDetailObject = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id' => $kpiid));
            $kpiName = $kpiDetailObject->getKpiName();
            $kpiWeight = $kpiDetailObject->getWeightage();
            $monthDetails = array();
            for ($m = 1; $m <= 12; $m++) {
                $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date('Y')));
                array_push($monthDetails, $month);
            }
            $shipidarray = $em->createQueryBuilder()
                ->select('identity(b.shipDetailsId)')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.id = :kpiid')
                ->setParameter('kpiid', $kpiid)
                ->getQuery()
                ->getResult();
            $shipId = $shipidarray[0][1];
            $currentMonth = date('n');
            $new_monthdetail_date = new \DateTime($monthDetails[$currentMonth - 1]);
            $new_monthdetail_date->modify('last day of this month');
            $statusVerified = $currentMonth - 1;
            $monthlyShipDataStatus = $em->createQueryBuilder()
                ->select('b.status')
                ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                ->where('b.shipid = :shipId and b.dataofmonth = :monthDetail')
                ->setParameter('shipId', $shipId)
                ->setParameter('monthDetail', $new_monthdetail_date)
                ->getQuery()
                ->getResult();

            if (count($monthlyShipDataStatus) != 0 && $monthlyShipDataStatus[0]['status'] == 4) {
                $statusVerified = $currentMonth;
            } else {
                $statusFieldQuery = $em->createQueryBuilder()
                    ->select('b.status, b.dataofmonth')
                    ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                    ->where('b.shipid = :shipId ')
                    ->setParameter('shipId', $shipId)
                    ->getQuery()
                    ->getResult();
                if (count($statusFieldQuery) != 0 && $statusFieldQuery[count($statusFieldQuery) - 1]['status'] == 4) {
                    $dateFromDb = $statusFieldQuery[count($statusFieldQuery) - 1]['dataofmonth'];
                    $statusVerified = $dateFromDb->format('n');
                }
            }
            if ($kpiName == 'Vessel age') {

                $monthNameLetter = array();
                $monthlyKpiAverageValueTotal = array();
                $monthlyElementColorArray = array();
                $monthlyElementValueArray = array();
                $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipId));
                $shipname = $newshipid->getShipName();
                $manufacturingYear=$newshipid->getManufacturingYear();
                for ($monthCount = 0; $monthCount < $statusVerified; $monthCount++) {
                    $scorecardElementValueArray = array();
                    $kpiElementColorArray = array();
                    $scorecardElementRules = array();
                    $kpiSumValue = 0;
                    $time2 = strtotime($monthDetails[$monthCount]);
                    $monthInLetter = date('M', $time2);
                    array_push($monthNameLetter, $monthInLetter);
                    $new_monthdetail_date = new \DateTime($monthDetails[$monthCount]);
                    $new_monthdetail_date->modify('last day of this month');

                    if ($manufacturingYear == "") {
                        $yearcount = 0;
                    } else {

                        $man_datestring = $manufacturingYear . '-01';
                        $temp_man_year = new \DateTime($man_datestring);
                        $temp_man_year->modify('last day of this month');
                        $Vessage_count = $temp_man_year->diff($new_monthdetail_date)->y;
                    }
                    $vesselage = ($Vessage_count * $kpiWeight) / 20;
                    echo $vesselage;
                    array_push($monthlyKpiAverageValueTotal, $vesselage);
                }
                $series = array
                (
                    array("name" => "$kpiName", 'showInLegend' => false, 'color' => '#103a71', "data" => $monthlyKpiAverageValueTotal),

                );
                $ob = new Highchart();
                $ob->chart->renderTo('area');
                $ob->credits->enabled(false);
                $ob->chart->type('line');
                $ob->title->text($kpiName, array('style' => array('color' => 'red')));
                $ob->subtitle->style(array('color' => '#0000f0', 'fontWeight' => 'bold'));
                $ob->xAxis->categories($monthNameLetter);
                $ob->xAxis->labels(array('style' => array('color' => '#0000F0')));
                $ob->series($series);
                $ob->plotOptions->series(array('allowPointSelect' => true, 'dataLabels' => array('enabled' => true)));
                $ob->exporting->enabled(false);

                $listofcomment = $em->createQueryBuilder()
                    ->select('a.comment', 'a.datetime', 'b.username')
                    ->from('InitialShippingBundle:SendCommandRanking', 'a')
                    ->join('InitialShippingBundle:User', 'b', 'WITH', 'b.email = a.clientemail')
                    ->where('a.kpiid = :kpiid')
                    ->andwhere('b.email = :username')
                    ->setParameter('username', $email)
                    ->setParameter('kpiid', $kpiid)
                    ->getQuery()
                    ->getResult();

                if ($mode == 'pdftemplate_kpilevel') {
                    return array(
                        'elcolorarray' => array(),
                        'listofelement' => array(),
                        'montharraementy' => $monthNameLetter,
                        'avgscore' => $monthlyKpiAverageValueTotal,
                        'commentarray' => $listofcomment,
                        'monthlydata' => array(),
                        'elementRule' => array()
                    );
                }

                return $this->render(
                    'InitialShippingBundle:DashBorad:elementforkpi_ranking.html.twig',
                    array(
                        'listofelement' => array(),
                        'kpiname' => $kpiName,
                        'chart' => $ob,
                        'shipname' => $shipname,
                        'monthdetails' => $monthNameLetter,
                        'elementcolorarray' => array(),
                        'countmonth' => count($monthNameLetter),
                        'avgscore' => $monthlyKpiAverageValueTotal,
                        'kpiid' => $kpiid,
                        'commentarray' => $listofcomment,
                        'shipid' => $shipId,
                        'monthlydata' => array(),
                        'elementRule' => array()
                    )
                );
            }
            else
            {

                $elementForKpiList = $em->createQueryBuilder()
                    ->select('a.elementName', 'a.id', 'a.weightage')
                    ->from('InitialShippingBundle:RankingElementDetails', 'a')
                    ->where('a.kpiDetailsId = :kpiid')
                    ->setParameter('kpiid', $kpiid)
                    ->getQuery()
                    ->getResult();
                if (count($elementForKpiList) != 0) {
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
                    $monthlyweightage_status=array();
                    for ($monthCount = 0; $monthCount < $statusVerified; $monthCount++) {
                        $scorecardElementValueArray = array();
                        $kpiElementColorArray = array();
                        $scorecardElementRules = array();
                        $kpiSumValue = 0;
                        $time2 = strtotime($monthDetails[$monthCount]);
                        $monthInLetter = date('M', $time2);
                        array_push($monthNameLetter, $monthInLetter);
                        $new_monthdetail_date = new \DateTime($monthDetails[$monthCount]);
                        $new_monthdetail_date->modify('last day of this month');

                        for ($elementCount = 0; $elementCount < count($elementForKpiList); $elementCount++) {
                            $scorecardElementId = $elementForKpiList[$elementCount]['id'];
                            $scorecardElementWeight = $elementForKpiList[$elementCount]['weightage'];
                           /* $weightage_startus_Result = $em->createQueryBuilder()
                                ->select('a.weightage','a.endDate','a.id','a.status')
                                ->from('InitialShippingBundle:RankingElementWeightageStatus', 'a')
                                ->where('a.elementId = :elementid')
                                ->andWhere('a.endDate IS NULL')
                                ->setParameter('elementid', $scorecardElementId)
                                ->getQuery()
                                ->getResult();
                            if($weightage_startus_Result[0]['status']==1)
                            {*/
                            if($monthCount==0)
                            {
                                $weightage_all_result = $em->createQueryBuilder()
                                    ->select('a.weightage','a.endDate','a.id','a.status')
                                    ->from('InitialShippingBundle:RankingElementWeightageStatus', 'a')
                                    ->where('a.elementId = :elementid')
                                    ->setParameter('elementid', $scorecardElementId)
                                    ->getQuery()
                                    ->getResult();
                                $resultarray_weightage=array();
                                for($weightage_all_result_count=0;$weightage_all_result_count<count($weightage_all_result);$weightage_all_result_count++)
                                {
                                    $startdate=$weightage_all_result[$weightage_all_result_count]['endDate'];
                                    $endDate=$weightage_all_result[$weightage_all_result_count]['endDate'];
                                    if($startdate==$endDate)
                                    {

                                    }


                                }
                                array_push($monthlyweightage_status,$resultarray_weightage);
                            }

                            /*
                            }*/

                            $rankingElementRulesArray = $em->createQueryBuilder()
                                ->select('a.rules')
                                ->from('InitialShippingBundle:RankingRules', 'a')
                                ->where('a.elementDetailsId = :elementId')
                                ->setParameter('elementId', $scorecardElementId)
                                ->getQuery()
                                ->getResult();
                            $rankingElementResultColor = "";
                            $elementColorValue = 0;
                            $rankingElementResult = $em->createQueryBuilder()
                                ->select('b.elementdata, b.elementcolor')
                                ->from('InitialShippingBundle:Ranking_LookupData', 'b')
                                ->where('b.kpiDetailsId = :kpiId and b.shipDetailsId = :shipId and b.elementDetailsId = :elementId and b.monthdetail = :monthDetail')
                                ->setParameter('kpiId', $kpiid)
                                ->setParameter('shipId', $shipId)
                                ->setParameter('elementId', $scorecardElementId)
                                ->setParameter('monthDetail', $new_monthdetail_date)
                                ->getQuery()
                                ->getResult();
                            if (count($rankingElementResult) != 0) {
                                $rankingElementResultColor = $rankingElementResult[0]['elementcolor'];
                            } else {
                                $rankingElementResult[0]['elementdata'] = null;
                            }

                            if ($rankingElementResultColor == "false") {
                                $rankingElementResultColor = "";
                            }

                            if ($rankingElementResultColor == 'Green') {
                                $elementColorValue = $scorecardElementWeight;
                            } else if ($rankingElementResultColor == 'Yellow') {
                                $elementColorValue = $scorecardElementWeight / 2;
                            } else if ($rankingElementResultColor == 'Red') {
                                $elementColorValue = 0;
                            }

                            array_push($scorecardElementRules, $rankingElementRulesArray);
                            array_push($scorecardElementValueArray, (($rankingElementResult[0]['elementdata']) * $kpiWeight) / 100);
                            array_push($kpiElementColorArray, $rankingElementResultColor);
                            $elementValueWithWeight = $elementColorValue;
                            $kpiSumValue += $elementValueWithWeight;
                        }
                        array_push($monthlyKpiAverageValueTotal, ($kpiSumValue * $kpiWeight) / 100);
                        array_push($monthlyElementColorArray, $kpiElementColorArray);
                        array_push($monthlyElementValueArray, $scorecardElementValueArray);
                    }

                    $series = array
                    (
                        array("name" => "$kpiName", 'showInLegend' => false, 'color' => '#103a71', "data" => $monthlyKpiAverageValueTotal),

                    );
                    $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipId));
                    $shipname = $newshipid->getShipName();

                    $ob = new Highchart();
                    $ob->chart->renderTo('area');
                    $ob->credits->enabled(false);
                    $ob->chart->type('line');
                    $ob->title->text($kpiName, array('style' => array('color' => 'red')));
                    $ob->subtitle->style(array('color' => '#0000f0', 'fontWeight' => 'bold'));
                    $ob->xAxis->categories($monthNameLetter);
                    $ob->xAxis->labels(array('style' => array('color' => '#0000F0')));
                    $ob->series($series);
                    $ob->plotOptions->series(array('allowPointSelect' => true, 'dataLabels' => array('enabled' => true)));
                    $ob->exporting->enabled(false);

                    $listofcomment = $em->createQueryBuilder()
                        ->select('a.comment', 'a.datetime', 'b.username')
                        ->from('InitialShippingBundle:SendCommandRanking', 'a')
                        ->join('InitialShippingBundle:User', 'b', 'WITH', 'b.email = a.clientemail')
                        ->where('a.kpiid = :kpiid')
                        ->andwhere('b.email = :username')
                        ->setParameter('username', $email)
                        ->setParameter('kpiid', $kpiid)
                        ->getQuery()
                        ->getResult();

                    if ($mode == 'pdftemplate_kpilevel') {
                        return array(
                            'elcolorarray' => $monthlyElementColorArray,
                            'listofelement' => $elementForKpiList,
                            'montharraementy' => $monthNameLetter,
                            'avgscore' => $monthlyKpiAverageValueTotal,
                            'commentarray' => $listofcomment,
                            'monthlydata' => $monthlyElementValueArray,
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
                            'shipid' => $shipId,
                            'monthlydata' => $monthlyElementValueArray,
                            'elementRule' => $scorecardElementRules
                        )
                    );
                } else {
                    $newkpiid = $em->createQueryBuilder()
                        ->select('b.id','b.weightage')
                        ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                        ->where('b.kpiName = :kpiName')
                        ->setParameter('kpiName', $kpiName)
                        ->groupby('b.kpiName')
                        ->getQuery()
                        ->getResult();
                    $kpiWeight=$newkpiid[0]['weightage'];
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
                    for ($monthCount = 0; $monthCount < $statusVerified; $monthCount++) {
                        $scorecardElementValueArray = array();
                        $kpiElementColorArray = array();
                        $scorecardElementRules = array();
                        $kpiSumValue = 0;
                        $time2 = strtotime($monthDetails[$monthCount]);
                        $monthInLetter = date('M', $time2);
                        array_push($monthNameLetter, $monthInLetter);
                        $new_monthdetail_date = new \DateTime($monthDetails[$monthCount]);
                        $new_monthdetail_date->modify('last day of this month');

                        for ($elementCount = 0; $elementCount < count($elementForKpiList); $elementCount++) {
                            $scorecardElementId = $elementForKpiList[$elementCount]['id'];
                            $scorecardElementWeight = $elementForKpiList[$elementCount]['weightage'];

                            $rankingElementRulesArray = $em->createQueryBuilder()
                                ->select('a.rules')
                                ->from('InitialShippingBundle:RankingRules', 'a')
                                ->where('a.elementDetailsId = :elementId')
                                ->setParameter('elementId', $scorecardElementId)
                                ->getQuery()
                                ->getResult();
                            $rankingElementResultColor = "";
                            $elementColorValue = 0;
                            $rankingElementResult = $em->createQueryBuilder()
                                ->select('b.elementdata, b.elementcolor')
                                ->from('InitialShippingBundle:Ranking_LookupData', 'b')
                                ->where('b.kpiDetailsId = :kpiId and b.shipDetailsId = :shipId and b.elementDetailsId = :elementId and b.monthdetail = :monthDetail')
                                ->setParameter('kpiId', $newkpiid[0]['id'])
                                ->setParameter('shipId', $shipId)
                                ->setParameter('elementId', $scorecardElementId)
                                ->setParameter('monthDetail', $new_monthdetail_date)
                                ->getQuery()
                                ->getResult();
                            if (count($rankingElementResult) != 0) {
                                $rankingElementResultColor = $rankingElementResult[0]['elementcolor'];
                            } else {
                                $rankingElementResult[0]['elementdata'] = null;
                            }

                            if ($rankingElementResultColor == "false") {
                                $rankingElementResultColor = "";
                            }

                            if ($rankingElementResultColor == 'Green') {
                                $elementColorValue = $scorecardElementWeight;
                            } else if ($rankingElementResultColor == 'Yellow') {
                                $elementColorValue = $scorecardElementWeight / 2;
                            } else if ($rankingElementResultColor == 'Red') {
                                $elementColorValue = 0;
                            }

                            array_push($scorecardElementRules, $rankingElementRulesArray);
                            array_push($scorecardElementValueArray, (($rankingElementResult[0]['elementdata']) * $kpiWeight) / 100);
                            array_push($kpiElementColorArray, $rankingElementResultColor);
                            $elementValueWithWeight = $elementColorValue;
                            $kpiSumValue += $elementValueWithWeight;
                        }
                        array_push($monthlyKpiAverageValueTotal, ($kpiSumValue * $kpiWeight) / 100);
                        array_push($monthlyElementColorArray, $kpiElementColorArray);
                        array_push($monthlyElementValueArray, $scorecardElementValueArray);
                    }

                    $series = array
                    (
                        array("name" => "$kpiName", 'showInLegend' => false, 'color' => '#103a71', "data" => $monthlyKpiAverageValueTotal),

                    );
                    $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipId));
                    $shipname = $newshipid->getShipName();

                    $ob = new Highchart();
                    $ob->chart->renderTo('area');
                    $ob->chart->type('line');
                    $ob->credits->enabled(false);
                    $ob->title->text($kpiName, array('style' => array('color' => 'red')));
                    $ob->subtitle->style(array('color' => '#0000f0', 'fontWeight' => 'bold'));
                    $ob->xAxis->categories($monthNameLetter);
                    $ob->xAxis->labels(array('style' => array('color' => '#0000F0')));
                    $ob->series($series);
                    $ob->plotOptions->series(array('allowPointSelect' => true, 'dataLabels' => array('enabled' => true)));
                    $ob->exporting->enabled(false);

                    $listofcomment = $em->createQueryBuilder()
                        ->select('a.comment', 'a.datetime', 'b.username')
                        ->from('InitialShippingBundle:SendCommandRanking', 'a')
                        ->join('InitialShippingBundle:User', 'b', 'WITH', 'b.email = a.clientemail')
                        ->where('a.kpiid = :kpiid')
                        ->andwhere('b.email = :username')
                        ->setParameter('username', $email)
                        ->setParameter('kpiid', $kpiid)
                        ->getQuery()
                        ->getResult();

                    if ($mode == 'pdftemplate_kpilevel') {
                        return array(
                            'elementcolorarray' => $monthlyElementColorArray,
                            'listofelement' => $elementForKpiList,
                            'montharray' => $monthNameLetter,
                            'avgscore' => $monthlyKpiAverageValueTotal,
                            'commentarray' => $listofcomment,
                            'monthlydata' => $monthlyElementValueArray,
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
                            'shipid' => $shipId,
                            'monthlydata' => $monthlyElementValueArray,
                            'elementRule' => $scorecardElementRules
                        )
                    );
                }
            }

        } else {
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
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null) {
            $username = $user->getUsername();
            $useremailaddres = $user->getEmail();

            $userquery = $em->createQueryBuilder()
                ->select('a.emailId')
                ->from('InitialShippingBundle:CompanyDetails', 'a')
                ->where('a.adminName = :userId')
                ->setParameter('userId', $username)
                ->getQuery();
            $clientemailid = $userquery->getSingleScalarResult();

            //get Informaton From User

            $params = $request->request->get('send_command');
            $kpiid = $params['kpiid'];
            $newkpiid = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $kpiid));
            $kpiname = $newkpiid->getKpiName();
            $returnvaluefrommonth = $this->listallelementforkpiAction($kpiid, $request, 'pdftemplate_kpilevel');


            $filename = $params['filename'];
            $pdffilenamearray = explode(".", $filename);

            $kpiid = $params['kpiid'];

            $comment = $params['comment'];
            $checkboxvalue = '';
            if (count($params) < 6) {
                $checkboxvalue = 'No';
                $listofcommentarray = array();

            } else {
                $checkboxvalue = $params['addcomment'];
                $commentForElementKpi = $em->createQueryBuilder()
                    ->select('a.comment')
                    ->from('InitialShippingBundle:SendCommand', 'a')
                    ->join('InitialShippingBundle:User', 'b', 'WITH', 'b.email = a.clientemail')
                    ->where('a.kpiid = :kpiid')
                    ->andwhere('b.email = :username')
                    ->setParameter('username', $useremailaddres)
                    ->setParameter('kpiid', $kpiid)
                    ->getQuery()
                    ->getResult();
                $listofcommentarray = $commentForElementKpi;
            }
            $idforrecord = $params['lastid'];

            $today = date("Y-m-d H:i:s");
            $pageName = $request->query->get('page');
            $screenName = $this->get('translator')->trans($pageName);
            $date = date('l jS F Y h:i', time());
            $route = $request->attributes->get('_route');
            /*return $this->render('InitialShippingBundle:DashBorad:pdfreporttemplate_scorecard_kpi.html.twig', array(
                'link' => $filename,
                'screenName' => $screenName,
                'elementList' => $returnvaluefrommonth['listofelement'],
                'kpiname' => $kpiname,
                'montharray' => $returnvaluefrommonth['montharray'],
                'elementcolorarray' => $returnvaluefrommonth['elementcolorarray'],
                'commentarray' => $listofcommentarray,
                'kpi_color' => $returnvaluefrommonth['kpi_color'],
            ));*/


            $customerListDesign = $this->renderView('InitialShippingBundle:DashBorad:pdfreporttemplate_scorecard_kpi.html.twig', array(
                'link' => $filename,
                'screenName' => $screenName,
                'elementList' => $returnvaluefrommonth['listofelement'],
                'kpiname' => $kpiname,
                'montharray' => $returnvaluefrommonth['montharray'],
                'elementcolorarray' => $returnvaluefrommonth['elementcolorarray'],
                'commentarray' => $listofcommentarray,
                'kpi_color' => $returnvaluefrommonth['kpi_color'],
            ));
            $client = new HighchartController();
            $client->setContainer($this->container);
            $printPdf = $client->createPdf($customerListDesign, $screenName);

            $pdffilenamefullpath = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/brochures/' . $pdffilenamearray[0] . '.pdf';
            file_put_contents($pdffilenamefullpath, $printPdf);
            //assign file attachement for mail and Mailing Starts Here...u
            $useremaildid = $params['clientemail'];
            $mailidarray = array();
            if (filter_var($useremaildid, FILTER_VALIDATE_EMAIL)) {
                array_push($mailidarray, $useremaildid);
            } else {
                $findsemail = $em->createQueryBuilder()
                    ->select('a.useremailid')
                    ->from('InitialShippingBundle:EmailUsers', 'a')
                    ->join('InitialShippingBundle:EmailGroup', 'b', 'WITH', 'b.id = a.groupid')
                    ->where('b.groupname = :sq')
                    ->ORwhere('a.useremailid = :sb')
                    ->setParameter('sq', $useremaildid)
                    ->setParameter('sb', $useremaildid)
                    ->getQuery()
                    ->getResult();


                //assign file attachement for mail and Mailing Starts Here...u
                for ($ma = 0; $ma < count($findsemail); $ma++) {
                    array_push($mailidarray, $findsemail[$ma]['useremailid']);
                }
            }
            //Mailing Ends....
            $rankinglookuptable = array('from_emailid' => $clientemailid, 'to_emailids' => $mailidarray, 'filename' => $pdffilenamearray[0] . '.pdf', 'comment' => $comment, 'subject' => $kpiname);
            $gearman = $this->get('gearman');
            $gearman->doBackgroundJob('InitialShippingBundleserviceReadExcelWorker~common_mail_function', json_encode($rankinglookuptable));
            //Mailing Ends....
            //Update Process Starts Here...
            $session = new Session();
            $kpiandelementids = $session->get('commandid');
            if ($kpiandelementids != null) {
                $entityobject = $em->getRepository('InitialShippingBundle:SendCommand')->find($kpiandelementids);
                $entityobject->setClientemail($clientemailid);
                $entityobject->setFilename($pdffilenamearray[0] . '.pdf');
                $em->flush();
            }

            $response = new JsonResponse();
            $response->setData(array('updatemsg' => "Report has been send"));
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Add comment for kpireports
     *
     * @Route("/addcommentkpi", name="addcommentkpi")
     */
    public function addcommentAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        //get client Email Id
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null) {
            $username = $user->getUsername();
            $useremailaddres = $user->getEmail();

            $userquery = $em->createQueryBuilder()
                ->select('a.emailId')
                ->from('InitialShippingBundle:CompanyDetails', 'a')
                ->where('a.adminName = :userId')
                ->setParameter('userId', $username)
                ->getQuery();
            $clientemailid = $userquery->getSingleScalarResult();

            //get Informaton From User

            $params = $request->request->get('send_command');
            $kpiid = $params['kpiid'];
            $newkpiid = $em->getRepository('InitialShippingBundle:RankingKpiDetails')->findOneBy(array('id' => $kpiid));
            $kpiname = $newkpiid->getKpiName();
            $returnvaluefrommonth = $this->listallelementforkpi_rankingAction($kpiid, $request, 'pdftemplate_kpilevel');


            $filename = $params['filename'];
            $pdffilenamearray = explode(".", $filename);

            $kpiid = $params['kpiid'];

            $comment = $params['comment'];
            $checkboxvalue = '';
            if (count($params) < 6) {
                $checkboxvalue = 'No';
                $listofcommentarray = array();

            } else {
                $checkboxvalue = $params['addcomment'];
                $listofcomment = $em->createQueryBuilder()
                    ->select('a.comment', 'a.datetime', 'b.adminName')
                    ->from('InitialShippingBundle:SendCommandRanking', 'a')
                    ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.emailId = a.clientemail')
                    ->where('a.kpiid = :kpiid')
                    ->andwhere('b.emailId = :username')
                    ->setParameter('username', $useremailaddres)
                    ->setParameter('kpiid', $kpiid)
                    ->getQuery()
                    ->getResult();
                $listofcommentarray = $listofcomment;
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
                'listofelement' => $returnvaluefrommonth['listofelement'],
                'kpiname' => $kpiname,
                'montharray' => $returnvaluefrommonth['montharray'],
                'elementcolorarray' => $returnvaluefrommonth['elementcolorarray'],
                'countmonth' => count($returnvaluefrommonth['elementcolorarray']),
                'avgscore' => $returnvaluefrommonth['avgscore'],
                'monthlydata' => $returnvaluefrommonth['monthlydata'],
                'commentarray' => $listofcommentarray,
                'datetime' => $today,
                'elementRule' => $returnvaluefrommonth['elementRule']
            ));
            $client = new HighchartController();
            $client->setContainer($this->container);
            $printPdf = $client->createPdf($customerListDesign, $screenName);

            $pdffilenamefullpath = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/brochures/' . $pdffilenamearray[0] . '.pdf';
            file_put_contents($pdffilenamefullpath, $printPdf);


            //$sendcommand=new SendCommand();
            //assign file attachement for mail and Mailing Starts Here...u
            $useremaildid = $params['clientemail'];
            $mailidarray = array();

            if (filter_var($useremaildid, FILTER_VALIDATE_EMAIL)) {
                array_push($mailidarray, $useremaildid);

            } else {
                $findsemail = $em->createQueryBuilder()
                    ->select('a.useremailid')
                    ->from('InitialShippingBundle:EmailUsers', 'a')
                    ->join('InitialShippingBundle:EmailGroup', 'b', 'WITH', 'b.id = a.groupid')
                    ->where('b.groupname = :sq')
                    ->ORwhere('a.useremailid = :sb')
                    ->setParameter('sq', $useremaildid)
                    ->setParameter('sb', $useremaildid)
                    ->getQuery()
                    ->getResult();


                //assign file attachement for mail and Mailing Starts Here...u
                for ($ma = 0; $ma < count($findsemail); $ma++) {
                    array_push($mailidarray, $findsemail[$ma]['emailid']);
                }
            }


            //Mailing Ends....
            $rankinglookuptable = array('from_emailid' => $clientemailid, 'to_emailids' => $mailidarray, 'filename' => $pdffilenamearray[0] . '.pdf', 'comment' => $comment, 'subject' => $kpiname);
            $gearman = $this->get('gearman');
            $gearman->doBackgroundJob('InitialShippingBundleserviceReadExcelWorker~common_mail_function', json_encode($rankinglookuptable));
            //Mailing Ends....
            //Update Process Starts Here...
            $session = new Session();
            $kpiandelementids = $session->get('commandid');
            if ($kpiandelementids != null) {
                $entityobject = $em->getRepository('InitialShippingBundle:SendCommandRanking')->find($kpiandelementids);
                $entityobject->setClientemail($clientemailid);
                $entityobject->setFilename($pdffilenamearray[0] . '.pdf');
                $em->flush();
            }

            $response = new JsonResponse();
            $response->setData(array('updatemsg' => "Report has been send"));
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Add comment for shipreports
     *
     * @Route("/addcomment_ship", name="addcomment_ship")
     */
    public function addcommentforshipreportsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        //get client Email Id
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null) {
            $username = $user->getUsername();
            $useremailaddres = $user->getEmail();

            $userquery = $em->createQueryBuilder()
                ->select('a.emailId')
                ->from('InitialShippingBundle:CompanyDetails', 'a')
                ->where('a.adminName = :userId')
                ->setParameter('userId', $username)
                ->getQuery();
            $clientemailid = $userquery->getSingleScalarResult();
            $params = $request->request->get('send_command');
            $kpiid = $params['kpiid'];
            $returnvaluefrommonth = $this->listallkpiforship_rankingAction($kpiid, $year = ' ', $request, 'pdftemplate_shiplevel');
            $filename = $params['filename'];
            $pdffilenamearray = explode(".", $filename);
            $newkpiid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $kpiid));
            $kpiname = $newkpiid->getShipName();
            $comment = $params['comment'];
            $checkboxvalue = '';
            if (count($params) < 6) {
                $checkboxvalue = 'No';
                $listofcommentarray = array();

            } else {
                $checkboxvalue = $params['addcomment'];
                $listofcomment = $em->createQueryBuilder()
                    ->select('a.comment', 'a.datetime', 'b.adminName')
                    ->from('InitialShippingBundle:SendCommandRanking', 'a')
                    ->join('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.emailId = a.clientemail')
                    ->where('a.shipid = :shipid')
                    ->andwhere('b.emailId = :username')
                    ->setParameter('username', $useremailaddres)
                    ->setParameter('shipid', $kpiid)
                    ->getQuery()
                    ->getResult();
                $listofcommentarray = $listofcomment;
            }
            $idforrecord = $params['lastid'];

            $today = date("Y-m-d H:i:s");
            $pageName = $request->query->get('page');
            $screenName = $this->get('translator')->trans($pageName);
            $date = date('l jS F Y h:i', time());
            $route = $request->attributes->get('_route');

            $customerListDesign = $this->renderView('InitialShippingBundle:DashBorad:pdfreporttemplateforship.html.twig', array(
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
                'ageofvessel' => $returnvaluefrommonth['ageofvessel'],
                'commentarray' => $listofcommentarray,
                'kpimonthdata' => $returnvaluefrommonth['kpimonthdata'],
                'currentyear' => date('Y')
            ));

            $client = new HighchartController();
            $client->setContainer($this->container);
            $printPdf = $client->createPdf($customerListDesign, $screenName);

            $pdffilenamefullpath = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/brochures/' . $pdffilenamearray[0] . '.pdf';
            file_put_contents($pdffilenamefullpath, $printPdf);

            $useremaildid = $params['clientemail'];
            $mailidarray = array();
            if (filter_var($useremaildid, FILTER_VALIDATE_EMAIL))
            {

                array_push($mailidarray, $useremaildid);
            }
            else
            {
                $findsemail = $em->createQueryBuilder()
                    ->select('a.useremailid')
                    ->from('InitialShippingBundle:EmailUsers', 'a')
                    ->join('InitialShippingBundle:EmailGroup', 'b', 'WITH', 'b.id = a.groupid')
                    ->where('b.groupname = :sq')
                    ->ORwhere('a.useremailid = :sb')
                    ->setParameter('sq', $useremaildid)
                    ->setParameter('sb', $useremaildid)
                    ->getQuery()
                    ->getResult();


                //assign file attachement for mail and Mailing Starts Here...u
                for ($ma = 0; $ma < count($findsemail); $ma++) {
                    /* $mailer = $this->container->get('mailer');
                     $message = \Swift_Message::newInstance()
                         ->setFrom($clientemailid)
                         ->setTo($findsemail[$ma]['emailid'])
                         ->setSubject($kpiname)
                         ->setBody($comment);
                     $message->attach(\Swift_Attachment::fromPath($pdffilenamefullpath)->setFilename($pdffilenamearray[0] . '.pdf'));
                     $mailer->send($message);*/
                    array_push($mailidarray, $findsemail[$ma]['emailid']);
                }
            }


            //Mailing Ends....
            $rankinglookuptable = array('from_emailid' => $clientemailid, 'to_emailids' => $mailidarray, 'filename' => $pdffilenamearray[0] . '.pdf', 'comment' => $comment, 'subject' => $kpiname);
            $gearman = $this->get('gearman');
            $gearman->doBackgroundJob('InitialShippingBundleserviceReadExcelWorker~common_mail_function', json_encode($rankinglookuptable));
            //Update Process Starts Here...

            $session = new Session();
            $kpiandelementids = $session->get('commandid');
            if ($kpiandelementids != null) {
                $entityobject = $em->getRepository('InitialShippingBundle:SendCommandRanking')->findOneBy(array('id' => $kpiandelementids));
                $entityobject->setClientemail($clientemailid);
                $entityobject->setFilename($pdffilenamearray[0] . '.pdf');
                $em->flush();
            }

            $response = new JsonResponse();
            $response->setData(array('updatemsg' => "Report has been send"));
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
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
            $WateMarkImagePath = '/images/pioneer_logo_01.png';
            $series = array(
                array("name" => "", 'showInLegend' => false, 'color' => '#103a71', "data" => array())
            );
            $ob = new Highchart();
            $ob->chart->renderTo('area');
            $ob->chart->type('line');
            $ob->credits->enabled(false);
            //$ob->chart->plotBackgroundImage($WateMarkImagePath);
            $ob->title->text('', array('style' => array('color' => 'red')));
            $ob->subtitle->style(array('color' => '#0000f0', 'fontWeight' => 'bold'));
            $ob->xAxis->categories(array());
            $ob->xAxis->labels(array('style' => array('color' => '#0000F0')));
            $ob->yAxis->max(100);
            $ob->yAxis->title(array('text' => 'Values', 'style' => array('color' => '#0000F0')));
            $ob->series($series);
            $ob->plotOptions->series(array('allowPointSelect' => true, 'dataLabels' => array('enabled' => true)));
            $ob->exporting->enabled(false);

            $listAllShipForCompany = $query->getResult();
            return $this->render(
                'InitialShippingBundle:DashBorad:report_ranking.html.twig',
                array('listofships' => $listAllShipForCompany, 'chart' => $ob)
            );

        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Reports For Ranking
     *
     * @Route("/view_ranking_reports", name="view_ranking_reports")
     */
    public function view_ranking_reportsAction(Request $request, $sendReport = '')
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null) {
            $userId = $user->getId();
            $userName = $user->getUsername();
            $shipid = $request->request->get('shipid');
            $Newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
            $shipname = $Newshipid->getShipName();
            $man_year = $Newshipid->getManufacturingYear();
            $year = $request->request->get('year');
            $today = date("Y-m-d H:i:s");
            $pageName = $request->query->get('page');
            $screenName = $this->get('translator')->trans($pageName);
            $date = date('l jS F Y h:i', time());
            $route = $request->attributes->get('_route');
            $oneyear_montharray = array();
            $rankingKpiWeightarray = array();
            $scorecardElementRules = array();
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
            $initial = 0;
            $statusVerified = 0;
            $currentRequestYear = date('Y');
            if ($currentRequestYear == $year) {
                $currentMonth = date('n');
                $new_monthdetail_date = new \DateTime($oneyear_montharray[$currentMonth - 1]);
                $new_monthdetail_date->modify('last day of this month');
                $statusVerified = $currentMonth - 1;
                $monthlyShipDataStatus = $em->createQueryBuilder()
                    ->select('b.status')
                    ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                    ->where('b.shipid = :shipId and b.dataofmonth = :monthDetail')
                    ->setParameter('shipId', $shipid)
                    ->setParameter('monthDetail', $new_monthdetail_date)
                    ->getQuery()
                    ->getResult();

                if (count($monthlyShipDataStatus) != 0 && $monthlyShipDataStatus[0]['status'] == 4) {
                    $statusVerified = $currentMonth;
                } else {
                    $statusFieldQuery = $em->createQueryBuilder()
                        ->select('b.status, b.dataofmonth')
                        ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                        ->where('b.shipid = :shipId and b.dataofmonth = :monthDetail')
                        ->setParameter('shipId', $shipid)
                        ->setParameter('monthDetail', $new_monthdetail_date)
                        ->getQuery()
                        ->getResult();
                    if (count($statusFieldQuery) != 0 && $statusFieldQuery[count($statusFieldQuery) - 1]['status'] == 4) {
                        $dateFromDb = $statusFieldQuery[count($statusFieldQuery) - 1]['dataofmonth'];
                        $statusVerified = $dateFromDb->format('n');
                    }
                }
            } else {
                for ($yearCount = 0; $yearCount < count($oneyear_montharray); $yearCount++) {
                    $monthObject = new \DateTime($oneyear_montharray[$yearCount]);
                    $monthObject->modify('last day of this month');
                    $statusFieldQuery = $em->createQueryBuilder()
                        ->select('b.status, b.dataofmonth')
                        ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                        ->where('b.shipid = :shipId and b.dataofmonth = :monthDetail')
                        ->setParameter('shipId', $shipid)
                        ->setParameter('monthDetail', $monthObject)
                        ->getQuery()
                        ->getResult();
                    if (count($statusFieldQuery) != 0) {
                        for ($statusFieldCount = 0; $statusFieldCount < count($statusFieldQuery); $statusFieldCount++) {
                            if ($statusFieldQuery[$statusFieldCount]['status'] == 4) {
                                $dateFromDb = $statusFieldQuery[$statusFieldCount]['dataofmonth'];
                                $initial = $dateFromDb->format('n');
                                $statusVerified = 12 - $initial;
                            }
                        }
                    }
                }
            }

            $monthlyKpiValue = array();
            $newcategories = array();
            $monthlyKpiAverageScore = array();
            $monthlyKpiAverageValueTotal = array();
            $ElementName_Weightage = array();
            $dataforgraphforship = array();
            $NewMonthlyKPIValue = array();
            $NewMonthlyAvgTotal = array();
            $vesseldata=array();
            $NewMonthColor = array();
            for ($d = $initial; $d < $statusVerified; $d++) {
                $time2 = strtotime($oneyear_montharray[$d]);
                $monthinletter = date('M', $time2);
                array_push($newcategories, $monthinletter);
                $new_monthdetail_date = new \DateTime($oneyear_montharray[$d]);
                $new_monthdetail_date->modify('last day of this month');

                $scorecardElementValueArray = array();
                $rankingKpiValueCountArray = array();
                $Newkpivalue = array();
                $NewKpiAvg = array();
                $NewKpiColor = array();
                for ($rankingKpiCount = 0; $rankingKpiCount < count($rankingKpiList); $rankingKpiCount++) {
                    $rankingElementValueTotal = 0;
                    $rankingKpiId = $rankingKpiList[$rankingKpiCount]['id'];
                    $rankingKpiWeight = $rankingKpiList[$rankingKpiCount]['weightage'];
                    $rankingKpiName = $rankingKpiList[$rankingKpiCount]['kpiName'];
                    array_push($rankingKpiWeightarray, $rankingKpiWeight);
                    if ($rankingKpiName == 'Vessel age')
                    {
                        if ($man_year == "") {
                            $yearcount = 0;
                        } else {

                            $man_datestring = $man_year. '-01';
                            $temp_man_year = new \DateTime($man_datestring);
                            $temp_man_year->modify('last day of this month');
                            $Vessage_count = $temp_man_year->diff($new_monthdetail_date)->y;
                        }
                        $vesselage = ($Vessage_count * $rankingKpiWeight) / 20;
                        array_push($rankingKpiValueCountArray, $vesselage);
                        array_push($vesseldata,$vesselage);
                    }
                    else
                    {

                    }
                    $elementForKpiList = $em->createQueryBuilder()
                        ->select('a.elementName', 'a.id', 'a.weightage')
                        ->from('InitialShippingBundle:RankingElementDetails', 'a')
                        ->where('a.kpiDetailsId = :kpiid')
                        ->setParameter('kpiid', $rankingKpiId)
                        ->getQuery()
                        ->getResult();

                    $kpiSumValue = 0;
                    $NewElementColor = array();
                    $Elment_Value = array();
                    if (count($elementForKpiList) > 0) {
                        if ($d == 0) {
                            $ElementName_Weightage[$rankingKpiId] = $elementForKpiList;
                        }
                        for ($elementCount = 0; $elementCount < count($elementForKpiList); $elementCount++) {
                            $scorecardElementId = $elementForKpiList[$elementCount]['id'];
                            $scorecardElementWeight = $elementForKpiList[$elementCount]['weightage'];
                            $elementResultColor = "";
                            $elementColorValue = 0;
                            $rankingElementRulesArray = $em->createQueryBuilder()
                                ->select('a.rules')
                                ->from('InitialShippingBundle:RankingRules', 'a')
                                ->where('a.elementDetailsId = :elementId')
                                ->setParameter('elementId', $scorecardElementId)
                                ->getQuery()
                                ->getResult();

                            $rankingElementResult = $em->createQueryBuilder()
                                ->select('b.elementdata, b.elementcolor')
                                ->from('InitialShippingBundle:Ranking_LookupData', 'b')
                                ->where('b.kpiDetailsId = :kpiId and b.shipDetailsId = :shipId and b.elementDetailsId = :elementId and b.monthdetail = :monthDetail')
                                ->setParameter('kpiId', $rankingKpiId)
                                ->setParameter('shipId', $shipid)
                                ->setParameter('elementId', $scorecardElementId)
                                ->setParameter('monthDetail', $new_monthdetail_date)
                                ->getQuery()
                                ->getResult();
                            if (count($rankingElementResult) != 0) {
                                $elementResultColor = $rankingElementResult[0]['elementcolor'];
                            } else {
                                $rankingElementResult[0]['elementdata'] = 0;
                            }

                            if ($elementResultColor == "false") {
                                $elementColorValue = 0;
                            }

                            if ($elementResultColor == 'Green') {
                                $elementColorValue = $scorecardElementWeight;
                            } else if ($elementResultColor == 'Yellow') {
                                $elementColorValue = $scorecardElementWeight / 2;
                            } else if ($elementResultColor == 'Red') {
                                $elementColorValue = 0;
                            }

                            array_push($scorecardElementRules, $rankingElementRulesArray);
                            array_push($scorecardElementValueArray, (($rankingElementResult[0]['elementdata'])*$rankingKpiWeight)/100);
                            $elementValueWithWeight = $elementColorValue;
                            $kpiSumValue += $elementValueWithWeight;
                            $rankingElementValueTotal += $elementColorValue;
                            //  array_push($Elment_Value, $rankingElementResult[0]['elementdata']);
                            array_push($Elment_Value, (($rankingElementResult[0]['elementdata'])*$rankingKpiWeight)/100);
                            array_push($NewElementColor, $elementResultColor);
                        }
                        array_push($monthlyKpiAverageValueTotal, ($kpiSumValue * $rankingKpiWeight) / 100);
                        $NewKpiAvg[$rankingKpiId] = ($kpiSumValue * $rankingKpiWeight) / 100;
                        if ($rankingElementValueTotal != 105) {
                            array_push($rankingKpiValueCountArray, ($rankingElementValueTotal * $rankingKpiWeight / 100));
                        } else {
                            array_push($rankingKpiValueCountArray, null);
                        }
                    }
                    if (count($elementForKpiList) == 0) {
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
                        if ($d == 0) {
                            $ElementName_Weightage[$rankingKpiId] = $elementForKpiList;
                        }

                        for ($elementCount = 0; $elementCount < count($elementForKpiList); $elementCount++) {
                            $scorecardElementId = $elementForKpiList[$elementCount]['id'];
                            $scorecardElementWeight = $elementForKpiList[$elementCount]['weightage'];
                            $rankingElementRulesArray = $em->createQueryBuilder()
                                ->select('a.rules')
                                ->from('InitialShippingBundle:RankingRules', 'a')
                                ->where('a.elementDetailsId = :elementId')
                                ->setParameter('elementId', $scorecardElementId)
                                ->getQuery()
                                ->getResult();
                            $elementResultColor = "";
                            $elementColorValue = 0;
                            $rankingElementResult = $em->createQueryBuilder()
                                ->select('b.elementdata, b.elementcolor')
                                ->from('InitialShippingBundle:Ranking_LookupData', 'b')
                                ->where('b.kpiDetailsId = :kpiId and b.shipDetailsId = :shipId and b.elementDetailsId = :elementId and b.monthdetail = :monthDetail')
                                ->setParameter('kpiId', $newkpiid)
                                ->setParameter('shipId', $shipid)
                                ->setParameter('elementId', $scorecardElementId)
                                ->setParameter('monthDetail', $new_monthdetail_date)
                                ->getQuery()
                                ->getResult();
                            if (count($rankingElementResult) != 0) {
                                $elementResultColor = $rankingElementResult[0]['elementcolor'];
                            } else {
                                $rankingElementResult[0]['elementdata'] = 0;
                            }

                            if ($elementResultColor == "false") {
                                $elementColorValue = 0;
                            }

                            if ($elementResultColor == 'Green') {
                                $elementColorValue = $scorecardElementWeight;
                            } else if ($elementResultColor == 'Yellow') {
                                $elementColorValue = $scorecardElementWeight / 2;
                            } else if ($elementResultColor == 'Red') {
                                $elementColorValue = 0;
                            }

                            array_push($scorecardElementRules, $rankingElementRulesArray);
                            //array_push($scorecardElementValueArray, $rankingElementResult[0]['elementdata']);
                            array_push($scorecardElementValueArray, (($rankingElementResult[0]['elementdata'])*$rankingKpiWeight)/100);
                            $elementValueWithWeight = $elementColorValue;
                            $kpiSumValue += $elementValueWithWeight;
                            $rankingElementValueTotal += $elementColorValue;
                            array_push($Elment_Value, (($rankingElementResult[0]['elementdata'])*$rankingKpiWeight)/100);
                            array_push($NewElementColor, $elementResultColor);

                        }
                        array_push($monthlyKpiAverageValueTotal, ($kpiSumValue * $rankingKpiWeight) / 100);
                        $NewKpiAvg[$rankingKpiId] = ($kpiSumValue * $rankingKpiWeight) / 100;
                        if ($rankingElementValueTotal != 105) {
                            array_push($rankingKpiValueCountArray, ($rankingElementValueTotal * $rankingKpiWeight / 100));
                        } else {
                            array_push($rankingKpiValueCountArray, null);
                        }
                    }
                    $Newkpivalue[$rankingKpiId] = $Elment_Value;
                    $NewKpiColor[$rankingKpiId] = $NewElementColor;
                }
                array_push($monthlyKpiValue, $rankingKpiValueCountArray);
                if (array_sum($rankingKpiValueCountArray) != 0) {
                    array_push($monthlyKpiAverageScore, array_sum($rankingKpiValueCountArray));
                    array_push($dataforgraphforship, array_sum($rankingKpiValueCountArray));
                } else if (array_sum($rankingKpiValueCountArray) == 0) {
                    array_push($monthlyKpiAverageScore, 0);
                    array_push($dataforgraphforship, 0);
                }
                array_push($NewMonthlyKPIValue, $Newkpivalue);
                array_push($NewMonthlyAvgTotal, $NewKpiAvg);
                array_push($NewMonthColor, $NewKpiColor);
            }
            $New_overallfindingelementgraph = array();
            $New_overallfindingelementvalue = array();
            $New_overallfindingelementcolor = array();

            for ($SplitKpiCount = 0; $SplitKpiCount < count($rankingKpiList); $SplitKpiCount++) {
                $rankingKpiId = $rankingKpiList[$SplitKpiCount]['id'];
                $rankingKpiName = $rankingKpiList[$SplitKpiCount]['kpiName'];
                $New_Month_Avg_Total = array();
                $New_Month_Element_Value = array();
                $New_Month_Element_Color = array();
                for ($New_FindKpivalueCount = 0; $New_FindKpivalueCount < $statusVerified; $New_FindKpivalueCount++) {
                    $New_Month_Avg_Total[$New_FindKpivalueCount] = $NewMonthlyAvgTotal[$New_FindKpivalueCount][$rankingKpiId];
                    $New_Month_Element_Value[$New_FindKpivalueCount] = $NewMonthlyKPIValue[$New_FindKpivalueCount][$rankingKpiId];
                    $New_Month_Element_Color[$New_FindKpivalueCount] = $NewMonthColor[$New_FindKpivalueCount][$rankingKpiId];
                }
                $New_overallfindingelementgraph[$rankingKpiId] = $New_Month_Avg_Total;
                $New_overallfindingelementvalue[$rankingKpiId] = $New_Month_Element_Value;
                $New_overallfindingelementcolor[$rankingKpiId] = $New_Month_Element_Color;
            }

            $shipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipid));
            $shipname = $shipid->getShipName();
            $man_year = $shipid->getManufacturingYear();
            if ($man_year == "") {
                $yearcount = 0;
            } else {
                $currentdatestring = date('Y-01-01');
                $d1 = new \DateTime($currentdatestring);
                $man_datestring = $man_year . '-01-' . '01';
                $d2 = new \DateTime($man_datestring);
                $diff = $d2->diff($d1);
                $yearcount = $diff->y + 1;
            }

            if ($sendReport == 'sendReport') {
                return array(
                    'listofkpi' => $rankingKpiList,
                    'kpiweightage' => $rankingKpiWeightarray,
                    'montharray' => $newcategories,
                    'shipname' => $shipname,
                    'countmonth' => count($newcategories),
                    'avgscore' => $monthlyKpiAverageScore,
                    'shipid' => $shipid->getId(),
                    'chartdata' => $dataforgraphforship,
                    'kpimonthdata' => $monthlyKpiValue,
                    'vesseldatas'=>$vesseldata,
                    'currentyear' => $currentyear,
                    'ageofvessel' => $yearcount,
                    'kpigraph' => $New_overallfindingelementgraph,
                    'elementcolorarray' => $New_overallfindingelementcolor,
                    'monthlydata' => $New_overallfindingelementvalue,
                    'elementRule' => $scorecardElementRules,
                    'vesseldatas'=>$vesseldata,
                    'listofelement' => $ElementName_Weightage,
                );
            }
            $response = new JsonResponse();
            $response->setData
            (
                array(
                    'listofkpi' => $rankingKpiList,
                    'kpiweightage' => $rankingKpiWeightarray,
                    'montharray' => $newcategories,
                    'shipname' => $shipname,
                    'countmonth' => count($newcategories),
                    'avgscore' => $monthlyKpiAverageScore,
                    'shipid' => $shipid->getId(),
                    'chartdata' => $dataforgraphforship,
                    'kpimonthdata' => $monthlyKpiValue,
                    'currentyear' => $currentyear,
                    'ageofvessel' => $yearcount,
                    'vesseldatas'=>$vesseldata,
                    'kpigraph' => $New_overallfindingelementgraph,
                    'elementcolorarray' => $New_overallfindingelementcolor,
                    'monthlydata' => $New_overallfindingelementvalue,
                    'elementRule' => $scorecardElementRules,
                    'listofelement' => $ElementName_Weightage,
                )
            );
            return $response;

        } else {
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
        $currentdateitme=date('Y-m-d-H-i-s');
        $user = $this->getUser();
        if ($user != null) {
            $reportObject = $this->view_ranking_reportsAction($request, 'sendReport');
            $rankingKpiList = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id', 'b.weightage')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.shipDetailsId = :shipid')
                ->setParameter('shipid', $reportObject['shipid'])
                ->getQuery()
                ->getResult();
            $mpdf = $this->container->get('tfox.mpdfport')->getMPdf();
            $mpdf->defaultheaderline = 0;
            $mpdf->defaultheaderfontstyle = 'B';
            $WateMarkImagePath = $this->container->getParameter('kernel.root_dir') . '/../web/images/pioneer_logo_02.png';
            $mpdf->SetWatermarkImage($WateMarkImagePath);
            //$mpdf->SetProtection(array('print', 'copy'), 'robert', 'Star123');
            $mpdf->showWatermarkImage = true;
            $graphObject = array(
                'chart' => array('plotBackgroundImage'=>$WateMarkImagePath,'renderTo' => 'areaId', 'type' => "line"),
                'exporting' => array('enabled' => false),
                'credits'=>array('enabled' => false),
                'plotOptions' => array('series' => array(
                    "allowPointSelect" => true,
                    "dataLabels" => array(
                        "enabled" => true
                    )
                )),
                'series' => array(
                    array('name' => 'Series', 'showInLegend' => false, 'color' => '#103a71', 'data' => $reportObject['chartdata'])
                ),
                'subtitle' => array('style' => array('color' => '#0000f0', 'fontWeight' => 'bold')),
                'title' => array('text' => $reportObject['shipname']),
                'xAxis' => array('categories' => $reportObject['montharray'], 'labels' => array('style' => array('color' => '#0000F0'))),
                'yAxis' => array('max' => 100, 'title' => array('text' => 'Values', 'style' => array('color' => '#0000F0'))),
            );
            $jsondata = json_encode($graphObject);
            if (!file_exists($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles')) {
                mkdir($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles', 0777, true);
            }
            if (!file_exists($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph')) {
                mkdir($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph', 0777, true);
            }
            $pdffilenamefullpath = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/ship_' . $reportObject['shipid'].'_'.$currentdateitme. '.json';
            file_put_contents($pdffilenamefullpath, $jsondata);
            $Highchartconvertjs = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js -infile ';

            $outfile = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph/shipimage_' . $reportObject['shipid'].'_'.$currentdateitme. '.png';
            $JsonFileDirectroy = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/ship_' . $reportObject['shipid'].'_'.$currentdateitme. '.json -outfile ' . $outfile . ' -scale 2.5 -width 1065';
            $ImageGeneration = 'phantomjs ' . $Highchartconvertjs . $JsonFileDirectroy;
            $handle = popen($ImageGeneration, 'r');
            $charamee = fread($handle, 2096);
            /* return $this->render('InitialShippingBundle:DashBorad:overallranking_report_template.html.twig', array(
                  'shipid' => $reportObject['shipid'],
                  'screenName' => 'Ranking Report',
                  'userName' => '',
                  'date' => date('Y-m-d'),
                  'link' => 'shipimage_' . $reportObject['shipid'].'_'.$currentdateitme. '.png',
                  'listofkpi' => $reportObject['listofkpi'],
                  'kpiweightage' => $reportObject['kpiweightage'],
                  'montharray' => $reportObject['montharray'],
                  'shipname' => $reportObject['shipname'],
                  'countmonth' => count($reportObject['montharray']),
                  'avgscore' => $reportObject['avgscore'],
                  'ageofvessel' => $reportObject['ageofvessel'],
                  'kpimonthdata' => $reportObject['kpimonthdata'],
                  'currentyear' => date('Y')
              ));*/
            $customerListDesign = $this->renderView('InitialShippingBundle:DashBorad:overallranking_report_template.html.twig', array(
                'shipid' => $reportObject['shipid'],
                'screenName' => 'Ranking Report',
                'userName' => '',
                'date' => date('Y-m-d'),
                'link' => 'shipimage_' . $reportObject['shipid'].'_'.$currentdateitme. '.png',
                'listofkpi' => $reportObject['listofkpi'],
                'kpiweightage' => $reportObject['kpiweightage'],
                'montharray' => $reportObject['montharray'],
                'shipname' => $reportObject['shipname'],
                'countmonth' => count($reportObject['montharray']),
                'avgscore' => $reportObject['avgscore'],
                'ageofvessel' => $reportObject['ageofvessel'],
                'kpimonthdata' => $reportObject['kpimonthdata'],
                'currentyear' => date('Y')
            ));
            $mpdf->AddPage('', 4, '', 'on');
            $mpdf->SetFooter('|Date/Time: {DATE l jS F Y h:i}| Page No: {PAGENO}');
            $mpdf->WriteHTML($customerListDesign);
            for ($KpiPdfcount = 0; $KpiPdfcount < count($rankingKpiList); $KpiPdfcount++) {
                $kpiName = $rankingKpiList[$KpiPdfcount]['kpiName'];
                $kpiid = $rankingKpiList[$KpiPdfcount]['id'];
                $weightage = $rankingKpiList[$KpiPdfcount]['weightage'];
                if ($kpiName != 'Vessel age') {
                    $graphObject = array(
                        'chart' => array('plotBackgroundImage' => $WateMarkImagePath, 'renderTo' => 'areaId', 'type' => "line"),
                        'exporting' => array('enabled' => false),
                        'credits'=>array('enabled' => false),
                        'plotOptions' => array('series' => array(
                            "allowPointSelect" => true,
                            "dataLabels" => array(
                                "enabled" => true
                            )
                        )),
                        'series' => array(
                            array('name' => 'Series', 'showInLegend' => false, 'color' => '#103a71', 'data' => $reportObject['kpigraph'][$kpiid])
                        ),
                        'subtitle' => array('style' => array('color' => '#0000f0', 'fontWeight' => 'bold')),
                        'title' => array('text' => $kpiName),
                        'xAxis' => array('categories' => $reportObject['montharray'], 'labels' => array('style' => array('color' => '#0000F0'))),
                        'yAxis' => array('max' => $weightage, 'title' => array('text' => 'Values', 'style' => array('color' => '#0000F0'))),
                    );
                }
                else
                {
                    $graphObject = array(
                        'chart' => array('plotBackgroundImage' => $WateMarkImagePath, 'renderTo' => 'areaId', 'type' => "line"),
                        'exporting' => array('enabled' => false),
                        'credits'=>array('enabled' => false),
                        'plotOptions' => array('series' => array(
                            "allowPointSelect" => true,
                            "dataLabels" => array(
                                "enabled" => true
                            )
                        )),
                        'series' => array(
                            array('name' => 'Series', 'showInLegend' => false, 'color' => '#103a71', 'data' => $reportObject['vesseldatas'])
                        ),
                        'subtitle' => array('style' => array('color' => '#0000f0', 'fontWeight' => 'bold')),
                        'title' => array('text' => $kpiName),
                        'xAxis' => array('categories' => $reportObject['montharray'], 'labels' => array('style' => array('color' => '#0000F0'))),
                        'yAxis' => array('max' => $weightage, 'title' => array('text' => 'Values', 'style' => array('color' => '#0000F0'))),
                    );
                }
                $jsondata = json_encode($graphObject);
                $pdffilenamefullpath = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/kpi_' . $kpiid.'_'.$currentdateitme. '.json';
                file_put_contents($pdffilenamefullpath, $jsondata);
                $Highchartconvertjs = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js -infile ';
                $outfile = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph/kpiimage_' . $kpiid.'_'.$currentdateitme. '.png';
                $JsonFileDirectroy = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/kpi_' . $kpiid.'_'.$currentdateitme. '.json -outfile ' . $outfile . ' -scale 2.5 -width 1065';
                $ImageGeneration = 'phantomjs ' . $Highchartconvertjs . $JsonFileDirectroy;
                $handle = popen($ImageGeneration, 'r');
                $charamee = fread($handle, 2096);

                $customerListDesign = $this->renderView('InitialShippingBundle:DashBorad:overallranking_kpi_template.html.twig', array(
                    'kpiid' => $kpiid,
                    'screenName' => 'Ranking Report',
                    'userName' => '',
                    'date' => date('Y-m-d'),
                    'link' => 'kpiimage_' . $kpiid .'_'.$currentdateitme. '.png',
                    'montharray' => $reportObject['montharray'],
                    'kpiname' => $kpiName,
                    'countmonth' => count($reportObject['montharray']),
                    'kpigraph' => $reportObject['kpigraph'][$kpiid],
                    'elementcolorarray' => $reportObject['elementcolorarray'][$kpiid],
                    'monthlydata' => $reportObject['monthlydata'][$kpiid],
                    'elementRule' => $reportObject['elementRule'],
                    'listofelement' => $reportObject['listofelement'][$kpiid],
                    'countofelement' => count($reportObject['listofelement'][$kpiid]),
                    'currentyear' => date('Y')
                ));

                $mpdf->AddPage('', 4, '', 'on');
                $mpdf->SetFooter('|Date/Time: {DATE l jS F Y h:i}| Page No: {PAGENO}');
                $mpdf->WriteHTML($customerListDesign);
            }
            $content = $mpdf->Output('', 'S');
            $response = new Response();
            $response->setContent($content);
            $response->headers->set('Content-Type', 'application/pdf');
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Send Reports to Mailing For Ranking
     *
     * @Route("/send_rankingreports_mail", name="send_rankingreports_mail")
     */
    public function sendreports_mail_rankingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $currentdateitme=date('Y-m-d-H-i-s');
        if ($user != null) {
            $email = $user->getEmail();
            $reportObject = $this->view_ranking_reportsAction($request, 'sendReport');
            $rankingKpiList = $em->createQueryBuilder()
                ->select('b.kpiName', 'b.id', 'b.weightage')
                ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                ->where('b.shipDetailsId = :shipid')
                ->setParameter('shipid', $reportObject['shipid'])
                ->getQuery()
                ->getResult();
            $mpdf = $this->container->get('tfox.mpdfport')->getMPdf();
            $mpdf->defaultheaderline = 0;
            $mpdf->defaultheaderfontstyle = 'B';
            $WateMarkImagePath = $this->container->getParameter('kernel.root_dir') . '/../web/images/pioneer_logo_02.png';
            $mpdf->SetWatermarkImage($WateMarkImagePath);
            $mpdf->showWatermarkImage = true;
            $graphObject = array(
                'chart' => array('renderTo' => 'areaId', 'type' => "line"),
                'exporting' => array('enabled' => false),
                'credits'=>array('enabled' => false),
                'plotOptions' => array('series' => array(
                    "allowPointSelect" => true,
                    "dataLabels" => array(
                        "enabled" => true
                    )
                )),
                'series' => array(
                    array('name' => 'Series', 'showInLegend' => false, 'color' => '#103a71', 'data' => $reportObject['chartdata'])
                ),
                'subtitle' => array('style' => array('color' => '#0000f0', 'fontWeight' => 'bold')),
                'title' => array('text' => $reportObject['shipname']),
                'xAxis' => array('categories' => $reportObject['montharray'], 'labels' => array('style' => array('color' => '#0000F0'))),
                'yAxis' => array('max' => 100, 'title' => array('text' => 'Values', 'style' => array('color' => '#0000F0'))),
            );
            $jsondata = json_encode($graphObject);
            if (!file_exists($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles')) {
                mkdir($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles', 0777, true);
            }
            if (!file_exists($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph')) {
                mkdir($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph', 0777, true);
            }
            $pdffilenamefullpath = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/ship_' . $reportObject['shipid'].'_' .$currentdateitme. '.json';
            file_put_contents($pdffilenamefullpath, $jsondata);
            $Highchartconvertjs = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js -infile ';
            $outfile = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph/shipimage_' . $reportObject['shipid'].'_'.$currentdateitme. '.png';
            $JsonFileDirectroy = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/ship_' . $reportObject['shipid'].'_' .$currentdateitme. '.json -outfile ' . $outfile . ' -scale 2.5 -width 1065';
            $ImageGeneration = 'phantomjs ' . $Highchartconvertjs . $JsonFileDirectroy;
            $handle = popen($ImageGeneration, 'r');
            $charamee = fread($handle, 2096);
            $customerListDesign = $this->renderView('InitialShippingBundle:DashBorad:overallranking_report_template.html.twig', array(
                'shipid' => $reportObject['shipid'],
                'screenName' => 'Ranking Report',
                'userName' => '',
                'date' => date('Y-m-d'),
                'link' => 'shipimage_' . $reportObject['shipid'].'_'.$currentdateitme. '.png',
                'listofkpi' => $reportObject['listofkpi'],
                'kpiweightage' => $reportObject['kpiweightage'],
                'montharray' => $reportObject['montharray'],
                'shipname' => $reportObject['shipname'],
                'countmonth' => count($reportObject['montharray']),
                'avgscore' => $reportObject['avgscore'],
                'ageofvessel' => $reportObject['ageofvessel'],
                'kpimonthdata' => $reportObject['kpimonthdata'],
                'currentyear' => date('Y')
            ));
            $mpdf->AddPage('', 4, '', 'on');
            $mpdf->SetFooter('|Date/Time: {DATE l jS F Y h:i}| Page No: {PAGENO}');
            $mpdf->WriteHTML($customerListDesign);
            for ($KpiPdfcount = 0; $KpiPdfcount < count($rankingKpiList); $KpiPdfcount++) {
                $kpiName = $rankingKpiList[$KpiPdfcount]['kpiName'];
                $kpiid = $rankingKpiList[$KpiPdfcount]['id'];
                $weightage = $rankingKpiList[$KpiPdfcount]['weightage'];
                if ($kpiName != 'Vessel age') {
                    $graphObject = array(
                        'chart' => array('renderTo' => 'areaId', 'type' => "line"),
                        'exporting' => array('enabled' => false),
                        'credits'=>array('enabled' => false),
                        'plotOptions' => array('series' => array(
                            "allowPointSelect" => true,
                            "dataLabels" => array(
                                "enabled" => true
                            )
                        )),
                        'series' => array(
                            array('name' => 'Series', 'showInLegend' => false, 'color' => '#103a71', 'data' => $reportObject['kpigraph'][$kpiid])
                        ),
                        'subtitle' => array('style' => array('color' => '#0000f0', 'fontWeight' => 'bold')),
                        'title' => array('text' => $kpiName),
                        'xAxis' => array('categories' => $reportObject['montharray'], 'labels' => array('style' => array('color' => '#0000F0'))),
                        'yAxis' => array('max' => $weightage, 'title' => array('text' => 'Values', 'style' => array('color' => '#0000F0'))),
                    );
                }
                else
                {
                    $graphObject = array(
                        'chart' => array('renderTo' => 'areaId', 'type' => "line"),
                        'exporting' => array('enabled' => false),
                        'credits'=>array('enabled' => false),
                        'plotOptions' => array('series' => array(
                            "allowPointSelect" => true,
                            "dataLabels" => array(
                                "enabled" => true
                            )
                        )),
                        'series' => array(
                            array('name' => 'Series', 'showInLegend' => false, 'color' => '#103a71', 'data' => $reportObject['vesseldatas'])
                        ),
                        'subtitle' => array('style' => array('color' => '#0000f0', 'fontWeight' => 'bold')),
                        'title' => array('text' => $kpiName),
                        'xAxis' => array('categories' => $reportObject['montharray'], 'labels' => array('style' => array('color' => '#0000F0'))),
                        'yAxis' => array('max' => $weightage, 'title' => array('text' => 'Values', 'style' => array('color' => '#0000F0'))),
                    );
                }
                $jsondata = json_encode($graphObject);
                $pdffilenamefullpath = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/kpi_' . $kpiid.'_'.$currentdateitme. '.json';
                file_put_contents($pdffilenamefullpath, $jsondata);
                $Highchartconvertjs = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js -infile ';
                $outfile = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph/kpiimage_' . $kpiid .'_'.$currentdateitme. '.png';
                $JsonFileDirectroy = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/kpi_' . $kpiid .'_'.$currentdateitme. '.json -outfile ' . $outfile . ' -scale 2.5 -width 1065';
                $ImageGeneration = 'phantomjs ' . $Highchartconvertjs . $JsonFileDirectroy;
                $handle = popen($ImageGeneration, 'r');
                $charamee = fread($handle, 2096);

                $customerListDesign = $this->renderView('InitialShippingBundle:DashBorad:overallranking_kpi_template.html.twig', array(
                    'kpiid' => $kpiid,
                    'screenName' => 'Ranking Report',
                    'userName' => '',
                    'date' => date('Y-m-d'),
                    'link' => 'kpiimage_' . $kpiid.'_'.$currentdateitme. '.png',
                    'montharray' => $reportObject['montharray'],
                    'kpiname' => $kpiName,
                    'countmonth' => count($reportObject['montharray']),
                    'kpigraph' => $reportObject['kpigraph'][$kpiid],
                    'elementcolorarray' => $reportObject['elementcolorarray'][$kpiid],
                    'monthlydata' => $reportObject['monthlydata'][$kpiid],
                    'elementRule' => $reportObject['elementRule'],
                    'listofelement' => $reportObject['listofelement'][$kpiid],
                    'countofelement' => count($reportObject['listofelement'][$kpiid]),
                    'currentyear' => date('Y')
                ));

                $mpdf->AddPage('', 4, '', 'on');
                $mpdf->SetFooter('|Date/Time: {DATE l jS F Y h:i}| Page No: {PAGENO}');
                $mpdf->WriteHTML($customerListDesign);
            }
            $content = $mpdf->Output('', 'S');
            $fileName = $reportObject['shipname'] . date('Y-m-d H-i-s') . '.pdf';
            if (!file_exists($this->container->getParameter('kernel.root_dir') . '/../web/uploads/brochures')) {
                mkdir($this->container->getParameter('kernel.root_dir') . '/../web/uploads/brochures', 0777, true);
            }
            $Finalpdffile = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/brochures/' . $fileName;
            file_put_contents($Finalpdffile, $content);
            $useremaildid = $request->request->get('clientemail');
            $mailbox = $request->request->get('comment');
            $mailidarray = array();
            if (filter_var($useremaildid, FILTER_VALIDATE_EMAIL)) {
                array_push($mailidarray, $useremaildid);
            } else {
                $findsemail = $em->createQueryBuilder()
                    ->select('a.useremailid')
                    ->from('InitialShippingBundle:EmailUsers', 'a')
                    ->join('InitialShippingBundle:EmailGroup', 'b', 'WITH', 'b.id = a.groupid')
                    ->where('b.groupname = :sq')
                    ->ORwhere('a.useremailid = :sb')
                    ->setParameter('sq', $useremaildid)
                    ->setParameter('sb', $useremaildid)
                    ->getQuery()
                    ->getResult();


                //assign file attachement for mail and Mailing Starts Here...u
                for ($ma = 0; $ma < count($findsemail); $ma++) {
                    /* $mailer = $this->container->get('mailer');
                     $message = \Swift_Message::newInstance()
                         ->setFrom($clientemailid)
                         ->setTo($findsemail[$ma]['emailid'])
                         ->setSubject($kpiname)
                         ->setBody($comment);
                     $message->attach(\Swift_Attachment::fromPath($pdffilenamefullpath)->setFilename($pdffilenamearray[0] . '.pdf'));
                     $mailer->send($message);*/
                    array_push($mailidarray, $findsemail[$ma]['emailid']);
                }
            }
            //Mailing Ends....
            $rankinglookuptable = array('from_emailid' => $email, 'to_emailids' => $mailidarray, 'filename' => $fileName, 'comment' => $mailbox, 'subject' => $reportObject['shipname']);
            $gearman = $this->get('gearman');
            $gearman->doBackgroundJob('InitialShippingBundleserviceReadExcelWorker~common_mail_function', json_encode($rankinglookuptable));
            $response = new JsonResponse();
            $response->setData(array('updatemsg' => "Report has been send"));
            return $response;
        } else {
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
        if ($user != null) {
            $userId = $user->getId();
            $userName = $user->getUsername();
            return $this->render(
                'InitialShippingBundle:DashBorad:rankingreport_allships.html.twig'
            );

        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Ranking Overall Shipreports.
     *
     * @Route("/overall_ships_rankingreports", name="overall_ships_rankingreports")
     */
    public function overall_ships_rankingreportsAction(Request $request, $mode = '', $dataofmonth = '', $year = ' ')
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

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
        $year = $request->request->get('year');
        $oneyear_montharray = array();
        $oneChart_Data = array();
        if ($year == ' ') {
            for ($m = 1; $m <= 12; $m++) {
                $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date('Y')));
                array_push($oneyear_montharray, $month);
            }
        }
        if ($year != ' ') {
            for ($m = 1; $m <= 12; $m++) {
                $month = date('Y-m-d', mktime(0, 0, 0, $m, 1, date($year)));
                array_push($oneyear_montharray, $month);
            }
        }
        $newcategories = array();
        $DataRankingReports = 0;
        for ($shipCount = 0; $shipCount < count($listAllShipForCompany); $shipCount++) {
            $rankingShipName = $listAllShipForCompany[$shipCount]['shipName'];
            $manufacturingYear = $listAllShipForCompany[$shipCount]['manufacturingYear'];
            $rankingShipId = $listAllShipForCompany[$shipCount]['id'];
            $initial = 0;
            $statusVerified = 0;
            $currentRequestYear = date('Y');
            if ($currentRequestYear == $year) {
                $currentMonth = date('n');
                $new_monthdetail_date = new \DateTime($oneyear_montharray[$currentMonth - 1]);
                $new_monthdetail_date->modify('last day of this month');
                $statusVerified = $currentMonth - 1;
                $monthlyShipDataStatus = $em->createQueryBuilder()
                    ->select('b.status')
                    ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                    ->where('b.shipid = :shipId and b.dataofmonth = :monthDetail')
                    ->setParameter('shipId', $rankingShipId)
                    ->setParameter('monthDetail', $new_monthdetail_date)
                    ->getQuery()
                    ->getResult();

                if (count($monthlyShipDataStatus) != 0 && $monthlyShipDataStatus[0]['status'] == 4) {
                    $statusVerified = $currentMonth;
                } else {
                    $statusFieldQuery = $em->createQueryBuilder()
                        ->select('b.status, b.dataofmonth')
                        ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                        ->where('b.shipid = :shipId and b.dataofmonth = :monthDetail')
                        ->setParameter('shipId', $rankingShipId)
                        ->setParameter('monthDetail', $new_monthdetail_date)
                        ->getQuery()
                        ->getResult();
                    if (count($statusFieldQuery) != 0 && $statusFieldQuery[count($statusFieldQuery) - 1]['status'] == 4) {
                        $dateFromDb = $statusFieldQuery[count($statusFieldQuery) - 1]['dataofmonth'];
                        $statusVerified = $dateFromDb->format('n');
                    }
                }
            } else {
                for ($yearCount = 0; $yearCount < count($oneyear_montharray); $yearCount++) {
                    $monthObject = new \DateTime($oneyear_montharray[$yearCount]);
                    $monthObject->modify('last day of this month');
                    $statusFieldQuery = $em->createQueryBuilder()
                        ->select('b.status, b.dataofmonth')
                        ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                        ->where('b.shipid = :shipId and b.dataofmonth = :monthDetail')
                        ->setParameter('shipId', $rankingShipId)
                        ->setParameter('monthDetail', $monthObject)
                        ->getQuery()
                        ->getResult();
                    if (count($statusFieldQuery) != 0) {
                        for ($statusFieldCount = 0; $statusFieldCount < count($statusFieldQuery); $statusFieldCount++) {
                            if ($statusFieldQuery[$statusFieldCount]['status'] == 4) {
                                $dateFromDb = $statusFieldQuery[$statusFieldCount]['dataofmonth'];
                                $initial = $dateFromDb->format('n');
                                $statusVerified = 12 - $initial;
                            }
                        }
                    }
                }
            }
            $ShipDetailDataarray = array();
            for ($d = $initial; $d < $statusVerified; $d++) {
                $monthcount = 0;
                $time2 = strtotime($oneyear_montharray[$d]);
                $monthinletter = date('M', $time2);
                if ($shipCount == 0) {
                    array_push($newcategories, $monthinletter);
                }
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

                for ($rankingKpiCount = 0; $rankingKpiCount < count($rankingKpiList); $rankingKpiCount++) {
                    $rankingElementValueTotal = 0;
                    $rankingKpiId = $rankingKpiList[$rankingKpiCount]['id'];
                    $rankingKpiWeight = $rankingKpiList[$rankingKpiCount]['weightage'];
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

                    if ($rankingElementList > 0) {
                        if ($monthcount == 0) {
                            $DataRankingReports++;

                        }
                        for ($rankingElementCount = 0; $rankingElementCount < count($rankingElementList); $rankingElementCount++) {
                            $rankingElementId = $rankingElementList[$rankingElementCount]['id'];
                            $rankingElementWeight = $rankingElementList[$rankingElementCount]['weightage'];
                            $elementResultColor = "";
                            $rankingElementColorValue = 0;
                            $rankingElementResult = $em->createQueryBuilder()
                                ->select('b.elementdata, b.elementcolor')
                                ->from('InitialShippingBundle:Ranking_LookupData', 'b')
                                ->where('b.kpiDetailsId = :kpiId and b.shipDetailsId = :shipId and b.elementDetailsId = :elementId and b.monthdetail = :monthDetail')
                                ->setParameter('kpiId', $rankingKpiId)
                                ->setParameter('shipId', $rankingShipId)
                                ->setParameter('elementId', $rankingElementId)
                                ->setParameter('monthDetail', $new_monthdetail_date)
                                ->getQuery()
                                ->getResult();
                            if (count($rankingElementResult) != 0) {
                                $elementResultColor = $rankingElementResult[0]['elementcolor'];
                            } else {
                                $rankingElementResult[0]['elementdata'] = 0;
                            }

                            if ($elementResultColor == "false") {
                                $rankingElementColorValue = 0;
                            }

                            if ($elementResultColor == 'Green') {
                                $rankingElementColorValue = $rankingElementWeight;
                            } else if ($elementResultColor == 'Yellow') {
                                $rankingElementColorValue = $rankingElementWeight / 2;
                            } else if ($elementResultColor == 'Red') {
                                $rankingElementColorValue = 0;
                            }
                            $rankingElementValueTotal += $rankingElementColorValue;
                        }
                        $monthcount++;
                    }
                    array_push($rankingKpiValueCountArray, ($rankingElementValueTotal * $rankingKpiWeight / 100));
                }
                if ($manufacturingYear == "") {
                    $yearcount = 0;
                } else {
                    $currentdatestring = date('Y-01-01');
                    $d1 = new \DateTime($currentdatestring);
                    $man_datestring = $manufacturingYear . '-01-' . '01';
                    $d2 = new \DateTime($man_datestring);
                    $diff = $d2->diff($d1);
                    $yearcount = $diff->y + 1;
                    $vesselage = 20 / $yearcount;
                }
                array_push($ShipDetailDataarray, (array_sum($rankingKpiValueCountArray)));


            }
            array_push($oneChart_Data, array("name" => $rankingShipName, 'showInLegend' => true, "data" => $ShipDetailDataarray));
        }
        if ($mode == 'overallreports') {
            return array(
                'montharray' => $newcategories,
                'chartdata' => $oneChart_Data,
                'year' => $year
            );
        }
        $response = new JsonResponse();
        $response->setData
        (
            array(
                'montharray' => $newcategories,
                'chartdata' => $oneChart_Data,
                'year' => $year
            )
        );
        return $response;

    }

    /**
     * Allships Reports For Ranking
     *
     * @Route("/allships_send_rankingreports", name="allships_send_rankingreports")
     */
    public function allshipss_endreports_rankingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $currentdateitme=date('Y-m-d-H-i-s');
        $user = $this->getUser();
        if ($user != null) {
            $reportObject = $this->overall_ships_rankingreportsAction($request, 'overallreports');
            $mpdf = $this->container->get('tfox.mpdfport')->getMPdf();
            $mpdf->defaultheaderline = 0;
            $mpdf->defaultheaderfontstyle = 'B';
            $WateMarkImagePath = $this->container->getParameter('kernel.root_dir') . '/../web/images/pioneer_logo_02.png';
            $mpdf->SetWatermarkImage($WateMarkImagePath);
            $mpdf->showWatermarkImage = true;
            $graphObject = array(
                'chart' => array('renderTo' => 'areaId', 'type' => "line"),
                'exporting' => array('enabled' => false),
                'credits'=>array('enabled' => false),
                'legend' => array('layout' => 'vertical', 'align' => 'right', 'verticalAlign' => 'middle', 'borderWidth' => 0),
                'plotOptions' => array('series' => array(
                    "allowPointSelect" => true,
                    "dataLabels" => array(
                        "enabled" => true
                    )
                )),
                'series' => $reportObject['chartdata'],
                'subtitle' => array('style' => array('color' => '#0000f0', 'fontWeight' => 'bold')),
                'title' => array('text' => ''),
                'xAxis' => array('categories' => $reportObject['montharray'], 'labels' => array('style' => array('color' => '#0000F0'))),
                'yAxis' => array('max' => 100, 'title' => array('text' => 'Values', 'style' => array('color' => '#0000F0'))),
            );
            //$fileName = $reportObject['shipname'] . date('Y-m-d H-i-s') . '.pdf';
            $currentdatetime = date('Y-m-d-H-i-s');
            $jsondata = json_encode($graphObject);
            $pdffilenamefullpath = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/overall_ship_'.$currentdateitme.'.json';
            file_put_contents($pdffilenamefullpath, $jsondata);
            $Highchartconvertjs = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js -infile ';
            $outfile = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph/overall_ship_'.$currentdateitme.'.png';
            $JsonFileDirectroy = $pdffilenamefullpath . ' -outfile ' . $outfile . ' -scale 2.5 -width 1065';
            $ImageGeneration = 'phantomjs ' . $Highchartconvertjs . $JsonFileDirectroy;
            $handle = popen($ImageGeneration, 'r');
            $charamee = fread($handle, 2096);
            $htmlContentfor_report = $this->renderView('InitialShippingBundle:DashBorad:overall_shipreport_ranking_template.html.twig', array(
                'screenName' => 'Ranking Report',
                'userName' => '',
                'date' => date('Y-m-d'),
                'link' => 'overall_ship_'.$currentdateitme.'.png',
            ));
            $mpdf->AddPage('', 4, '', 'on');
            $mpdf->SetFooter('|Date/Time: {DATE l jS F Y h:i}| Page No: {PAGENO}');
            $mpdf->WriteHTML($htmlContentfor_report);
            $content = $mpdf->Output('', 'S');
            $response = new Response();
            $response->setContent($content);
            $response->headers->set('Content-Type', 'application/pdf');
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Allships  Reports Send to Mailing For Ranking
     *
     * @Route("/allships_send_rankingreports_mail", name="allships_send_rankingreports_mail")
     */
    public function allships_sendreports_mail_rankingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $currentdateitme=date('Y-m-d-H-i-s');
        $email = $user->getEmail();
        $reportObject = $this->overall_ships_rankingreportsAction($request, 'overallreports');
        $mpdf = $this->container->get('tfox.mpdfport')->getMPdf();
        $mpdf->defaultheaderline = 0;
        $mpdf->defaultheaderfontstyle = 'B';
        $WateMarkImagePath = $this->container->getParameter('kernel.root_dir') . '/../web/images/pioneer_logo_02.png';
        $mpdf->SetWatermarkImage($WateMarkImagePath);
        $mpdf->showWatermarkImage = true;
        $graphObject = array(
            'chart' => array('renderTo' => 'areaId', 'type' => "line"),
            'exporting' => array('enabled' => false),
            'credits'=>array('enabled' => false),
            'legend' => array('layout' => 'vertical', 'align' => 'right', 'verticalAlign' => 'middle', 'borderWidth' => 0),
            'plotOptions' => array('series' => array(
                "allowPointSelect" => true,
                "dataLabels" => array(
                    "enabled" => true
                )
            )),
            'series' => $reportObject['chartdata'],
            'subtitle' => array('style' => array('color' => '#0000f0', 'fontWeight' => 'bold')),
            'title' => array('text' => ''),
            'xAxis' => array('categories' => $reportObject['montharray'], 'labels' => array('style' => array('color' => '#0000F0'))),
            'yAxis' => array('max' => 100, 'title' => array('text' => 'Values', 'style' => array('color' => '#0000F0'))),
        );
        //$fileName = $reportObject['shipname'] . date('Y-m-d H-i-s') . '.pdf';
        $currentdatetime = date('Y-m-d-H-i-s');
        $jsondata = json_encode($graphObject);
        $pdffilenamefullpath = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/overall_ship_'.$currentdateitme.'.json';
        file_put_contents($pdffilenamefullpath, $jsondata);
        $Highchartconvertjs = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js -infile ';
        $outfile = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph/overall_ship_'.$currentdateitme.'.png';
        $JsonFileDirectroy = $pdffilenamefullpath . ' -outfile ' . $outfile . ' -scale 2.5 -width 1065';
        $ImageGeneration = 'phantomjs ' . $Highchartconvertjs . $JsonFileDirectroy;
        $handle = popen($ImageGeneration, 'r');
        $charamee = fread($handle, 2096);
        $htmlContentfor_report = $this->renderView('InitialShippingBundle:DashBorad:overall_shipreport_ranking_template.html.twig', array(
            'screenName' => 'Ranking Report',
            'userName' => '',
            'date' => date('Y-m-d'),
            'link' => 'overall_ship_'.$currentdateitme.'.png',
        ));
        $mpdf->AddPage('', 4, '', 'on');
        $mpdf->SetFooter('|Date/Time: {DATE l jS F Y h:i}| Page No: {PAGENO}');
        $mpdf->WriteHTML($htmlContentfor_report);
        $content = $mpdf->Output('', 'S');
        $fileName = 'overallshipreports_'. date('Y-m-d H-i-s') . '.pdf';
        $pdffilenamefullpath = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/brochures/' . $fileName;
        file_put_contents($pdffilenamefullpath, $content);
        $useremaildid = $request->request->get('clientemail');
        $mailbox = $request->request->get('comment');
        $mailidarray = array();
        if (filter_var($useremaildid, FILTER_VALIDATE_EMAIL)) {
            array_push($mailidarray, $useremaildid);
        } else {
            $findsemail = $em->createQueryBuilder()
                ->select('a.useremailid')
                ->from('InitialShippingBundle:EmailUsers', 'a')
                ->join('InitialShippingBundle:EmailGroup', 'b', 'WITH', 'b.id = a.groupid')
                ->where('b.groupname = :sq')
                ->ORwhere('a.useremailid = :sb')
                ->setParameter('sq', $useremaildid)
                ->setParameter('sb', $useremaildid)
                ->getQuery()
                ->getResult();


            //assign file attachement for mail and Mailing Starts Here...u
            for ($ma = 0; $ma < count($findsemail); $ma++) {
                /* $mailer = $this->container->get('mailer');
                 $message = \Swift_Message::newInstance()
                     ->setFrom($clientemailid)
                     ->setTo($findsemail[$ma]['emailid'])
                     ->setSubject($kpiname)
                     ->setBody($comment);
                 $message->attach(\Swift_Attachment::fromPath($pdffilenamefullpath)->setFilename($pdffilenamearray[0] . '.pdf'));
                 $mailer->send($message);*/
                array_push($mailidarray, $findsemail[$ma]['emailid']);
            }
        }
        //Mailing Ends....
        $rankinglookuptable = array('from_emailid' => $email, 'to_emailids' => $mailidarray, 'filename' => $fileName, 'comment' => $mailbox, 'subject' => $reportObject['shipname']);
        $gearman = $this->get('gearman');
        $gearman->doBackgroundJob('InitialShippingBundleserviceReadExcelWorker~common_mail_function', json_encode($rankinglookuptable));
        $response = new JsonResponse();
        $response->setData(array('updatemsg' => "Report has been send"));
        return $response;

    }
}