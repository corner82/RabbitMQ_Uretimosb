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
 * @author Okan CİRAN Ğ
 */
class SysCity extends \DAL\DalSlim {

    /**    
     * @author Okan CIRAN
     * @ sys_city tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  18.12.2015
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
                UPDATE sys_city
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
     * @ sys_city tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  18.12.2015  
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
                    a.country_id, 
		    COALESCE(NULLIF(c.name, ''), c.name_eng) AS country_name,  
                    COALESCE(NULLIF(a.name, ''), a.name_eng) AS name,    
                    a.name_eng, 
                    a.deleted, 
                    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active,  
                    a.language_parent_id, 
                    a.language_code, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,  
                    a.user_id, 
		    u.username,
                    a.priority, 
                    a.city_id     
                FROM sys_city  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = a.language_code AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = a.language_code AND sd1.deleted = 0 AND sd1.active = 0
                INNER JOIN sys_countrys c ON c.id = a.country_id AND c.language_code = a.language_code AND c.deleted = 0 AND c.active = 0 
                INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.user_id  
                WHERE a.deleted =0 AND a.language_code = :language_code 
                ORDER BY a.priority ASC, name                
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
     * @ sys_city tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  18.12.2015
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction(); 
            $statement = $pdo->prepare("
                INSERT INTO sys_city(
                        country_id, 
                        name, 
                        name_eng, 
                        deleted, 
                        active, 
                        language_parent_id, 
                        language_code, 
                        user_id, 
                        priority, 
                        city_id  )
                VALUES (
                        :country_id, 
                        :name, 
                        :name_eng, 
                        :deleted, 
                        :active, 
                        :language_parent_id, 
                        :language_code, 
                        :user_id, 
                        :priority, 
                        :city_id
                                                ");
            $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
            $statement->bindValue(':name_eng', $params['name_eng'], \PDO::PARAM_STR);
            $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
            $statement->bindValue(':language_parent_id', $params['language_parent_id'], \PDO::PARAM_INT);
            $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
            $statement->bindValue(':priority', $params['priority'], \PDO::PARAM_INT);
            $statement->bindValue(':country_id', $params['country_id'], \PDO::PARAM_INT);
            $statement->bindValue(':city_id', $params['city_id'], \PDO::PARAM_INT);
            $statement->bindValue(':active', $params['active'], \PDO::PARAM_INT);
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('sys_city_id_seq');
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            $pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**   
     * @author Okan CIRAN
     * sys_city tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  18.12.2015
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();          
            $statement = $pdo->prepare("
                UPDATE sys_city
                SET    
                    country_id= :country_id, 
                    name=:name,  
                    name_eng=:name_eng,  
                    active= :active,                 
                    language_parent_id = :language_parent_id,
                    language_code=:language_code,  
                    user_id = :user_id,
                    priority= :priority, 
                    city_id= :city_id                    
                WHERE id = :id");
            $statement->bindValue(':id',  $params['id'], \PDO::PARAM_INT);
            $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
            $statement->bindValue(':name_eng', $params['name_eng'], \PDO::PARAM_STR);
            $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
            $statement->bindValue(':language_parent_id', $params['language_parent_id'], \PDO::PARAM_INT);
            $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
            $statement->bindValue(':priority', $params['priority'], \PDO::PARAM_INT);
            $statement->bindValue(':country_id', $params['country_id'], \PDO::PARAM_INT);
            $statement->bindValue(':city_id', $params['city_id'], \PDO::PARAM_INT);
            $statement->bindValue(':active', $params['active'], \PDO::PARAM_INT);
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            $pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * Datagrid fill function used for testing
     * user interface datagrid fill operation   
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_city tablosundan kayıtları döndürür !!
     * @version v 1.0  18.12.2015
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
            $sort = "a.priority ASC, name";            
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
       
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $sql = "
              SELECT 
                    a.id, 
                    a.country_id, 
		    COALESCE(NULLIF(c.name, ''), c.name_eng) AS country_name,  
                    COALESCE(NULLIF(a.name, ''), a.name_eng) AS name,    
                    a.name_eng, 
                    a.deleted, 
                    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active,  
                    a.language_parent_id, 
                    a.language_code, 
		    COALESCE(NULLIF(l.language_eng,''), l.language) AS language_name,  
                    a.user_id, 
		    u.username,
                    a.priority, 
                    a.city_id     
                FROM sys_city  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = a.language_code AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = a.language_code AND sd1.deleted = 0 AND sd1.active = 0
                INNER JOIN sys_countrys c ON c.id = a.country_id AND c.language_code = a.language_code AND c.deleted = 0 AND c.active = 0 
                INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.user_id 
                WHERE deleted = 0 AND a.language_code = :language_code     
                    " . $whereNameSQL . "
                    AND country_id = :country_id 
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
            // echo debugPDO($sql, $parameters);            
            $statement->bindValue(':country_id', $args['country_id'], \PDO::PARAM_INT);
            $statement->bindValue(':language_code', $args['language_code'], \PDO::PARAM_STR);             
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
     * @ Gridi doldurmak için sys_city tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  18.12.2015
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $whereSQL = " WHERE a.language_code = '".$params['language_code']."' AND a.country_id =  ".intval($params['country_id']);
            $whereSQL1 = " WHERE a1.language_code = '".$params['language_code']."' AND a1.country_id = ".intval($params['country_id'])." AND a1.deleted = 0 ";
            $whereSQL2 = " WHERE a2.language_code = '".$params['language_code']."' AND a2.country_id = ".intval($params['country_id'])." AND a2.deleted = 1 ";
           
            $sql = "
                        SELECT 
                        count(a.id) AS count ,
                            (SELECT count(a1.id) AS toplam FROM sys_city a1
                            INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 15 AND sd1.first_group= a1.deleted AND sd1.language_code = a1.language_code AND sd1.deleted = 0 AND sd1.active = 0
                            INNER JOIN sys_specific_definitions sd11 ON sd11.main_group = 16 AND sd11.first_group= a1.active AND sd11.language_code = a1.language_code AND sd11.deleted = 0 AND sd11.active = 0
                            INNER JOIN sys_countrys c1 ON c1.id = a1.country_id AND c1.language_code = a1.language_code AND c1.deleted = 0 AND c1.active = 0 
                            INNER JOIN sys_language l1 ON l1.language_main_code = a1.language_code AND l1.deleted = 0 AND l1.active = 0 
                            INNER JOIN info_users u1 ON u1.id   = a1.user_id  
                             " . $whereSQL1 . ") AS undeleted_count, 
                            (SELECT count(a2.id) as toplam FROM sys_city a2
                            INNER JOIN sys_specific_definitions sd2 ON sd2.main_group = 15 AND sd2.first_group= a2.deleted AND sd2.language_code = a2.language_code AND sd2.deleted = 0 AND sd2.active = 0
                            INNER JOIN sys_specific_definitions sd12 ON sd12.main_group = 16 AND sd12.first_group= a2.active AND sd12.language_code = a2.language_code AND sd12.deleted = 0 AND sd12.active = 0
                            INNER JOIN sys_countrys c2 ON c2.id = a2.country_id AND c2.language_code = a2.language_code AND c2.deleted = 0 AND c2.active = 0 
                            INNER JOIN sys_language l2 ON l2.language_main_code = a2.language_code AND l2.deleted =0 AND l2.active = 0 
                            INNER JOIN info_users u2 ON u2.id   = a2.user_id  
                             " . $whereSQL2 . ") AS deleted_count 
                    FROM sys_city a
                    INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = a.language_code AND sd.deleted = 0 AND sd.active = 0
                    INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = a.language_code AND sd1.deleted = 0 AND sd1.active = 0
                    INNER JOIN sys_countrys c ON c.id = a.country_id AND c.language_code = a.language_code AND c.deleted = 0 AND c.active = 0 
                    INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active = 0 
                    INNER JOIN info_users u ON u.id = a.user_id  
                    " . $whereSQL . "
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
     * user interface datagrid fill operation get row count for widget
     * @author Okan CIRAN
     * @ combobox ı doldurmak için sys_city tablosundan çekilen kayıtları döndürür   !!
     * @version v 1.0  17.12.2015
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillComboBox($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $sql = "
               SELECT 
                    a.city_id AS id,
                    COALESCE(NULLIF(a.name, ''), a.name_eng) AS name,
                     a.name_eng
                FROM sys_city a                 
                WHERE a.language_code =  '".$params['language_code']."' AND a.active = 0 AND a.deleted = 0 
                AND country_id =  ".intval($params['country_id'])." 
                ORDER BY a.priority ASC, name                
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
     * @author Okan CIRAN
     * @ sys_city tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  29.12.2015
     * @return array
     * @throws \PDOException
     */
    public function insertLanguageTemplate($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $statement = $pdo->prepare(" 
                INSERT INTO sys_city(
                    country_id, name, name_eng, language_parent_id, 
                    language_id, user_id, priority, city_id, language_code)                  
               SELECT    
                    country_id, name, name_eng, language_id, language_parent_id, 
		    user_id, priority, city_id, language_main_code
               FROM ( 
                       SELECT c.country_id,
                            '' AS name,                            
                            COALESCE(NULLIF(c.name_eng, ''), c.name) as name_eng, 
                            l.id as language_id,  
                            (SELECT x.id FROM sys_city x WHERE x.id =:id AND x.deleted =0 AND x.active =0 AND x.language_parent_id =0) AS language_parent_id,                            
                            c.user_id, 
			    c.priority,
			    city_id, 	 
                            l.language_main_code
                        FROM sys_city c
                        LEFT JOIN sys_language l ON l.deleted =0 AND l.active =0 
                        WHERE c.id =".intval($params['id'])." 
                        ) AS xy   
                        WHERE xy.language_main_code NOT IN 
                           (SELECT distinct language_code 
                           FROM sys_city cx 
                           WHERE (cx.language_parent_id =".intval($params['id'])."  OR cx.id =".intval($params['id'])." ) AND cx.deleted =0 AND cx.active =0)
                ");           
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('sys_city_id_seq');
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            $pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    
}
