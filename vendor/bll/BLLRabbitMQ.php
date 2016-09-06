<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace BLL;

/**
 * abstract business layer class
 * extended from BLL\AbstractBLL
 * @author Mustafa Zeynel Dağlı
 */
abstract class BLLRabbitMQ extends \BLL\AbstractBLL implements 
                                            \DAL\DalManagerInterface, 
                                            \DAL\DalInterface{
    /**
     * DAL Manager instance
     * @var \Zend\ServiceManager\ServiceLocatorInterface 
     */
    protected $dalManager;
    
    /**
     * constructor
     */
    public function __construct() {
        
    }

    /**
     * get DAL Manager
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getDAlManager() {
        return $this->dalManager;
    }

    /**
     * set dal Manager
     * @param \Zend\ServiceManager\ServiceLocatorInterface $dalManager
     */
    public function setDalManager(\Zend\ServiceManager\ServiceLocatorInterface $dalManager) {
        $this->dalManager = $dalManager;
    }



}

