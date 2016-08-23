<?php

namespace Initial\ShippingBundle\Controller;

use Ob\HighchartsBundle\Highcharts\Highchart;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Initial\ShippingBundle\Form\ScorecardDataImportType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
/**
 * PieChartDashboardController.
 *
 * @Route("/piechart")
 */
class PieChartDashboardController extends Controller
{
    /**
     * Lists all PieChartDashboard Elements.
     *
     * @Route("/{serialized}/listall", name="vessel_piechart")
     */
    public function indexAction(Request $request,$serialized,$mode = '')
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null) {
            $userId=$user->getId();
            $shipids = explode('_', $serialized);
            $dataofmonth=$shipids[0];
            $stringdate='01-'.$dataofmonth;
            $currentMonthObject = new \DateTime($stringdate);
            $currentMonthObject->modify('last day of this month');
            $overallShipDetailArray=array();
            $colorstatusvarable="";
            for ($shipCount = 1; $shipCount < count($shipids); $shipCount++) {
                $rankingKpiValueCountArray = array();
                $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id' => $shipids[$shipCount]));
                $rankingShipName = $newshipid->getshipName();
                $manufacturingYear = $newshipid->getmanufacturingYear();;
                $rankingShipId = $newshipid->getId();
                $rankingKpiList = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id', 'b.weightage')
                    ->from('InitialShippingBundle:RankingKpiDetails', 'b')
                    ->where('b.shipDetailsId = :shipid')
                    ->setParameter('shipid', $rankingShipId)
                    ->getQuery()
                    ->getResult();

                for ($rankingKpiCount = 0; $rankingKpiCount < count($rankingKpiList); $rankingKpiCount++) {
                    $rankingElementValueTotal = 0;
                    $rankingKpiId = $rankingKpiList[$rankingKpiCount]['id'];
                    $rankingKpiWeight = $rankingKpiList[$rankingKpiCount]['weightage'];
                    $rankingKpiName = $rankingKpiList[$rankingKpiCount]['kpiName'];
                    //array_push($rankingKpiWeightarray, $rankingKpiWeight);
                    if ($rankingKpiName=='Vessel age')
                    {
                        if ($manufacturingYear == "")
                        {
                            $yearcount = 0;
                        }
                        else
                        {

                            $man_datestring = $manufacturingYear . '-01-' . '01';
                            $temp_man_year = new \DateTime($man_datestring);
                            $temp_man_year->modify('last day of this month');
                            $Vessage_count= $temp_man_year->diff($currentMonthObject)->y;
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
                            ->setParameter('datamonth', $currentMonthObject)
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
                                    ->setParameter('monthDetail', $currentMonthObject)
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
                                ->setParameter('datamonth', $currentMonthObject)
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
                                    ->setParameter('monthDetail', $currentMonthObject)
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

                $yearChange = $currentMonthObject->format('Y');
                if(array_sum($rankingKpiValueCountArray)>=80)
                {
                    $colorstatusvarable="#1ea50b";

                }
                else if(array_sum($rankingKpiValueCountArray)>=70)
                {
                    $colorstatusvarable="#feba06";
                }
                else if(array_sum($rankingKpiValueCountArray)<70)
                {
                    $colorstatusvarable="#b30000";
                }
                $temp_url = '/dashboard/' . $rankingShipId . '/' . $yearChange . '/listallkpiforship_ranking';
                $temparrayvaluedatas=array('name'=>$rankingShipName,'y'=>array_sum($rankingKpiValueCountArray),'url'=>$temp_url,'color'=>$colorstatusvarable);
                array_push($overallShipDetailArray,$temparrayvaluedatas);

            }
            if ($mode == 'getnextmonthchart') {
                return array(
                    'changechartdata' => $overallShipDetailArray,
                );
            }
            $vessetitlearray=array( 'fontSize'=> '10px', 'fontFamily'=> 'Avenir LT Std Light' );
            $vessel_Piechart = new Highchart();
            $vessel_Piechart->chart->renderTo('area');
            $vessel_Piechart->chart->type('pie');
            $vessel_Piechart->chart->options3d(array('enabled'=> true,'alpha'=> 45));
            $vessel_Piechart->credits->enabled(false);
            $vessel_Piechart->title->text('');
            $vessel_Piechart->title->floating(true);
            $vessel_Piechart->title->align('center');
            $vessel_Piechart->title->verticalAlign('middle');
            $vessel_Piechart->title->style($vessetitlearray);
            $vessel_Piechart->plotOptions->pie(array(
                'innerSize'=> 100,
                'depth'=> 45
            ));
            $vessel_Piechart->plotOptions->series(array('borderWidth' => 0, 'dataLabels' => array('enabled' => true),
                'point' => array('events' => array('click' => new \Zend\Json\Expr('function () { location.href = this.options.url; }')))));
            $vessel_Piechart->series(array(array('name' => 'Vessel', 'data' => $overallShipDetailArray)));
            $vessel_Piechart->exporting->enabled(false);

            return $this->render('InitialShippingBundle:DashBoardAlerts:vesselalert.html.twig',array('chart'=>$vessel_Piechart,'currentmonthdata'=>$dataofmonth,'shipids'=>$serialized));
        }
        else
        {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }
    /**
     * Ajax Call For change of monthdata of Rankinng Chart
     *
     * @Route("/{dataofmonth}/{shipids}/{requestmonth}/monthchangeofrankingkpi", name="monthchangevessel_alters")
     */
    public function monthchangeofrankingkpiAction(Request $request, $dataofmonth,$shipids,$requestmonth)
    {
        $stringdate='01-'.$dataofmonth;
        $currentMonthObject = new \DateTime($stringdate);
        $currentMonthObject->modify('last day of this month');
        if($requestmonth=='proviousmonth')
        {
            $currentMonthObject->modify('first day of previous month');
        }
        if($requestmonth=='nextmonth')
        {
            $currentMonthObject->modify('first day of next month');
        }
        $newdataofmonth = $currentMonthObject->format('M-Y');
        $array_shipids=explode('_',$shipids);
        array_splice($array_shipids,0,1,$newdataofmonth);
        $shipids=implode('_',$array_shipids);
        $chartobject = $this->indexAction($request, $shipids, 'getnextmonthchart');
        $response = new JsonResponse();

        $response->setData(array('changechartdata' => $chartobject['changechartdata'],'dataofmonth'=>$newdataofmonth));
        return $response;

    }
    /**
     * Lists all PieChartDashboard kpi elements.
     *
     * @Route("/{serialized}/listall_kpipiechart", name="kpi_piechart")
     */
    public function listallkpiAction(Request $request,$serialized,$mode = '')
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user != null) {
            $userId = $user->getId();
            $kpiids = explode('_', $serialized);
            $dataofmonth = $kpiids[0];
            $stringdate = '01-' . $dataofmonth;
            $currentMonthObject = new \DateTime($stringdate);
            $currentMonthObject->modify('last day of this month');
            $monthlyScorecardKpiWeightAverageValueTotal = 0;
            $monthlyScorecardKpiColorArray = array();
            $monthlyKpiAverageValueTotal = array();
            $scorecardKpiColorArray = array();

            for ($kpiCount = 1; $kpiCount < count($kpiids); $kpiCount++) {
                $newkpi_id = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $kpiids[$kpiCount]));
                $scorecardAllKpiId = $newkpi_id->getId();
                $scorecardKpiWeight =$newkpi_id->getWeightage();
                $scorecardKpiName=$newkpi_id->getKpiName();
                $kpiResult = $em->createQueryBuilder()
                    ->select('b.kpiColor, b.individualKpiAverageScore')
                    ->from('InitialShippingBundle:Scorecard_LookupData', 'b')
                    ->where('b.kpiDetailsId = :kpiId and b.monthdetail = :monthDetail')
                    ->setParameter('kpiId', $scorecardAllKpiId)
                    ->setParameter('monthDetail', $currentMonthObject)
                    ->getQuery()
                    ->getResult();
                if (count($kpiResult) != 0) {
                    // $lastMonthKpiPieChart=
                    $kpi_Color_Result=$kpiResult[0]['kpiColor'];
                    array_push($scorecardKpiColorArray,$kpi_Color_Result );

                        if($kpi_Color_Result=='Green')
                        {
                           // array_push($greenarea_kpiids,$scorecardAllKpiId);

                        }
                        else if($kpi_Color_Result=='Red')
                        {
                           // array_push($redarea_kpiids,$scorecardAllKpiId);
                        }
                        else if($kpi_Color_Result=='Yellow')
                        {
                           // array_push($yellowarea_kpiids,$scorecardAllKpiId);
                        }

                    $monthlyScorecardKpiWeightAverageValueTotal += ($kpiResult[0]['individualKpiAverageScore'] * $scorecardKpiWeight) / 100;
                }
                else
                {
                    array_push($scorecardKpiColorArray, "");
                    $monthlyScorecardKpiWeightAverageValueTotal += 0;
                }
                array_push($monthlyScorecardKpiColorArray, $scorecardKpiColorArray);
                array_push($monthlyKpiAverageValueTotal, $monthlyScorecardKpiWeightAverageValueTotal);
            }
            $vessetitlearray=array( 'fontSize'=> '10px', 'fontFamily'=> 'Avenir LT Std Light' );
            $vessel_Piechart = new Highchart();
            $vessel_Piechart->chart->renderTo('area');
            $vessel_Piechart->chart->type('pie');
            $vessel_Piechart->chart->options3d(array('enabled'=> true,'alpha'=> 45));
            $vessel_Piechart->credits->enabled(false);
            $vessel_Piechart->title->text('');
            $vessel_Piechart->title->floating(true);
            $vessel_Piechart->title->align('center');
            $vessel_Piechart->title->verticalAlign('middle');
            $vessel_Piechart->title->style($vessetitlearray);
            $vessel_Piechart->plotOptions->pie(array(
                'innerSize'=> 100,
                'depth'=> 45
            ));
            $vessel_Piechart->plotOptions->series(array('borderWidth' => 0, 'dataLabels' => array('enabled' => true),
                'point' => array('events' => array('click' => new \Zend\Json\Expr('function () { location.href = this.options.url; }')))));
            $vessel_Piechart->series(array(array('name' => 'Vessel', 'data' => $monthlyScorecardKpiWeightAverageValueTotal)));
            $vessel_Piechart->exporting->enabled(false);
            return $this->render('InitialShippingBundle:DataVerficationRanking:backup.html.twig');


        }
        else
        {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }
}
