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
     * @ sys_machine_tool_groups tablosundan parametre olarak  gelen id kaydını siler. !!
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
                UPDATE sys_machine_tool_groups
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
     * @ sys_machine_tool_groups tablosundaki tüm kayıtları getirir.  !!
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
                        a.group_name ,
                        a.group_name_eng,          
                        a.parent_id,                  		                   
                        a.deleted, 
                        sd.description as state_deleted,                 
                        a.active, 
                        sd1.description as state_active, 
                        a.op_user_id,
                        u.username AS op_user_name ,
                        a.language_code, 
                        a.language_id, 
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,
                        a.language_parent_id
                FROM sys_machine_tool_groups  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0                             
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                INNER JOIN info_users u ON u.id = a.op_user_id                              
                ORDER BY a.parent_id, a.group_name
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
     * @ sys_machine_tool_groups tablosuna yeni bir kayıt oluşturur.  !!
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
                INSERT INTO sys_machine_tool_groups(
                        group_name, 
                        group_name_eng,
                        parent_id, 
                        language_id, 
                        op_user_id,                        
                        icon_class )
                VALUES (
                        :group_name, 
                        :group_name_eng, 
                        :parent_id, 
                        :language_id, 
                        :op_user_id,                        
                        :icon_class  
                                             )   ";
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':group_name', $params['group_name'], \PDO::PARAM_STR);
                    $statement->bindValue(':group_name_eng', $params['group_name_eng'], \PDO::PARAM_STR);                    
                    $statement->bindValue(':parent_id', $params['parent_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':icon_class', $params['icon_class'], \PDO::PARAM_STR);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_machine_tool_groups_id_seq');
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
     * @ sys_machine_tool_groups tablosunda user_id li consultant daha önce kaydedilmiş mi ?  
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
            FROM sys_machine_tool_groups  a                          
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
     * sys_machine_tool_groups tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
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
                UPDATE sys_machine_tool_groups
                SET   
                    group_name = :group_name, 
                    group_name_eng= :group_name_eng,                    
                    parent_id = :parent_id, 
                    language_id = :language_id, 
                    op_user_id= :op_user_id,                        
                    icon_class = :icon_class  
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':group_name', $params['group_name'], \PDO::PARAM_STR);
                    $statement->bindValue(':group_name_eng', $params['group_name_eng'], \PDO::PARAM_STR);                   
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':parent_id', $params['parent_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':icon_class', $params['icon_class'], \PDO::PARAM_STR);
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
     * @ Gridi doldurmak için sys_machine_tool_groups tablosundan kayıtları döndürür !!
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
        if (isset($args['sort']) && $args['sort'] != "") {
            $sort = trim($args['sort']);
            $sortArr = explode(",", $sort);
            if (count($sortArr) === 1)
                $sort = trim($args['sort']);
        } else {
            $sort = "a.parent_id, a.group_name ";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
            //print_r($orderArr);
            if (count($orderArr) === 1)
                $order = trim($args['order']);
        } else {
            //$order = "desc";
            $order = "ASC";
        }
 
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $sql = "
                 SELECT 
                        a.id, 
                        a.group_name ,
                        a.group_name_eng,          
                        a.parent_id,                  		                   
                        a.deleted, 
                        sd.description as state_deleted,                 
                        a.active, 
                        sd1.description as state_active, 
                        a.op_user_id,
                        u.username AS op_user_name     
                FROM sys_machine_tool_groups  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                WHERE a.deleted =0              
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
     * @ Gridi doldurmak için sys_machine_tool_groups tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  15.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');      
            $sql = "
               SELECT 
                    COUNT(a.id) AS COUNT  
                FROM sys_machine_tool_groups  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id                  
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
     * @ tree doldurmak için sys_machine_tool_groups tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  15.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMachineToolGroups($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
             $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }
            $parentId = 0;
            if (isset($params['parent_id']) && $params['parent_id'] != "") {
                $parentId = $params['parent_id'];
            }
            $sql = "                
                SELECT                    
                    a.id,                     
                    COALESCE(NULLIF(ax.group_name, ''), a.group_name_eng) as name ,
                    a.parent_id,
                    a.active ,
                    CASE
                        (CASE 
                            (SELECT DISTINCT 1 state_type FROM sys_machine_tool_groups WHERE parent_id = a.id AND deleted = 0)    
                             WHEN 1 THEN 'closed'
                             ELSE 'open'   
                             END ) 
                         WHEN 'open' THEN COALESCE(NULLIF((SELECT DISTINCT 'closed' FROM sys_machine_tools mz WHERE mz.machine_tool_grup_id =a.id AND mz.deleted = 0), ''), 'open')   
                    ELSE 'closed'
                    END AS state_type,
                    CASE
                        (SELECT DISTINCT 1 parent_id FROM sys_machine_tool_groups WHERE id = a.id AND deleted = 0 AND parent_id =0 )    
                        WHEN 1 THEN 'true'
                    ELSE 'false'   
                    END AS root_type,
                    a.icon_class,
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_machine_tool_groups WHERE parent_id = a.id AND deleted = 0)    
                         WHEN 1 THEN 'false'
                    ELSE 'true'   
                    END AS last_node,
                    'false' as machine
                FROM sys_machine_tool_groups a  
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.deleted =0 AND lx.active =0 AND lx.id = " . intval($languageIdValue) . "
                LEFT JOIN sys_machine_tool_groups ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.language_id = lx.id
                WHERE                    
                    a.parent_id = " .intval($parentId) . " AND a.language_parent_id =0 AND 
                    a.deleted = 0  
                ORDER BY name  
             
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
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    
    
    /**
     * user interface fill operation   
     * @author Okan CIRAN
     * @ tree doldurmak için sys_machine_tool_groups tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  15.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillJustMachineToolGroups($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
             $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }
            $parentId = 0;
            if (isset($params['parent_id']) && $params['parent_id'] != "") {
                $parentId = $params['parent_id'];
            }
            $sql = "                
                SELECT                    
                    a.id,                     
                    COALESCE(NULLIF(ax.group_name, ''), a.group_name_eng) as name ,
                    a.parent_id,
                    a.active ,
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_machine_tool_groups WHERE parent_id = a.id AND deleted = 0)    
                        WHEN 1 THEN 'closed'
                        ELSE 'open' 
                    END AS state_type,
                    CASE
                        (SELECT DISTINCT 1 parent_id FROM sys_machine_tool_groups WHERE id = a.id AND deleted = 0 AND parent_id =0 )    
                        WHEN 1 THEN 'true'
                    ELSE 'false'   
                    END AS root_type,
                    a.icon_class,
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_machine_tool_groups WHERE parent_id = a.id AND deleted = 0)    
                         WHEN 1 THEN 'false'
                    ELSE 'true'   
                    END AS last_node,
                    'false' AS machine
                FROM sys_machine_tool_groups a  
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.deleted =0 AND lx.active =0 AND lx.id = " . intval($languageIdValue) . "
                LEFT JOIN sys_machine_tool_groups ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.language_id = lx.id
                WHERE                    
                    a.parent_id = " .intval($parentId) . " AND a.language_parent_id =0 AND 
                    a.deleted = 0  
                ORDER BY name  
             
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
     * user interface fill operation   
     * @author Okan CIRAN
     * @ tree doldurmak için sys_machine_tool tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  19.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMachineToolGroupsMachines($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }
            $parentId = 0;
            if (isset($params['parent_id']) && $params['parent_id'] != "") {
                $parentId = $params['parent_id'];
            }
            $sql =" 
                SELECT                    
                    mt.id, 
                    COALESCE(NULLIF( (mtx.machine_tool_name), ''), mt.machine_tool_name_eng) AS name,            
                    -1 AS parent_id,
                    a.active ,
                    'open' AS state_type,                                          
                    'false' AS root_type,
                    Null AS icon_class,
                    'true' AS last_node,
                    'true' as machine   
                FROM sys_machine_tool_groups a 
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0   
		INNER join sys_machine_tools mt on mt.machine_tool_grup_id = a.id AND mt.language_id = l.id AND mt.active =0 AND mt.deleted =0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
		LEFT join sys_machine_tools mtx on mtx.id = mt.id AND mtx.language_id = lx.id AND mtx.deleted =0 AND mtx.active =0 AND mtx.language_parent_id =0                         
                WHERE                    
                   a.id =  " .intval($parentId) . " AND 
                   a.deleted = 0 AND
                   a.active =0 
                ORDER BY name        
                                 ";
             $statement = $pdo->prepare( $sql);
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

     /**
     * user interface fill operation   
     * @author Okan CIRAN
     * @ tree doldurmak için sys_machine_tool tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  19.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMachineToolGroupsMachineProperties($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }
            $machineId = 0;
            $addSql =" WHERE a.deleted =0 AND a.active =0  
                AND a.language_parent_id =0  ";
            if (isset($params['machine_id']) && $params['machine_id'] != "") {
                $machineId = $params['machine_id'];
                $addSql .=" AND a.machine_tool_id= " . intval($machineId);
            }
            $statement = $pdo->prepare("                
               
                SELECT 
                    a.id, 
                    cast(a.machine_tool_id as text) as machine_id ,	
                    COALESCE(NULLIF(mtx.machine_tool_name, ''), mt.machine_tool_name_eng) AS machine_names,	
                    COALESCE(NULLIF(pdx.property_name, ''), pd.property_name_eng) AS property_names,
                    pd.property_name_eng,
                    a.property_value, 
                    u.id AS unit_id,
                    COALESCE(NULLIF(u.unitcode, ''), u.unitcode_eng) AS unitcodes                  
                FROM sys_machine_tool_properties a
		LEFT JOIN sys_language lx ON lx.id =". intval($languageIdValue)."  AND lx.deleted =0 AND lx.active =0                      
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  				
                INNER JOIN sys_machine_tools mt ON (mt.id = a.machine_tool_id OR mt.language_parent_id = a.machine_tool_id ) AND mt.language_id = l.id            
                LEFT JOIN sys_machine_tools mtx ON (mtx.id = a.machine_tool_id OR mtx.language_parent_id = a.machine_tool_id ) AND mtx.language_id = lx.id              
                INNER JOIN sys_machine_tool_property_definition pd ON pd.id = a.machine_tool_property_definition_id AND pd.language_parent_id = 0              
                LEFT JOIN sys_machine_tool_property_definition pdx ON (pdx.id = a.machine_tool_property_definition_id OR pdx.language_parent_id = a.machine_tool_property_definition_id) AND pdx.language_id = lx.id             
                LEFT JOIN sys_units u ON (u.id = a.unit_id OR u.language_parent_id = a.unit_id) AND u.language_id = l.id                 
                ".$addSql."                
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
