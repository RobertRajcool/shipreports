<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Form\AddExcelFileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\Chart;
use Initial\ShippingBundle\Form\ChartType;
use Ob\HighchartsBundle\Highcharts\Highchart;

/**
 * Chart controller.
 *
 * @Route("/chart")
 */
class ChartController extends Controller
{
    /**
     * Lists all Chart entities.
     *
     * @Route("/", name="chart_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $charts = $em->getRepository('InitialShippingBundle:Chart')->findAll();

        return $this->render('InitialShippingBundle:chart:index.html.twig', array(
            'charts' => $charts,
        ));
    }

    private function createCreateForm(Chart $chart)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $form = $this->createForm(new ChartType($id), $chart, array(
            'action' => $this->generateUrl('createchart'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create '));
        $form->add('add', 'submit', array('label' => 'Reading.....'));

        return $form;
    }
    /**
     * Creates a new Chart entity.
     *
     * @Route("/new", name="chart_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $chart = new Chart();

        $form = $this->createCreateForm($chart);
        /*
        $form = $this->createForm(new ChartType($id), $chart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($chart);
            $em->flush();

            return $this->redirectToRoute('chart_show', array('id' => $chart->getId()));
        }
        */
        return $this->render('InitialShippingBundle:chart:new.html.twig', array(
            'chart' => $chart,
            'form' => $form->createView(),
        ));
    }
    /**
     * Adding Kpi Values.
     *
     * @Route("/createchart", name="createchart")
     * @Method("Post")
     */
    public function chartgenerateAction(Request $request)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $params = $request->request->get('chart');
        $kpiname = $params['kpiname'];
        $elementid=$params['elementDetailsId'];
        $fromdate = $params['fromdate'];
        $todate = $params['todate'];
        $em=$this->getDoctrine()->getManager();
        $findkpiname=$em -> createQueryBuilder()
            ->select('b.kpiName')
            ->from('InitialShippingBundle:KpiDetails','b')
            ->where('b.id = :userId')
            ->setParameter('userId',$kpiname)
            ->getQuery()
            ->getSingleScalarResult();
        $dbshiparrayquery=$em -> createQueryBuilder()
            ->select('a.shipName','a.id')
            ->from('InitialShippingBundle:ShipDetails','a')
            ->leftjoin('InitialShippingBundle:CompanyDetails','b','WITH','b.id = a.companyDetailsId')
            ->leftjoin('InitialShippingBundle:CompanyUsers','c','WITH','b.id = c.companyName')
            ->leftjoin('InitialShippingBundle:User','d','WITH','d.username = b.adminName or d.username = c.userName')
            ->where('d.id = :userId')
            ->setParameter('userId',$id)
            ->getQuery();

        $charobj=new Chart();

        $frommonthtostring=$fromdate['year'].'-'.$fromdate['month'].'-'.$fromdate['day'];
        $tomonthtostring=$todate['year'].'-'.$todate['month'].'-'.$todate['day'];
        $newcategories=$charobj->get_months($frommonthtostring,$tomonthtostring);



        $ob = new Highchart();

        $dbshipsname=$dbshiparrayquery->getResult();
        //loop for assign name for series//

        for($kj=0;$kj<count($dbshipsname);$kj++)
        {
            $newseries[$kj]['name']=$dbshipsname[$kj]['shipName'];

            $getvalue=$em -> createQueryBuilder()
                ->select('a.value')
                ->from('InitialShippingBundle:ReadingKpiValues','a')
                ->where('a.shipDetailsId = :userId')
                ->andwhere('a.elementDetailsId = :elementid')
                ->setParameter('userId',$dbshipsname[$kj]['id'])
                ->setParameter('elementid',$elementid)
                ->getQuery()
                ->getResult();
            for($l=0;$l<count($getvalue);$l++)
            {
                if(empty($getvalue[$l]['value']) && is_null($getvalue[$l]['value']))
                {
                    $newseries[$kj]['data'][$l]=0;
                }
                if(!(empty($getvalue[$l]['value']) && is_null($getvalue[$l]['value'])))
                {
                    $newseries[$kj]['data'][$l]=(float)$getvalue[$l]['value'];
                }

            }



        }

      //Adding data to javascript chart function starts Here.. //

        $ob->chart->renderTo('linechart');
        $ob->title->text('Star Systems Reporting Tool ',array('style'=>array('color' => 'red')));
        $ob->subtitle->text($findkpiname);
        $ob->subtitle->style(array('color'=>'#0000f0','fontWeight'=>'bold'));
        $ob->xAxis->categories($newcategories);
        //$ob->yAxis->title(array('text'  => $kpiname),array('style' => array('color' => '#221DBB')));
        $ob->series($newseries);
        $ob->plotOptions->series(array('allowPointSelect'=>true,'dataLabels'=>array('enabled'=>true)));
        //Adding data to javascript chart function  Ends Here..//


        return $this->render('InitialShippingBundle:HighChart:hightchart.html.twig', array(
            'chart' => $ob
        ));

    }

    /**
     * Finds and displays a Chart entity.
     *
     * @Route("/{id}", name="chart_show")
     * @Method("GET")
     */
    public function showAction(Chart $chart)
    {
        $deleteForm = $this->createDeleteForm($chart);

        return $this->render('InitialShippingBundle:chart:show.html.twig', array(
            'chart' => $chart,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Chart entity.
     *
     * @Route("/{id}/edit", name="chart_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Chart $chart)
    {
        $deleteForm = $this->createDeleteForm($chart);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\ChartType', $chart);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($chart);
            $em->flush();

            return $this->redirectToRoute('chart_edit', array('id' => $chart->getId()));
        }

        return $this->render('InitialShippingBundle:chart:edit.html.twig', array(
            'chart' => $chart,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Chart entity.
     *
     * @Route("/{id}", name="chart_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Chart $chart)
    {
        $form = $this->createDeleteForm($chart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($chart);
            $em->flush();
        }

        return $this->redirectToRoute('chart_index');
    }

    /**
     * Creates a form to delete a Chart entity.
     *
     * @param Chart $chart The Chart entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Chart $chart)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('chart_delete', array('id' => $chart->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
