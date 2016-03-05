<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\KpiDetails;
use Initial\ShippingBundle\Form\KpiDetailsType;

/**
 * KpiDetails controller.
 *
 * @Route("/kpidetails", name="kpidetails_index")
 */
class KpiDetailsController extends Controller
{
    /**
     * Lists all KpiDetails entities.
     *
     * @Route("/", name="kpidetails_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $kpiDetails = $em->getRepository('InitialShippingBundle:KpiDetails')->findAll();

        return $this->render('kpidetails/index.html.twig', array(
            'kpiDetails' => $kpiDetails,
        ));
    }


    /**
     * Lists all KpiDetails entities.
     *
     * @Route("{id}/select", name="kpidetails_select")
     * @Method("GET")
     */
    public function selectAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $kpiDetails = $em->getRepository('InitialShippingBundle:KpiDetails')->findByShipDetailsId($id);

        return $this->render('kpidetails/index.html.twig', array(
            'kpiDetails' => $kpiDetails,
        ));
    }


    /**
     * Lists all KpiDetails entities.
     *
     * @Route("/select", name="kpidetails_select1")
     * @Method("GET")
     */
    public function select1Action()
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $em = $this->getDoctrine()->getManager();

        if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $query = $em->createQueryBuilder()
                ->select('a')
                ->from('InitialShippingBundle:KpiDetails','a')
                ->leftjoin('InitialShippingBundle:ShipDetails','d', 'WITH', 'd.id = a.shipDetailsId')
                ->leftjoin('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = d.companyDetailsId')
                ->leftjoin('InitialShippingBundle:User','c','WITH','c.username = b.adminName')
                ->where('c.id = :userId')
                ->setParameter('userId',$userId)
                ->getQuery();
        }
        else
        {
            $query = $em->createQueryBuilder()
                ->select('a')
                ->from('InitialShippingBundle:KpiDetails','a')
                ->leftjoin('InitialShippingBundle:ShipDetails','c', 'WITH', 'c.id = a.shipDetailsId')
                ->leftjoin('InitialShippingBundle:User','b','WITH','b.companyid = c.companyDetailsId')
                ->where('b.id = :userId')
                ->setParameter('userId',$userId)
                ->getQuery();
        }

        $kpiDetails = $query->getResult();


        return $this->render('kpidetails/index.html.twig', array(
            'kpiDetails' => $kpiDetails,
        ));
    }


    /**
     * Creates a new KpiDetails entity.
     *
     * @Route("/new", name="kpidetails_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');

        $kpiDetail = new KpiDetails();
        $form = $this->createForm(new KpiDetailsType($id,$role), $kpiDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($kpiDetail);
            $em->flush();

            return $this->redirectToRoute('kpidetails_show', array('id' => $kpiDetail->getId()));
        }

        return $this->render('kpidetails/new.html.twig', array(
            'kpiDetail' => $kpiDetail,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new KpiDetails entity.
     *
     * @Route("/new1", name="kpidetails_new1")
     * @Method({"GET", "POST"})
     */
    public function new1Action(Request $request)
    {
        $params = $request->request->get('kpi_details');
        $kpiName = $params['kpiName'];
        $shipDetailsId = $params['shipDetailsId'];
        $val = count($shipDetailsId);
//print_r($j);die;
        $description = $params['description'];
        $activeDate = $params['activeDate'];
        $endDate = $params['endDate'];
        $cellName = $params['cellName'];
        $cellDetails = $params['cellDetails'];

        $monthtostring=$activeDate['year'].'-'.$activeDate['month'].'-'.$activeDate['day'];
        $new_date=new \DateTime($monthtostring);
        $monthtostring1=$endDate['year'].'-'.$endDate['month'].'-'.$endDate['day'];
        $new_date1=new \DateTime($monthtostring1);

        $em = $this->getDoctrine()->getManager();


        for($i=0;$i<$val;$i++)
        {
            $kpidetails = new KpiDetails();
            $kpidetails -> setShipDetailsId($this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id'=>$shipDetailsId[$i])));
            $kpidetails -> setKpiName($kpiName);
            $kpidetails -> setDescription($description);
            $kpidetails -> setActiveDate($new_date);
            $kpidetails -> setEndDate($new_date1);
            $kpidetails -> setCellName($cellName);
            $kpidetails -> setCellDetails($cellDetails);
            $em->persist($kpidetails);
            $em->flush();
        }

        return $this->redirectToRoute('kpidetails_select1');

    }

    /**
     * Finds and displays a KpiDetails entity.
     *
     * @Route("/{id}", name="kpidetails_show")
     * @Method("GET")
     */
    public function showAction(KpiDetails $kpiDetail)
    {
        $deleteForm = $this->createDeleteForm($kpiDetail);

        return $this->render('kpidetails/show.html.twig', array(
            'kpiDetail' => $kpiDetail,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing KpiDetails entity.
     *
     * @Route("/{id}/edit", name="kpidetails_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, KpiDetails $kpiDetail)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');

        $deleteForm = $this->createDeleteForm($kpiDetail);
        $editForm = $this->createForm(new KpiDetailsType($id,$role), $kpiDetail);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($kpiDetail);
            $em->flush();

            return $this->redirectToRoute('kpidetails_edit', array('id' => $kpiDetail->getId()));
        }

        return $this->render('kpidetails/edit.html.twig', array(
            'kpiDetail' => $kpiDetail,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a KpiDetails entity.
     *
     * @Route("/{id}", name="kpidetails_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, KpiDetails $kpiDetail)
    {
        $form = $this->createDeleteForm($kpiDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($kpiDetail);
            $em->flush();
        }

        return $this->redirectToRoute('kpidetails_index');
    }

    /**
     * Creates a form to delete a KpiDetails entity.
     *
     * @param KpiDetails $kpiDetail The KpiDetails entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(KpiDetails $kpiDetail)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('kpidetails_delete', array('id' => $kpiDetail->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}