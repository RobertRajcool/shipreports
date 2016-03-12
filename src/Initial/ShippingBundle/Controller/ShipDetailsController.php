<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\ShipDetails;
use Initial\ShippingBundle\Form\ShipDetailsType;

/**
 * ShipDetails controller.
 *
 * @Route("/shipdetails", name="shipdetails_index")
 */
class ShipDetailsController extends Controller
{
    /**
     * Lists all ShipDetails entities.
     *
     * @Route("/", name="shipdetails_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $shipDetails = $em->getRepository('InitialShippingBundle:ShipDetails')->findAll();

        return $this->render('shipdetails/index.html.twig', array(
            'shipDetails' => $shipDetails,
        ));
    }


    /**
     * Lists all ShipDetails entities.
     *
     * @Route("/{id}/select", name="shipdetails_select")
     * @Method("GET")
     */
    public function selectAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $shipDetails = $em->getRepository('InitialShippingBundle:ShipDetails')->findByCompanyDetailsId($id);

        return $this->render('shipdetails/index.html.twig', array(
            'shipDetails' => $shipDetails,
        ));
    }


    /**
     * Lists all ShipDetails entities.
     *
     * @Route("/select1", name="shipdetails_select1")
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
                ->from('InitialShippingBundle:ShipDetails','a')
                ->leftjoin('InitialShippingBundle:CompanyDetails','b', 'WITH', 'b.id = a.companyDetailsId')
                ->leftjoin('InitialShippingBundle:User','c','WITH','c.username = b.adminName')
                ->where('c.id = :userId')
                ->setParameter('userId',$userId)
                ->getQuery();
        }
        else
        {
            $query = $em->createQueryBuilder()
                ->select('a')
                ->from('InitialShippingBundle:ShipDetails','a')
                ->leftjoin('InitialShippingBundle:User','b','WITH','b.companyid = a.companyDetailsId')
                ->where('b.id = :userId')
                ->setParameter('userId',$userId)
                ->getQuery();
        }
        $shipDetails = $query->getResult();

        return $this->render('shipdetails/index.html.twig', array(
            'shipDetails' => $shipDetails,
        ));
    }



    /**
     * Creates a new ShipDetails entity.
     *
     * @Route("/new", name="shipdetails_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $shipDetail = new ShipDetails();
        $form = $this->createForm('Initial\ShippingBundle\Form\ShipDetailsType', $shipDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($shipDetail);
            $em->flush();

            return $this->redirectToRoute('shipdetails_show', array('id' => $shipDetail->getId()));
        }

        return $this->render('shipdetails/new.html.twig', array(
            'shipDetail' => $shipDetail,
            'form' => $form->createView(),
        ));
    }


    /**
     * Creates a new ShipDetails entity.
     *
     * @Route("/new1", name="shipdetails_new1")
     * @Method({"GET", "POST"})
     */
    public function new1Action(Request $request)
    {
        $params = $request->request->get('ship_details');
        $shipName = $params['shipName'];
        $description = $params['description'];

        $user = $this->getUser();
        $userId = $user->getId();
        $em = $this->getDoctrine()->getManager();

        if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $query = $em->createQueryBuilder()
                ->select('a.id')
                ->from('InitialShippingBundle:CompanyDetails','a')
                ->leftjoin('InitialShippingBundle:User','c','WITH','c.username = a.adminName')
                ->where('c.id = :userId')
                ->setParameter('userId',$userId)
                ->getQuery();
        }
        else
        {
            $query = $em->createQueryBuilder()
                ->select ('identity(a.companyid)')
                ->from('InitialShippingBundle:User','a')
                ->where('a.id = :userId')
                ->setParameter('userId',$userId)
                ->getQuery();
        }

        $ans = $query->getResult();
        $companyName=$ans[0]['id'];

        $course = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:CompanyDetails')->findOneBy(array('id'=>$companyName));

        $shipdetails = new ShipDetails();
        $shipdetails->setCompanyDetailsId($course);
        $shipdetails->setShipName($shipName);
        $shipdetails->setDescription($description);

        $em->persist($shipdetails);
        $em->flush();

        return $this->redirectToRoute('shipdetails_show', array('id' => $shipdetails->getId()));

    }


    /**
     * Finds and displays a ShipDetails entity.
     *
     * @Route("/{id}", name="shipdetails_show")
     * @Method("GET")
     */
    public function showAction(ShipDetails $shipDetail)
    {
        $deleteForm = $this->createDeleteForm($shipDetail);

        return $this->render('shipdetails/show.html.twig', array(
            'shipDetail' => $shipDetail,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ShipDetails entity.
     *
     * @Route("/{id}/edit", name="shipdetails_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ShipDetails $shipDetail)
    {
        $deleteForm = $this->createDeleteForm($shipDetail);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\ShipDetailsType', $shipDetail);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($shipDetail);
            $em->flush();

            return $this->redirectToRoute('shipdetails_index', array('id' => $shipDetail->getId()));
        }

        return $this->render('shipdetails/edit.html.twig', array(
            'shipDetail' => $shipDetail,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ShipDetails entity.
     *
     * @Route("/{id}", name="shipdetails_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ShipDetails $shipDetail)
    {
        $form = $this->createDeleteForm($shipDetail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($shipDetail);
            $em->flush();
        }

        return $this->redirectToRoute('shipdetails_index');
    }

    /**
     * Creates a form to delete a ShipDetails entity.
     *
     * @param ShipDetails $shipDetail The ShipDetails entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ShipDetails $shipDetail)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('shipdetails_delete', array('id' => $shipDetail->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}