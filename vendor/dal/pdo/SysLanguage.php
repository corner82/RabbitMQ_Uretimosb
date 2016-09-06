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
 * @author Okan CİRANĞ
 */
class SysLanguage extends \DAL\DalSlim {

    /**     
     * @author Okan CIRAN
     * @ sys_language tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  07.12.2015
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
                UPDATE sys_language
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
     * @ sys_language tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  07.12.2015    
     * @param array | null $params
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $statement = $pdo->prepare("
                  SELECT                    
                    a.id, 
                    a.country_name, 
                    a.country_name_eng, 
                    a.country_id, 		
                    a.language_parent_id,		
                    a.deleted, 
		    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active, 		
                    a.icon_road, 		
                    a.user_id, 
                    u.username,
                    a.country_code3, 		
                    a.link, 		
                    a.language_code, 		
                    a.language_id, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,  		
                    a.parent_id, 		                    
                    COALESCE(NULLIF(a.language, ''), a.language_eng) AS language, 
                    a.language_eng,
                    a.language_main_code,
                    a.priority                    
                FROM sys_language  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND 
			sd.language_id = a.language_id  AND sd.active =0 AND sd.deleted=0
		INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND 
			sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.user_id  
                ORDER BY a.priority, language                 
                                 ");
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
     * @ sys_language tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  08.12.2015
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $sql = " 
            SELECT  
                language_code as name , 
                '" . $params['language_code'] . "' as value , 
                language_code ='" . $params['language_code'] . "' as control,
                concat(language_code , ' dil kodu daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) as message                             
            FROM sys_language        
            WHERE language_code = '" . $params['language_code'] . "'               
                               ";
            $statement = $pdo->prepare($sql);            
            $statement->execute();
            $kontrol = $statement->fetchAll(\PDO::FETCH_ASSOC);         

            if (!isset($kontrol[0]['control'])) {  
            $pdo->beginTransaction();
            /**
             * table names and column names will be changed for specific use
             */
            $statement = $pdo->prepare("
                INSERT INTO sys_language(
                        country_name, country_name_eng, country_id, language_parent_id, 
                        icon_road, user_id, country_code3, link, language_code, 
                        language_id, parent_id, language_eng, language_main_code, language, 
                        priority)  
                VALUES (
                        :country_name, 
                        :country_name_eng, 
                        :country_id, 
                        :language_parent_id, 
                        :icon_road, 
                        :user_id, 
                        :country_code3, 
                        :link, 
                        :language_code, 
                        :language_id, 
                        :parent_id, 
                        :language_eng, 
                        :language_main_code, 
                        :language, 
                        :priority
                                                ");
            $statement->bindValue(':country_name', $params['country_name'], \PDO::PARAM_STR);
            $statement->bindValue(':country_name_eng', $params['country_name_eng'], \PDO::PARAM_STR);
            $statement->bindValue(':country_id', $params['country_id'], \PDO::PARAM_INT);
            $statement->bindValue(':language_parent_id', $params['language_parent_id'], \PDO::PARAM_INT);
            $statement->bindValue(':icon_road', $params['icon_road'], \PDO::PARAM_STR);
            $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_STR);
            $statement->bindValue(':country_code3', $params['country_code3'], \PDO::PARAM_STR);
            $statement->bindValue(':link', $params['link'], \PDO::PARAM_STR);            
            $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_INT);
            $statement->bindValue(':language_id', $params['language_id'], \PDO::PARAM_INT);
            $statement->bindValue(':parent_id', $params['parent_id'], \PDO::PARAM_INT);
            $statement->bindValue(':language_eng', $params['language_eng'], \PDO::PARAM_STR);
            $statement->bindValue(':language_main_code', $params['language_main_code'], \PDO::PARAM_STR);
            $statement->bindValue(':language', $params['language'], \PDO::PARAM_STR);
            $statement->bindValue(':priority', $params['priority'], \PDO::PARAM_INT);  
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('sys_language_id_seq');
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            $pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);           
            } else {           
               // $result  = $kontrol;             
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**    
     * @author Okan CIRAN
     * sys_language tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  07.12.2015
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();          
            $statement = $pdo->prepare("
                UPDATE sys_language
                SET              
                    country_name = :country_name, 
                    country_name_eng = :country_name_eng, 
                    country_id  = :country_id, 
                    language_parent_id  = :language_parent_id, 
                    icon_road  = :icon_road, 
                    user_id  = :user_id, 
                    country_code3  = :country_code3, 
                    link  = :link, 
                    language_code  = :language_code, 
                    language_id  = :language_id, 
                    parent_id  = :parent_id, 
                    language_eng  = :language_eng, 
                    language_main_code  = :language_main_code, 
                    language  = :language, 
                    priority  = :priority
                WHERE id = :id");
            //Bind our value to the parameter :id.
            $statement->bindValue(':id',  $params['id'], \PDO::PARAM_INT);
            //Bind our :model parameter.     
            $statement->bindValue(':country_name', $params['country_name'], \PDO::PARAM_STR);
            $statement->bindValue(':country_name_eng', $params['country_name_eng'], \PDO::PARAM_STR);
            $statement->bindValue(':country_id', $params['country_id'], \PDO::PARAM_INT);
            $statement->bindValue(':language_parent_id', $params['language_parent_id'], \PDO::PARAM_INT);
            $statement->bindValue(':icon_road', $params['icon_road'], \PDO::PARAM_STR);
            $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_STR);
            $statement->bindValue(':country_code3', $params['country_code3'], \PDO::PARAM_STR);
            $statement->bindValue(':link', $params['link'], \PDO::PARAM_STR);            
            $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_INT);
            $statement->bindValue(':language_id', $params['language_id'], \PDO::PARAM_INT);
            $statement->bindValue(':parent_id', $params['parent_id'], \PDO::PARAM_INT);
            $statement->bindValue(':language_eng', $params['language_eng'], \PDO::PARAM_STR);
            $statement->bindValue(':language_main_code', $params['language_main_code'], \PDO::PARAM_STR);
            $statement->bindValue(':language', $params['language'], \PDO::PARAM_STR);
            $statement->bindValue(':priority', $params['priority'], \PDO::PARAM_INT);           
            //Execute our UPDATE statement.
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
     * @ Gridi doldurmak için sys_language tablosundan kayıtları döndürür !!
     * @version v 1.0  08.12.2015
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
            $sort = " a.priority, language";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);    
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
                    a.country_name, 
                    a.country_name_eng, 
                    a.country_id, 		
                    a.language_parent_id,		
                    a.deleted, 
		    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active, 		
                    a.icon_road, 		
                    a.user_id, 
                    u.username,
                    a.country_code3, 		
                    a.link, 		
                    a.language_code, 		
                    a.language_id, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,  		
                    a.parent_id, 		                    
                    COALESCE(NULLIF(a.language, ''), a.language_eng) AS language, 
                    a.language_eng,
                    a.language_main_code,
                    a.priority
                FROM sys_language  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND 
			sd.language_id = a.language_id  AND sd.active =0 AND sd.deleted=0
		INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND 
			sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.user_id  
                WHERE a.language_id = :language_id                                               
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
            $statement->bindValue(':language_id', $args['language_id'], \PDO::PARAM_INT);
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
     * @ Gridi doldurmak için sys_language tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  08.12.2015
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $sql = "             
	     SELECT                    
			COUNT(a.id) AS COUNT , 
			(SELECT COUNT(a1.id) FROM sys_language a1
			INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 15 AND sd1.first_group= a1.deleted AND 
				sd1.language_id = a1.language_id AND sd1.active =0 AND sd1.deleted = 0
			INNER JOIN sys_specific_definitions sd12 ON sd12.main_group = 16 AND sd12.first_group = a1.active AND 
				sd12.language_id = a1.language_id AND sd12.deleted = 0 AND sd12.active = 0
			INNER JOIN sys_language l1 ON l1.id = a1.language_id AND l1.deleted = 0 AND l1.active = 0 
			INNER JOIN info_users u1 ON u1.id = a1.user_id               
			WHERE a1.language_id = :language_id AND a1.deleted =0) AS undeleted_count, 
		       (SELECT COUNT(a2.id) FROM sys_language a2
			INNER JOIN sys_specific_definitions sd2 ON sd2.main_group = 15 AND sd2.first_group = a2.deleted AND 
				sd2.language_id = a2.language_id AND sd2.active =0 AND sd2.deleted=0
			INNER JOIN sys_specific_definitions sd12 ON sd12.main_group = 16 AND sd12.first_group = a2.active AND 
				sd12.language_id = a2.language_id AND sd12.deleted = 0 AND sd12.active = 0
			INNER JOIN sys_language l2 ON l2.id = a2.language_id AND l2.deleted = 0 AND l2.active = 0 
			INNER JOIN info_users u2 ON u2.id = a2.user_id               
			WHERE a2.language_id = :language_id AND a2.deleted =1) AS deleted_count  		
                FROM sys_language a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND 
			sd.language_id = a.language_id AND sd.active =0 AND sd.deleted=0
		INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND 
			sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.user_id  
	    where a.language_id = :language_id
                    ";
            $statement = $pdo->prepare($sql);
            $statement->bindValue(':language_id', $args['language_id'], \PDO::PARAM_INT);
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
     * @ combobox ı doldurmak için sys_language tablosundan çekilen kayıtları döndürür   !!
     * @version v 1.0  17.12.2015
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillComboBox() {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $statement = $pdo->prepare("
                SELECT                    
                    a.id, 	
                    a.language, 
                    a.language_eng,		
                    a.language_main_code                                 
                FROM sys_language  a       
                WHERE  
                    a.deleted = 0 and a.active =0    
                ORDER BY a.priority                
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
     * @ sys_language tablosundan id degerini getirir.  !!
     * @version v 1.0  03.02.2016    
     * @param array | null $params
     * @return array
     * @throws \PDOException
     */
    public function getLanguageId($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $sql = "     
                SELECT                    
                    a.id   ,
                    a.language_main_code ='" . $params['language_code'] . "'  as control
                FROM sys_language a                                
                where a.deleted =0 AND a.active = 0 AND 
                    a.language_main_code = '" . $params['language_code'] . "'               
                LIMIT 1                ";
           //  echo debugPDO($sql, $params);   
            $statement = $pdo->prepare($sql);
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
