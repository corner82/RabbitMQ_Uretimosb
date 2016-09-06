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
 * Business layer manager,
 * extended from Zend\ServiceManager\ServiceManager
 * @author Mustafa Zeynel Dağlı 
 */
class BLLManager extends \Zend\ServiceManager\ServiceManager implements
                                                    \DAL\DalManagerInterface{
    
    /**
     * DAL Manager instance
     * @var \Zend\ServiceManager\ServiceLocatorInterface 
     */
    protected $dalManager;


                                                        /**
     * constructor
     * @param \Zend\ServiceManager\ConfigInterface $config
     */
    public function __construct(\Zend\ServiceManager\ConfigInterface $config = null) {
        parent::__construct($config);
    }
    
    /**
     * Attempt to create an instance via an invokable class
     * overriden Zend\ServiceManager\ServiceManager 'createFromInvokable' func.
     * @param  string $canonicalName
     * @param  string $requestedName
     * @return null|\stdClass
     * @throws Exception\ServiceNotFoundException If resolved class does not exist
     */
    protected function createFromInvokable($canonicalName, $requestedName)
    {
        //print_r($canonicalName);
        $invokable = $this->invokableClasses[$canonicalName];
        if (!class_exists($invokable)) {
            throw new Exception\ServiceNotFoundException(sprintf(
                '%s: failed retrieving "%s%s" via invokable class "%s"; class does not exist',
                get_class($this) . '::' . __FUNCTION__,
                $canonicalName,
                ($requestedName ? '(alias: ' . $requestedName . ')' : ''),
                $invokable
            ));
        }
        $instance = new $invokable;
        $instance->setDalManager($this->getDAlManager());
        return $instance;
    }

    /**
     * 
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getDAlManager(){
        return $this->dalManager;
    } 
    
    /**
     * set DAL Manager
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceManager
     */
    public function setDalManager(\Zend\ServiceManager\ServiceLocatorInterface $dalManager) {
        $this->dalManager = $dalManager;
    }
    
    
}
