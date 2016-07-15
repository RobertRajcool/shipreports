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
                array(
                    "name" => "",
                    'color' => '#103a71',
                    "data" => ""
                ),
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
            return $this->render('archivedreport/index.html.twig', array(
                'vesselList' => $listAllShipForCompany,
                'chart' => $ob,
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

            $response = new JsonResponse();
            $response->setData(array(
                'yearKpiColorArray' => $monthlyScorecardKpiColorArray,
                'kpiAvgScore' => $monthlyKpiAverageValueTotal,
                'monthName' => $monthLetterArray,
                'kpiNameList' => $scorecardKpiList,
                'elementNameList' => $overallElementListArray,
                'elementColorArray' => $overallMonthlyElementColorArray,
                'changeChartData' => $monthlyKpiAverageValueTotal,
                'elementLevelChartData' => $overallMonthlyKpiSumValue
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
