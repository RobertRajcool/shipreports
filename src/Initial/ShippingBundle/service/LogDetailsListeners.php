<?php
/**
 * Created by PhpStorm.
 * User: lawrance
 * Date: 2/9/16
 * Time: 5:35 PM
 */

namespace Initial\ShippingBundle\service;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Initial\ShippingBundle\Entity\LogDetails;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PDO;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Symfony\Component\HttpFoundation\Session\Session;


class LogDetailsListeners
{

    private $container;
    private $session = null;
    private $dbHost;
    private $dbUser;
    private $dbPassword;
    private $dbName;


    public function __construct(ContainerInterface $container, Session $session, $dbHost, $dbUser, $dbPassword,$dbName) {
        $this->container = $container;
        $this->session = $session;
        $this->dbHost = $dbHost;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbName=$dbName;
    }
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $entityReflected = new \ReflectionObject($entity);
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $classAnnotations = $reader->getClassAnnotations($entityReflected);
        $changeSets = $eventArgs->getEntityChangeSet();
        $table = get_class($entity);
        $tablePK = $entity->getId();
        $securityContext = $this->container->get('security.context');
        $token = $securityContext->getToken();
        $user = $token->getUser();
        $userId = $user->getId();
        $this->logFieldUpdate($changeSets, $table, $tablePK, $userId);

    }
    private function logFieldUpdate($changeSets, $table, $tablePK, $userId)
    {
        $config = new Configuration();

        $pdo = new PDO("mysql:host=" . $this->dbHost . ";dbname=". $this->dbName, $this->dbUser, $this->dbPassword);
        $params = array('pdo_mysql', $this->dbUser, $this->dbPassword);
        $params['pdo'] = $pdo;
        $connection = DriverManager::getConnection($params, $config);
        $date = new \DateTime();
        foreach ($changeSets as $field => $values) {
            if (gettype($values[0]) != "object" && gettype($values[1]) != "object" && $values[0] != $values[1]) {
                $connection->insert('log_details', array('CreatedOnDateTime' => $date->format('Y-m-d H:i:s'), 'CreatedByID' => $userId, 'TableName' => $table, 'fieldName' => $field, 'TablePKID' => $tablePK, 'oldvalue' => $values[0], 'newvalue' => $values[1]));
            }
        }
        $connection->close();
        $pdo = null;  // Proper way to close a PDO connection: http://www.php.net/manual/en/pdo.connections.php
    }
}