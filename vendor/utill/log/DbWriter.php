<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/sanalfabrika for the canonical source repository
 * @copyright Copyright (c) 2016 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Utill\Log;

/**
 * log writer for database
 * @author Mustafa Zeynel Dağlı
 * @version 0.2  11/03/2016
 */
class DbWriter extends AbstractWriter implements 
                                    \DAL\DalManagerInterface{
    
    /**
     * DAL Manager instance
     * @var \Zend\ServiceManager\ServiceLocatorInterface 
     */
    protected $dalManager;

    /**
     * write log to database
     * @param array || null $params
     */
    public function write($params = null) {
        try {
            $logProcesser = $this->getBLLManager()->get('logConnectionBLL');
            $logProcesser->insert(array('pk'=>$params['pk'], 
                                        'type_id'=>$params['type_id']));
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    /**
     * get DAL Manager
     * @return \Zend\ServiceManager\ServiceLocatorInterface 
     */
    public function getDAlManager() {
        return $this->dalManager;
    }

    /**
     * set Dal Manager
     * @param \Zend\ServiceManager\ServiceLocatorInterface $dalManager
     */
    public function setDalManager(\Zend\ServiceManager\ServiceLocatorInterface $dalManager) {
        $this->dalManager = $dalManager;
    }

}
