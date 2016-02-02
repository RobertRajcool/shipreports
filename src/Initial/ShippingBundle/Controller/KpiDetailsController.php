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

        $query = $em->createQueryBuilder()
            ->select('a')
            ->from('InitialShippingBundle:KpiDetails','a')
            ->leftjoin('InitialShippingBundle:ShipDetails','e', 'WITH', 'e.id = a.shipDetailsId')
            ->leftjoin('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = e.companyDetailsId')
            ->leftjoin('InitialShippingBundle:CompanyUsers','d', 'WITH', 'b.id = d.companyName')
            ->leftjoin('InitialShippingBundle:User','c','WITH','c.username = b.adminName or c.username = d.userName')
            ->where('c.id = :userId')
            ->setParameter('userId',$userId)
            ->getQuery();
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

        $kpiDetail = new KpiDetails();
        $form = $this->createForm(new KpiDetailsType($id), $kpiDetail);
        $form->handleRequest($request);
        //$form = $this->createForm(new KpiDetailsType($id));
        //$form->handleRequest($request);

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

        $deleteForm = $this->createDeleteForm($kpiDetail);
        $editForm = $this->createForm(new KpiDetailsType($id), $kpiDetail);
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
