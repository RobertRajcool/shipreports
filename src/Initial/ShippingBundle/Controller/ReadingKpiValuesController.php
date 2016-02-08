<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\ReadingKpiValues;
use Initial\ShippingBundle\Form\ReadingKpiValuesType;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * ReadingKpiValues controller.
 *
 * @Route("/readingkpivalues")
 */
class ReadingKpiValuesController extends Controller
{
    /**
     * Lists all ReadingKpiValues entities.
     *
     * @Route("/", name="readingkpivalues_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user1 = $this->getUser();
        $userId = $user1->getId();

        $readingKpiValues = $em->getRepository('InitialShippingBundle:ReadingKpiValues')->findAll();

        return $this->render('InitialShippingBundle:readingkpivalues:index.html.twig', array(
            'readingKpiValues' => $readingKpiValues,
        ));
    }

    /**
     * Creates a new ReadingKpiValues entity.
     *
     * @Route("/new", name="readingkpivalues_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $readingKpiValue = new ReadingKpiValues();
        //$form = $this->createForm('Initial\ShippingBundle\Form\ReadingKpiValuesType', $readingKpiValue);
        $form = $this->createForm(new ReadingKpiValuesType($id), $readingKpiValue);
        $form->handleRequest($request);
        /*

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($readingKpiValue);
            $em->flush();

            return $this->redirectToRoute('readingkpivalues_show', array('id' => $readingKpiValue->getId()));
        }*/

        return $this->render('InitialShippingBundle:readingkpivalues:new.html.twig', array(
            'readingKpiValue' => $readingKpiValue,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a ReadingKpiValues entity.
     *
     * @Route("/{id}", name="readingkpivalues_show")
     * @Method("GET")
     */
    public function showAction(ReadingKpiValues $readingKpiValue)
    {
        $deleteForm = $this->createDeleteForm($readingKpiValue);

        return $this->render('InitialShippingBundle:readingkpivalues:show.html.twig', array(
            'readingKpiValue' => $readingKpiValue,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ReadingKpiValues entity.
     *
     * @Route("/{id}/edit", name="readingkpivalues_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ReadingKpiValues $readingKpiValue)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $deleteForm = $this->createDeleteForm($readingKpiValue);
        $editForm = $this->createForm(new ReadingKpiValuesType($id), $readingKpiValue);
        $editForm->handleRequest($request);
        /*

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($readingKpiValue);
            $em->flush();

            return $this->redirectToRoute('readingkpivalues_edit', array('id' => $readingKpiValue->getId()));
        }
        */

        return $this->render('InitialShippingBundle:readingkpivalues:edit.html.twig', array(
            'readingKpiValue' => $readingKpiValue,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ReadingKpiValues entity.
     *
     * @Route("/{id}", name="readingkpivalues_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ReadingKpiValues $readingKpiValue)
    {
        $form = $this->createDeleteForm($readingKpiValue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($readingKpiValue);
            $em->flush();
        }

        return $this->redirectToRoute('readingkpivalues_index');
    }

    /**
     * Creates a form to delete a ReadingKpiValues entity.
     *
     * @param ReadingKpiValues $readingKpiValue The ReadingKpiValues entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ReadingKpiValues $readingKpiValue)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('readingkpivalues_delete', array('id' => $readingKpiValue->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Refresh Kpi List.
     *
     * @Route("/kpilist", name="refreshkpilist")
     * @Method("Post")
     */
    public function refreshkpilistAction(Request $request)

    {

        $data = $request->request->get('shipid');


        $em=$this->getDoctrine()->getManager();
        //echo 'id value: '.$data;

        $query = $em->createQueryBuilder()
            ->select('b.kpiName', 'b.id')
            ->from('InitialShippingBundle:KpiDetails', 'b')
            ->where('b.shipDetailsId = :shipdetailsid')
            ->setParameter('shipdetailsid', $data)
            ->add('orderBy', 'b.id  ASC ')
            ->getQuery();
        $ids = $query->getResult();
        $response = new JsonResponse();
        $response->setData(array('kpiNameArray' => $ids));

        return $response;



    }
    /**
     * Refresh Kpi List.
     *
     * @Route("/elementlist", name="refreshelementcall")
     * @Method("Post")
     */
    public function refreshelementlistAction(Request $request)

    {

        $kpidetailsid = $request->request->get('elementId');


        $em=$this->getDoctrine()->getManager();
        //echo 'id value: '.$data;

        $query = $em->createQueryBuilder()
            ->select('b.elementName', 'b.id')
            ->from('InitialShippingBundle:ElementDetails', 'b')
            ->where('b.kpiDetailsId = :kpidetailsid')
            ->setParameter('kpidetailsid', $kpidetailsid)
            ->add('orderBy', 'b.id  ASC ')
            ->getQuery();
        $elementids = $query->getResult();

        $response = new JsonResponse();
        $response->setData(array('ElementNameArray' => $elementids));

        return $response;



    }

    /**
     * Adding Kpi Values.
     *
     * @Route("/readingelementvalues", name="readingelementvalues")
     * @Method("Post")
     */
    public function new1Action(Request $request)
    {
        $params = $request->request->get('reading_kpi_values');

        $shipid = $params['shipDetailsId'];
        $kpiid = $params['kpiDetailsId'];
        $elementId = $params['elementDetailsId'];
        $month = $params['monthdetail'];
        $value = $params['value'];
        $monthtostring=$month['year'].'-'.$month['month'].'-'.$month['day'];
        $new_date=new \DateTime($monthtostring);


        $em = $this->getDoctrine()->getManager();
        $newkpiid = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$kpiid));
        $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id'=>$shipid));
        $newelementid = $em->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id'=>$elementId));
        $readingkpivalue=new ReadingKpiValues();
        $readingkpivalue->setKpiDetailsId($newkpiid);
        $readingkpivalue->setElementDetailsId($newelementid);
        $readingkpivalue->setShipDetailsId($newshipid);
        $readingkpivalue->setMonthdetail($new_date);
        $readingkpivalue->setValue($value);


        $em->persist($readingkpivalue);
        $em->flush();

        return $this->redirectToRoute('readingkpivalues_index');

    }



    /**
     * Update kpi Values.
     *
     * @Route("/updatekpivalues", name="updatekpivalues")
     * @Method("Post")
     */
    public function updateAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $params = $request->request->get('reading_kpi_values');
        $id = $params['id'];
        $entity = $em->getRepository('InitialShippingBundle:ReadingKpiValues')->find($id);


        $shipid = $params['shipDetailsId'];
        $kpiid = $params['kpiDetailsId'];
        $elementId = $params['elementDetailsId'];
        $month = $params['monthdetail'];
        $value = $params['value'];
        $monthtostring=$month['year'].'-'.$month['month'].'-'.$month['day'];
        $new_date=new \DateTime($monthtostring);


        $newkpiid = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$kpiid));
        $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id'=>$shipid));
        $newelementid = $em->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id'=>$elementId));
        $readingkpivalue=new ReadingKpiValues();
        $entity->setKpiDetailsId($newkpiid);
        $entity->setElementDetailsId($newelementid);
        $entity->setShipDetailsId($newshipid);
        $entity->setMonthdetail($new_date);
        $entity->setValue($value);
        $em->flush();

        return $this->redirectToRoute('readingkpivalues_index');

    }


}
