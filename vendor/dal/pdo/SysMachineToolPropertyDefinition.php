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
class SysMachineToolPropertyDefinition extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_machine_tool_property_definition tablosundan parametre olarak  gelen id kaydını siler. !!
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
                UPDATE sys_machine_tool_property_definition
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
     * @ sys_machine_tool_property_definition tablosundaki tüm kayıtları getirir.  !!
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
                        a.machine_tool_grup_id,
                        tg.group_name AS tool_group_name,  
                        tg.group_name_eng AS tool_group_name_eng,  
                        a.property_name ,
                        a.property_name_eng,          
                        a.unit_grup_id, 
                        COALESCE(NULLIF(su.system, ''), su.system_eng) AS unit_group_name,  
                        a.algorithmic_id,   
                        sd18.description AS state_algorithmic,                		                   
                        a.deleted, 
                        sd15.description AS state_deleted,                 
                        a.active, 
                        sd16.description AS state_active, 
                        a.language_code, 
                        a.language_id, 
			COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,               
			a.language_parent_id,                     
                        a.op_user_id,
                        u.username AS op_user_name     
                FROM sys_machine_tool_property_definition  a
                INNER JOIN sys_machine_tool_groups tg ON tg.id = a.machine_tool_grup_id AND tg.active = 0 AND tg.deleted =0 
                INNER JOIN sys_units su ON su.main = a.unit_grup_id AND su.sub IS NULL AND su.active = 0 AND su.deleted =0 AND su.language_id = a.language_id
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = a.language_id AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = a.language_id AND sd16.deleted = 0 AND sd16.active = 0                             
		INNER JOIN sys_specific_definitions sd18 ON sd18.main_group = 18 AND sd18.first_group= a.algorithmic_id AND sd18.language_id = a.language_id AND sd18.deleted = 0 AND sd18.active = 0                                             
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                INNER JOIN info_users u ON u.id = a.op_user_id                                              
                ORDER BY tg.group_name , a.property_name
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
     * @ sys_machine_tool_property_definition tablosuna yeni bir kayıt oluşturur.  !!
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
                INSERT INTO sys_machine_tool_property_definition(
                         machine_tool_grup_id, 
                         property_name, 
                         property_name_eng, 
                         unit_grup_id, 
                         algorithmic_id,                          
                         language_id, 
                         op_user_id, 
                         language_code
                         )
                VALUES (
                        :machine_tool_grup_id, 
                        :property_name, 
                        :property_name_eng, 
                        :unit_grup_id, 
                        :algorithmic_id,
                        :language_id, 
                        :op_user_id, 
                        :language_code
                                             )   ";
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':machine_tool_grup_id', $params['machine_tool_grup_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':property_name', $params['property_name'], \PDO::PARAM_STR);
                    $statement->bindValue(':property_name_eng', $params['property_name_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':unit_grup_id', $params['unit_grup_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':algorithmic_id', $params['algorithmic_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_machine_tool_property_definition_id_seq');
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
     * @ sys_machine_tool_property_definition tablosunda user_id li consultant daha önce kaydedilmiş mi ?  
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
               a.group_name  AS name , 
               '" . $params['group_name'] . "' AS value , 
                LOWER(a.group_name) =LOWER(TRIM('" . $params['group_name'] . "')) AS control,
                CONCAT(a.group_name, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_machine_tool_property_definition  a                          
            WHERE a.group_name = " . intval($params['group_name']) . "            
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
     * sys_machine_tool_property_definition tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
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
                UPDATE sys_machine_tool_property_definition
                SET   
                       machine_tool_grup_id = :machine_tool_grup_id, 
                       property_name = :property_name, 
                       property_name_eng = :property_name_eng, 
                       unit_grup_id = :unit_grup_id, 
                       algorithmic_id = :algorithmic_id,
                       language_id = :language_id, 
                       op_user_id = :op_user_id, 
                       language_code = :language_code,
                       active = :active                       
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':machine_tool_grup_id', $params['machine_tool_grup_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':property_name', $params['property_name'], \PDO::PARAM_STR);
                    $statement->bindValue(':property_name_eng', $params['property_name_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':unit_grup_id', $params['unit_grup_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':algorithmic_id', $params['algorithmic_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
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
     * @ Gridi doldurmak için sys_machine_tool_property_definition tablosundan kayıtları döndürür !!
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
            $sort = "tg.group_name , a.property_name ";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
            //print_r($orderArr);
            if (count($orderArr) === 1)
                $order = trim($args['order']);
        } else {
            $order = "ASC";
        }

        $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
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
                        a.machine_tool_grup_id,
                        tg.group_name AS tool_group_name,  
                        tg.group_name_eng AS tool_group_name_eng,  
                        a.property_name ,
                        a.property_name_eng,          
                        a.unit_grup_id, 
                        COALESCE(NULLIF(su.system, ''), su.system_eng) AS unit_group_name,  
                        a.algorithmic_id,   
                        sd18.description AS state_algorithmic,                		                   
                        a.deleted, 
                        sd15.description AS state_deleted,                 
                        a.active, 
                        sd16.description AS state_active, 
                        a.language_code, 
                        a.language_id, 
			COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,               
			a.language_parent_id,                     
                        a.op_user_id,
                        u.username AS op_user_name     
                FROM sys_machine_tool_property_definition  a
                INNER JOIN sys_machine_tool_groups tg ON tg.id = a.machine_tool_grup_id AND tg.active = 0 AND tg.deleted =0 
                INNER JOIN sys_units su ON su.main = a.unit_grup_id AND su.sub IS NULL AND su.active = 0 AND su.deleted =0 AND su.language_id = a.language_id
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = a.language_id AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = a.language_id AND sd16.deleted = 0 AND sd16.active = 0                             
		INNER JOIN sys_specific_definitions sd18 ON sd18.main_group = 18 AND sd18.first_group= a.algorithmic_id AND sd18.language_id = a.language_id AND sd18.deleted = 0 AND sd18.active = 0                                             
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                INNER JOIN info_users u ON u.id = a.op_user_id   
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
     * @ Gridi doldurmak için sys_machine_tool_property_definition tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
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
                FROM sys_machine_tool_property_definition  a
                INNER JOIN sys_machine_tool_groups tg ON tg.id = a.machine_tool_grup_id AND tg.active = 0 AND tg.deleted =0 
                INNER JOIN sys_units su ON su.main = a.unit_grup_id AND su.sub IS NULL AND su.active = 0 AND su.deleted =0 AND su.language_id = a.language_id
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = a.language_id AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = a.language_id AND sd16.deleted = 0 AND sd16.active = 0                             
		INNER JOIN sys_specific_definitions sd18 ON sd18.main_group = 18 AND sd18.first_group= a.algorithmic_id AND sd18.language_id = a.language_id AND sd18.deleted = 0 AND sd18.active = 0                                             
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                INNER JOIN info_users u ON u.id = a.op_user_id    
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
     * @ tree doldurmak için sys_machine_tool_property_definition tablosundan machine_tool_grup_id si
     * verilen kayıtları döndürür !! machine_tool_grup_id değeri boş yada bulunamazsa tüm kayıtları döndürür.
     * @version v 1.0  17.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMachineToolGroupPropertyDefinitions($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $machineToolGrupId = 0;
            $whereSql =" WHERE a.deleted = 0 ";
            if (isset($params['machine_tool_grup_id']) && $params['machine_tool_grup_id'] != "") {
                $machineToolGrupId = $params['machine_tool_grup_id'];
                $whereSql .= " AND a.machine_tool_grup_id = " . $machineToolGrupId . "   ";
            } else {
                $whereSql .= "";
            }
            
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }
            $whereSql .= " AND a.language_id = " . intval($languageIdValue);

            $statement = $pdo->prepare("                
                  SELECT                    
                    a.id, 
                    COALESCE(NULLIF(a.property_name, ''), a.property_name_eng) AS name,            
                    a.property_name_eng as name_eng,
                    a.unit_grup_id,
                    a.active ,                    
                    'open' AS state_type,
                    'false' AS root_type 
                FROM sys_machine_tool_property_definition a       
                " . $whereSql . "    
                ORDER BY name      
                           
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

}
