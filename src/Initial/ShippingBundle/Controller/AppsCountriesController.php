<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\AppsCountries;
use Initial\ShippingBundle\Form\AppsCountriesType;

/**
 * AppsCountries controller.
 *
 * @Route("/appscountries")
 */
class AppsCountriesController extends Controller
{
    /**
     * Lists all AppsCountries entities.
     *
     * @Route("/", name="appscountries_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $appsCountries = $em->getRepository('InitialShippingBundle:AppsCountries')->findAll();

        return $this->render('appscountries/index.html.twig', array(
            'appsCountries' => $appsCountries,
        ));
    }

    /**
     * Creates a new AppsCountries entity.
     *
     * @Route("/new", name="appscountries_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $appsCountry = new AppsCountries();
        $form = $this->createForm('Initial\ShippingBundle\Form\AppsCountriesType', $appsCountry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($appsCountry);
            $em->flush();

            return $this->redirectToRoute('appscountries_show', array('id' => $appscountries->getId()));
        }

        return $this->render('appscountries/new.html.twig', array(
            'appsCountry' => $appsCountry,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a AppsCountries entity.
     *
     * @Route("/{id}", name="appscountries_show")
     * @Method("GET")
     */
    public function showAction(AppsCountries $appsCountry)
    {
        $deleteForm = $this->createDeleteForm($appsCountry);

        return $this->render('appscountries/show.html.twig', array(
            'appsCountry' => $appsCountry,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing AppsCountries entity.
     *
     * @Route("/{id}/edit", name="appscountries_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, AppsCountries $appsCountry)
    {
        $deleteForm = $this->createDeleteForm($appsCountry);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\AppsCountriesType', $appsCountry);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($appsCountry);
            $em->flush();

            return $this->redirectToRoute('appscountries_edit', array('id' => $appsCountry->getId()));
        }

        return $this->render('appscountries/edit.html.twig', array(
            'appsCountry' => $appsCountry,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a AppsCountries entity.
     *
     * @Route("/{id}", name="appscountries_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, AppsCountries $appsCountry)
    {
        $form = $this->createDeleteForm($appsCountry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($appsCountry);
            $em->flush();
        }

        return $this->redirectToRoute('appscountries_index');
    }

    /**
     * Creates a form to delete a AppsCountries entity.
     *
     * @param AppsCountries $appsCountry The AppsCountries entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(AppsCountries $appsCountry)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('appscountries_delete', array('id' => $appsCountry->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
