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
class SysMachineToolProperties extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_machine_tool_properties tablosundan parametre olarak  gelen id kaydını siler. !!
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
                UPDATE sys_machine_tool_properties
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . $userIdValue . "     
                WHERE id = :id");
                //Execute our DELETE statement.
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
     * @ sys_machine_tool_properties tablosundaki tüm kayıtları getirir.  !!
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
                    a.machine_tool_id, 
                    COALESCE(NULLIF(mt.machine_tool_name, ''), mt.machine_tool_name_eng) AS machine_tool_names,  
                    mt.machine_tool_name_eng,             
                    a.machine_tool_property_definition_id, 
                    COALESCE(NULLIF(mtpd.property_name, ''), mtpd.property_name_eng) AS property_names,  
                    mtpd.property_name_eng,
                    a.property_value, 
                    a.unit_id,  			
                    COALESCE(NULLIF(su.unitcode, ''), su.unitcode_eng) AS unitcodes,               
                    su.unitcode_eng,
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
                FROM sys_machine_tool_properties a
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0  
                INNER JOIN sys_machine_tools mt ON mt.id = a.machine_tool_id 
                INNER JOIN sys_units su ON su.id = a.unit_id AND su.active = 0 AND su.deleted =0 AND su.language_id = a.language_id                          
                INNER JOIN sys_machine_tool_property_definition mtpd ON mtpd.id = a.machine_tool_property_definition_id
                ORDER BY machine_tool_names, property_names,unitcodes
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
     * @ sys_machine_tool_properties tablosuna yeni bir kayıt oluşturur.  !!
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
                INSERT INTO sys_machine_tool_properties(
                 machine_tool_id, 
                        machine_tool_property_definition_id, 
                        property_value,
                        unit_id,                         
                        op_user_id,
                        language_id        
                        )
                VALUES (
                        :machine_tool_id, 
                        :machine_tool_property_definition_id, 
                        ".  floatval($params['property_value']).",
                        :unit_id,                         
                        :op_user_id,                         
                        :language_id  
                                             )   ";                    
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':machine_tool_id', $params['machine_tool_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':machine_tool_property_definition_id', $params['machine_tool_property_definition_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':unit_id', $params['unit_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);                    
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_machine_tool_properties_id_seq');
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
     * @ sys_machine_tool_properties tablosunda user_id li consultant daha önce kaydedilmiş mi ?  
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
            FROM sys_machine_tool_properties  a
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
     * sys_machine_tool_properties tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
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
                UPDATE sys_machine_tool_properties
                SET   
                       machine_tool_id = :machine_tool_id, 
                       machine_tool_property_definition_id = :machine_tool_property_definition_id, 
                       property_value =  ". floatval($params['property_value']).",
                       unit_id = :unit_id,                         
                       op_user_id = :op_user_id,
                       language_id = :language_id,
                       active = :active
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':machine_tool_id', $params['machine_tool_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':machine_tool_property_definition_id', $params['machine_tool_property_definition_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':unit_id', $params['unit_id'], \PDO::PARAM_INT);
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
     * @ Gridi doldurmak için sys_machine_tool_properties tablosundan kayıtları döndürür !!
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
            $sort = "machine_tool_names, property_names,unitcodes ";
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
                    a.machine_tool_id, 
                    COALESCE(NULLIF(mt.machine_tool_name, ''), mt.machine_tool_name_eng) AS machine_tool_names,  
                    mt.machine_tool_name_eng,             
                    a.machine_tool_property_definition_id, 
                    COALESCE(NULLIF(mtpd.property_name, ''), mtpd.property_name_eng) AS property_names,  
                    mtpd.property_name_eng,
                    a.property_value, 
                    a.unit_id,  			
                    COALESCE(NULLIF(su.unitcode, ''), su.unitcode_eng) AS unitcodes,               
                    su.unitcode_eng,
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
                FROM sys_machine_tool_properties a
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0  
                INNER JOIN sys_machine_tools mt ON mt.id = a.machine_tool_id 
                INNER JOIN sys_units su ON su.id = a.unit_id AND su.active = 0 AND su.deleted =0 AND su.language_id = a.language_id                          
                INNER JOIN sys_machine_tool_property_definition mtpd ON mtpd.id = a.machine_tool_property_definition_id             
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
     * @ Gridi doldurmak için sys_machine_tool_properties tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
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
                FROM sys_machine_tool_properties a
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0  
                INNER JOIN sys_machine_tools mt ON mt.id = a.machine_tool_id 
                INNER JOIN sys_units su ON su.id = a.unit_id AND su.active = 0 AND su.deleted =0 AND su.language_id = a.language_id                          
                INNER JOIN sys_machine_tool_property_definition mtpd ON mtpd.id = a.machine_tool_property_definition_id             
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

 
   /**
     * user interface fill operation   
     * @author Okan CIRAN
     * @ tree doldurmak için sys_machine_tool tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  19.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMachineToolFullProperties($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }
            $parentId = 0;
            if (isset($params['id']) && $params['id'] != "") {
                $parentId = $params['id'];
            }
            
            $whereSql = " WHERE 
                               a.deleted =0 AND 
                               a.active = 0 AND
                               a.language_parent_id = 0 AND 
                               a.parent_id = " . intval($parentId)." AND
                               a.language_id = " . intval($languageIdValue);
                               
            $sql = "                
                SELECT 
                    a.id,
                    COALESCE(NULLIF(a.property_name, ''), a.property_name_eng) AS property_names,  
                    a.property_name_eng ,
                    CASE 
                    (SELECT DISTINCT 1 state_type FROM sys_machine_tool_property_definition ax WHERE ax.parent_id = a.id AND ax.deleted = 0)    
                     WHEN 1 THEN 'closed'
                     ELSE 'open'   
                     END AS state_type  
                FROM sys_machine_tool_property_definition  a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  	                                
                ".$whereSql ."   
                ORDER BY property_names               
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

    /**
     * user interface datagrid fill operation get row count for widget
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_machine_tool_properties tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  17.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMachineToolFullPropertiesRtc($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');    
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }
              $parentId = 0;
            if (isset($params['id']) && $params['id'] != "") {
                $parentId = $params['id'];
            }
            $whereSql = " WHERE 
                                a.deleted =0 AND 
                                a.active = 0 AND
                                a.language_parent_id = 0 AND 
                                a.parent_id = " . intval($parentId)." AND 
                                a.language_id = " . intval($languageIdValue);                               
                     
            $sql = "
                 SELECT 
                     COUNT(a.id) AS COUNT               
                FROM sys_machine_tool_property_definition  a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  	                                
                ".$whereSql."
                             
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

}
