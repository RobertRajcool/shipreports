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
    public function reportGenerateAction(Request $request, $pdfMode="")
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
        $overallMonthlyKpiSumValue = array();
        for ($dateCount=0; $dateCount<count($monthArray);$dateCount++)
        {
            $monthlyKpiSumValue = array();
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
                $scorecardAllKpiId = $scorecardKpiList[$kpiCount]['id'];
                $scorecardKpiWeight = $scorecardKpiList[$kpiCount]['weightage'];

                $scorecardElementArray = $em->createQueryBuilder()
                    ->select('c.id, c.elementName,  c.weightage')
                    ->from('InitialShippingBundle:ElementDetails', 'c')
                    ->where('c.kpiDetailsId = :kpiId' )
                    ->setParameter('kpiId', $scorecardAllKpiId)
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
                        $elementResultColor = "";
                        $elementColorValue=0;
                        $scorecardElementResult = $em->createQueryBuilder()
                            ->select('b.elementcolor')
                            ->from('InitialShippingBundle:Scorecard_LookupData', 'b')
                            ->where('b.kpiDetailsId = :kpiId and b.elementDetailsId = :elementId and b.monthdetail = :monthDetail')
                            ->setParameter('kpiId', $scorecardAllKpiId)
                            ->setParameter('elementId', $scorecardElementId)
                            ->setParameter('monthDetail', $monthDetail )
                            ->getQuery()
                            ->getResult();
                        if(count($scorecardElementResult)!=0)
                        {
                            $elementResultColor = $scorecardElementResult[0]['elementcolor'];
                        }
                        array_push($scorecardElementColorArray,$elementResultColor);
                    }
                }
                $kpiResult = $em->createQueryBuilder()
                    ->select('b.kpiColor, b.individualKpiAverageScore')
                    ->from('InitialShippingBundle:Scorecard_LookupData', 'b')
                    ->where('b.kpiDetailsId = :kpiId and b.monthdetail = :monthDetail')
                    ->setParameter('kpiId', $scorecardAllKpiId)
                    ->setParameter('monthDetail', $monthDetail )
                    ->getQuery()
                    ->getResult();
                if(count($kpiResult)!=0)
                {
                    array_push($scorecardKpiColorArray,$kpiResult[0]['kpiColor']);
                    $monthlyScorecardKpiWeightAverageValueTotal+=($kpiResult[0]['individualKpiAverageScore']*$scorecardKpiWeight)/100;
                    array_push($monthlyKpiSumValue,$kpiResult[0]['individualKpiAverageScore']);
                }
                else
                {
                    array_push($scorecardKpiColorArray,"");
                    $monthlyScorecardKpiWeightAverageValueTotal+=0;
                    array_push($monthlyKpiSumValue,0);
                }
                array_push($monthlyScorecardElementColorArray,$scorecardElementColorArray);
            }
            array_push($monthlyScorecardKpiColorArray,$scorecardKpiColorArray);
            array_push($monthlyKpiAverageValueTotal,$monthlyScorecardKpiWeightAverageValueTotal);
            array_push($overallMonthlyElementColorArray,$monthlyScorecardElementColorArray);
            array_push($overallMonthlyKpiSumValue,$monthlyKpiSumValue);
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
        if($pdfMode==1)
        {
            return array(
                'yearKpiColorArray' => $monthlyScorecardKpiColorArray,
                'kpiAvgScore' => $monthlyKpiAverageValueTotal,
                'monthName' => $monthLetterArray,
                'kpiNameList' => $scorecardKpiList,
                'elementNameList' => $overallElementListArray,
                'elementColorArray' => $overallMonthlyElementColorArray,
                'changeChartData' => $monthlyKpiAverageValueTotal,
                'elementLevelChartData' => $overallMonthlyKpiSumValue
            );
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
            'elementLevelChartData' => $overallMonthlyKpiSumValue
        ));
        return $response;
    }

    /**
     * ScorecardReport pdfReport.
     *
     * @Route("/pdfReport", name="scorecard_report_pdfReport")
     */
    public function pdfReportAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $todayTime = date("H:i:s");
        $todayDate = date("Y-m-d");

        $returnObject = $this->reportGenerateAction( $request,1);

        $pdfObject = $this->container->get('tfox.mpdfport')->getMPdf();
        $pdfObject->defaultheaderline = 0;
        $pdfObject->defaultheaderfontstyle = 'B';
        $waterMarkImage= $this->container->getParameter('kernel.root_dir').'/../web/images/pioneer_logo.png';
        $pdfObject ->SetWatermarkImage($waterMarkImage);
        $pdfObject ->showWatermarkImage = true;

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
                array('name'=>'Series','showInLegend'=>false,'color'=>'#103a71','data'=>$returnObject['changeChartData'])
            ),
            'subtitle'=>array('style'=>array('color'=>'#0000f0','fontWeight'=>'bold')),
            'title'=>array('text'=>'Graph Title'),
            'xAxis'=>array('categories'=>$returnObject['monthName'],'labels'=>array('style'=>array('color'=>'#0000F0'))),
            'yAxis'=>array('max'=>3,'min'=>0)
        );
        $jsonFileData=json_encode($graphObject);
        $jsonFilePath= $this->container->getParameter('kernel.root_dir').'/../web/phantomjs/scorecard/graphJsonFile/graphData'.$todayDate.$todayTime.'.json';
        file_put_contents($jsonFilePath, $jsonFileData);
        $HighChartLocation = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js ';
        $inFile = $this->container->getParameter('kernel.root_dir').'/../web/phantomjs/scorecard/graphJsonFile/graphData'.$todayDate.$todayTime.'.json ';
        $outFile=$this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/scorecard/graphImageFile/graphImage'.$todayDate.$todayTime.'.png ';
        $imageGeneration = 'phantomjs '.$HighChartLocation.'-infile '.$inFile.'-outfile '.$outFile.' -scale 2.5 -width 1024';
        $fileHandle = popen($imageGeneration, 'r');
        $result = fread($fileHandle, 2096);

        $customerListDesign= $this->renderView('InitialShippingBundle:ScorecardReport:finalPdfTemplate.html.twig',
            array(
                'yearKpiColorArray' => $returnObject['yearKpiColorArray'],
                'kpiAvgScore' => $returnObject['kpiAvgScore'],
                'monthName' => $returnObject['monthName'],
                'kpiNameList' => $returnObject['kpiNameList'],
                'imageSource' => 'graphImage'.$todayDate.$todayTime.'.png',
                'headerTitle' => 'Pioneer Scorecard Report'
            ));

        $pdfObject->AddPage('', 4, '', 'on');
        $pdfObject->SetFooter('|Date/Time: {DATE l jS F Y h:i}| Page No: {PAGENO}');
        $pdfObject->WriteHTML($customerListDesign);


        for($kpiCount=0;$kpiCount<count($returnObject['kpiNameList']);$kpiCount++)
        {
            $kpiDataArray = array();
            $elementColorArray = array();
            $elementNameList = array();
            $kpiColorArray = array();
            array_push($elementNameList,$returnObject['elementNameList'][$kpiCount]);
            for($monthCount=0;$monthCount<count($returnObject['monthName']);$monthCount++)
            {
                array_push($kpiDataArray,$returnObject['elementLevelChartData'][$monthCount][$kpiCount]);
                array_push($elementColorArray,$returnObject['elementColorArray'][$monthCount][$kpiCount]);
                array_push($kpiColorArray,$returnObject['yearKpiColorArray'][$monthCount][$kpiCount]);
            }
            $kpiGraphObject = array(
                'chart'=>array('renderTo'=>'areaId','type'=>"line"),
                'exporting'=>array('enabled'=>false),
                'plotOptions'=>array('series'=>array(
                    "allowPointSelect"=>true,
                    "dataLabels"=>array(
                        "enabled"=>true
                    )
                )),
                'series'=>array(
                    array('name'=>'Series','showInLegend'=>false,'color'=>'#103a71','data'=>$kpiDataArray)
                ),
                'subtitle'=>array('style'=>array('color'=>'#0000f0','fontWeight'=>'bold')),
                'title'=>array('text'=>$returnObject['kpiNameList'][$kpiCount]['kpiName']),
                'xAxis'=>array('categories'=>$returnObject['monthName'],'labels'=>array('style'=>array('color'=>'#0000F0'))),
                'yAxis'=>array('max'=>3,'min'=>0)
            );

            $kpiJsonFileData=json_encode($kpiGraphObject);
            $kpiJsonFilePath= $this->container->getParameter('kernel.root_dir').'/../web/phantomjs/scorecard/graphJsonFile/KPI-'.$returnObject['kpiNameList'][$kpiCount]['id'].$todayDate.$todayTime.'.json';
            file_put_contents($kpiJsonFilePath, $kpiJsonFileData);
            $kpiHighChartLocation = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js ';
            $kpiInFile = $this->container->getParameter('kernel.root_dir').'/../web/phantomjs/scorecard/graphJsonFile/KPI-'.$returnObject['kpiNameList'][$kpiCount]['id'].$todayDate.$todayTime.'.json ';
            $kpiOutFile=$this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/scorecard/graphImageFile/KPI-'.$returnObject['kpiNameList'][$kpiCount]['id'].$todayDate.$todayTime.'.png ';
            $kpiImageGeneration = 'phantomjs '.$kpiHighChartLocation.'-infile '.$kpiInFile.'-outfile '.$kpiOutFile.' -scale 2.5 -width 1024';
            $kpiFileHandle = popen($kpiImageGeneration, 'r');
            $kpiResult = fread($kpiFileHandle, 2096);

            $customerListDesign= $this->renderView('InitialShippingBundle:ScorecardReport:kpiLevelPdfTemplate.html.twig',
                array(
                    'yearKpiColorArray' => $kpiColorArray,
                    'monthName' => $returnObject['monthName'],
                    'kpiNameList' => $returnObject['kpiNameList'][$kpiCount]['kpiName'],
                    'imageSource' => 'KPI-'.$returnObject['kpiNameList'][$kpiCount]['id'].$todayDate.$todayTime.'.png',
                    'headerTitle' => $returnObject['kpiNameList'][$kpiCount]['kpiName'],
                    'elementNameList' => $elementNameList,
                    'elementColorArray' =>$elementColorArray
                ));

            $pdfObject->AddPage('', 4, '', 'on');
            $pdfObject->SetFooter('|Date/Time: {DATE l jS F Y h:i}| Page No: {PAGENO}');
            $pdfObject->WriteHTML($customerListDesign);
        }

        $response = new Response();
        $content = $pdfObject->Output('', 'S');
        $response->setContent($content);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;

       /* $em = $this->getDoctrine()->getManager();
        $returnObject = $this->reportGenerateAction( $request,1);
        $series = array
        (
            array("name" => "Management Performance", 'color' => '#103a71', "data" => $returnObject['changeChartData']),
        );
        $ob = new Highchart();
        $ob->chart->renderTo('area');
        $ob->chart->type('line');
        $ob->title->text(' ', array('style' => array('color' => 'red')));
        $ob->subtitle->style(array('color' => '#0000f0', 'fontWeight' => 'bold'));
        $ob->xAxis->categories($returnObject['monthName']);
        $ob->xAxis->labels(array('style' => array('color' => '#0000F0')));
        $ob->yAxis->max(3);
        $ob->series($series);
        $ob->plotOptions->series(array('allowPointSelect' => true, 'dataLabels' => array('enabled' => true)));
        $ob->exporting->enabled(false);

        return $this->render('InitialShippingBundle:ScorecardReport:reportPdfTemplate.html.twig',
            array(
                'chart'=>$ob,
                'yearKpiColorArray' => $returnObject['yearKpiColorArray'],
                'kpiAvgScore' => $returnObject['kpiAvgScore'],
                'monthName' => $returnObject['monthName'],
                'kpiNameList' => $returnObject['kpiNameList'],
                'elementNameList' => $returnObject['elementNameList'],
                'elementColorArray' => $returnObject['elementColorArray'],
                'changeChartData' => $returnObject['changeChartData'],
                'elementLevelChartData' => $returnObject['elementLevelChartData']
            ));*/

        /*$graphObject = array(
            'chart'=>array('renderTo'=>'areaId','type'=>"line"),
            'exporting'=>array('enabled'=>false),
            'plotOptions'=>array('series'=>array(
            "allowPointSelect"=>true,
            "dataLabels"=>array(
                "enabled"=>true
                )
            )),
            'series'=>array(
                    array('name'=>'Series','showInLegend'=>false,'color'=>'blue','data'=>$returnObject['changeChartData'])
                    ),
            'subtitle'=>array('style'=>array('color'=>'#0000f0','fontWeight'=>'bold')),
            'title'=>array('text'=>'Graph Title'),
            'xAxis'=>array('categories'=>$returnObject['monthName'],'labels'=>array('style'=>array('color'=>'#0000F0'))),
        );
        $jsonFileData=json_encode($graphObject);
        $jsonFilePath= $this->container->getParameter('kernel.root_dir').'/../web/phantomjs/scorecard/graphJsonFile/graphData_2.json';
        file_put_contents($jsonFilePath, $jsonFileData);
        $HighChartLocation = $this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/highcharts-convert.js ';
        $inFile = $this->container->getParameter('kernel.root_dir').'/../web/phantomjs/scorecard/graphJsonFile/graphData_1.json ';
        $outFile=$this->container->getParameter('kernel.root_dir') . '/../web/phantomjs/scorecard/graphImageFile/shipImage.png ';
        $imageGeneration = 'phantomjs '.$HighChartLocation.'-infile '.$inFile.'-outfile '.$outFile.' -scale 2.5 -width 1065';
        $fileHandle = popen($imageGeneration, 'r');
        $result = fread($fileHandle, 2096);

        $client = new HighchartController();
        $client->setContainer($this->container);
        $printPdf = $this->createPdf($customerListDesign, 'Scorecard Report');

        $response = new Response();
        $response->setContent($printPdf);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;*/
    }

}


