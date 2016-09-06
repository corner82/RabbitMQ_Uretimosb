<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/RabbitMQ_SanalFabrika for the canonical source repository
 * @copyright Copyright (c) 2016 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Utill\MQ\Receivers;

 

 class AbstractReceiver implements \DAL\DalManagerInterface,
                                    \BLL\BLLManagerInterface,
                                    \Zend\ServiceManager\ServiceLocatorAwareInterface{
    
     const QUEUE_NAME = 'userLogin2_queue'; 
     const PAGE_ENTRY_LOG_QUEUE_NAME = 'userPageEntryLog2_queue';
     const SERVICE_ENTRY_LOG_QUEUE_NAME = 'serviceEntryLog2_queue'; 
     
    /**
     * connection user
     * @var string
     */
    protected $user = 'guest';
    
    /**
     * connection password
     * @var string
     */
    protected $password = 'guest';
    
    /**
     * connection port
     * @var integer
     */
    protected $port = 5672;
    
    /**
     * connection server
     * @var string
     */
    protected $server = 'localhost';
    
    /**
     * durable, make sure that RabbitMQ will never lose our queue if a crash occurs
     * @var boolean
     */
    protected $durable = true;
    
    /**
     * auto delete - the queue is deleted when all consumers have finished using it
     * @var boolean
     */
    protected $autoDelete = false;
    
    /**
     * queue passive or not
     * @var boolean
     */
    protected $queuePassive = false;


    /**
     * Rabbit channel queue name
     * @var string
     */
    protected $queueName;
    
    /**
     * consumer tag - Identifier for the consumer, valid within the current channel. just string
     * @var string
     */
    protected $consumerTag = '';
    
    /**
     * no local - TRUE: the server will not send messages to the connection that published them
     * @var boolean
     */
    protected $noLocal = false;
    
    /**
     * no ack, false - acks turned on, true - off.  send a proper acknowledgment from the worker, once we're done with a task
     * @var boolean
     */
    protected $noAck = false;
    
    /**
     * exclusive - queues may only be accessed by the current connection
     * @var boolean
     */
    protected $exclusive = false;
    
    /**
     * no wait - TRUE: the server will not respond to the method. The client should not wait for a reply method
     * @var boolean
     */
    protected $noWait = false;
    
    /**
     * callback function for message queue
     * @var string
     */
    protected $queueCallBack;
    
    /**
     * prefetch size - prefetch window size in octets, null meaning "no specific limit"
     * @var integer || null
     */
    protected $preFetchSize = null;
    
    /**
     * prefetch count - prefetch window in terms of whole messages
     * @var integer
     */
    protected $preFetchCount = 1;
    
    /**
     * global - global=null to mean that the QoS settings should apply per-consumer, 
     * global=true to mean that the QoS settings should apply per-channel
     * @var boolean || null
     */
    protected $global = null;
    
    /**
     * data access layer manager
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $dalManager;
    
    /**
     * data access layer manager
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $bllManager;
    
    /**
     * service manager instance
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceManager;


    /**
     * set channel user
     * @param type string
     */
    public function setUser($user) {
        $this->user = $user;
    }
    
    /**
     * return channel user
     * @return string
     */
    public function getUser() {
        return $this->user;
    }
    
    /**
     * set channel user password
     * @param type string
     */
    public function setPassword($password) {
        $this->password = $password;
    }
    
    /**
     * return channel user password
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }
    
    /**
     * set messaging server
     * @param type string
     */
    public function setServer($server) {
        $this->server = $server;
    }
    
    /**
     * return messaging server
     * @return string
     */
    public function getServer() {
        return $this->server;
    }
    
    /**
     * set messaging server port
     * @param type integer
     */
    public function setPort($port) {
        $this->port = $port;
    }
    
    /**
     * return messaging server port
     * @return integer
     */
    public function getPort() {
        return $this->port;
    }
    
    /**
     * set messaging queue name
     * @param type string
     */
    public function setQueueName($queueName) {
        $this->queueName = $queueName;
    }
    
    /**
     * return messaging queue name
     * @return string
     */
    public function getQueueName() {
        return $this->queueName;
    }
    
    /**
     * set messaging queue passive
     * @param type boolean
     */
    public function setQueuePassive($queuePassive) {
        $this->queuePassive = $queuePassive;
    }
    
    /**
     * return messaging queue passive
     * @return boolean
     */
    public function getQueuePassive() {
        return $this->queuePassive;
    }
    
    /**
     * set messaging queue durable or not
     * @param type boolean
     */
    public function setDurable($durable) {
        $this->durable = $durable;
    }
    
    /**
     * return messaging queue durable or not
     * @return boolean
     */
    public function getDurable() {
        return $this->durable;
    }
    
    /**
     * set messaging queue exclusive
     * @param type boolean
     */
    public function setExclusive($exclusive) {
        $this->exclusive = $exclusive;
    }
    
    /**
     * return messaging queue exclusive
     * @return boolean
     */
    public function getExclusive() {
        return $this->exclusive;
    }
    
    /**
     * set messaging queue autodelete or not
     * @param type boolean
     */
    public function setAutoDelete($autoDelete) {
        $this->autoDelete = $autoDelete;
    }
    
    /**
     * return messaging queue autodelete or not
     * @return boolean
     */
    public function getAutoDelete() {
        return $this->autoDelete;
    }
    
    /**
     * set messaging queue no local
     * @param type boolean
     */
    public function setNoLocal($noLocal) {
        $this->noLocal = $noLocal;
    }
    
    /**
     * return messaging queue no local
     * @return boolean
     */
    public function getNoLocal() {
        return $this->noLocal;
    }
    
    /**
     * set messaging queue no ack
     * @param type boolean
     */
    public function setNoAck($noAck) {
        $this->noAck = $noAck;
    }
    
    /**
     * return messaging queue no ack
     * @return boolean
     */
    public function getNoAck() {
        return $this->noAck;
    }
    
    /**
     * set messaging queue no wait
     * @param type boolean
     */
    public function setNoWait($noWait) {
        $this->noWait = $noWait;
    }
    
    /**
     * return messaging queue no wait
     * @return boolean
     */
    public function getNoWait() {
        return $this->noWait;
    }
    
    /**
     * set messaging queue callback function name 
     * @param type string
     */
    public function setCallback($callback) {
        $this->queueCallBack = $callback;
    }
    
    /**
     * return messaging queue callback function name
     * @return boolean
     */
    public function getCallback() {
        return $this->queueCallBack;
    }
    
    /**
     * set messaging queue prefetch size
     * @param type integer
     */
    public function setPreFetchSize($preFetchSize) {
        $this->preFetchSize = $preFetchSize;
    }
    
    /**
     * return messaging queue prefetch size
     * @return integer || null
     */
    public function getPreFetchSize() {
        return $this->preFetchSize;
    }
    
    /**
     * set messaging queue prefetch count
     * @param type integer
     */
    public function setPreFetchCount($preFetchCount) {
        $this->preFetchCount= $preFetchCount;
    }
    
    /**
     * return messaging queue prefetch count
     * @return integer
     */
    public function getPreFetchCount() {
        return $this->preFetchCount;
    }
    
    /**
     * set messaging queue attributes global(for every consumer)
     * @param type boolean
     */
    public function setGlobal($global) {
        $this->global= $global;
    }
    
    /**
     * return messaging queue attributes global(for every consumer)
     * @return boolean || null
     */
    public function getGlobal() {
        return $this->global;
    }

    /**
     * return DAL Manager
     * @return \DAL\DalManager $dalManager
     */
    public function getDAlManager() {
        return $this->dalManager;
    }

    /**
     * set DAL Manager
     * @param \Zend\ServiceManager\ServiceLocatorInterface $dalManager
     */
    public function setDalManager(\Zend\ServiceManager\ServiceLocatorInterface $dalManager) {
        $this->dalManager = $dalManager;
    }

    /**
     * get BLL manager
     * @return \Zend\ServiceManager\ServiceLocatorInterfac
     */
    public function getBLLManager() {
        return $this->bllManager;
    }

    /**
     * set BLL manager
     * @param \Zend\ServiceManager\ServiceLocatorInterface $BLLManager
     */
    public function setBLLManager(\Zend\ServiceManager\ServiceLocatorInterface $bllManager) {
        $this->bllManager = $bllManager;
    }

    /**
     * get service manager
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator() {
        return $this->serviceManager;
    }

    /**
     * ser service manager
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        $this->serviceManager = $serviceLocator;
    }

}

