<?php

namespace Initial\ShippingBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\ArchivedReport;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Symfony\Component\HttpFoundation\Response;

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
                array("name" => "Management Performance", 'showInLegend' => false, 'color' => '#103a71', "data" => ""),
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
            if($inactiveMonth=='check') {
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
                    $inactiveMonth = $dateFromDb->format('n');
                    $activeMonth = $inactiveMonth - 2;
                }
            }
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
                array_push($monthlyKpiAverageValueTotal, (float)bcdiv($monthlyScorecardKpiWeightAverageValueTotal,1,3));
                array_push($overallMonthlyElementColorArray, $monthlyScorecardElementColorArray);
                array_push($overallMonthlyKpiSumValue, $monthlyKpiSumValue);
            }

            $series = array(
                array("name" => "Management Performance", 'showInLegend' => false, 'color' => 'blue', "data" => $monthlyKpiAverageValueTotal)
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

                $todayTime = date("H:i:s");
                $todayDate = date("Y-m-d");
                $pdfObject = $this->container->get('tfox.mpdfport')->getMPdf();
                $pdfObject->defaultheaderline = 0;
                $pdfObject->defaultheaderfontstyle = 'B';
                $waterMarkImage = $this->container->getParameter('kernel.root_dir') . '/../web/images/pioneer_logo_02.png';
                $pdfObject->SetWatermarkImage($waterMarkImage);
                $pdfObject->showWatermarkImage = true;

                $graphObject = array(
                    'chart' => array('renderTo' => 'areaId', 'type' => "line"),
                    'exporting' => array('enabled' => false),
                    'plotOptions' => array('series' => array(
                        "allowPointSelect" => true,
                        "dataLabels" => array(
                            "enabled" => true
                        )
                    )),
                    'series' => array(
                        array('name' => 'Series', 'showInLegend' => false, 'color' => '#103a71', 'data' => $monthlyKpiAverageValueTotal)
                    ),
                    'subtitle' => array('style' => array('color' => '#0000f0', 'fontWeight' => 'bold')),
                    'title' => array('text' => ''),
                    'xAxis' => array('categories' => $monthLetterArray, 'labels' => array('style' => array('color' => '#0000F0'))),
                    'yAxis' => array('max' => 3, 'min' => 0)
                );

                $jsonFileData = json_encode($graphObject);
                $jsonFilePath = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/graphData' . $todayDate . $todayTime . '.json';
                file_put_contents($jsonFilePath, $jsonFileData);
                $HighChartLocation = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js ';
                $inFile = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/graphData' . $todayDate . $todayTime . '.json ';
                $outFile = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph/graphImage' . $todayDate . $todayTime . '.png ';
                $imageGeneration = 'phantomjs ' . $HighChartLocation . '-infile ' . $inFile . '-outfile ' . $outFile . ' -scale 2.5 -width 1024';
                $fileHandle = popen($imageGeneration, 'r');
                $result = fread($fileHandle, 2096);

                $customerListDesign = $this->renderView('InitialShippingBundle:ScorecardReport:finalPdfTemplate.html.twig',
                    array(
                        'yearKpiColorArray' => $monthlyScorecardKpiColorArray,
                        'kpiAvgScore' => $monthlyKpiAverageValueTotal,
                        'monthName' => $monthLetterArray,
                        'kpiNameList' => $scorecardKpiList,
                        'imageSource' => 'graphImage' . $todayDate . $todayTime . '.png',
                        'headerTitle' => 'Scorecard Report'
                    ));

                $pdfObject->AddPage('', 4, '', 'on');
                $pdfObject->SetFooter('|{DATE l jS F Y H:i}| Page No: {PAGENO}');
                $pdfObject->WriteHTML($customerListDesign);

                for ($kpiCount = 0; $kpiCount < count($scorecardKpiList); $kpiCount++) {
                    $kpiDataArray = array();
                    $elementColorArray = array();
                    $elementNameList = array();
                    $kpiColorArray = array();
                    array_push($elementNameList, $overallElementListArray[$kpiCount]);
                    for ($monthCount = 0; $monthCount < count($monthLetterArray); $monthCount++) {
                        array_push($kpiDataArray, (int)$overallMonthlyKpiSumValue[$monthCount][$kpiCount]);
                        array_push($elementColorArray, $overallMonthlyElementColorArray[$monthCount][$kpiCount]);
                        array_push($kpiColorArray, $monthlyScorecardKpiColorArray[$monthCount][$kpiCount]);
                    }
                    $kpiGraphObject = array(
                        'chart' => array('renderTo' => 'areaId', 'type' => "line"),
                        'exporting' => array('enabled' => false),
                        'plotOptions' => array('series' => array(
                            "allowPointSelect" => true,
                            "dataLabels" => array(
                                "enabled" => true
                            )
                        )),
                        'series' => array(
                            array('name' => 'Series', 'showInLegend' => false, 'color' => '#103a71', 'data' => $kpiDataArray)
                        ),
                        'subtitle' => array('style' => array('color' => '#0000f0', 'fontWeight' => 'bold')),
                        'title' => array('text' => $scorecardKpiList[$kpiCount]['kpiName']),
                        'xAxis' => array('categories' => $monthLetterArray, 'labels' => array('style' => array('color' => '#0000F0'))),
                        'yAxis' => array('max' => 3, 'min' => 0)
                    );

                    $kpiJsonFileData = json_encode($kpiGraphObject);
                    $kpiJsonFilePath = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/KPI-' . $scorecardKpiList[$kpiCount]['id'] . $todayDate . $todayTime . '.json';
                    file_put_contents($kpiJsonFilePath, $kpiJsonFileData);
                    $kpiHighChartLocation = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js ';
                    $kpiInFile = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/KPI-' . $scorecardKpiList[$kpiCount]['id'] . $todayDate . $todayTime . '.json ';
                    $kpiOutFile = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph/KPI-' . $scorecardKpiList[$kpiCount]['id'] . $todayDate . $todayTime . '.png ';
                    $kpiImageGeneration = 'phantomjs ' . $kpiHighChartLocation . '-infile ' . $kpiInFile . '-outfile ' . $kpiOutFile . ' -scale 2.5 -width 1024';
                    $kpiFileHandle = popen($kpiImageGeneration, 'r');
                    $kpiResult = fread($kpiFileHandle, 2096);

                    $customerListDesign = $this->renderView('InitialShippingBundle:ScorecardReport:kpiLevelPdfTemplate.html.twig',
                        array(
                            'yearKpiColorArray' => $kpiColorArray,
                            'monthName' => $monthLetterArray,
                            'kpiNameList' => $scorecardKpiList[$kpiCount]['kpiName'],
                            'imageSource' => 'KPI-' . $scorecardKpiList[$kpiCount]['id'] . $todayDate . $todayTime . '.png',
                            'headerTitle' => $scorecardKpiList[$kpiCount]['kpiName'],
                            'elementNameList' => $elementNameList,
                            'elementColorArray' => $elementColorArray
                        ));

                    $pdfObject->AddPage('', 4, '', 'on');
                    $pdfObject->SetFooter('|{DATE l jS F Y H:i}| Page No: {PAGENO}');
                    $pdfObject->WriteHTML($customerListDesign);
                }
                if (!file_exists($this->container->getParameter('kernel.root_dir') . '/../web/pdfs')) {
                    mkdir($this->container->getParameter('kernel.root_dir') . '/../web/pdfs', 0777, true);
                }
                $pdfFilePath = $this->container->getParameter('kernel.root_dir') . '/../web/pdfs/' . $reportName . '.pdf';
                $pdfObject->Output($pdfFilePath,'F');
            }

            $archivedReport = $em->createQueryBuilder()
                ->select('a.fileName, a.reportType, a.dateTime, identity(a.userId), a.id')
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
            $lastdayofYear='01-12-'.$year;
            $lastMonthdateObject=new \DateTime($lastdayofYear);
            $lastMonthdateObject->modify('last day of this month');
            $statusFieldQuery = $em->createQueryBuilder()
                ->select('b.dataofmonth,b.status')
                ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                ->where('b.shipid = :shipId and b.status = :monthStatus')
                ->andwhere('b.dataofmonth <= :activeDate')
                ->setParameter('activeDate', $lastMonthdateObject)
                ->setParameter('monthStatus', 4)
                ->setParameter('shipId', $vesselId)
                ->groupby('b.dataofmonth')
                ->getQuery()
                ->getResult();
            if ($year == ' ') {
                for ($m = 0; $m < count($statusFieldQuery); $m++) {
                    $currentDate=$statusFieldQuery[$m]['dataofmonth'];
                    array_push($oneyear_montharray, $currentDate->format('Y-m-d'));
                }
                $currentyear = date('Y');
            }
            if ($year != ' ') {
                for ($m = 0; $m < count($statusFieldQuery); $m++) {
                    $currentDate=$statusFieldQuery[$m]['dataofmonth'];
                    array_push($oneyear_montharray, $currentDate->format('Y-m-d'));
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
            /*  $currentRequestYear = date('Y');
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
              }*/

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
            for ($d = 0; $d < count($oneyear_montharray); $d++) {
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
                for ($New_FindKpivalueCount = 0; $New_FindKpivalueCount < count($oneyear_montharray); $New_FindKpivalueCount++) {
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
                $todayTime = date("H:i:s");
                $todayDate = date("Y-m-d");
                $currentdateitme=date('Y-m-d-H-i-s');
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

                $mpdf = $this->container->get('tfox.mpdfport')->getMPdf();
                $mpdf->defaultheaderline = 0;
                $mpdf->defaultheaderfontstyle = 'B';
                /* $WateMarkImagePath = $this->container->getParameter('kernel.root_dir') . '/../web/images/pioneer_logo_02.png';
                 $mpdf->SetWatermarkImage($WateMarkImagePath);
                 $mpdf->showWatermarkImage = true;*/
                $graphObject = array(
                    'chart' => array('renderTo' => 'areaId', 'type' => "line",'width'=>1065),
                    'exporting' => array('enabled' => false),
                    'credits'=>array('enabled' => false),
                    'plotOptions' => array('series' => array(
                        "allowPointSelect" => true,
                        "dataLabels" => array(
                            "enabled" => true
                        )
                    )),
                    'series' => array(
                        array('name' => 'Series', 'showInLegend' => false, 'color' => '#103a71', 'data' => $dataforgraphforship)
                    ),
                    'subtitle' => array('style' => array('color' => '#0000f0', 'fontWeight' => 'bold')),
                    'title' => array('text' => $shipname),
                    'xAxis' => array('categories' => $newcategories, 'labels' => array('style' => array('color' => '#0000F0'))),
                    'yAxis' => array('max' => 100, 'title' => array('text' => 'Values', 'style' => array('color' => '#0000F0'))),
                );

                $jsondata = json_encode($graphObject);
                if (!file_exists($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles')) {
                    mkdir($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles', 0777, true);
                }
                if (!file_exists($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph')) {
                    mkdir($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph', 0777, true);
                }
                $pdffilenamefullpath = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/'.$todayTime.$todayDate.'.json';
                file_put_contents($pdffilenamefullpath, $jsondata);
                $Highchartconvertjs = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js -infile ';

                $outfile = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph/'.$todayTime.$todayDate.'.png';
                $jsonFile = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/'.$todayTime.$todayDate.'.json';
                $ImageGeneration = 'phantomjs '.$Highchartconvertjs.$jsonFile.' -outfile '.$outfile.' -scale 2.5 -width 1065';
                $handle = popen($ImageGeneration, 'r');
                $charamee = fread($handle, 2096);

                $customerListDesign = $this->renderView('InitialShippingBundle:DashBorad:overallranking_report_template.html.twig', array(
                    'shipid' => $vesselId,
                    'screenName' => 'Ranking Report',
                    'userName' => '',
                    'date' => date('Y-m-d'),
                    'link' => $todayTime.$todayDate.'.png',
                    'listofkpi' => $rankingKpiList,
                    'kpiweightage' => $rankingKpiWeightarray,
                    'montharray' => $newcategories,
                    'shipname' => $shipname,
                    'countmonth' => count($newcategories),
                    'avgscore' => $monthlyKpiAverageScore,
                    'ageofvessel' => $yearcount,
                    'kpimonthdata' => $monthlyKpiValue,
                    'currentyear' => $year
                ));
                $mpdf->AddPage('A4-L');
                $mpdf->SetFooter('|Date/Time: {DATE l jS F Y h:i}| Page No: {PAGENO}');
                $mpdf->WriteHTML($customerListDesign);

                for ($KpiPdfcount = 0; $KpiPdfcount < count($rankingKpiList); $KpiPdfcount++) {
                    $kpiName = $rankingKpiList[$KpiPdfcount]['kpiName'];
                    $kpiid = $rankingKpiList[$KpiPdfcount]['id'];
                    $weightage = $rankingKpiList[$KpiPdfcount]['weightage'];
                    if ($kpiName != 'Vessel age') {
                        $graphObject = array(
                            'chart' => array('renderTo' => 'areaId', 'type' => "line",'width'=>1065),
                            'exporting' => array('enabled' => false),
                            'credits'=>array('enabled' => false),
                            'plotOptions' => array('series' => array(
                                "allowPointSelect" => true,
                                "dataLabels" => array(
                                    "enabled" => true
                                )
                            )),
                            'series' => array(
                                array('name' => 'Series', 'showInLegend' => false, 'color' => '#103a71', 'data' => $New_overallfindingelementgraph[$kpiid])
                            ),
                            'subtitle' => array('style' => array('color' => '#0000f0', 'fontWeight' => 'bold')),
                            'title' => array('text' => $kpiName),
                            'xAxis' => array('categories' => $newcategories, 'labels' => array('style' => array('color' => '#0000F0'))),
                            'yAxis' => array('max' => $weightage, 'title' => array('text' => 'Values', 'style' => array('color' => '#0000F0'))),
                        );
                    }
                    else
                    {
                        $graphObject = array(
                            'chart' => array('renderTo' => 'areaId', 'type' => "line",'width'=>1065),
                            'exporting' => array('enabled' => false),
                            'credits'=>array('enabled' => false),
                            'plotOptions' => array('series' => array(
                                "allowPointSelect" => true,
                                "dataLabels" => array(
                                    "enabled" => true
                                )
                            )),
                            'series' => array(
                                array('name' => 'Series', 'showInLegend' => false, 'color' => '#103a71', 'data' => $vesseldata)
                            ),
                            'subtitle' => array('style' => array('color' => '#0000f0', 'fontWeight' => 'bold')),
                            'title' => array('text' => $kpiName),
                            'xAxis' => array('categories' => $newcategories, 'labels' => array('style' => array('color' => '#0000F0'))),
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
                        'montharray' => $newcategories,
                        'kpiname' => $kpiName,
                        'countmonth' => count($newcategories),
                        'kpigraph' => $New_overallfindingelementgraph[$kpiid],
                        'elementcolorarray' => $New_overallfindingelementcolor[$kpiid],
                        'monthlydata' => $New_overallfindingelementvalue[$kpiid],
                        'elementRule' => $scorecardElementRules,
                        'listofelement' => $ElementName_Weightage[$kpiid],
                        'countofelement' => count($ElementName_Weightage[$kpiid]),
                        'currentyear' => $year
                    ));

                    $mpdf->AddPage('A4-L');
                    $mpdf->SetFooter('|Date/Time: {DATE l jS F Y h:i}| Page No: {PAGENO}');
                    $mpdf->WriteHTML($customerListDesign);
                }
                if (!file_exists($this->container->getParameter('kernel.root_dir') . '/../web/pdfs')) {
                    mkdir($this->container->getParameter('kernel.root_dir') . '/../web/pdfs', 0777, true);
                }
                $pdfFilePath = $this->container->getParameter('kernel.root_dir') . '/../web/pdfs/' . $reportName . '.pdf';
                $mpdf->Output($pdfFilePath,'F');
            }

            $archivedReport = $em->createQueryBuilder()
                ->select('a.fileName, a.reportType, a.dateTime, identity(a.userId), a.id')
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
     * @Route("/archived_ranking_predefined_report", name="archived_ranking_predefined_report")
     */
    public function rankingPredefinedReportAction(Request $request,$mode = '')
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $userName = $user->getUsername();
        if ($user != null) {
            $em = $this->getDoctrine()->getManager();
            $year = $request->request->get('selectedYear');
            $title = $request->request->get('title');

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

            $vesselArray = array();
            $newcategories = array();
            $DataRankingReports = 0;

            $monthlyShipDataStatus = $em->createQueryBuilder()
                ->select('b.status, b.dataofmonth')
                ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                ->where('b.shipid = :shipId')
                ->setParameter('shipId', $listAllShipForCompany[0]['id'])
                ->getQuery()
                ->getResult();
            $statusMonth = $monthlyShipDataStatus[count($monthlyShipDataStatus)-1]['dataofmonth'];

            for ($shipCount = 0; $shipCount < count($listAllShipForCompany); $shipCount++) {
                $rankingShipName = $listAllShipForCompany[$shipCount]['shipName'];
                $rankingShipId = $listAllShipForCompany[$shipCount]['id'];

                array_push($vesselArray,$rankingShipName);

                $initial = 0;
                $statusVerified = 0;

                if($title == 'As on date') {
                    $initial= 0;
                    $statusVerified = date('n');
                } elseif($title == 'Quarterly') {
                    $initial = 0;
                    $statusVerified= 3;
                } elseif($title == 'Half-Yearly') {
                    $initial = 0;
                    $statusVerified= 6;
                } elseif ($title == 'Last two months') {
                    $statusVerified = $statusMonth->format('n');
                    $initial = $statusVerified-3;
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
                    array_push($ShipDetailDataarray, (array_sum($rankingKpiValueCountArray)));
                }
                array_push($oneChart_Data, array("name" => $rankingShipName, 'showInLegend' => true, "data" => $ShipDetailDataarray));
            }

            $series = array();
            for($monthCount=0;$monthCount<count($newcategories);$monthCount++) {
                $dataArray = array();
                for($i=0;$i<count($oneChart_Data);$i++) {
                    array_push($dataArray,$oneChart_Data[$i]['data'][$monthCount]);
                }
                array_push($series,array("name" => $newcategories[$monthCount], "data" => $dataArray));
            }
            if($mode=='printReports')
            {
                return array(
                    'series' => $series,
                    'year' => $year,
                    'title' => $title,
                    'categories' => $vesselArray
                );
            }
            $response = new JsonResponse();
            $response->setData(array(
                    'series' => $series,
                    'year' => $year,
                    'title' => $title,
                    'categories' => $vesselArray
                )
            );
            return $response;

        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }
    /**
     * Lists all ArchivedReport Ranking Reports to Print.
     *
     * @Route("/archived_ranking_predefined_report_print", name="archived_ranking_predefined_report_print")
     */
    public function rankingPredefinedReportPrintAction(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $userName = $user->getUsername();
        if ($user != null) {
            $em = $this->getDoctrine()->getManager();
            $reportObject = $this->rankingPredefinedReportAction($request, 'printReports');
            $currentdateitme=date('Y-m-d-H-i-s');
            $mpdf = $this->container->get('tfox.mpdfport')->getMPdf();
            $mpdf->defaultheaderline = 0;
            $mpdf->defaultheaderfontstyle = 'B';
            $WateMarkImagePath = $this->container->getParameter('kernel.root_dir') . '/../web/images/pioneer_logo_02.png';
            $mpdf->SetWatermarkImage($WateMarkImagePath);
            $mpdf->showWatermarkImage = true;
            $graphObject = array(
                'colors'=>array("#7cb5ec", "#f7a35c", "#90ee7e", "#7798BF", "#aaeeee", "#ff0066", "#eeaaee",
                    "#55BF3B", "#DF5353", "#7798BF", "#aaeeee"),
                'chart' => array('renderTo' => 'areaId', 'type' => "column",'width'=>965,'height'=>525,'backgroundColor'> null,
                    'style'=>array('fontFamily'=>'Dosis, sans-serif')),
                'exporting' => array('enabled' => false),
                'credits'=>array('enabled' => false),
                'plotOptions' => array('column' => array(
                    'pointPadding'=> 0.2,
                    'borderWidth'=> 0),
                ),
                'series' => $reportObject['series'],
                'title' => array('text' => $reportObject['title']),
                'xAxis' => array('categories' => $reportObject['categories'],'crosshair'=> true,'gridLineWidth'=> 1,'labels'=>array('style'=>array('fontSize'=>'12px'))),
                'yAxis' => array('max' => 100,  'minorTickInterval'=> 'auto','title' => array('text' => 'Month wise data','labels'=>array('style'=>array('fontSize'=>'12px')))),
                'background2'=> '#F0F0EA'
            );
            $jsondata = json_encode($graphObject);
            if (!file_exists($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles')) {
                mkdir($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles', 0777, true);
            }
            if (!file_exists($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph')) {
                mkdir($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph', 0777, true);
            }
            $pdffilenamefullpath = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/predefinedreports_'.$currentdateitme.'.json';
            file_put_contents($pdffilenamefullpath, $jsondata);
            $Highchartconvertjs = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js -infile ';
            $outfile = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph/predefinedreports_'.$currentdateitme.'.png';
            $JsonFileDirectroy = $pdffilenamefullpath . ' -outfile ' . $outfile . ' -scale 2.5 -width 1065';
            $ImageGeneration = 'phantomjs ' . $Highchartconvertjs . $JsonFileDirectroy;
            $handle = popen($ImageGeneration, 'r');
            $charamee = fread($handle, 2096);
            $htmlContentfor_report = $this->renderView('InitialShippingBundle:DashBorad:predefinedreports_ranking.html.twig', array(
                'screenName' => 'Predefined  Reports',
                'userName' => '',
                'date' => date('Y-m-d'),
                'link' => 'predefinedreports_'.$currentdateitme.'.png',
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
     * Lists all ArchivedReport Ranking Report to send Mail.
     *
     * @Route("/archived_ranking_predefined_report_mail", name="archived_ranking_predefined_report_mail")
     */
    public function rankingPredefinedReportMailAction(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $userName = $user->getUsername();
        if ($user != null) {
            $em = $this->getDoctrine()->getManager();
            //   $year = $request->request->get('predefine_year');
            $title = $request->request->get('predefine_title');

            /*if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
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

            $vesselArray = array();
            $newcategories = array();
            $DataRankingReports = 0;

            $monthlyShipDataStatus = $em->createQueryBuilder()
                ->select('b.status, b.dataofmonth')
                ->from('InitialShippingBundle:Ranking_LookupStatus', 'b')
                ->where('b.shipid = :shipId')
                ->setParameter('shipId', $listAllShipForCompany[0]['id'])
                ->getQuery()
                ->getResult();
            $statusMonth = $monthlyShipDataStatus[count($monthlyShipDataStatus)-1]['dataofmonth'];

            for ($shipCount = 0; $shipCount < count($listAllShipForCompany); $shipCount++) {
                $rankingShipName = $listAllShipForCompany[$shipCount]['shipName'];
                $rankingShipId = $listAllShipForCompany[$shipCount]['id'];

                array_push($vesselArray,$rankingShipName);

                $initial = 0;
                $statusVerified = 0;

                if($title == 'As on date') {
                    $initial= 0;
                    $statusVerified = date('n');
                } elseif($title == 'Quarterly') {
                    $initial = 0;
                    $statusVerified= 3;
                } elseif($title == 'Half-Yearly') {
                    $initial = 0;
                    $statusVerified= 6;
                } elseif ($title == 'Last two months') {
                    $statusVerified = $statusMonth->format('n');
                    $initial = $statusVerified-3;
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
                    array_push($ShipDetailDataarray, (array_sum($rankingKpiValueCountArray)));
                }
                array_push($oneChart_Data, array("name" => $rankingShipName, 'showInLegend' => true, "data" => $ShipDetailDataarray));
            }

            $series = array();
            for($monthCount=0;$monthCount<count($newcategories);$monthCount++) {
                $dataArray = array();
                for($i=0;$i<count($oneChart_Data);$i++) {
                    array_push($dataArray,$oneChart_Data[$i]['data'][$monthCount]);
                }
                array_push($series,array("name" => $newcategories[$monthCount], "data" => $dataArray));
            }*/
            $reportObject = $this->rankingPredefinedReportAction($request, 'printReports');
            $currentdateitme=date('Y-m-d-H-i-s');
            $mpdf = $this->container->get('tfox.mpdfport')->getMPdf();
            $mpdf->defaultheaderline = 0;
            $mpdf->defaultheaderfontstyle = 'B';
            $WateMarkImagePath = $this->container->getParameter('kernel.root_dir') . '/../web/images/pioneer_logo_02.png';
            $mpdf->SetWatermarkImage($WateMarkImagePath);
            $mpdf->showWatermarkImage = true;
            $graphObject = array(
                'colors'=>array("#7cb5ec", "#f7a35c", "#90ee7e", "#7798BF", "#aaeeee", "#ff0066", "#eeaaee",
                    "#55BF3B", "#DF5353", "#7798BF", "#aaeeee"),
                'chart' => array('renderTo' => 'areaId', 'type' => "column",'width'=>965,'height'=>525,'backgroundColor'> null,
                    'style'=>array('fontFamily'=>'Dosis, sans-serif')),
                'exporting' => array('enabled' => false),
                'credits'=>array('enabled' => false),
                'plotOptions' => array('column' => array(
                    'pointPadding'=> 0.2,
                    'borderWidth'=> 0),
                    'candlestick'=>array('lineColor'=>'#404048') ),
                'series' => $reportObject['series'],
                'title' => array('text' => $reportObject['title']),
                'xAxis' => array('categories' => $reportObject['categories'],'crosshair'=> true,'gridLineWidth'=> 1,'labels'=>array('style'=>array('fontSize'=>'12px'))),
                'yAxis' => array('max' => 100,  'minorTickInterval'=> 'auto','title' => array('text' => 'Month wise data','labels'=>array('style'=>array('fontSize'=>'12px')))),
                'background2'=> '#F0F0EA'
            );
            $jsondata = json_encode($graphObject);
            if (!file_exists($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles')) {
                mkdir($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles', 0777, true);
            }
            if (!file_exists($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph')) {
                mkdir($this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph', 0777, true);
            }
            $pdffilenamefullpath = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofjsonfiles/predefinedreports_'.$currentdateitme.'.json';
            file_put_contents($pdffilenamefullpath, $jsondata);
            $Highchartconvertjs = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js -infile ';
            $outfile = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/listofgraph/predefinedreports_'.$currentdateitme.'.png';
            $JsonFileDirectroy = $pdffilenamefullpath . ' -outfile ' . $outfile . ' -scale 2.5 -width 1065';
            $ImageGeneration = 'phantomjs ' . $Highchartconvertjs . $JsonFileDirectroy;
            $handle = popen($ImageGeneration, 'r');
            $charamee = fread($handle, 2096);
            $htmlContentfor_report = $this->renderView('InitialShippingBundle:DashBorad:predefinedreports_ranking.html.twig', array(
                'screenName' => 'Predefined  Reports',
                'userName' => '',
                'date' => date('Y-m-d'),
                'link' => 'predefinedreports_'.$currentdateitme.'.png',
            ));
            $mpdf->AddPage('', 4, '', 'on');
            $mpdf->SetFooter('|Date/Time: {DATE l jS F Y h:i}| Page No: {PAGENO}');
            $mpdf->WriteHTML($htmlContentfor_report);
            $content = $mpdf->Output('', 'S');
            $fileName = 'predefinedreports_'. date('Y-m-d H-i-s') . '.pdf';
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
            $email = $user->getEmail();
            //Mailing Ends....
            $rankinglookuptable = array('from_emailid' => $email, 'to_emailids' => $mailidarray, 'filename' => $fileName, 'comment' => $mailbox, 'subject' => 'Predefine Reports');
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
                ->select('a.fileName, a.reportType, a.dateTime, identity(a.userId),a.id')
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
     * Lists all ArchivedReport entities.
     *
     * @Route("/archived_report_pdf_download/{id}", name="archived_report_pdf_download")
     * @Method("GET")
     */
    public function pdfReportDownloadAction(Request $request,ArchivedReport $archivedReport)
    {
        $user = $this->getUser();
        if ($user != null) {
            $fileName_fromDb = $archivedReport->getFileName();
            $directoryLocation = $this->container->getParameter('kernel.root_dir') . '/../web/pdfs';
            $filePath = $directoryLocation . '/' . $fileName_fromDb . '.pdf';
            $content = file_get_contents($filePath);
            $response = new Response();
            $response->setContent($content);
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . $fileName_fromDb . "\"");
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
