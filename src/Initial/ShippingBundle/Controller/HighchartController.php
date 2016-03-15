<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Initial\ShippingBundle\Entity\SendCommand;

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
        $x=0;

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
            'chart' => $ob,'kpiid'=>$x
        ));
    }
    public  function  areachartAction()
    {
        $x=0;

        $series = array(
            array("name" => "Trichy", 'color' => 'red',   "data" => array(7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6)),
            array("name" =>"Thiruvannamalai",'color' => 'blue', "data" =>array(-0.2, 0.8, 5.7, 11.3, 17.0, 0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5)),
            array("name" =>"Vellore",'color' => 'green', "data" =>array(-0.9, 0.6, 3.5, 8.4, 13.5, 17.0, 18.6, 17.9, 14.3, 9.0, 3.9, 1.0)),
            array("name" =>"Chennai",'color' => 'yellow', "data" =>array(3.9, 4.2, 5.7, 8.5, 11.9, 15.2, 17.0, 16.6, 14.2, 10.3, 6.6, 4.8))
        );
        $categories = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
        $imgedir= $this->container->getParameter('kernel.root_dir').'/../web/uploads/brochures/';
        $ob = new Highchart();
        $ob->chart->renderTo('linechart');
        $ob->chart->type('area');
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
            'chart' => $ob,'kpiid'=>$x
        ));
    }

    public  function  piechartAction()
    {
        $x=0;

        $ob = new Highchart();
        $ob->chart->renderTo('linechart');
        $ob->title->text('Browser market shares at a specific website in 2010');
        $ob->plotOptions->pie(array(
            'allowPointSelect'  => true,
            'cursor'    => 'pointer',
            'dataLabels'    => array('enabled' => false),
            'showInLegend'  => true
        ));
        $data = array(
            array('Firefox', 45.0),
            array('IE', 26.8),
            array('Chrome', 12.8),
            array('Safari', 8.5),
            array('Opera', 6.2),
            array('Others', 0.7),
        );
        $ob->series(array(array('type' => 'pie','name' => 'Browser share', 'data' => $data)));



        return $this->render('InitialShippingBundle:HighChart:hightchart.html.twig', array(
            'chart' => $ob,'kpiid'=>$x
        ));
    }


    public function addchartAction(Request $request)

    {
        $batikdir= $this->container->getParameter('kernel.root_dir').'/../web/uploads/brochures/';
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
        /*
        $imagedir=$this->container->getParameter('kernel.root_dir').'/../web/uploads/brochures/'.$filename. $fileext;
        $image = new Imagick();
        $image->readImageBlob(file_get_contents($imagedir));
        $image->setImageFormat("png24");
        $image->resizeImage(1024, 768, imagick::FILTER_LANCZOS, 1);
        $image->writeImage('image.png');
        */

        return new JsonResponse($filename . $fileext);

    }

    public function addcommentAction(Request $request)
    {
        $em=$this->getDoctrine()->getManager();
        //get client Email Id
        $user = $this->getUser();
        $username = $user->getUsername();

        $userquery = $em->createQueryBuilder()
            ->select('a.emailId')
            ->from('InitialShippingBundle:CompanyDetails','a')
            ->where('a.adminName = :userId')
            ->setParameter('userId',$username)
            ->getQuery();
        $clientemailid=$userquery->getSingleScalarResult();

        //get Informaton From User
        $params = $request->request->get('send_command');
        $filename = $params['filename'];
        $pdffilename=explode('.',$filename);
        $kpiid=$params['kpiid'];
        $newkpiid = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id' => $kpiid));
        $comment = $params['comment'];
        $today = date("Y-m-d H:i:s");
        $imagedirect= $this->container->getParameter('kernel.root_dir').'/../web/uploads/brochures/'.$filename;
        $title="This is Pdf";
        $mpdf = $this->container->get('tfox.mpdfport')->getMPdf();
        $mpdf->defaultheaderline = 0;
        $mpdf->defaultheaderfontstyle = 'B';
        $mpdf->AddPage('', 4, '', 'on');
        $mpdf->SetHeader('Type: ' . $title . '|Date/Time: {DATE l jS F Y h:i}| Page No: {PAGENO}');
        $mpdf->WriteHTML('<h1>Graph Detail</h1>
                            <p><img src="'.$imagedirect.'" alt="Loader Image"/></p>
                            <h2>Comment</h2>
                            <p>'.$comment.'</p>
                            ');
        $uploaddir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/brochures/'.$pdffilename[0].'.pdf';
        $content = $mpdf->Output('', 'S');
        file_put_contents($uploaddir, $content);
        $useremaildid=$params['clientemail'];


        $sendcommand=new SendCommand();
        //assign file attachement for mail and Mailing Starts Here...u
       $mailer = $this->container->get('mailer');
        $message = \Swift_Message::newInstance()
            ->setFrom('lawrance@commusoft.co.uk')
            ->setTo($useremaildid)
            ->setSubject($today."  Month Graph!!!!")
            ->setBody($comment)
        ;
        $message->attach(\Swift_Attachment::fromPath($uploaddir)->setFilename($pdffilename[0].'.pdf'));
        $mailer->send($message);
        //Mailing Ends....
        //Insertion Starts Here...
        $sendcommand->setFilename($pdffilename[0].'.pdf');
        $sendcommand->setClientemail("lawrance@commusoft.co.uk");
        $datetime = new \DateTime();
        $sendcommand->setUseremialid($useremaildid);
        $sendcommand->setComment($comment);
        $sendcommand->setDatetime($datetime);
        $sendcommand->setKpiid($newkpiid);
        $em->persist($sendcommand);
        $em->flush();
        //return $this->redirectToRoute('showcomment');
        return $this->redirect($this->generateUrl('showcomment', array('page' => 1)));
    }
    public function showcommentAction($page)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $username = $user->getUsername();
        $clientemailid = $em->createQueryBuilder()
            ->select('a.emailId')
            ->from('InitialShippingBundle:CompanyDetails','a')
            ->where('a.adminName = :userId')
            ->setParameter('userId',$username)
            ->getQuery()
            ->getSingleScalarResult();
        $total_records = $em->createQueryBuilder()
            ->select('count(j.id)')
            ->from('InitialShippingBundle:SendCommand','j')
            ->Where('j.clientemail = :clientemail')
            ->setParameter('clientemail', $clientemailid)
            ->getQuery()
            ->getSingleScalarResult();

        $record_per_page = $this->container->getParameter('maxrecords_per_page');
        $last_page = ceil($total_records / $record_per_page);
        $previous_page = $page > 1 ? $page - 1 : 1;
        $next_page = $page < $last_page ? $page + 1 : $last_page;
        $userdetails=$em->getRepository('InitialShippingBundle:SendCommand')->findBy(array(), array('id' => 'DESC'), $record_per_page, ($page - 1) * $record_per_page);

        return $this->render('InitialShippingBundle:ExcelFileviews:showcomment.html.twig', array(
            'userdetails' => $userdetails,
            'last_page' => $last_page,
            'previous_page' => $previous_page,
            'current_page' => $page,
            'next_page' => $next_page,
            'total_jobs' => $total_records
        ));

    }
    public function downloadchartAction($filename,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $uploaddir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/brochures/'.$filename;
        $content = file_get_contents($uploaddir);

        $response = new Response();

        $response->headers->set('Content-Type', 'image/svg+xml');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$filename);

        $response->setContent($content);
        return $response;
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


    //For  Pdf



    public function createPdfAction(Request $request)
    {

        $pageName = $request->query->get('page');
        $screenName = $this->get('translator')->trans($pageName);
        $date = date('l jS F Y h:i', time());
        $route = $request->attributes->get('_route');

        $customerListDesign = $this->renderView('InitialShippingBundle:HighChart:createpdf.html.twig', array(
            'link' => 'http://oss.oetiker.ch/rrdtool/gallery/energy_graph.png',
            'screenName' => $screenName,
            'userName' => '',
            'date' => $date
        ));

        $printPdf = $this->createPdf($customerListDesign, $screenName);



        $response = new Response();
        $response->setContent($printPdf);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }



    public function createPdf($html, $title)
    {
        $mpdf = $this->container->get('tfox.mpdfport')->getMPdf();
        $mpdf->defaultheaderline = 0;
        $mpdf->defaultheaderfontstyle = 'B';
        $mpdf->AddPage('', 4, '', 'on');
        $mpdf->SetHeader('Type: ' . $title . '|Date/Time: {DATE l jS F Y h:i}| Page No: {PAGENO}');
        //$mpdf->SetTitle($title);
        $mpdf->WriteHTML($html);
       // $output= $mpdf->Output('filename.pdf','F');
        $uploaddir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/brochures/filename.pdf';

        $content = $mpdf->Output('', 'S');
        file_put_contents($uploaddir, $content);
        return $content;
    }




}
