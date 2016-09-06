<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Utill\Factories\ResultSetFactories;

/**
 * base factory class for resultset helpers
 * @author Mustafa Zeynel Dağlı
 * @since 11/02/2016
 */
class LogWriterFactory extends \Utill\Factories\AbstractFactory {
    
    /**
     * constructor function 
     */
    public function __construct() {
        
    }

    /**
     * 
     * @param string || null $identifier
     * @param array || null $params
     * @return type \Utill\Log\AbstractWriter
     */
    protected function getUtility($identifier = null, $params = null) {
        if(method_exists($this,
                        $identifier)) {
            return $this->$identifier($params);
        }
    }
    
    /**
     * get log database writer
     * @param array || null $params
     * @return \Utill\Log\DbWriter
     */
    private function dbWrite($params = null) {
        $dbWriter = new \Utill\Log\DbWriter();
        $dbWriter->setDalManager($params['dalManager']);
        return $dbWriter;
    }

}
