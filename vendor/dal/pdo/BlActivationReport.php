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
class BlActivationReport extends \DAL\DalSlim {

    /**    
     * @author Okan CIRAN
     * @ sys_activation_report tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  04.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
        try {           
        } catch (\PDOException $e /* Exception $e */) {           
        }
    }

    /**
     * basic select from database  example for PDO prepared
     * statements, table names are irrevelant and should be changed on specific 
     * @author Okan CIRAN
     * @ sys_activation_report tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  04.02.2016  
     * @param array | null $args  
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $statement = $pdo->prepare("
                    SELECT 
                        a.id,                        
                        a.s_datetime,  
                        a.s_date,
                        a.operation_type_id,
                        op.operation_name,                         
			a.language_id, 			
                        a.language_code, 
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                                                
                        a.op_user_id,
                        u.username,
                        acl.name as role_name,
                        a.service_name,                         
                        a.table_name,
                        a.about_id
                    FROM sys_activation_report a    
                    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0
                    INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active =0 
                    INNER JOIN info_users u ON u.id = a.op_user_id                      
                    INNER JOIN sys_acl_roles acl ON acl.id = u.role_id   
                    ORDER BY a.s_date desc ,op.operation_name  
                          ");            
            $statement->execute();
            $result = $statement->fetcAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
           // $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * basic insert database example for PDO prepared
     * statements, table names are irrevelant and should be changed on specific 
     * @author Okan CIRAN
     * @ sys_activation_report tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  04.02.2016
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
           // $pdo->beginTransaction();         
            $statement = $pdo->prepare("
                INSERT INTO sys_activation_report(
                        op_user_id, 
                        operation_type_id,                         
                        language_id, 
                        language_code, 
                        service_name, 
                        table_name, 
                        about_id
                        )
                VALUES (
                        :op_user_id, 
                        :operation_type_id,                         
                        :language_id, 
                        :language_code, 
                        :service_name, 
                        :table_name, 
                        :about_id
                                                ");
            $statement->bindValue(':op_user_id', $params['op_user_id'], \PDO::PARAM_INT);
            $statement->bindValue(':operation_type_id', $params['operation_type_id'], \PDO::PARAM_INT);            
            //$statement->bindValue(':language_parent_id', $params['language_parent_id'], \PDO::PARAM_INT);
            $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
            $statement->bindValue(':service_name', $params['service_name'], \PDO::PARAM_STR);
            $statement->bindValue(':table_name', $params['table_name'], \PDO::PARAM_STR);
            $statement->bindValue(':about_id', $params['about_id'], \PDO::PARAM_INT);
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('sys_activation_report_id_seq');
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //$pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {
            //$pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * basic update database example for PDO prepared
     * statements, table names are irrevelant and should be changed on specific
     * @author Okan CIRAN
     * sys_activation_report tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  04.02.2016
     * @param array | null $args  
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
        } catch (\PDOException $e /* Exception $e */) {            
        }
    }
    
    /**
     * 
     * @author Okan CIRAN
     * @ public key e ait danışmanın gerçekleştirdiği operasyonları ve adetlerinin döndürür  !!
     * @version v 1.0  04.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getConsultantOperation($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');             
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));            
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId['resultSet'][0]['user_id'];
                
            $sql = "     
               SELECT count(a.id) AS adet , 
                    a.operation_type_id,
                    op.operation_name as aciklama
                FROM sys_activation_report a    
                INNER JOIN sys_operation_types op ON op.parent_id = 2 AND op.id = a.operation_type_id  AND op.deleted =0 AND op.active =0
                INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active =0 
                INNER JOIN info_users u ON u.id = a.op_user_id      
                INNER JOIN sys_acl_roles acl ON acl.id = u.role_id  
                WHERE 
                    a.op_user_id = ".intval($opUserIdValue)."
                GROUP BY a.operation_type_id, op.operation_name
                ORDER BY op.operation_name
                    ";  
            $statement = $pdo->prepare($sql);
            // echo debugPDO($sql, $params);
            $statement->execute();       
            $result = $statement->fetchAll(\PDO::FETCH_CLASS);        
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            return json_encode($result);
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pk';              
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
          //  $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
 
     
    /**
     * 
     * @author Okan CIRAN
     * @ Aktif firma sayısını döndürür  !!
     * @version v 1.0  05.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getAllFirmCount($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');  
            $sql = "     
                SELECT 
                    COUNT(id) AS adet ,
                    'Firma Sayısı' AS aciklama
                FROM info_firm_profile 
                WHERE deleted =0 AND active =0                
                    ";  
            $statement = $pdo->prepare($sql);
            $statement->execute();       
            $result = $statement->fetchAll(\PDO::FETCH_CLASS);        
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            return json_encode($result);         
        } catch (\PDOException $e /* Exception $e */) {
          //  $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
     /**
     * 
     * @author Okan CIRAN
     * @ Aktif firma sayısını döndürür  !!
     * @version v 1.0  05.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getConsultantFirmCount($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');             
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));            
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId['resultSet'][0]['user_id'];
                
            $sql = "     
                SELECT 
                    COUNT(id) AS adet ,
                    'Firma Sayısı' AS aciklama
                FROM info_firm_profile 
                WHERE deleted =0 AND active =0 AND                 
                     consultant_id = ".intval($opUserIdValue)."
                
                    ";  
            $statement = $pdo->prepare($sql);
              echo debugPDO($sql, $params);
            $statement->execute();       
            $result = $statement->fetchAll(\PDO::FETCH_CLASS);        
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            return json_encode($result);
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pk';        
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {      
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

 
      /**
     * 
     * @author Okan CIRAN
     * @ Aktif firma sayısını döndürür  !!
     * @version v 1.0  05.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getConsultantUpDashBoardCount($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');             
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));            
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId['resultSet'][0]['user_id'];
                
            $sql = "  
                
SELECT ids,aciklama,adet FROM  (
				SELECT ids, aciklama, adet from (
						SELECT      
						    1 as ids, 
						    cast('Toplam Firma Sayısı' as character varying(50))  AS aciklama,                      
						     cast(COALESCE(count(a.id),0) as character varying(5))   AS adet                          
						FROM info_firm_profile a
						WHERE deleted =0 AND active =0  
				) as dasda
                   UNION 
				SELECT   ids,  aciklama, adet from (
						SELECT 
						    2 as ids, 
						    cast('Onaylanmış Firma Sayısı' as character varying(50))  AS aciklama,   
						      cast(COALESCE(count(a.id),0) as character varying(5))   AS adet                    
						FROM info_firm_profile a
						INNER JOIN sys_operation_types op ON op.parent_id = 2 AND a.operation_type_id = op.id AND op.active = 0 AND op.deleted =0
						WHERE a.deleted =0 AND a.active =0 AND
						      a.operation_type_id = 5
				) as dasdb
                     UNION 
				SELECT  ids,   aciklama,    adet from (
						SELECT  3 as ids,
						 cast('Danışmanın Firma Sayısı' as character varying(50))  AS aciklama,                      
						        cast(COALESCE(count(a.id),0) as character varying(5))   AS adet                     
						FROM info_firm_profile a                                    
						INNER JOIN info_users u ON u.id = a.consultant_id      
						INNER JOIN sys_acl_roles acl ON acl.id = u.role_id  
						WHERE a.active = 0 AND a.deleted = 0 AND 
						    a.consultant_id = ".intval($opUserIdValue)." 
				) as dasc
                    UNION 
				SELECT  ids, aciklama, adet from (
						SELECT   4 as ids,  
						cast('Danışman Onayı Bekleyen Firma' as character varying(50))  AS aciklama,                      
						    cast(COALESCE(count(a.id),0) as character varying(5))   AS adet                         
						FROM info_firm_profile a                                    
						INNER JOIN info_users u ON u.id = a.consultant_id      
						INNER JOIN sys_acl_roles acl ON acl.id = u.role_id  
						INNER JOIN sys_operation_types op ON op.parent_id = 1 AND a.operation_type_id = op.id AND op.active = 0 AND op.deleted =0
						WHERE 
						    a.consultant_id =".intval($opUserIdValue)." 
				 ) as dasdd
				 
		   ) AS ttemp
               ORDER BY ids 
                    ";  
            
            
            $statement = $pdo->prepare($sql);
            //  echo debugPDO($sql, $params);
            $statement->execute();       
            $result = $statement->fetchAll(\PDO::FETCH_CLASS);        
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            return json_encode($result);
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pk';        
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);            }
        } catch (\PDOException $e /* Exception $e */) {  
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

     /**
     * 
     * @author Okan CIRAN
     * @ Danışmanın onay bekleyen firmalarının bilgilerini döndürür  !!
     * @version v 1.0  05.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    
    public function getConsWaitingForConfirm($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');             
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));            
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId['resultSet'][0]['user_id'];
                
            $sql = "                   
               SELECT 
                    aciklama , 
                    CASE
                        WHEN sure_int > 50000 THEN CAST(SUBSTRING(sure FROM 1 FOR POSITION(' ' IN sure )-1 ) AS integer)
                    ELSE 0 
                    END AS sure
                FROM (
                    SELECT a.id,
                        EXTRACT(EPOCH FROM age(a.s_date)) AS sure_int,  
			a.firm_name AS aciklama, 
                       CAST(CURRENT_TIMESTAMP - a.s_date AS VARCHAR(20)) AS sure
                    FROM  info_firm_profile a                 
                    INNER JOIN info_users u ON u.id = a.consultant_id   
                    INNER JOIN sys_operation_types op ON op.parent_id = 1 AND a.operation_type_id = op.id AND op.active = 0 AND op.deleted =0
                    WHERE 
                        a.consultant_id = ".intval($opUserIdValue)."                     
                    ) AS asdasd
                ORDER BY sure DESC
                LIMIT 6
  
                    ";  
            $statement = $pdo->prepare($sql);
          // echo debugPDO($sql, $params);
            $statement->execute();       
            $result = $statement->fetchAll(\PDO::FETCH_CLASS);        
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            return json_encode($result);
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pk';          
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

 
   
}
