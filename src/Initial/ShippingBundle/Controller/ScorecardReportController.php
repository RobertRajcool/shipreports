<?php
/**
 * Created by PhpStorm.
 * User: Hariprakash
 * Date: 19/4/16
 * Time: 5:21 PM
 */
namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\DateTime;
use Ob\HighchartsBundle\Highcharts\Highchart;

/**
 * ScorecardReportController.
 *
 * @Route("/scorecard_report")
 */
class ScorecardReportController extends Controller
{
    /**
     * ScorecardReport Home.
     *
     * @Route("/", name="scorecard_report_home")
     */
    public function indexAction()
    {
        $series = array
        (
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

        return $this->render('InitialShippingBundle:ScorecardReport:home.html.twig',
        array(
            'chart'=>$ob,
        ));
    }

    /**
     * ScorecardReport Home.
     *
     * @Route("/reportGenerate", name="scorecard_report_report")
     */
    public function reportGenerateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
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

        $activeMonth = $request->request->get('activeMonth');
        $activeYear = $request->request->get('activeYear');
        $inactiveMonth = $request->request->get('endMonth');
        $inactiveYear = $request->request->get('endYear');
        $monthArray = array();
        $start    = new \DateTime(date('Y-m-d', mktime(0,0,0,$activeMonth+1, 0, date($activeYear))));
        $start->modify('first day of this month');
        $end      = new \DateTime(date('Y-m-d', mktime(0,0,0,$inactiveMonth+1, 0, date($inactiveYear))));
        $end->modify('first day of next month');
        $interval = \DateInterval::createFromDateString('1 month');
        $period   = new \DatePeriod($start, $interval, $end);
        foreach ($period as $dt) {
            array_push($monthArray,$dt->format("Y-m-d"));
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
        $overallElementListArray = array();
        $overallMonthlyElementColorArray = array();
        for ($dateCount=0; $dateCount<count($monthArray);$dateCount++)
        {
            $monthlyScorecardElementColorArray = array();
            $scorecardKpiColorArray = array();
            $date = strtotime($monthArray[$dateCount]);
            $monthLetterFormat = date('M', $date);
            array_push($monthLetterArray, $monthLetterFormat);
            $monthDetail = new \DateTime($monthArray[$dateCount]);
            $monthDetail->modify('last day of this month');
            $monthlyScorecardKpiWeightAverageValueTotal = 0;

            for($kpiCount=0;$kpiCount<count($scorecardKpiList);$kpiCount++)
            {
                $scorecardElementColorArray = array();
                $scorecardKpiId = $scorecardKpiList[0]['id'];
                $scorecardAllKpiId = $scorecardKpiList[$kpiCount]['id'];
                $scorecardKpiWeight = $scorecardKpiList[$kpiCount]['weightage'];
                $scorecardKpiName = $scorecardKpiList[$kpiCount]['kpiName'];
                $kpiSumValue=0;

                $scorecardElementArray = $em->createQueryBuilder()
                    ->select('c.id, c.elementName,  c.weightage, sum(a.value) as value')
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
                if($dateCount==0)
                {
                    $scorecardElementList = $em->createQueryBuilder()
                        ->select('c.id, c.elementName,  c.weightage')
                        ->from('InitialShippingBundle:ElementDetails', 'c')
                        ->where('c.kpiDetailsId = :kpiId' )
                        ->setParameter('kpiId', $scorecardAllKpiId)
                        ->orderBy('c.id')
                        ->getQuery()
                        ->getResult();
                    array_push($overallElementListArray,$scorecardElementList);
                }

                if(count($scorecardElementArray)>0)
                {
                    for($elementCount=0;$elementCount<count($scorecardElementArray);$elementCount++)
                    {
                        $scorecardElementId = $scorecardElementArray[$elementCount]['id'];
                        $scorecardElementName = $scorecardElementArray[$elementCount]['elementName'];
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
                        array_push($scorecardElementColorArray,$elementResultColor);
                        $elementValueWithWeight = $elementColorValue * (((int)$scorecardElementWeight) / 100);
                        $kpiSumValue+=$elementValueWithWeight;
                    }
                }
                else
                {
                    $kpiSumValue="";
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
                array_push($monthlyScorecardElementColorArray,$scorecardElementColorArray);
            }
            array_push($monthlyScorecardKpiColorArray,$scorecardKpiColorArray);
            array_push($monthlyKpiAverageValueTotal,$monthlyScorecardKpiWeightAverageValueTotal);
            array_push($overallMonthlyElementColorArray,$monthlyScorecardElementColorArray);
        }

        $series = array
        (
            array("name" => "Management Performance",'showInLegend'=> false, 'color' => 'blue', "data" => $monthlyKpiAverageValueTotal),

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
            'changeChartData' => $monthlyKpiAverageValueTotal
        ));
        return $response;
    }
}
