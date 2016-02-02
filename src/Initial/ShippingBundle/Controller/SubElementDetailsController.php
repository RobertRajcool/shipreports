<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\SubElementDetails;
use Initial\ShippingBundle\Form\SubElementDetailsType;

/**
 * SubElementDetails controller.
 *
 * @Route("/subelementdetails")
 */
class SubElementDetailsController extends Controller
{
    /**
     * Lists all SubElementDetails entities.
     *
     * @Route("/", name="subelementdetails_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $subElementDetails = $em->getRepository('InitialShippingBundle:SubElementDetails')->findAll();

        return $this->render('subelementdetails/index.html.twig', array(
            'subElementDetails' => $subElementDetails,
        ));
    }


    /**
     * Lists all SubElementDetails entities.
     *
     * @Route("/{id}/select", name="subelementdetails_select")
     * @Method("GET")
     */
    public function selectAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $subElementDetails = $em->getRepository('InitialShippingBundle:SubElementDetails')->findByElementDetailsId($id);

        return $this->render('subelementdetails/index.html.twig', array(
            'subElementDetails' => $subElementDetails,
        ));
    }

    /**
     * Lists all SubElementDetails entities.
     *
     * @Route("/select1", name="subelementdetails_select1")
     * @Method("GET")
     */
    public function select1Action()
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $em = $this->getDoctrine()->getManager();


        $query = $em->createQueryBuilder()
            ->select('a')
            ->from('InitialShippingBundle:SubElementDetails','a')
            ->leftjoin('InitialShippingBundle:ElementDetails','g', 'WITH', 'g.id = a.elementDetailsId')
            ->leftjoin('InitialShippingBundle:KpiDetails','f', 'WITH', 'f.id = g.kpiDetailsId')
            ->leftjoin('InitialShippingBundle:ShipDetails','e', 'WITH', 'e.id = f.shipDetailsId')
            ->leftjoin('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = e.companyDetailsId')
            ->leftjoin('InitialShippingBundle:CompanyUsers','d', 'WITH', 'b.id = d.companyName')
            ->leftjoin('InitialShippingBundle:User','c','WITH','c.username = b.adminName or c.username = d.userName')
            ->where('c.id = :userId')
            ->setParameter('userId',$userId)
            ->getQuery();
        $subElementDetails = $query->getResult();

        return $this->render('subelementdetails/index.html.twig', array(
            'subElementDetails' => $subElementDetails,
        ));
    }



    /**
     * Creates a new SubElementDetails entity.
     *
     * @Route("/new", name="subelementdetails_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $subElementDetail = new SubElementDetails();
        $form = $this->createForm(new SubElementDetailsType($id), $subElementDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($subElementDetail);
            $em->flush();

            return $this->redirectToRoute('subelementdetails_show', array('id' => $subElementDetail->getId()));
        }

        return $this->render('subelementdetails/new.html.twig', array(
            'subElementDetail' => $subElementDetail,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a SubElementDetails entity.
     *
     * @Route("/{id}", name="subelementdetails_show")
     * @Method("GET")
     */
    public function showAction(SubElementDetails $subElementDetail)
    {
        $deleteForm = $this->createDeleteForm($subElementDetail);

        return $this->render('subelementdetails/show.html.twig', array(
            'subElementDetail' => $subElementDetail,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing SubElementDetails entity.
     *
     * @Route("/{id}/edit", name="subelementdetails_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, SubElementDetails $subElementDetail)
    {
        $deleteForm = $this->createDeleteForm($subElementDetail);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\SubElementDetailsType', $subElementDetail);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($subElementDetail);
            $em->flush();

            return $this->redirectToRoute('subelementdetails_edit', array('id' => $subElementDetail->getId()));
        }

        return $this->render('subelementdetails/edit.html.twig', array(
            'subElementDetail' => $subElementDetail,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a SubElementDetails entity.
     *
     * @Route("/{id}", name="subelementdetails_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, SubElementDetails $subElementDetail)
    {
        $form = $this->createDeleteForm($subElementDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($subElementDetail);
            $em->flush();
        }

        return $this->redirectToRoute('subelementdetails_index');
    }

    /**
     * Creates a form to delete a SubElementDetails entity.
     *
     * @param SubElementDetails $subElementDetail The SubElementDetails entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(SubElementDetails $subElementDetail)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('subelementdetails_delete', array('id' => $subElementDetail->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
