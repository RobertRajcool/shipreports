<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\CompanyDetails;
use Initial\ShippingBundle\Form\CompanyDetailsType;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * CompanyDetails controller.
 *
 * @Route("/companydetails")
 */
class CompanyDetailsController extends Controller
{
    /**
     * Lists all CompanyDetails entities.
     *
     * @Route("/", name="companydetails_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $companyDetails = $em->getRepository('InitialShippingBundle:CompanyDetails')->findAll();

        return $this->render('companydetails/index.html.twig', array(
            'companyDetails' => $companyDetails,
        ));
    }

    /**
     * Creates a new CompanyDetails entity.
     *
     * @Route("/new", name="companydetails_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $companyDetail = new CompanyDetails();
        $form = $this->createForm('Initial\ShippingBundle\Form\CompanyDetailsType', $companyDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($companyDetail);
            $em->flush();

            return $this->redirectToRoute('companydetails_show', array('id' => $companyDetail->getId()));
        }

        return $this->render('companydetails/new.html.twig', array(
            'companyDetail' => $companyDetail,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new CompanyDetails entity.
     *
     * @Route("/newcompany", name="companydetails_newcompany")
     */
    public function newcompanyAction()
    {
        //echo "New companyy action";
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('a.companyName,a.id')
            ->from('InitialShippingBundle:CompanyDetails','a')
            ->getQuery();
        $shipDetails = $query->getResult();

        //print_r($shipDetails);die;

        $response = new JsonResponse();
        $response->setData(array('companyNameArray' => $shipDetails));

        return $response;
    }

    /**
     * Creates a new CompanyDetails entity.
     *
     * @Route("/newadmin", name="companydetails_newadmin")
     */
    public function newadminAction(Request $request)
    {
        $id = $request->request->get('selectid');
        //echo "New companyy action";
       // $id = $request->request->get(data);

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('a.adminName,a.emailId')
            ->from('InitialShippingBundle:CompanyDetails','a')
            ->where('a.id = :userId')
            ->setParameter('userId',$id)
            ->getQuery();
        $adminDetails = $query->getResult();

       // print_r($adminDetails);die;

        $response = new JsonResponse();
        $response->setData(array('adminNameArray' => $adminDetails));

        return $response;
    }


    /**
     * Finds and displays a CompanyDetails entity.
     *
     * @Route("/{id}", name="companydetails_show")
     * @Method("GET")
     */
    public function showAction(CompanyDetails $companyDetail)
    {
        $deleteForm = $this->createDeleteForm($companyDetail);

        return $this->render('companydetails/show.html.twig', array(
            'companyDetail' => $companyDetail,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing CompanyDetails entity.
     *
     * @Route("/{id}/edit", name="companydetails_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, CompanyDetails $companyDetail)
    {
        $deleteForm = $this->createDeleteForm($companyDetail);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\CompanyDetailsType', $companyDetail);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($companyDetail);
            $em->flush();

            return $this->redirectToRoute('companydetails_edit', array('id' => $companyDetail->getId()));
        }

        return $this->render('companydetails/edit.html.twig', array(
            'companyDetail' => $companyDetail,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a CompanyDetails entity.
     *
     * @Route("/{id}", name="companydetails_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, CompanyDetails $companyDetail)
    {
        $form = $this->createDeleteForm($companyDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($companyDetail);
            $em->flush();
        }

        return $this->redirectToRoute('companydetails_index');
    }

    /**
     * Creates a form to delete a CompanyDetails entity.
     *
     * @param CompanyDetails $companyDetail The CompanyDetails entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(CompanyDetails $companyDetail)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('companydetails_delete', array('id' => $companyDetail->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }


}
