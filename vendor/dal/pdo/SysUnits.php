<?php

/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace DAL\PDO;

/**
 * Class using Zend\ServiceManager\FactoryInterface
 * created to be used by DAL MAnager
 * @
 * @author Okan CIRAN
 * @since 17.02.2016
 */
class SysUnits extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_units tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  17.02.2016
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = $this->getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $userIdValue = $userId ['resultSet'][0]['user_id'];
                $statement = $pdo->prepare(" 
                UPDATE sys_units
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . $userIdValue . "     
                WHERE id = :id");         
                $update = $statement->execute();
                $afterRows = $statement->rowCount();
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
            } else {
                $errorInfo = '23502';  /// 23502  not_null_violation
                $pdo->rollback();
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '');
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    } 

    /**
     * @author Okan CIRAN
     * @ sys_units tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  17.02.2016  
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $statement = $pdo->prepare("               
                SELECT 
                    a.id, 
                    a.main, 
                    a.sub,
                    COALESCE(NULLIF(a.system, ''), a.system_eng) AS systems,  
                    a.system_eng,  
		    COALESCE(NULLIF(a.abbreviation, ''), a.abbreviation_eng) AS abbreviations,  
                    a.abbreviation_eng,  
		    COALESCE(NULLIF(a.unitcode, ''), a.unitcode_eng) AS unitcodes,  
                    a.unitcode_eng,  
                    COALESCE(NULLIF(a.unit, ''), a.unit_eng) AS units,  
                    a.unit_eng,                 
                    a.deleted, 
                    sd15.description AS state_deleted,                 
                    a.active, 
                    sd16.description AS state_active, 
                    a.op_user_id,
                    u.username AS op_user_name,
                    a.language_code, 
                    a.language_id, 
                    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,               
                    a.language_parent_id
                FROM sys_units a
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0                  
                ORDER BY a.main, a.sub, systems
                                 ");
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ sys_units tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  17.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = $this->getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    } else {
                        $languageIdValue = 647;
                    }

                    $sql = "
                INSERT INTO sys_units(                       
                        main, 
                        sub, 
                        system_eng, 
                        unit_eng, 
                        abbreviation_eng, 
                        unitcode_eng, 
                        unit, 
                        system, 
                        unitcode, 
                        abbreviation, 
                        language_id, 
                        parent_id, 
                        op_user_id  
                        )
                VALUES (                        
                        :main, 
                        :sub, 
                        :system_eng, 
                        :unit_eng, 
                        :abbreviation_eng, 
                        :unitcode_eng, 
                        :unit, 
                        :system, 
                        :unitcode, 
                        :abbreviation, 
                        :language_id, 
                        :parent_id, 
                        :op_user_id   
                                             )   ";                    
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':main', $params['main'], \PDO::PARAM_INT);
                    $statement->bindValue(':sub', $params['sub'], \PDO::PARAM_INT);
                    $statement->bindValue(':system_eng', $params['system_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':abbreviation_eng', $params['abbreviation_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':unit', $params['unit'], \PDO::PARAM_STR);
                    $statement->bindValue(':system', $params['system'], \PDO::PARAM_STR);
                    $statement->bindValue(':unitcode', $params['unitcode'], \PDO::PARAM_STR);
                    $statement->bindValue(':abbreviation', $params['abbreviation'], \PDO::PARAM_STR);
                    $statement->bindValue(':parent_id', $params['parent_id'], \PDO::PARAM_INT);                       
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);                    
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_units_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23505';
                    $errorInfoColumn = 'group_name';
                    $pdo->rollback();                    
                    return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);                    
                }
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pk';
                $pdo->rollback();
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ sys_units tablosunda user_id li consultant daha önce kaydedilmiş mi ?  
     * @version v 1.0 15.01.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveRecords($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $addSql = "";
            if (isset($params['id'])) {
                $addSql = " AND a.id != " . intval($params['id']) . " ";
            }
            $sql = " 
            SELECT  
                 a.property_value   AS name , 
                 " . $params['property_value '] . " AS value , 
                 1 =1 AS control,
                 CONCAT(mt.machine_tool_name ,' - ', mtpd.property_name ,': ',a.property_value  ,' ',su.unitcode,  ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_units  a
            INNER JOIN sys_machine_tools mt ON mt.id = a.machine_tool_id 
            INNER JOIN sys_units su ON su.id = a.unit_id AND su.active = 0 AND su.deleted =0 AND su.language_id = a.language_id                          
            INNER JOIN sys_machine_tool_property_definition mtpd ON mtpd.id = a.machine_tool_property_definition_id                          
            WHERE a.machine_tool_id =  " . intval($params['machine_tool_id']) . "            
            AND a.machine_tool_property_definition_id =" . intval($params['machine_tool_property_definition_id']) . "
            AND a.unit_id = " . intval($params['unit_id']) . "
            AND a.property_value = " . floatval($params['property_value']) . "
            " . $addSql . " 
            AND a.deleted =0    
                               ";
            $statement = $pdo->prepare($sql);
            //   echo debugPDO($sql, $params);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * sys_units tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  17.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = $this->getUserId(array('pk' => $params['pk'], 'id' => $params['id']));
            if (!\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];
                $kontrol = $this->haveRecords($params);
                if (\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    } else {
                        $languageIdValue = 647;
                    }

                    $sql = "
                UPDATE sys_units
                SET   
                    main = :main, 
                    sub  = :sub, 
                    system_eng  = :system_eng, 
                    unit_eng = :unit_eng, 
                    abbreviation_eng = :abbreviation_eng, 
                    unitcode_eng  = :unitcode_eng, 
                    unit  = :unit, 
                    system  = :system, 
                    unitcode  = :unitcode, 
                    abbreviation  = :abbreviation, 
                    language_id = :language_id, 
                    parent_id = :parent_id, 
                    op_user_id = :op_user_id,
                    active = :active
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':main', $params['main'], \PDO::PARAM_INT);
                    $statement->bindValue(':sub', $params['sub'], \PDO::PARAM_INT);
                    $statement->bindValue(':system_eng', $params['system_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':abbreviation_eng', $params['abbreviation_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':unit', $params['unit'], \PDO::PARAM_STR);
                    $statement->bindValue(':system', $params['system'], \PDO::PARAM_STR);
                    $statement->bindValue(':unitcode', $params['unitcode'], \PDO::PARAM_STR);
                    $statement->bindValue(':abbreviation', $params['abbreviation'], \PDO::PARAM_STR);
                    $statement->bindValue(':parent_id', $params['parent_id'], \PDO::PARAM_INT);                       
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);   
                    $statement->bindValue(':active', $params['active'], \PDO::PARAM_INT);                    
                    $update = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    // 23505 	unique_violation
                    $errorInfo = '23505'; 
                    $errorInfoColumn = 'machine_tool_id,machine_tool_property_definition_id,unit_id,property_value';
                     $pdo->rollback();                  
                    return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pk';
                 $pdo->rollback();
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_units tablosundan kayıtları döndürür !!
     * @version v 1.0  17.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGrid($args = array()) {
        if (isset($args['page']) && $args['page'] != "" && isset($args['rows']) && $args['rows'] != "") {
            $offset = ((intval($args['page']) - 1) * intval($args['rows']));
            $limit = intval($args['rows']);
        } else {
            $limit = 10;
            $offset = 0;
        }

        $sortArr = array();
        $orderArr = array();
        if (isset($args['sort']) && $args['sort'] != "") {
            $sort = trim($args['sort']);
            $sortArr = explode(",", $sort);
            if (count($sortArr) === 1)
                $sort = trim($args['sort']);
        } else {
            $sort = "a.main, a.sub, systems";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
            if (count($orderArr) === 1)
                $order = trim($args['order']);
        } else { 
            $order = "ASC";
        }
        $languageId = SysLanguage::getLanguageId(array('language_code' => $args['language_code']));
        if (\Utill\Dal\Helper::haveRecord($languageId)) {
            $languageIdValue = $languageId ['resultSet'][0]['id'];
        } else {
            $languageIdValue = 647;
        }
        $whereSql = " AND a.language_id = " . intval($languageIdValue);
 
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $sql = "
                 SELECT 
                    a.id, 
                    a.main, 
                    a.sub,
                    COALESCE(NULLIF(a.system, ''), a.system_eng) AS systems,  
                    a.system_eng,  
		    COALESCE(NULLIF(a.abbreviation, ''), a.abbreviation_eng) AS abbreviations,  
                    a.abbreviation_eng,  
		    COALESCE(NULLIF(a.unitcode, ''), a.unitcode_eng) AS unitcodes,  
                    a.unitcode_eng,  
                    COALESCE(NULLIF(a.unit, ''), a.unit_eng) AS units,  
                    a.unit_eng,                 
                    a.deleted, 
                    sd15.description AS state_deleted,                 
                    a.active, 
                    sd16.description AS state_active, 
                    a.op_user_id,
                    u.username AS op_user_name,
                    a.language_code, 
                    a.language_id, 
                    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,               
                    a.language_parent_id
                FROM sys_units a
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0                                  
                WHERE a.deleted =0 
                " . $whereSql . "
                ORDER BY    " . $sort . " "
                    . "" . $order . " "
                    . "LIMIT " . $pdo->quote($limit) . " "
                    . "OFFSET " . $pdo->quote($offset) . " ";
            $statement = $pdo->prepare($sql);
            $parameters = array(
                'sort' => $sort,
                'order' => $order,
                'limit' => $pdo->quote($limit),
                'offset' => $pdo->quote($offset),
            );
            //   echo debugPDO($sql, $parameters);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**
     * user interface datagrid fill operation get row count for widget
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_units tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  17.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');    
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }
            $whereSql = " WHERE a.deleted =0 AND a.language_id = " . intval($languageIdValue);
            
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT  
                FROM sys_units a
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0  
                " . $whereSql . "               
                    ";
            $statement = $pdo->prepare($sql);
           // echo debugPDO($sql, $params);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

 
    public function getUnits($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }

            $whereSql = " WHERE a.active =0 AND a.deleted = 0 AND a.language_id = ". intval($languageIdValue); 
            
            if (isset($params['main']) && $params['main'] != "") {
                $whereSql .= " AND a.main = " . intval($params['main']) .
                             " AND a.sub IS NOT NULL ";
            } else {
                $whereSql .= " AND a.sub IS NULL ";
            }

            $sql = "
               SELECT 
                    a.id,                   
                    COALESCE(NULLIF(su.system, ''), a.system_eng) AS systems,  
                    a.system_eng,  
		    COALESCE(NULLIF(su.abbreviation, ''), a.abbreviation_eng) AS abbreviations,  
                    a.abbreviation_eng,  
		    COALESCE(NULLIF(su.unitcode, ''), a.unitcode_eng) AS unitcodes,  
                    a.unitcode_eng,  
                    COALESCE(NULLIF(su.unit, ''), a.unit_eng) AS units,  
                    a.unit_eng 
                FROM sys_units a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =".$languageIdValue." AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_units su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id                
                " . $whereSql . "                
                ORDER BY a.main, a.sub, systems,unitcodes            
                                 ";
            $statement = $pdo->prepare($sql);            
        //    echo debugPDO($sql, $params);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

 
 
    public function fillUnitsTree($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }

            $whereSql = " WHERE a.active =0 AND a.deleted = 0 " ; 
            
            if (isset($params['id']) && $params['id'] != "") {
                $whereSql .= " AND a.parent_id  = " . intval($params['id']) ;
                             
            } else {
                $whereSql .= "  AND a.parent_id = 0 ";
            }

            $sql = "
               SELECT 
                    a.id,                                     
		    CASE 
                        a.parent_id    
                            WHEN 0 THEN COALESCE(NULLIF(su.system, ''), a.system_eng)  
                            ELSE COALESCE(NULLIF(su.unitcode, ''), a.unitcode_eng) 
                    END AS unitcodes,
                    CASE 
                        a.parent_id    
                            WHEN 0 THEN a.system_eng  
                            ELSE a.unitcode_eng
                    END AS unitcodes_eng,
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_units ax WHERE ax.parent_id = a.id AND ax.deleted = 0)    
                            WHEN 1 THEN 'closed'
                            ELSE 'open'   
                    END AS state_type  
                FROM sys_units a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_units su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id                
                " . $whereSql . "                
                ORDER BY a.id            
                                 ";
            $statement = $pdo->prepare($sql);            
       //echo debugPDO($sql, $params);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    
    public function fillUnitsTreeRtc($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }

            $whereSql = " WHERE a.active =0 AND a.deleted = 0 " ; 
            
            if (isset($params['id']) && $params['id'] != "") {
                $whereSql .= " AND a.parent_id  = " . intval($params['id']) ;
                             
            } else {
                $whereSql .= "  AND a.parent_id = 0 ";
            }

            $sql = "
               SELECT 
                    COUNT(a.id ) as COUNT 
                FROM sys_units a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_units su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id                
                " . $whereSql . "                
                       
                                 ";
            $statement = $pdo->prepare($sql);            
           //  echo debugPDO($sql, $params);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

}
