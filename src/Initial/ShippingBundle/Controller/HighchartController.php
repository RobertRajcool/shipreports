<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class HighchartController extends Controller
{
    public function indexAction($name)
    {
        return new Response('<html><body>Hello '.$name.'!</body></html>');
    }
    public function showchartAction()
    {
        $name="Lawrance Robert Raj";
        $this->forward('app.hello_controller:indexAction', array('name' => $name));
    }
    public  function  chartAction()
    {

        $series = array(
            array("name" => "Trichy", 'color' => 'red',   "data" => array(7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6)),
            array("name" =>"Thiruvannamalai",'color' => 'blue', "data" =>array(-0.2, 0.8, 5.7, 11.3, 17.0, 0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5)),
            array("name" =>"Vellore",'color' => 'green', "data" =>array(-0.9, 0.6, 3.5, 8.4, 13.5, 17.0, 18.6, 17.9, 14.3, 9.0, 3.9, 1.0)),
            array("name" =>"Chennai",'color' => 'yellow', "data" =>array(3.9, 4.2, 5.7, 8.5, 11.9, 15.2, 17.0, 16.6, 14.2, 10.3, 6.6, 4.8))
        );
        $categories = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
        $imgedir= $this->container->getParameter('kernel.root_dir').'/../web/uploads/brochures/';
        $ob = new Highchart();
        $ob->chart->renderTo('linechart');  // The #id of the div where to render the chart
        $ob->exporting->url($imgedir);
        $ob->exporting->enabled(false);
        $ob->title->text('Star Systems Reporting Tool ',array('style'=>array('color' => 'red')));
        $ob->subtitle->text('Tamil Nadu Whether Report');
        $ob->subtitle->style(array('color'=>'#0000f0','fontWeight'=>'bold'));
        //$ob->subtitle->text(array('text'  => "Tamil Nadu Whether Report"));
        //$ob->xAxis->title(array('text'  => "Temperature (°C)"));
        $ob->xAxis->categories($categories);
        $ob->series($series);




        return $this->render('InitialShippingBundle:HighChart:hightchart.html.twig', array(
            'chart' => $ob
        ));
    }
    public function addchartAction(Request $request)

    {
        $batikdir= $this->container->getParameter('kernel.root_dir').'/../web/uploads/brochures/';

        //define ('BATIK_PATH', 'batik-rasterizer.jar');




        $svgname =(string)$request->request->get('svgid');
        $imgtype=$request->request->get('typeid');
        $filewidth=$request->request->get('filewidth');
         $fileanys=$request->request->get('fileanys');
        $filename="";
        if (!$filename or !preg_match('/^[A-Za-z0-9\-_ ]+$/', $filename)) {
            $filename = 'chart_'.date('Y-m-d H-i-s');
        }
        /*
        if (get_magic_quotes_gpc()) {
            $svg = stripslashes($svgname);
        }
// check for malicious attack in SVG

        if(strpos($svg,"<!ENTITY") !== false || strpos($svg,"<!DOCTYPE") !== false)
        {
            exit("Execution is stopped, the posted SVG could contain code for a malicious attack");
        }*/

        $tempName = md5(rand());
// allow no other than predefined types
        if ($imgtype == 'image/png') {
            $typeString = '-m image/png';
            $ext = 'png';

        } elseif ($imgtype == 'image/jpeg') {
            $typeString = '-m image/jpeg';
            $ext = 'jpg';
        } elseif ($imgtype == 'application/pdf') {
            $typeString = '-m application/pdf';
            $ext = 'pdf';
        } elseif ($imgtype == 'image/svg+xml') {
            $ext = 'svg';
        } else { // prevent fallthrough from global variables
            $ext = 'txt';
        }
        $outfile = "$batikdir..$ext";
        $fileext=".svg";
        //$myfile = fopen($batikdir.$filename.$fileext, "w");

        if (isset($typeString)) {

            // size
            $width = '';
            if ($filewidth) {
                $width = (int)$filewidth;
                if ($width) $width = "-w $width";
            }
            // generate the temporary file
           // file_put_contents("test.txt", "Hello World. Testing!");
            if (!file_put_contents($batikdir . $filename . $fileext, $svgname)) {
                die("Couldn't create temporary file. Check that the directory permissions for
			the /temp directory are set to 777.");
            }


        }

        return new JsonResponse($filename . $fileext);

    }

    public function sendbackendAction(Request $request)
    {
        // Code to get that user triggered a bunch of notifications to an array of user ids.
        $userIds = array(1,2,3,4);
    $message = 'some message from the frontend user';

    //Get Gearman and tell it to run in the background a 'job'
    $this->get('gearman')->doBackgroundJob('InitialShippingBundleserviceReadExcelWorker~readexcelsheet',
        json_encode(array('user_ids'=>$userIds,'message'=>$message)));

    return new Response('Your notifications are being sent now!');
    }

    public function downloadPdfAction()
    {

        return $this->render('InitialShippingBundle:CompanyUsers:viewpdf.html.twig', array(
            'name' => 'Lawrance Robert Raj.C',
            'pdf_output' => 'custom_pdf_output_filename.pdf'
        ));
    }
}
