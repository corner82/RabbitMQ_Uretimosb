<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace BLL\BLL;

/**
 * Business Layer class for operation type tools
 * @author Mustafa Zeynel Dağlı
 * @since 11/02/2016
 */
class SysOperationTypesTools extends \BLL\BLLSlim {

    /**
     * constructor
     */
    public function __construct() {
        //parent::__construct();
    }

    /**
     * Data insert function
     * @param array | null $params
     * @return array
     */
    public function insert($params = array()) {
        $DAL = $this->getDALManager()->get('sysOperationTypesToolsPDO');
        return $DAL->insert($params);
    }

    /**
     * Data update function
     * @param array $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->getDALManager()->get('sysOperationTypesToolsPDO');
        return $DAL->update($params);
    }

    /**
     * Data delete function
     * @param array $params
     * @return array
     */
    public function delete($params = array()) {
        $DAL = $this->getDALManager()->get('sysOperationTypesToolsPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->getDALManager()->get('sysOperationTypesToolsPDO');
        return $DAL->getAll($params);
    }

    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid($params = array()) {

        $DAL = $this->getDALManager()->get('sysOperationTypesToolsPDO');
        $resultSet = $DAL->fillGrid($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->getDALManager()->get('sysOperationTypesToolsPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);
        return $resultSet['resultSet'];
    }
 
   /**
     * Function to fill operation types on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillConsultantOperationsTools($params = array()) {
        $DAL = $this->getDALManager()->get('sysOperationTypesToolsPDO');
        $resultSet = $DAL->fillConsultantOperationsTools($params);  
        return $resultSet['resultSet'];
    }
    
  

}
