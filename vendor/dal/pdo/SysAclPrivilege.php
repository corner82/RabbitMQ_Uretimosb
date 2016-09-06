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
 */
class SysAclPrivilege extends \DAL\DalSlim {

    /**    
     * @author Okan CIRAN
     * @ sys_acl_privilege tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  13-01-2016
     * @param type $params
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
                UPDATE sys_acl_privilege
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
     * @ sys_acl_privilege tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  13-01-2016 
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $statement = $pdo->prepare("
                SELECT 
                        a.id,                       
                        a.name AS name,
                        a.icon_class, 
                        a.c_date AS create_date,
                        a.deleted, 
                        sd.description AS state_deleted,                 
                        a.active, 
                        sd1.description AS state_active,  
                        a.description,                                     
                        a.user_id,
                        u.username                    
                FROM sys_acl_privilege a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0                             
                INNER JOIN info_users u ON u.id = a.user_id    
                WHERE a.deleted =0 AND a.language_code = :language_code    
                ORDER BY a.name                  
                                 ");
            $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR); 
            $statement->execute();
            $result = $statement->fetcAll(\PDO::FETCH_ASSOC);
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
     * @ sys_acl_privilege tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0 21.01.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveRecords($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');    
            $addSql = "";
            if (isset($params['id'])) {
                $addSql = " AND id != " . intval($params['id']) . " ";
            }
            $sql = " 
           SELECT  
                name as name , 
                '" . $params['name'] . "' as value , 
                name ='" . $params['name'] . "' as control,
                concat(name , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) as message                             
            FROM sys_acl_privilege        
            WHERE name = '" . $params['name'] . "'"
                    . $addSql . " 
               AND deleted =0   
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
     * @ sys_acl_privilege tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  13-01-2016
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $kontrol = $this->haveRecords($params); 
            if (!\Utill\Dal\Helper::haveRecord($kontrol)) { 
                $sql = "
                INSERT INTO sys_acl_privilege(
                        name, icon_class, parent, user_id, description)
                VALUES (
                        :name,
                        :icon_class,                                                            
                        :user_id,
                        :description                      
                                                ";
                $statement = $pdo->prepare($sql);
                $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
                $statement->bindValue(':icon_class', $params['icon_class'], \PDO::PARAM_STR);              
                $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
                $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('sys_acl_privilege_id_seq');
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                $pdo->commit();

                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
            } else {  
                $errorInfo = '23505';     // 23505 	unique_violation
                 $pdo->rollback();
                $result= $kontrol;  
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => ''); 
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**     
     * @author Okan CIRAN
     * sys_acl_privilege tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  13-01-2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();
             $kontrol = $this->haveRecords($params); 
            if (!\Utill\Dal\Helper::haveRecord($kontrol)) {                
            $statement = $pdo->prepare("
                UPDATE sys_acl_privilege
                SET   
                    name = :name, 
                    icon_class = :icon_class, 
                    active = :active,   
                    user_id= :user_id,  
                    description = :description                                           
                WHERE id = :id");
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
            $statement->bindValue(':icon_class', $params['icon_class'], \PDO::PARAM_STR);
            $statement->bindValue(':active', $params['active'], \PDO::PARAM_INT);
            $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
            $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            $pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
            } else {        
                $errorInfo = '23505'; // 23505 	unique_violation
                 $pdo->rollback();
              //  $result= $kontrol;            
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '');
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**    
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_acl_privilege tablosundan kayıtları döndürür !!
     * @version v 1.0  13-01-2016
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
            $sort = "a.name";
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
                        a.name AS name,
                        a.icon_class, 
                        a.c_date AS create_date,
                        a.deleted, 
                        sd.description AS state_deleted,                 
                        a.active, 
                        sd1.description AS state_active,  
                        a.description,                                     
                        a.user_id,
                        u.username                    
                FROM sys_acl_privilege a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0                             
                INNER JOIN info_users u ON u.id = a.user_id    
                WHERE a.deleted =0             
                ORDER BY " . $sort . " "
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
            //  echo debugPDO($sql, $parameters);
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
     * @ Gridi doldurmak için sys_acl_privilege tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  13-01-2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $whereSQL = '';
            $whereSQL1 = ' WHERE a1.deleted =0';
            $whereSQL2 = ' WHERE a2.deleted =1';
            
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT ,
                    (SELECT COUNT(a1.id) FROM sys_acl_privilege a1  
                    INNER JOIN sys_specific_definitions sd1x ON sd1x.main_group = 15 AND sd1x.first_group= a1.deleted AND sd1x.language_code = 'tr' AND sd1x.deleted = 0 AND sd1x.active = 0
                    INNER JOIN sys_specific_definitions sd11 ON sd11.main_group = 16 AND sd11.first_group= a1.active AND sd11.language_code = 'tr' AND sd11.deleted = 0 AND sd11.active = 0                             
                    INNER JOIN info_users u1 ON u1.id = a1.user_id 
                    " . $whereNameSQL1 . ") AS undeleted_count, 
                    (SELECT COUNT(a2.id) FROM sys_acl_privilege a2
                    INNER JOIN sys_specific_definitions sd2 ON sd2.main_group = 15 AND sd2.first_group= a2.deleted AND sd2.language_code = 'tr' AND sd2.deleted = 0 AND sd2.active = 0
                    INNER JOIN sys_specific_definitions sd12 ON sd12.main_group = 16 AND sd12.first_group= a2.active AND sd12.language_code = 'tr' AND sd12.deleted = 0 AND sd12.active = 0                             
                    INNER JOIN info_users u2 ON u2.id = a2.user_id 			
                    " . $whereNameSQL2 . ") AS deleted_count                        
                FROM sys_acl_privilege a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0                             
                INNER JOIN info_users u ON u.id = a.user_id 
                " . $whereNameSQL . "
                    ";
            $statement = $pdo->prepare($sql);
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
     * @author Okan CIRAN
     * @ combobox doldurmak için sys_acl_privilege tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  13-01-2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillComboBoxFullPrivilege($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');         
            $statement = $pdo->prepare("
                SELECT                    
                    a.id, 	
                    a.name AS name ,
                    a.active
                FROM sys_acl_privilege a       
                WHERE 
                    a.deleted = 0     
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
