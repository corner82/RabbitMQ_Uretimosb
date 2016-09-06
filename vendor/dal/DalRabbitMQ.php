<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace DAL;

/**
 * abstract DAL class for DAl layer base classes
 * @author Mustafa Zeynel Dağlı
 */
abstract class DalRabbitMQ extends AbstractDal
                                implements \Zend\ServiceManager\ServiceLocatorInterface {
    
    /**
     * service manager instance
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceManager;
    
    
    /**
     * implemented method from \DAL\DalInterface has been overriden
     * @param array $params
     * @author Mustafa Zeynel Dağlı
     * @since 16/01/2016
     */
    public function haveRecords($params = array()) {
        
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
    
    public function get($name) {
        
    }

    public function has($name) {
        
    }

}
