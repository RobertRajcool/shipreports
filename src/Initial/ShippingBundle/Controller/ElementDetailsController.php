<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\ElementRules;
use Initial\ShippingBundle\Tests\Controller\ElementRulesControllerTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        if($this->container->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            $query = $em->createQueryBuilder()
                ->select('a')
                ->from('InitialShippingBundle:ElementDetails','a')
                ->leftjoin('InitialShippingBundle:KpiDetails','f', 'WITH', 'f.id = a.kpiDetailsId')
                ->leftjoin('InitialShippingBundle:ShipDetails','d', 'WITH', 'd.id = f.shipDetailsId')
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
                ->from('InitialShippingBundle:ElementDetails','a')
                ->leftjoin('InitialShippingBundle:KpiDetails','f', 'WITH', 'f.id = a.kpiDetailsId')
                ->leftjoin('InitialShippingBundle:ShipDetails','c', 'WITH', 'c.id = f.shipDetailsId')
                ->leftjoin('InitialShippingBundle:User','b','WITH','b.companyid = c.companyDetailsId')
                ->where('b.id = :userId')
                ->setParameter('userId',$userId)
                ->getQuery();
        }

        $elementDetails = $query->getResult();

        return $this->render('elementdetails/index.html.twig', array(
            'elementDetails' => $elementDetails,
        ));
    }


    /**
     * Creates a new elementDetails entity.
     *
     * @Route("/new", name="elementdetails_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $id = $user->getId();
        $role=$this->container->get('security.context')->isGranted('ROLE_ADMIN');

        $elementdetails = new ElementDetails();
        $form = $this->createForm(new ElementDetailsType($id,$role), $elementdetails);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($elementdetails);
            $em->flush();

            return $this->redirectToRoute('elementdetails_show', array('id' => $elementdetails->getId()));
        }

        return $this->render('elementdetails/new.html.twig', array(
            'elementDetail' => $elementdetails,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new ElementDetails entity.
     *
     * @Route("/new1", name="elementdetails_new1")
     * @Method({"GET", "POST"})
     */
    public function new1Action(Request $request)
    {
        $params = $request->request->get('element_details');
        $kpiDetailsId  = $params['kpiDetailsId'];
        $elementName   = $params['elementName'];
        $description   = $params['description'];
        $cellName      = $params['cellName'];
        $cellDetails   = $params['cellDetails'];
        $activatedDate = $params['activatedDate'];
        $endDate       = $params['endDate'];

        $monthtostring=$activatedDate['year'].'-'.$activatedDate['month'].'-'.$activatedDate['day'];
        $new_date=new \DateTime($monthtostring);
        $monthtostring1=$endDate['year'].'-'.$endDate['month'].'-'.$endDate['day'];
        $new_date1=new \DateTime($monthtostring1);

        $weightage     = $params['weightage'];
        $rules         = $params['rules'];

        $course = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$kpiDetailsId));

        $elementDetail = new ElementDetails();
        $elementDetail->setkpiDetailsId($course);
        $elementDetail->setelementName($elementName);
        $elementDetail->setdescription($description);
        $elementDetail->setcellName($cellName);
        $elementDetail->setcellDetails($cellDetails);
        $elementDetail->setactivatedDate($new_date);
        $elementDetail->setendDate($new_date1);
        $elementDetail->setweightage($weightage);
        $elementDetail->setrules($rules);

        $em = $this->getDoctrine()->getManager();
        $em->persist($elementDetail);
        $em->flush();

        $id = $elementDetail->getId();


        for ($i=1;$i<=$rules;$i++)
        {
            $elementRules = new ElementRules();
            $elementRules->setElementDetailsId($this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id'=>$id)));
            $variable = "rules-$i";
            $engine_rules = $params[$variable];
            $elementRules->setRules($engine_rules);
            $em->persist($elementRules);
            $em->flush();
        }

        return $this->redirectToRoute('elementdetails_show', array('id' => $elementDetail->getId()));

    }


    /**
     * Creates a new elementDetails entity.
     *
     * @Route("/element_rule", name="elementdetails_element_rule")
     */
    public function elementruleAction(Request $request)
    {
        $id = $request->request->get('element_Id');
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('a.rules')
            ->from('InitialShippingBundle:ElementRules','a')
            ->where('a.elementDetailsId = :element_Id')
            ->setParameter('element_Id',$id)
            ->getQuery();

        $element_rules = $query->getResult();
        $response = new JsonResponse();
        $response->setData(array('Element_Rule_Array' => $element_rules));

        return $response;
    }

    /**
     * Creates a new elementDetails entity.
     *
     * @Route("/element_rule_edit", name="elementdetails_element_rule_edit")
     */
    public function elementruleeditAction(Request $request)
    {
        $id = $request->request->get('element_Id');
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('a.rules')
            ->from('InitialShippingBundle:ElementRules','a')
            ->where('a.elementDetailsId = :element_Id')
            ->setParameter('element_Id',$id)
            ->getQuery();

        $element_rules = $query->getResult();
        $response = new JsonResponse();
        $response->setData(array('Element_Rule_Array' => $element_rules));

        return $response;
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
        $role=$this->container->get('security.context')->isGranted('ROLE_ADMIN');

        $deleteForm = $this->createDeleteForm($elementDetail);
        $editForm = $this->createForm(new ElementDetailsType($id,$role), $elementDetail);
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