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
 * Business Layer class for report Configuration entity
 */
class InfoFirmProfile extends \BLL\BLLSlim {

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
        $DAL = $this->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->insert($params);
    }

    /**
     * Data update function
     * @param array | null $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->update($params);
    }

    /**
     * Data delete function
    * @param array | null $params
     * @return array
     */
    public function delete($params = array()) {
        $DAL = $this->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array | null $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->getAll($params);
    }

    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid($params = array()) {
        $DAL = $this->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->fillGrid($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);
        return $resultSet['resultSet'];
    }

    /**
     * Data delete action function
     * @param array | null $params
     * @return array
     */
    public function deletedAct( $params = array()) {
        $DAL = $this->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->deletedAct($params);
    }

    /**
     * Function to fill combobox on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillComboBox($params = array()) {
        $DAL = $this->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->fillComboBox($params);
        return $resultSet['resultSet'];
    }

    /**
     * Data insert function (active languages)
     * @param array | null $params
     * @return array
     */
    public function insertLanguageTemplate($params = array()) {
        $DAL = $this->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->insertLanguageTemplate($params);
        return $resultSet['resultSet'];
    }

   /**
     * Function to fill text on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillTextLanguageTemplate($params = array()) {
        $DAL = $this->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->fillTextLanguageTemplate($params);
        return $resultSet['resultSet'];
    }
    
    
     /**
     * Data insert function
     * @param array | null $params
     * @return array
     */
    public function insertTemp($params = array()) {
        $DAL = $this->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->insertTemp($params);
    }

}
