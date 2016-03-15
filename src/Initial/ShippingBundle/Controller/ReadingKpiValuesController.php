<?php

namespace Initial\ShippingBundle\Controller;

use Initial\ShippingBundle\Entity\CommonFunctions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\ReadingKpiValues;
use Initial\ShippingBundle\Form\ReadingKpiValuesType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
     * @Route("/{page}", name="readingkpivalues_index")
     * @Method("GET")
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();
        $docobject=new ReadingKpiValues();
        $user1 = $this->getUser();
        $userId = $user1->getId();

        //$readingKpiValues = $em->getRepository('InitialShippingBundle:ReadingKpiValues')->findAll();
        $total_records = $em->getRepository('InitialShippingBundle:ReadingKpiValues')->countActiveRecords($docobject->getId());
        $record_per_page = $this->container->getParameter('maxrecords_per_page');
        $last_page = ceil($total_records / $record_per_page);
        $previous_page = $page > 1 ? $page - 1 : 1;
        $next_page = $page < $last_page ? $page + 1 : $last_page;
        $readingKpiValues=$em->getRepository('InitialShippingBundle:ReadingKpiValues')->findBy(array(), array('id' => 'DESC'), $record_per_page, ($page - 1) * $record_per_page);

        return $this->render('InitialShippingBundle:readingkpivalues:index.html.twig', array(
            'readingKpiValues' => $readingKpiValues,
            'last_page' => $last_page,
            'previous_page' => $previous_page,
            'current_page' => $page,
            'next_page' => $next_page,
            'total_jobs' => $total_records
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
        $session = new Session();
       // $session->start();



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


        $returnarray=array();
        $sessionkpielementid=array();
        $k=0;
        for($i=0;$i<count($ids);$i++)
        {
            $kpiid=$ids[$i]['id'];
            $kpiname=$ids[$i]['kpiName'];

            $query = $em->createQueryBuilder()
                ->select('b.elementName', 'b.id')
                ->from('InitialShippingBundle:ElementDetails', 'b')
                ->where('b.kpiDetailsId = :kpidetailsid')
                ->setParameter('kpidetailsid', $kpiid)
                ->add('orderBy', 'b.id  ASC ')
                ->getQuery();
            $elementids = $query->getResult();
            if(count($elementids)==0)
            {
                $query1 = $em->createQueryBuilder()
                    ->select('b.kpiName', 'b.id')
                    ->from('InitialShippingBundle:KpiDetails', 'b')
                    ->where('b.kpiName = :kpiName')
                    ->setParameter('kpiName', $kpiname)
                    ->add('orderBy', 'b.id  ASC ')
                    ->groupby('b.kpiName')
                    ->getQuery();

                $ids1 = $query1->getResult();
                $newkpiid=$ids1[0]['id'];
                $newkpiname=$ids1[0]['kpiName'];
                $query = $em->createQueryBuilder()
                    ->select('b.elementName', 'b.id')
                    ->from('InitialShippingBundle:ElementDetails', 'b')
                    ->where('b.kpiDetailsId = :kpidetailsid')
                    ->setParameter('kpidetailsid', $newkpiid)
                    ->add('orderBy', 'b.id  ASC ')
                    ->getQuery();
                $elementids = $query->getResult();
                for($j=0;$j<count($elementids);$j++)
                {
                    $sessionkpielementid[$newkpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$newkpiname][$j] = $elementids[$j]['elementName'];


                }
            }
            else
            {

                for($j=0;$j<count($elementids);$j++)
                {
                    $sessionkpielementid[$kpiid][$j] = $elementids[$j]['id'];
                    $returnarray[$kpiname][$j] = $elementids[$j]['elementName'];

                }
            }




        }

        $session->set('sessionkpielementid', $sessionkpielementid);
        $response = new JsonResponse();
        $response->setData(array('kpiNameArray' => $returnarray));
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

        $elementvalue = $request->request->get('myvalue');
        $elementidkpiid = $request->request->get('myelement');
        $em=$this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()
            ->select('b.id')
            ->from('InitialShippingBundle:ElementDetails', 'b')
            ->where('b.elementName = :elementName')
            ->setParameter('elementName', $elementidkpiid)
            ->getQuery();
        $elementids = $query->getOneOrNullResult();
        $read1 = "";
        $result="";
        $rulesarray = $em->createQueryBuilder()
            ->select('b.rules')
            ->from('InitialShippingBundle:ElementRules', 'b')
            ->where('b.elementDetailsId = :elementDetailsId')
            ->setParameter('elementDetailsId', $elementids['id'])
            ->getQuery()
            ->getResult();
        $totalcountofrulesarry=count($rulesarray);
        if ($totalcountofrulesarry > 0)
        {

            for ($aaa = 0; $aaa < count($rulesarray); $aaa++)
            {

                $jsfiledirectry = $this->container->getParameter('kernel.root_dir') . '/../web/js/87f1824_part_1_nodejs_3.js \'' . $rulesarray[$aaa]['rules'] . ' \' ' . $elementvalue;
                $jsfilename = 'node ' . $jsfiledirectry;
                $handle = popen($jsfilename, 'r');
                $read = fread($handle, 2096);
                $read1 = str_replace("\n", '', $read);
                if ($read1 != "false")
                {
                    break;
                }

            }
            if ($read1 == "false")
            {
                $result="Please Enter correct value";

            }
            //If Element rule return null answer that shows error message Ends Here//
            else
            {
                $result=$read1;

            }
        }
        else
        {
            $result=$elementvalue;

        }





        $response = new JsonResponse();
        $response->setData(array('ElementNameArray' => $result));

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
        $session = new Session();
        $kpiandelementids= $session->get('sessionkpielementid');
        $params = $request->request->get('reading_kpi_values');
        $elementvalues=$request->request->get('newelemetvalues');

        $em = $this->getDoctrine()->getManager();
        $shipid = $params['shipDetailsId'];
        $month = $params['monthdetail'];
        $monthtostring=$month['year'].'-'.$month['month'].'-'.$month['day'];
        $new_date=new \DateTime($monthtostring);
        $new_date->modify('first day of this month');
        $k=0;
        foreach($kpiandelementids as $kpikey => $kpipvalue)
        {

            $newshipid = $em->getRepository('InitialShippingBundle:ShipDetails')->findOneBy(array('id'=>$shipid));
            $newkpiid = $em->getRepository('InitialShippingBundle:KpiDetails')->findOneBy(array('id'=>$kpikey));
            foreach($kpipvalue as $elementkey => $elementvalue)
            {
                $newelementid = $em->getRepository('InitialShippingBundle:ElementDetails')->findOneBy(array('id'=>$elementvalue));

                    $readingkpivalue=new ReadingKpiValues();
                    $readingkpivalue->setKpiDetailsId($newkpiid);
                    $readingkpivalue->setElementDetailsId($newelementid);
                    $readingkpivalue->setShipDetailsId($newshipid);
                    $readingkpivalue->setMonthdetail($new_date);
                    $readingkpivalue->setValue($elementvalues[$k]);
                    $em->persist($readingkpivalue);
                    $em->flush();
               $k++;

            }

        }

        /*$datafromuser=array('shipid'=>$shipid,'kpiid'=>$kpiids,'elementId'=>$elementid,'value'=>$elementvalue,'dataofmonth'=>$monthtostring);
        $gearman = $this->get('gearman');       //$datafromuser=array();
        $gearman->doBackgroundJob('InitialShippingBundleserviceReadExcelWorker~kpivalues', json_encode($datafromuser));*/
        $session->remove('sessionkpielementid');
        $againsessionvalue=$session->get('sessionkpielementid');

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

    public function getAllPosts($currentPage = 1)
    {
        // Create our query
        $query = $this->createQueryBuilder('p')
            ->orderBy('p.created', 'DESC')
            ->getQuery();

        // No need to manually get get the result ($query->getResult())

        $paginator = $this->paginate($query, $currentPage);

        return $paginator;
    }
    public function paginate($dql, $page = 1, $limit = 5)
    {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }


}
