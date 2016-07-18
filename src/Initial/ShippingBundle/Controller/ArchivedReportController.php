<?php

namespace Initial\ShippingBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\ArchivedReport;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ob\HighchartsBundle\Highcharts\Highchart;

/**
 * ArchivedReport controller.
 *
 * @Route("/archivedreport")
 */
class ArchivedReportController extends Controller
{
    /**
     * Lists all ArchivedReport entities.
     *
     * @Route("/", name="archivedreport_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $user = $this->getUser();
        if ($user != null) {
            $em = $this->getDoctrine()->getManager();
            $userId = $user->getId();
            $userName = $user->getUsername();
            $role = $user->getRoles();
            $listAllShipForCompany = " ";
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
            }
            $series = array(
                array("name" => "", 'color' => '#103a71', "data" => ""),
            );
            $ob = new Highchart();
            $ob->chart->renderTo('area');
            $ob->chart->type('line');
            $ob->title->text(' ', array('style' => array('color' => 'red')));
            $ob->subtitle->style(array('color' => '#0000f0', 'fontWeight' => 'bold'));
            $ob->xAxis->categories('');
            $ob->xAxis->labels(array('style' => array('color' => '#0000F0')));
            $ob->yAxis->max(3);
            $ob->series($series);
            $ob->plotOptions->series(array('allowPointSelect' => true, 'dataLabels' => array('enabled' => true)));
            $ob->exporting->enabled(false);

            $series_ranking = array(
                array("name" => "", 'showInLegend' => false, 'color' => '#103a71', "data" => array())
            );
            $ob_ranking = new Highchart();
            $ob_ranking->chart->renderTo('area_ranking');
            $ob_ranking->chart->type('line');
            $ob_ranking->credits->enabled(false);
            $ob_ranking->title->text('', array('style' => array('color' => 'red')));
            $ob_ranking->subtitle->style(array('color' => '#0000f0', 'fontWeight' => 'bold'));
            $ob_ranking->xAxis->categories(array());
            $ob_ranking->xAxis->labels(array('style' => array('color' => '#0000F0')));
            $ob_ranking->yAxis->max(100);
            $ob_ranking->yAxis->title(array('text' => 'Values', 'style' => array('color' => '#0000F0')));
            $ob_ranking->series($series_ranking);
            $ob_ranking->plotOptions->series(array('allowPointSelect' => true, 'dataLabels' => array('enabled' => true)));
            $ob_ranking->exporting->enabled(false);

            return $this->render('archivedreport/index.html.twig', array(
                'vesselList' => $listAllShipForCompany,
                'chart' => $ob,
                'rankingChart' => $ob_ranking,
                'heading' => 'Management Performance'
            ));
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    /**
     * Lists all ArchivedReport entities.
     *
     * @Route("/archived_scorecard_report", name="archived_scorecard_report")
     */
    public function scorecardReportAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $em = $this->getDoctrine()->getManager();

            $activeMonth = $request->request->get('activeMonth');
            $activeYear = $request->request->get('activeYear');
            $inactiveMonth = $request->request->get('endMonth');
            $inactiveYear = $request->request->get('endYear');
            $archiveStatus = $request->request->get('archiveStatus');
            $monthArray = array();
            $startDate = new \DateTime(date('Y-m-d', mktime(0, 0, 0, $activeMonth + 1, 0, date($activeYear))));
            $startDate->modify('first day of this month');
            $endDate = new \DateTime(date('Y-m-d', mktime(0, 0, 0, $inactiveMonth + 1, 0, date($inactiveYear))));
            $endDate->modify('first day of next month');
            $dateInterval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($startDate, $dateInterval, $endDate);
            foreach ($period as $dt) {
                array_push($monthArray, $dt->format("Y-m-d"));
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
            $overallElementListArray = array();
            $overallMonthlyElementColorArray = array();
            $overallMonthlyKpiSumValue = array();
            for ($dateCount = 0; $dateCount < count($monthArray); $dateCount++) {
                $monthlyKpiSumValue = array();
                $monthlyScorecardElementColorArray = array();
                $scorecardKpiColorArray = array();
                $date = strtotime($monthArray[$dateCount]);
                $monthLetterFormat = date('M', $date);
                array_push($monthLetterArray, $monthLetterFormat);
                $monthDetail = new \DateTime($monthArray[$dateCount]);
                $monthDetail->modify('last day of this month');
                $monthlyScorecardKpiWeightAverageValueTotal = 0;

                for ($kpiCount = 0; $kpiCount < count($scorecardKpiList); $kpiCount++) {
                    $scorecardElementColorArray = array();
                    $scorecardAllKpiId = $scorecardKpiList[$kpiCount]['id'];
                    $scorecardKpiWeight = $scorecardKpiList[$kpiCount]['weightage'];

                    $scorecardElementArray = $em->createQueryBuilder()
                        ->select('c.id, c.elementName,  c.weightage')
                        ->from('InitialShippingBundle:ElementDetails', 'c')
                        ->where('c.kpiDetailsId = :kpiId')
                        ->setParameter('kpiId', $scorecardAllKpiId)
                        ->getQuery()
                        ->getResult();
                    if ($dateCount == 0) {
                        $scorecardElementList = $em->createQueryBuilder()
                            ->select('c.id, c.elementName,  c.weightage')
                            ->from('InitialShippingBundle:ElementDetails', 'c')
                            ->where('c.kpiDetailsId = :kpiId')
                            ->setParameter('kpiId', $scorecardAllKpiId)
                            ->orderBy('c.id')
                            ->getQuery()
                            ->getResult();
                        array_push($overallElementListArray, $scorecardElementList);
                    }

                    if (count($scorecardElementArray) > 0) {
                        for ($elementCount = 0; $elementCount < count($scorecardElementArray); $elementCount++) {
                            $scorecardElementId = $scorecardElementArray[$elementCount]['id'];
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
                            array_push($scorecardElementColorArray, $elementResultColor);
                        }
                    }
                    $kpiResult = $em->createQueryBuilder()
                        ->select('b.kpiColor, b.individualKpiAverageScore')
                        ->from('InitialShippingBundle:Scorecard_LookupData', 'b')
                        ->where('b.kpiDetailsId = :kpiId and b.monthdetail = :monthDetail')
                        ->setParameter('kpiId', $scorecardAllKpiId)
                        ->setParameter('monthDetail', $monthDetail)
                        ->getQuery()
                        ->getResult();
                    if (count($kpiResult) != 0) {
                        array_push($scorecardKpiColorArray, $kpiResult[0]['kpiColor']);
                        $monthlyScorecardKpiWeightAverageValueTotal += ($kpiResult[0]['individualKpiAverageScore'] * $scorecardKpiWeight) / 100;
                        array_push($monthlyKpiSumValue, (int)$kpiResult[0]['individualKpiAverageScore']);
                    } else {
                        array_push($scorecardKpiColorArray, "");
                        $monthlyScorecardKpiWeightAverageValueTotal += 0;
                        array_push($monthlyKpiSumValue, 0);
                    }
                    array_push($monthlyScorecardElementColorArray, $scorecardElementColorArray);
                }
                array_push($monthlyScorecardKpiColorArray, $scorecardKpiColorArray);
                array_push($monthlyKpiAverageValueTotal, $monthlyScorecardKpiWeightAverageValueTotal);
                array_push($overallMonthlyElementColorArray, $monthlyScorecardElementColorArray);
                array_push($overallMonthlyKpiSumValue, $monthlyKpiSumValue);
            }

            $series = array
            (
                array("name" => "Management Performance", 'showInLegend' => false, 'color' => 'blue', "data" => $monthlyKpiAverageValueTotal),

            );

            $ob = new Highchart();
            $ob->chart->renderTo('area');
            $ob->chart->type('line');
            $ob->title->text('Star Systems Reporting Tool ', array('style' => array('color' => 'red')));
            $ob->xAxis->categories($monthLetterArray);
            $ob->xAxis->labels(array('style' => array('color' => '#0000F0')));
            $ob->series($series);
            $ob->plotOptions->series(array('allowPointSelect' => true, 'dataLabels' => array('enabled' => true)));
            $ob->exporting->enabled(false);

            if($archiveStatus==1) {
                $reportName = $startDate->format('M').'-'.$startDate->format('Y').' '.'to'.' '.$endDate->format('M').'-'.$endDate->format('Y');
                $userId = $user->getId();
                $dateTime = date("Y-m-d H:i:s");
                $dateTimeObj = new \DateTime($dateTime);

                $archivedReportObj = new ArchivedReport();
                $archivedReportObj->setFileName($reportName);
                $archivedReportObj->setDateTime($dateTimeObj);
                $archivedReportObj->setUserId($em->getRepository('InitialShippingBundle:User')->findOneBy(array('id' => $userId)));
                $archivedReportObj->setReportType('scorecard');
                $em->persist($archivedReportObj);
                $em->flush();
            }

            $archivedReport = $em->createQueryBuilder()
                ->select('a.fileName, a.reportType, a.dateTime, identity(a.userId)')
                ->from('InitialShippingBundle:ArchivedReport', 'a')
                ->getQuery()
                ->getResult();

            $userDetailsArray = array();

            for($fileCount=0;$fileCount<count($archivedReport);$fileCount++) {
                $userDetails = $em->createQueryBuilder()
                    ->select('a.username, a.email, a.fullname, a.imagepath')
                    ->from('InitialShippingBundle:User', 'a')
                    ->where('a.id = :userId')
                    ->setParameter('userId',$archivedReport[$fileCount]['1'])
                    ->getQuery()
                    ->getResult();
                array_push($userDetailsArray,$userDetails);
            }

            $response = new JsonResponse();
            $response->setData(array(
                'yearKpiColorArray' => $monthlyScorecardKpiColorArray,
                'kpiAvgScore' => $monthlyKpiAverageValueTotal,
                'monthName' => $monthLetterArray,
                'kpiNameList' => $scorecardKpiList,
                'elementNameList' => $overallElementListArray,
                'elementColorArray' => $overallMonthlyElementColorArray,
                'changeChartData' => $monthlyKpiAverageValueTotal,
                'elementLevelChartData' => $overallMonthlyKpiSumValue,
                'userDetails' => $userDetailsArray,
                'archivedReports' => $archivedReport
            ));
            return $response;

        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Lists all ArchivedReport entities.
     *
     * @Route("/archived_ranking_report", name="archived_ranking_report")
     */
    public function rankingReportAction(Request $request) {
        $user = $this->getUser();
        if ($user != null) {
            $em = $this->getDoctrine()->getManager();
            $vesselId = $request->request->get('selectedVessel');
            $vesselObj = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $vesselId));
            $mfYear = $vesselObj->getManufacturingYear();
            $vesselName = $vesselObj->getShipName();
            $year = $request->request->get('selectedYear');
            $archiveStatus = $request->request->get('archiveStatus');

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
                ->setParameter('shipid', $vesselId)
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
                    ->setParameter('shipId', $vesselId)
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
                        ->setParameter('shipId', $vesselId)
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
                        ->setParameter('shipId', $vesselId)
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
                        if ($mfYear == "") {
                            $yearcount = 0;
                        } else {

                            $man_datestring = $mfYear. '-01';
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
                                ->setParameter('shipId', $vesselId)
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
                                ->setParameter('shipId', $vesselId)
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

            $shipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $vesselId));
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

            if($archiveStatus==1) {
                $reportName = $vesselName.'-'.$year;
                $userId = $user->getId();
                $dateTime = date("Y-m-d H:i:s");
                $dateTimeObj = new \DateTime($dateTime);

                $archivedReportObj = new ArchivedReport();
                $archivedReportObj->setFileName($reportName);
                $archivedReportObj->setDateTime($dateTimeObj);
                $archivedReportObj->setUserId($em->getRepository('InitialShippingBundle:User')->findOneBy(array('id' => $userId)));
                $archivedReportObj->setReportType('Ranking');
                $em->persist($archivedReportObj);
                $em->flush();
            }

            $archivedReport = $em->createQueryBuilder()
                ->select('a.fileName, a.reportType, a.dateTime, identity(a.userId)')
                ->from('InitialShippingBundle:ArchivedReport', 'a')
                ->getQuery()
                ->getResult();

            $userDetailsArray = array();

            for($fileCount=0;$fileCount<count($archivedReport);$fileCount++) {
                $userDetails = $em->createQueryBuilder()
                    ->select('a.username, a.email, a.fullname, a.imagepath')
                    ->from('InitialShippingBundle:User', 'a')
                    ->where('a.id = :userId')
                    ->setParameter('userId',$archivedReport[$fileCount]['1'])
                    ->getQuery()
                    ->getResult();
                array_push($userDetailsArray,$userDetails);
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
                    'userDetails' => $userDetailsArray,
                    'archivedReports' => $archivedReport
                )
            );
            return $response;

        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

    /**
     * Lists all ArchivedReport entities.
     *
     * @Route("/archived_report_show", name="archived_report_show")
     */
    public function archivedReportShowAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $em = $this->getDoctrine()->getManager();
            $archivedReport = $em->createQueryBuilder()
                ->select('a.fileName, a.reportType, a.dateTime, identity(a.userId)')
                ->from('InitialShippingBundle:ArchivedReport', 'a')
                ->getQuery()
                ->getResult();

            $userDetailsArray = array();

            for($fileCount=0;$fileCount<count($archivedReport);$fileCount++) {
                $userDetails = $em->createQueryBuilder()
                    ->select('a.username, a.email, a.fullname, a.imagepath')
                    ->from('InitialShippingBundle:User', 'a')
                    ->where('a.id = :userId')
                    ->setParameter('userId',$archivedReport[$fileCount]['1'])
                    ->getQuery()
                    ->getResult();
                array_push($userDetailsArray,$userDetails);
            }

            $response = new JsonResponse();
            $response->setData(array(
                'userDetails' => $userDetailsArray,
                'archivedReports' => $archivedReport
            ));
            return $response;

        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    /**
     * Finds and displays a ArchivedReport entity.
     *
     * @Route("/{id}", name="archivedreport_show")
     * @Method("GET")
     */
    public function showAction(ArchivedReport $archivedReport)
    {

        return $this->render('archivedreport/show.html.twig', array(
            'archivedReport' => $archivedReport,
        ));
    }
}
