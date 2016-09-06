<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/RabbitMQ_SanalFabrika for the canonical source repository
 * @copyright Copyright (c) 2016 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

require_once '..\vendor\autoload.php';

use Utill\MQ\Receivers\UserPageEntryLogReceiver as Receiver;

$serviceManagerUtillConfigObject = new \Utill\Service\Manager\config();
$serviceManagerConfig = new \Zend\ServiceManager\Config(
        $serviceManagerUtillConfigObject->getConfig());
        $serviceManager = new \Zend\ServiceManager\ServiceManager($serviceManagerConfig);
$dalManagerConfigObject = new \DAL\DalManagerConfig();
        $managerConfig = new \Zend\ServiceManager\Config($dalManagerConfigObject->getConfig());
        $dalManager = new \DAL\DalManager($managerConfig);
        $dalManager->setService('sManager', $serviceManager);
        

$BLLManagerConfigObject = new \BLL\BLLManagerConfig;
        $managerConfig = new \Zend\ServiceManager\Config($BLLManagerConfigObject->getConfig());
        $bllManager = new \BLL\BLLManager($managerConfig);
        $bllManager->setDalManager($dalManager);
        

 
$worker = new Receiver();   

$worker->setServiceLocator($serviceManager);
$worker->setBLLManager($bllManager);
$worker->setDalManager($dalManager);

$worker->setQueueName(Receiver::PAGE_ENTRY_LOG_QUEUE_NAME);
$worker->setCallback('process');
 
$worker->listen();
