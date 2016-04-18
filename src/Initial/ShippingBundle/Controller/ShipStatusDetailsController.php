<?php

namespace Initial\ShippingBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\ShipStatusDetails;

/**
 * ShipStatusDetails controller.
 *
 * @Route("/shipstatusdetails")
 */
class ShipStatusDetailsController extends Controller
{
    /**
     * Lists all ShipStatusDetails entities.
     *
     * @Route("/", name="shipstatusdetails_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $shipStatusDetails = $em->getRepository('InitialShippingBundle:ShipStatusDetails')->findAll();

        return $this->render('shipstatusdetails/index.html.twig', array(
            'shipStatusDetails' => $shipStatusDetails,
        ));
    }

    /**
     * Finds and displays a ShipStatusDetails entity.
     *
     * @Route("/{id}", name="shipstatusdetails_show")
     * @Method("GET")
     */
    public function showAction(ShipStatusDetails $shipStatusDetail)
    {

        return $this->render('shipstatusdetails/show.html.twig', array(
            'shipStatusDetail' => $shipStatusDetail,
        ));
    }
}
