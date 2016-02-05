<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\ElementDetails;
use Initial\ShippingBundle\Form\ElementDetailsType;

/**
 * ElementDetails controller.
 *
 * @Route("/elementdetails", name="elementdetails_index")
 */
class ElementDetailsController extends Controller
{
    /**
     * Lists all ElementDetails entities.
     *
     * @Route("/", name="elementdetails_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $elementDetails = $em->getRepository('InitialShippingBundle:ElementDetails')->findAll();

        return $this->render('elementdetails/index.html.twig', array(
            'elementDetails' => $elementDetails,
        ));
    }

    /**
     * Lists all ElementDetails entities.
     *
     * @Route("/{id}/select", name="elementdetails_select")
     * @Method("GET")
     */
    public function selectAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $elementDetails = $em->getRepository('InitialShippingBundle:ElementDetails')->findByKpiDetailsId($id);

        return $this->render('elementdetails/index.html.twig', array(
            'elementDetails' => $elementDetails,
        ));
    }


    /**
     * Lists all ElementDetails entities.
     *
     * @Route("/select", name="elementdetails_select1")
     * @Method("GET")
     */
    public function select1Action()
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('a')
            ->from('InitialShippingBundle:ElementDetails','a')
            ->leftjoin('InitialShippingBundle:KpiDetails','f', 'WITH', 'f.id = a.kpiDetailsId')
            ->leftjoin('InitialShippingBundle:ShipDetails','e', 'WITH', 'e.id = f.shipDetailsId')
            ->leftjoin('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = e.companyDetailsId')
            ->leftjoin('InitialShippingBundle:CompanyUsers','d', 'WITH', 'b.id = d.companyName')
            ->leftjoin('InitialShippingBundle:User','c','WITH','c.username = b.adminName or c.username = d.userName')
            ->where('c.id = :userId')
            ->setParameter('userId',$userId)
            ->getQuery();
        $elementDetails = $query->getResult();

        return $this->render('elementdetails/index.html.twig', array(
            'elementDetails' => $elementDetails,
        ));
    }




    /**
     * Creates a new ElementDetails entity.
     *
     * @Route("/new", name="elementdetails_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $elementDetail = new ElementDetails();
        $form = $this->createForm(new ElementDetailsType($id), $elementDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($elementDetail);
            $em->flush();

            return $this->redirectToRoute('elementdetails_show', array('id' => $elementDetail->getId()));
        }

        return $this->render('elementdetails/new.html.twig', array(
            'elementDetail' => $elementDetail,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a ElementDetails entity.
     *
     * @Route("/{id}", name="elementdetails_show")
     * @Method("GET")
     */
    public function showAction(ElementDetails $elementDetail)
    {
        $deleteForm = $this->createDeleteForm($elementDetail);

        return $this->render('elementdetails/show.html.twig', array(
            'elementDetail' => $elementDetail,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ElementDetails entity.
     *
     * @Route("/{id}/edit", name="elementdetails_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ElementDetails $elementDetail)
    {

        $user = $this->getUser();
        $id = $user->getId();

        $deleteForm = $this->createDeleteForm($elementDetail);
        $editForm = $this->createForm(new ElementDetailsType($id), $elementDetail);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($elementDetail);
            $em->flush();

            return $this->redirectToRoute('elementdetails_edit', array('id' => $elementDetail->getId()));
        }

        return $this->render('elementdetails/edit.html.twig', array(
            'elementDetail' => $elementDetail,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ElementDetails entity.
     *
     * @Route("/{id}", name="elementdetails_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ElementDetails $elementDetail)
    {
        $form = $this->createDeleteForm($elementDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($elementDetail);
            $em->flush();
        }

        return $this->redirectToRoute('elementdetails_index');
    }

    /**
     * Creates a form to delete a ElementDetails entity.
     *
     * @param ElementDetails $elementDetail The ElementDetails entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ElementDetails $elementDetail)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('elementdetails_delete', array('id' => $elementDetail->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
