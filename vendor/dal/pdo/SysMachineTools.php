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
 * @since 15.02.2016
 */
class SysMachineToolGroups extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_machine_tools tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  15.02.2016
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
                UPDATE sys_machine_tools
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
     * @ sys_machine_tools tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  15.02.2016  
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
                        mtg.group_name,
                        a.machine_tool_name, 
                        a.machine_tool_name_eng, 
                        a.machine_tool_grup_id, 
                        a.manufactuer_id, 
                        a.model, 
                        a.model_year, 
                        a.procurement, 
                        a.qqm, 
                        a.machine_code,	                   
                        a.deleted, 
                        sd.description as state_deleted,                 
                        a.active, 
                        sd1.description as state_active, 
                        a.op_user_id,
                        u.username AS op_user_name,
                        a.language_id ,
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                  
                        a.language_code 
                FROM sys_machine_tools a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                INNER JOIN sys_machine_tool_groups mtg ON mtg.id = a.machine_tool_grup_id AND mtg.active = 0 AND mtg.deleted = 0 AND mtg.language_id = a.language_id
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = a.language_id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0                             
                LEFT JOIN info_users u ON u.id = a.op_user_id                              
                ORDER BY a.language_id, mtg.group_name, a.machine_tool_name
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
     * @ sys_machine_tools tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  15.02.2016
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
                INSERT INTO sys_machine_tools(
                        machine_tool_name, 
                        machine_tool_name_eng, 
                        machine_tool_grup_id, 
                        manufactuer_id, 
                        model, 
                        model_year, 
                        procurement, 
                        qqm, 
                        machine_code, 
                        language_id, 
                        op_user_id, 
                        language_code
                        )
                VALUES (
                        :machine_tool_name, 
                        :machine_tool_name_eng, 
                        :machine_tool_grup_id, 
                        :manufactuer_id, 
                        :model, 
                        :model_year, 
                        :procurement, 
                        :qqm, 
                        :machine_code, 
                        :language_id, 
                        :op_user_id, 
                        :language_code
                                             )  
                    ";
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':machine_tool_name', $params['machine_tool_name'], \PDO::PARAM_STR);
                    $statement->bindValue(':machine_tool_name_eng', $params['machine_tool_name_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':machine_tool_grup_id', $params['machine_tool_grup_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':manufactuer_id', $params['manufactuer_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':model', $params['model'], \PDO::PARAM_STR);
                    $statement->bindValue(':model_year', $params['model_year'], \PDO::PARAM_INT);
                    $statement->bindValue(':procurement', $params['procurement'], \PDO::PARAM_INT);
                    $statement->bindValue(':qqm', $params['qqm'], \PDO::PARAM_INT);
                    $statement->bindValue(':machine_code', $params['machine_code'], \PDO::PARAM_STR);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_STR);
                    $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_machine_tools_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23505';
                    $pdo->rollback();
                    $result = $kontrol;
                    return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '');                    
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
     * @ sys_machine_tools tablosunda user_id li consultant daha önce kaydedilmiş mi ?  
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
			CONCAT(a.machine_tool_name) AS name , 
			'" . $params['machine_tool_name'] . "' AS value , 
			a.machine_tool_name ='" . $params['machine_tool_name'] . "' AS control,
                CONCAT(a.machine_tool_name, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
		FROM sys_machine_tools  a  
		INNER JOIN info_users_detail u ON u.root_id = a.op_user_id AND u.active = 0 AND u.deleted = 0                 
		WHERE a.machine_tool_name = '" . $params['machine_tool_name'] . "'
                    AND a.machine_tool_grup_id = " . intval($params['machine_tool_grup_id']) . "
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
     * sys_machine_tools tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  15.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = $this->getUserId(array('pk' => $params['pk'], 'id' => $params['id']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
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
                UPDATE sys_machine_tools
                SET   
                    machine_tool_name = :machine_tool_name, 
                    machine_tool_name_eng = :machine_tool_name_eng, 
                    machine_tool_grup_id = :machine_tool_grup_id, 
                    manufactuer_id = :manufactuer_id, 
                    model = :model, 
                    model_year = :model_year, 
                    procurement = :procurement, 
                    qqm = :qqm, 
                    machine_code = :machine_code, 
                    language_id = :language_id, 
                    op_user_id = :op_user_id, 
                    language_code = :language_code
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':machine_tool_name', $params['machine_tool_name'], \PDO::PARAM_STR);
                    $statement->bindValue(':machine_tool_name_eng', $params['machine_tool_name_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':machine_tool_grup_id', $params['machine_tool_grup_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':manufactuer_id', $params['manufactuer_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':model', $params['model'], \PDO::PARAM_STR);
                    $statement->bindValue(':model_year', $params['model_year'], \PDO::PARAM_INT);
                    $statement->bindValue(':procurement', $params['procurement'], \PDO::PARAM_INT);
                    $statement->bindValue(':qqm', $params['qqm'], \PDO::PARAM_INT);
                    $statement->bindValue(':machine_code', $params['machine_code'], \PDO::PARAM_STR);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_STR);
                    $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
                    $update = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    // 23505 	unique_violation
                    $errorInfo = '23505'; // $kontrol ['resultSet'][0]['message'];  
                    $pdo->rollback();                   
                    $errorInfoColumn = 'machine_tool_name';
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
     * @ Gridi doldurmak için sys_machine_tools tablosundan kayıtları döndürür !!
     * @version v 1.0  15.02.2016
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
        $whereSQL = "";
        if (isset($args['sort']) && $args['sort'] != "") {
            $sort = trim($args['sort']);
            $sortArr = explode(",", $sort);
            if (count($sortArr) === 1)
                $sort = trim($args['sort']);
        } else {
            $sort = "a.language_id, mtg.group_name, a.machine_tool_name";
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
        $whereSQL .= "  AND a.language_id = " . intval($languageIdValue);

        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $sql = "
                SELECT 
                        a.id, 
                        mtg.group_name,
                        a.machine_tool_name, 
                        a.machine_tool_name_eng, 
                        a.machine_tool_grup_id, 
                        a.manufactuer_id, 
                        a.model, 
                        a.model_year, 
                        a.procurement, 
                        a.qqm, 
                        a.machine_code,	                   
                        a.deleted, 
                        sd.description AS state_deleted,                 
                        a.active, 
                        sd1.description AS state_active, 
                        a.op_user_id,
                        u.username AS op_user_name,
                        a.language_id,
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                  
                        a.language_code 
                FROM sys_machine_tools a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                INNER JOIN sys_machine_tool_groups mtg ON mtg.id = a.machine_tool_grup_id AND mtg.active = 0 AND mtg.deleted = 0 AND mtg.language_id = a.language_id
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = a.language_id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0                             
                LEFT JOIN info_users u ON u.id = a.op_user_id  
                WHERE a.deleted =0  
                " . $whereSQL . "
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
     * @ Gridi doldurmak için sys_machine_tools tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  15.02.2016
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
            $whereSQL = "  WHERE a.deleted =0 AND a.language_id = " . intval($languageIdValue) . ",";

            $sql = "
               SELECT 
                    COUNT(a.id) AS COUNT  
                FROM sys_machine_tools a                  
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                INNER JOIN sys_machine_tool_groups mtg ON mtg.id = a.machine_tool_grup_id AND mtg.active = 0 AND mtg.deleted = 0 AND mtg.language_id = a.language_id
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0
                " . $whereSQL . "
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

}
