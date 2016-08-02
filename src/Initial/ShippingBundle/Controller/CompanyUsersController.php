<?php

namespace Initial\ShippingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Initial\ShippingBundle\Entity\CompanyUsers;
use Initial\ShippingBundle\Form\CompanyUsersType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Initial\ShippingBundle\Entity\User;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\Response;
use Initial\ShippingBundle\Entity\Backupreport;

/**
 * CompanyUsers controller.
 *
 * @Route("/companyusers")
 */
class CompanyUsersController extends Controller
{
    /**
     * Lists all CompanyUsers entities.
     *
     * @Route("/", name="companyusers_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $companyUsers = $em->getRepository('InitialShippingBundle:CompanyUsers')->findAll();

        return $this->render('companyusers/index.html.twig', array(
            'companyUsers' => $companyUsers,
        ));
    }


    /**
     * Lists all CompanyUsers entities.
     *
     * @Route("/select", name="companyusers_select")
     * @Method("GET")
     */
    public function selectAction()
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('a')
            ->from('InitialShippingBundle:CompanyUsers', 'a')
            ->leftjoin('InitialShippingBundle:CompanyDetails', 'b', 'WITH', 'b.id = a.companyName')
            ->leftjoin('InitialShippingBundle:User', 'c', 'WITH', 'c.username = b.adminName')
            ->where('c.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery();
        $companyUsers = $query->getResult();

        return $this->render('companyusers/index.html.twig', array(
            'companyUsers' => $companyUsers,
        ));
    }


    /**
     * Creates a new CompanyUsers entity.
     *
     * @Route("/new", name="companyusers_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $companyUser = new CompanyUsers();
        $form = $this->createForm('Initial\ShippingBundle\Form\CompanyUsersType', $companyUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($companyUser);
            $em->flush();

            return $this->redirectToRoute('companyusers_show', array('id' => $companyUser->getId()));
        }

        return $this->render('companyusers/new.html.twig', array(
            'companyUser' => $companyUser,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new CompanyUsers entity.
     *
     * @Route("/new1", name="companyusers_new1")
     * @Method({"GET", "POST"})
     */
    public function new1Action(Request $request)
    {

        $params = $request->request->get('company_users');
        $userName = $params['userName'];
        $role = $params['role'];
        $emailId = $params['emailId'];

        $user = $this->getUser();
        $userId = $user->getId();

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()
            ->select('a.id')
            ->from('InitialShippingBundle:CompanyDetails', 'a')
            ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'a.adminName = b.username')
            ->where('b.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        //$companyName = 3;

        $companyName = $query[0]['id'];
        $course = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:CompanyDetails')->findOneBy(array('id' => $companyName));

        $course1 = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:UserRole')->findOneBy(array('id' => $role));

        //print_r($course);die;

        $companyUser = new CompanyUsers();
        $companyUser->setCompanyName($course);
        $companyUser->setUserName($userName);
        $companyUser->setRole($course1);
        $companyUser->setEmailId($emailId);

        $em->persist($companyUser);
        $em->flush();


        return $this->redirectToRoute('companyusers_show', array('id' => $companyUser->getId()));
    }


    /**
     * Finds and displays a CompanyUsers entity.
     *
     * @Route("/{id}", name="companyusers_show")
     * @Method("GET")
     */
    public function showAction(CompanyUsers $companyUser)
    {
        $deleteForm = $this->createDeleteForm($companyUser);

        return $this->render('companyusers/show.html.twig', array(
            'companyUser' => $companyUser,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing CompanyUsers entity.
     *
     * @Route("/{id}/edit", name="companyusers_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, CompanyUsers $companyUser)
    {
        $deleteForm = $this->createDeleteForm($companyUser);
        $editForm = $this->createForm('Initial\ShippingBundle\Form\CompanyUsersType', $companyUser);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($companyUser);
            $em->flush();

            return $this->redirectToRoute('companyusers_edit', array('id' => $companyUser->getId()));
        }

        return $this->render('companyusers/edit.html.twig', array(
            'companyUser' => $companyUser,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a CompanyUsers entity.
     *
     * @Route("/{id}", name="companyusers_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, CompanyUsers $companyUser)
    {
        $form = $this->createDeleteForm($companyUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($companyUser);
            $em->flush();
        }

        return $this->redirectToRoute('companyusers_index');
    }

    /**
     * Creates a form to delete a CompanyUsers entity.
     *
     * @param CompanyUsers $companyUser The CompanyUsers entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(CompanyUsers $companyUser)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('companyusers_delete', array('id' => $companyUser->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }



    public function emailNameAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $email_value = $request->request->get('email');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.email')
                ->from('InitialShippingBundle:User', 'a')
                ->where('a.email = :email_name')
                ->setParameter('email_name', $email_value)
                ->getQuery();
            $UserDetail = $query->getResult();

            $response = new JsonResponse();
            if (count($UserDetail) != 0) {
                $response->setData(array(
                    'email' => 1,
                    'status' => 1
                ));
            } else {
                $response->setData(array(
                    'email' => 0,
                    'status' => 1
                ));
            }
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }


    public function mobileNumberAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $mobile_number = $request->request->get('mobile');
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.mobile')
                ->from('InitialShippingBundle:User', 'a')
                ->where('a.mobile = :mobile_no')
                ->setParameter('mobile_no', $mobile_number)
                ->getQuery();
            $UserDetail = $query->getResult();

            $response = new JsonResponse();
            if (count($UserDetail) != 0) {
                $response->setData(array(
                    'mobile' => 1,
                    'status' => 1
                ));
            } else {
                $response->setData(array(
                    'mobile' => 0,
                    'status' =>1
                ));
            }
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }



    public function userShowAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $id = $request->request->get('Id');
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.username', 'a.email', 'a.roles', 'a.mobile', 'a.fullname','a.imagepath')
                ->from('InitialShippingBundle:User', 'a')
                ->where('a.id = :user_Id')
                ->setParameter('user_Id', $id)
                ->getQuery();
            $Userdetail = $query->getResult();

            $user1 = $this->getUser();
            $userId = $user1->getId();

            $response = new JsonResponse();
            $response->setData(array(
                'User_detail' => $Userdetail,
            ));
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }



    public function sample_editAction(Request $request)
    {
        $user = $this->getUser();
        if ($user != null) {
            $id = $request->request->get('Id');

            $em = $this->getDoctrine()->getManager();
            $userManager = $this->get('fos_user.user_manager');

            $user = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:User')->findOneBy(array('id' => $id));
            $userName = $request->request->get('name');
            $Email = $request->request->get('email');
            $mobile = $request->request->get('mobile');
            $fullname = $request->request->get('fullname');
            $roles = $request->request->get('privileges');

            $uservalue = new User();
            $user->setRoles(array($roles));
            $user->setemail($Email);
            $user->setusername($userName);
            $user->setmobile($mobile);
            $user->setfullname($fullname);
            $em->flush();
            $userManager->updateUser($user);
            $show_response = $this->userShowAction($request);

            return $show_response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }


    public function editallAction(Request $request)
    {
        $user = $this->getUser();
        $user1 = $this->getUser();
        if ($user != null) {
            $userId = $user1->getId();
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQueryBuilder()
                ->select('a.id', 'a.username', 'a.email', 'a.roles', 'a.mobile', 'a.fullname', 'a.imagepath')
                ->from('InitialShippingBundle:User', 'a')
                ->where('a.id = :user_Id')
                ->setParameter('user_Id', $userId)
                ->getQuery();
            $Userdetail = $query->getResult();
            $path = $Userdetail[0];
            $id = $request->request->get('edit_user_id');

            $em = $this->getDoctrine()->getManager();
            $userManager = $this->get('fos_user.user_manager');

            $user = $this->getDoctrine()->getManager()->getRepository('InitialShippingBundle:User')->findOneBy(array('id' => $id));
            $userName = $request->request->get('edit_username');
            $Email = $request->request->get('edit_email');
            $mobile = $request->request->get('edit_mobile');
            $fullname = $request->request->get('edit_fullname');
            $roles = $request->request->get('privileges');
            $userfileName = $request->request->get('userimage');
            $uploaddir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/userimages/';
            $fileName = '_' . basename($_FILES['userimage']['name']);
            if (!file_exists($uploaddir)) {
                mkdir($uploaddir, 0777, true);
            }
            $uploadfile = $uploaddir . '_' . basename($_FILES['userimage']['name']);
            if (move_uploaded_file($_FILES['userimage']['tmp_name'], $uploadfile)) {
                echo "File is valid, and was successfully uploaded.\n";
            }

            $value = '_';

            $uservalue = new User();

            $user->setRoles(array($roles));
            $user->setemail($Email);
            $user->setusername($userName);
            $user->setmobile($mobile);
            $user->setfullname($fullname);
            if ($fileName == $value) {


                $user->setImagepath($path['imagepath']);
            } else {
                $user->setImagepath($fileName);
            }

            $em->flush();

            $userManager->updateUser($user);


            return $this->redirectToRoute('dashboardhome');
        }else{
            return $this->redirectToRoute('fos_user_security_login');
        }

    }



    /**
     * Lists all ShipDetails entities.
     *
     * @Route("/user_pdf", name="userdetails_pdf")
     * @Method("POST")
     */
    public function userPdfAction(Request $request, $status = '')
    {
        $user = $this->getUser();
        $userId = $user->getId();
        if ($user != null) {
            $role = $this->container->get('security.context')->isGranted('ROLE_ADMIN');
            $em = $this->getDoctrine()->getManager();

            if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
                $query = $em->createQueryBuilder()
                    ->select('a')
                    ->from('InitialShippingBundle:User', 'a')
                    ->getQuery();
            } else {
                $query = $em->createQueryBuilder()
                    ->select('a')
                    ->from('InitialShippingBundle:User', 'a')
                    ->leftjoin('InitialShippingBundle:User', 'b', 'WITH', 'b.companyid = a.companyDetailsId')
                    ->where('b.id = :userId')
                    ->setParameter('userId', $userId)
                    ->getQuery();
            }
            $userDetails = $query->getResult();

            $pdfObject = $this->container->get('tfox.mpdfport')->getMPdf();
            $pdfObject->defaultheaderline = 0;
            //$pdfObject->defaultheaderfontstyle = 'B';
            $waterMarkImage = $this->container->getParameter('kernel.root_dir') . '/../web/images/pioneer_logo_02.png';
            $pdfObject->SetWatermarkImage($waterMarkImage);
            $pdfObject->showWatermarkImage = true;

            $userDesign =  $this->renderView('companyusers/show.html.twig', array(
                'userDetails' => $userDetails,
                'headerTitle' => 'User Details'
            ));

            $pdfObject->AddPage('', 4, '', 'on');
            $pdfObject->SetFooter('|{DATE l jS F Y h:i}| Page No: {PAGENO}');
            $pdfObject->WriteHTML($userDesign);

            $response = new Response();
            $content = $pdfObject->Output('', 'S');
            $response->setContent($content);
            $response->headers->set('Content-Type', 'application/pdf');
            return $response;
        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }

    }

  public function DocumentAction()
  {
      $user = $this->getUser();
      $userId =$user->getId();
      if ($user != null) {

      return $this->render('companyusers/index.html.twig', array(

      ));
          }else{
           return $this->redirectToRoute('fos_user_security_login');
      }
  }

    public function BackupAction(){
        $user = $this->getUser();
        $userId =$user->getId();
        if ($user != null) {
            return $this->render('companyusers/edit.html.twig', array(

            ));
        }else{
            return $this->redirectToRoute('fos_user_security_login');
        }
    }


    public function dbBackupAction(Request $request)
    {
        $user = $this->getUser();
        $userId = $user->getId();
        $em = $this->getDoctrine()->getManager();
        if ($user != null) {
            $userId = $user->getId();
            $em = $this->getDoctrine()->getManager();
            $date = new \DateTime();
            $activeMonth = $request->request->get('activeMonth');
            $activeYear = $request->request->get('activeYear');
            $inactiveMonth = $request->request->get('endMonth');
            $inactiveYear = $request->request->get('endYear');
            $checkboxvalue = $request->request->get('archiveStatus');
            $stringstart_date = '01-' . $activeMonth . '-' . $activeYear;
            $start_Dateformat = new \DateTime($stringstart_date);
            $start_Dateformat->modify('last day of this month');
            $stringend_date = '01-' . $inactiveMonth . '-' . $inactiveYear;
            $end_Dateformat = new \DateTime($stringend_date);
            $end_Dateformat->modify('last day of this month');
            $connection = $em->getConnection();
            $listoftables = array();
            $sm = $connection->getSchemaManager();
            $refConn = new \ReflectionObject($connection);
            $refParams = $refConn->getProperty('_params');
            $refParams->setAccessible('public');
            $params = $refParams->getValue($connection);

            $filelocation = $this->container->getParameter('kernel.root_dir') . '/../web/sqlfiles';
            if (!is_dir($filelocation)) {
                mkdir($filelocation);
            }
            $outfile_filepath = $filelocation . '/' . $params['dbname'] . '.sql';
            if (file_exists($outfile_filepath)) {
                unlink($outfile_filepath);
            }
            $tables = $sm->listTables();
            foreach ($tables as $table) {
                array_push($listoftables, $table->getName());
            }
            for ($tablecount = 0; $tablecount < count($listoftables); $tablecount++) {
                $tablename = $listoftables[$tablecount];
                if ($tablecount == 0) {
                    $command = 'mysqldump -u' . $params['user'] . ' -p' . $params['password'] . ' ' . $params['dbname'] . ' ' . $tablename . '  > ' . $outfile_filepath;

                } else {
                    if ($tablename == 'reading_kpi_values' || $tablename == 'scorecard__lookup_data' || $tablename == 'scorecard__lookup_status' || $tablename == 'scorecard_data_import' || $tablename == 'ranking_monthly_data' || $tablename == 'ranking__lookup_status' || $tablename == 'ranking__lookup_data' || $tablename == 'excel_file_details') {
                        $newstart_date = $start_Dateformat->format('Y-m-d');
                        $newend_date = $end_Dateformat->format('Y-m-d');
                        $whereconditon = "--where=\"monthdetail between '" . $newstart_date . "' and '" . $newend_date . "'\"";
                        $command = 'mysqldump -u' . $params['user'] . ' -p' . $params['password'] . ' ' . $params['dbname'] . ' ' . $tablename . ' ' . $whereconditon . '  >> ' . $outfile_filepath;

                    } else {
                        $command = 'mysqldump -u' . $params['user'] . ' -p' . $params['password'] . ' ' . $params['dbname'] . ' ' . $tablename . '  >> ' . $outfile_filepath;
                    }

                }
                system($command);
            }

            $content = file_get_contents($outfile_filepath);
            $response = new Response();
            $response->setContent($content);
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . $params['dbname'] . ".sql\"");

            $backup = new Backupreport();
            $backup->setfileName($outfile_filepath);
            $backup->setusername($user);
            $backup->setDateTime($date);
            $em->persist($backup);
            $em->flush();
            return $response;
        }
        else{
            return $this->redirectToRoute('fos_user_security_login');
        }
    }





    public function archivedReportShowAction(Request $request)
   {
    $user = $this->getUser();
    if ($user != null) {
        $em = $this->getDoctrine()->getManager();
        $backupReport = $em->createQueryBuilder()
            ->select('a.fileName,a.dateTime, a.username,a.id')
            ->from('InitialShippingBundle:Backupreport', 'a')
            ->getQuery()
            ->getResult();
        $response = new JsonResponse();
        $response->setData(array(

            'archivedReports' => $backupReport,
        ));
        return $response;

    } else {
        return $this->redirectToRoute('fos_user_security_login');
    }
   }

    public function DownloadAction(Request $request,Backupreport $backupreport)
    {
        $user = $this->getUser();
        if ($user != null) {
            $fileName_fromDb = $backupreport->getFileName();


            $directoryLocation = $this->container->getParameter('kernel.root_dir') . '/../web/sqlfiles';
            $filePath = $fileName_fromDb;
            $content = file_get_contents($filePath);
            $response = new Response();
            $response->setContent($content);
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . 'Database_backup'. ".sql\"");
            return $response;

        } else {
            return $this->redirectToRoute('fos_user_security_login');
        }
    }

}