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
class InfoFirmMachineTool extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ info_firm_machine_tool tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0 18.02.2016
     * @param array | null $args
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
                UPDATE info_firm_machine_tool
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
     * @ info_firm_machine_tool tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  18.02.2016   
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }
            $statement = $pdo->prepare("
                     SELECT 
                        a.id,                        
                        a.firm_id,  
                        fp.firm_name,                                                                       
			a.s_date, 
                        a.c_date, 
			a.sys_machine_tool_id,
                        COALESCE(NULLIF(smt.machine_tool_name, ''), smt.machine_tool_name_eng) AS machine_tool_names,
                        smt.machine_tool_name_eng, 
                        a.profile_public, 
                        sd19.description AS state_profile_public, 
                        a.operation_type_id,
                        op.operation_name, 			
			a.act_parent_id,  
                        a.language_code, 
                        a.language_id, 
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                        
                        a.active, 
                        sd16.description AS state_active,  
                        a.deleted,
			sd15.description AS state_deleted, 
                        a.op_user_id,
                        u.username AS op_user,  
                        fp.owner_user_id AS owner_id ,
                        own.username as owner_username,
                        a.cons_allow_id,
                        sd14.description AS cons_allow,
                        a.availability_id ,
                        sd119.description AS state_availability,
                        a.language_parent_id
                    FROM info_firm_machine_tool a    		    
                    INNER JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0                      
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 
                    INNER JOIN info_users own ON own.id = fp.owner_user_id    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.language_id = lx.id  AND a.cons_allow_id = sd14.first_group  AND sd14.deleted =0 AND sd14.active =0
		    INNER JOIN sys_operation_types op ON (op.id = a.operation_type_id OR op.language_parent_id = a.operation_type_id) and op.language_id =lx.id  AND op.deleted =0 AND op.active =0                    
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = lx.id    AND sd15.deleted =0 AND sd15.active =0 
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = lx.id    AND sd16.deleted = 0 AND sd16.active = 0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.language_id = lx.id    AND sd19.deleted = 0 AND sd19.active = 0                    
		    INNER JOIN sys_machine_tools smt ON (smt.id = sys_machine_tool_id OR smt.language_parent_id = sys_machine_tool_id) AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = lx.id
                    INNER JOIN sys_specific_definitions sd119 ON sd119.main_group = 19 AND sd119.first_group=a.availability_id  AND sd119.language_id = lx.id AND sd119.deleted = 0 AND sd119.active = 0                    
		    ORDER BY l.priority	 
                          ");
            $statement->execute();
            $result = $statement->fetcAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ info_firm_machine_tool tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0 15.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function haveRecords($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $addSql = " AND a.deleted =0  ";
            if (isset($params['id'])) {
                $addSql .= " AND a.id != " . intval($params['id']);
            }
            $sql = " 
            SELECT  
                smt.machine_tool_name AS name , 
                smt.machine_tool_name AS value , 
                a.sys_machine_tool_id  = " . intval($params['machine_id']) . " AS control,
                CONCAT(smt.machine_tool_name , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message                             
            FROM info_firm_machine_tool a 
            INNER JOIN sys_machine_tools smt ON smt.id = a.sys_machine_tool_id AND smt.deleted =0 AND smt.active =0 
            WHERE a.firm_id = " . intval($params['firm_id']) . "
                AND a.sys_machine_tool_id =  " . intval($params['machine_id']) . "
                   " . $addSql . "                  
                               ";
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

    /**
     * @author Okan CIRAN
     * @ info_firm_machine_tool tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif yapar. !!
     * @version v 1.0  09.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makePassive($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            //$pdo->beginTransaction();
            $statement = $pdo->prepare(" 
                UPDATE info_firm_machine_tool
                SET                         
                    c_date =  timezone('Europe/Istanbul'::text, ('now'::text)::timestamp(0) with time zone) ,                     
                    active = 1                    
                WHERE id = :id");
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            $update = $statement->execute();
            $afterRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //$pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
        } catch (\PDOException $e /* Exception $e */) {
            //$pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ info_firm_machine_tool tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  18.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();

            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $addSql = " op_user_id, ";
                    $addSqlValue = " " . $opUserIdValue . ",";

                    $addSql .= " operation_type_id,  ";
                    if ((isset($params['operation_type_id']) && $params['operation_type_id'] != "")) {
                        $addSqlValue .= " " . intval($params['operation_type_id']) . ",";
                    } ELSE {
                        $addSqlValue .= " 29,";
                    }

                    $getConsultant = SysOsbConsultants::getConsultantIdForCompany(array('category_id' => 1));
                    if (\Utill\Dal\Helper::haveRecord($getConsultant['resultSet'][0]['consultant_id'])) {
                        $ConsultantId = $getConsultant ['resultSet'][0]['consultant_id'];
                    } else {
                        $ConsultantId = 1001;
                    }
                    $addSql .= " consultant_id,  ";
                    $addSqlValue .= " " . intval($ConsultantId) . ",";
                  
                    if (isset($params['profile_public'])) {
                        $addSql .= " profile_public, ";
                        $addSqlValue .= intval($params['profile_public']) . ", ";
                    }
                    
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    } else {
                        $languageIdValue = 647;
                    }
                    $addSql .= " language_id, ";
                    $addSqlValue .= " " . $languageIdValue . ",";

                    $statement = $pdo->prepare("
                   INSERT INTO info_firm_machine_tool(
                        firm_id,                         
                        sys_machine_tool_id,
                        language_code,
                        availability_id,
                         " . $addSql . "
                        act_parent_id
                        )
                VALUES (
                        :firm_id,                        
                        :sys_machine_tool_id,
                        :language_code, 
                        :availability_id, 
                         " . $addSqlValue . "
                        (SELECT last_value FROM info_firm_machine_tool_id_seq)
                        ");
                    $statement->bindValue(':firm_id', $params['firm_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':sys_machine_tool_id', $params['machine_id'], \PDO::PARAM_INT);                    
                    $statement->bindValue(':availability_id', $params['availability_id'], \PDO::PARAM_INT);                    
                    $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);                    
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('info_firm_machine_tool_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();

                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    // 23505  unique_violation
                    $errorInfo = '23505';
                    $errorInfoColumn = 'sys_machine_tool_id';
                    $pdo->rollback();
                    // $result = $kontrol;
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
     * info_firm_machine_tool tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  18.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];

                $kontrol = $this->haveRecords($params);
                if (\Utill\Dal\Helper::haveRecord($kontrol)) {

                    $this->makePassive(array('id' => $params['id']));
                    $addSql = " op_user_id, ";
                    $addSqlValue = " " . intval($opUserIdValue) . ",";

                    $addSql .= " operation_type_id,  ";
                    if ((isset($params['operation_type_id']) && $params['operation_type_id'] != "")) {
                        $addSqlValue .= " " . intval($params['operation_type_id']) . ",";
                    } ELSE {
                        $addSqlValue .= " 30,";
                    }

                    if ((isset($params['active']) && $params['active'] != "")) {
                        $addSql .= " active,  ";
                        $addSqlValue .= " " . intval($params['active']) . ",";
                    }
                    if ((isset($params['availability_id']) && $params['availability_id'] != "")) {
                        $addSql .= " availability_id,  ";
                        $addSqlValue .= " " . intval($params['availability_id']) . ",";
                    }

                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    } else {
                        $languageIdValue = 647;
                    }

             


                    $statement_act_insert = $pdo->prepare(" 
                 INSERT INTO info_firm_machine_tool(
                        profile_public, 
                        " . $addSql . "
                        firm_id, 
                        sys_machine_tool_id, 
                        consultant_id,                           
                        act_parent_id, 
                        language_code                          
                                         

                        )
                        SELECT  
                            " . intval($params['profile_public']) . " AS profile_public, 
                            " . $addSqlValue . "
                            " . intval($params['country_id']) . " AS country_id,                             
                            '" . $params['firm_name'] . "' AS firm_name, 
                            '" . $params['web_address'] . "' AS web_address, 
                            '" . $params['tax_office'] . "' AS tax_office, 
                            '" . $params['tax_no'] . "' AS tax_no, 
                            '" . $params['sgk_sicil_no'] . "' AS sgk_sicil_no, 
                            " . intval($params['ownership_status_id']) . " AS ownership_status_id, 
                            '" . $params['foundation_year'] . "' AS foundation_year, 
                            '" . $params['language_code'] . "' AS language_code,                             
                            '" . $params['firm_name_eng'] . "' AS firm_name_eng, 
                            '" . $params['firm_name_short'] . "' AS firm_name_short,
                            act_parent_id,  
                            auth_allow_id,
                             " . intval($languageIdValue) . " AS language_id,
                            '" . $params['description'] . "' AS description, 
                            '" . $params['description_eng'] . "' AS description_eng, 
                            '" . $params['duns_number'] . "' AS duns_number                                              
                        FROM info_firm_machine_tool 
                        WHERE id =  " . intval($params['id']) . " 
                        ");

                    $insert_act_insert = $statement_act_insert->execute();
                    $affectedRows = $statement_act_insert->rowCount();
                    $errorInfo = $statement_act_insert->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    // 23505  unique_violation
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
     * Datagrid fill function used for testing
     * user interface datagrid fill operation   
     * @author Okan CIRAN
     * @ Gridi doldurmak için info_firm_machine_tool tablosundan kayıtları döndürür !!
     * @version v 1.0  18.02.2016
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
        $whereSql = "";
        if (isset($args['sort']) && $args['sort'] != "") {
            $sort = trim($args['sort']);
            $sortArr = explode(",", $sort);
            if (count($sortArr) === 1)
                $sort = trim($args['sort']);
        } else {
            $sort = "l.priority,fp.firm_name,a.s_date	";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
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


        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $sql = "
                     SELECT 
                        a.id,                        
                        a.firm_id,  
                        fp.firm_name,                                                                       
			a.s_date, 
                        a.c_date, 
			a.sys_machine_tool_id,
                        COALESCE(NULLIF(smt.machine_tool_name, ''), smt.machine_tool_name_eng) AS machine_tool_names,
                        smt.machine_tool_name_eng, 
                        a.profile_public, 
                        sd19.description AS state_profile_public, 
                        a.operation_type_id,
                        op.operation_name, 			
			a.act_parent_id,  
                        a.language_code, 
                        a.language_id, 
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                        
                        a.active, 
                        sd16.description AS state_active,  
                        a.deleted,
			sd15.description AS state_deleted, 
                        a.op_user_id,
                        u.username as op_user,  
                        fp.owner_user_id  as owner_id ,
                        own.username as owner_username,
                        a.cons_allow_id,
                        sd14.description AS cons_allow  ,
                        a.availability_id ,
                        sd119.description AS state_availability,
                        a.language_parent_id
                    FROM info_firm_machine_tool a    		    
                    INNER JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0                      
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 
                    INNER JOIN info_users own ON own.id = fp.owner_user_id    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.language_id = lx.id  AND a.cons_allow_id = sd14.first_group  AND sd14.deleted =0 AND sd14.active =0
		    INNER JOIN sys_operation_types op ON (op.id = a.operation_type_id OR op.language_parent_id = a.operation_type_id) and op.language_id =lx.id  AND op.deleted =0 AND op.active =0                    
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = lx.id AND sd15.deleted =0 AND sd15.active =0 
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = lx.id AND sd16.deleted = 0 AND sd16.active = 0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.language_id = lx.id AND sd19.deleted = 0 AND sd19.active = 0                    
		    INNER JOIN sys_machine_tools smt ON (smt.id = sys_machine_tool_id OR smt.language_parent_id = sys_machine_tool_id) AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = lx.id
                    INNER JOIN sys_specific_definitions sd119 ON sd119.main_group = 19 AND sd119.first_group=a.availability_id  AND sd119.language_id = lx.id AND sd119.deleted = 0 AND sd119.active = 0                    
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
     * @ Gridi doldurmak için info_firm_machine_tool tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  18.02.2016
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
            $whereSQL = "   ";
            $whereSQL1 = " WHERE ax.deleted = 1 ";
            $whereSQL2 = " WHERE ay.deleted = 0 ";

            $sql = "
                 SELECT 
                    COUNT(a.id) AS COUNT , 
                    (SELECT COUNT(ax.id) FROM info_firm_machine_tool ax    
                    INNER JOIN sys_language lax ON lax.id = " . intval($languageIdValue) . " AND lax.deleted =0 AND lax.active =0                      
                    INNER JOIN sys_language lx ON lx.id = ax.language_id AND lx.deleted =0 AND lx.active =0                
                    INNER JOIN info_users ux ON ux.id = ax.op_user_id
                    INNER JOIN info_firm_profile fpx ON fpx.id = ax.firm_id AND fpx.active = 0 AND fpx.deleted = 0 
                    INNER JOIN info_users ownx ON ownx.id = fpx.owner_user_id    
		    INNER JOIN sys_specific_definitions sd14x ON sd14x.main_group = 14 AND sd14x.language_id = lax.id  AND ax.cons_allow_id = sd14x.first_group AND sd14x.deleted =0 AND sd14x.active =0
		    INNER JOIN sys_operation_types opx ON (opx.id = ax.operation_type_id OR opx.language_parent_id = ax.operation_type_id) and opx.language_id =lax.id AND opx.deleted =0 AND opx.active =0                    
		    INNER JOIN sys_specific_definitions sd15x ON sd15x.main_group = 15 AND sd15x.first_group= ax.deleted AND sd15x.language_id = lax.id AND sd15x.deleted =0 AND sd15x.active =0 
		    INNER JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.first_group= ax.active AND sd16x.language_id = lax.id AND sd16x.deleted = 0 AND sd16x.active = 0
		    INNER JOIN sys_specific_definitions sd19x ON sd19x.main_group = 19 AND sd19x.first_group= ax.profile_public AND sd19x.language_id = lax.id AND sd19x.deleted = 0 AND sd19x.active = 0                    
		    INNER JOIN sys_machine_tools smtx ON (smtx.id = sys_machine_tool_id OR smtx.language_parent_id = ax.sys_machine_tool_id) AND smtx.active =0 AND smtx.deleted = 0 AND smtx.language_id = lax.id
                    INNER JOIN sys_specific_definitions sd119x ON sd119x.main_group = 19 AND sd119x.first_group=ax.availability_id AND sd119x.language_id = lax.id AND sd119x.deleted = 0 AND sd119x.active = 0
                   
                    " . $whereSQL1 . "
		  ) AS undeleted_count,
                    (SELECT COUNT(ay.id) FROM info_firm_machine_tool ay    
                     INNER JOIN sys_language lay ON lay.id = " . intval($languageIdValue) . " AND lay.deleted =0 AND lay.active =0                      
                    INNER JOIN sys_language ly ON ly.id = ay.language_id AND ly.deleted =0 AND ly.active =0                
                    INNER JOIN info_users uy ON uy.id = ay.op_user_id
                    INNER JOIN info_firm_profile fpy ON fpy.id = ay.firm_id AND fpy.active = 0 AND fpy.deleted = 0 
                    INNER JOIN info_users owny ON owny.id = fpy.owner_user_id    
		    INNER JOIN sys_specific_definitions sd14y ON sd14y.main_group = 14 AND sd14y.language_id = lay.id  AND ay.cons_allow_id = sd14y.first_group AND sd14y.deleted =0 AND sd14y.active =0
		    INNER JOIN sys_operation_types opy ON (opy.id = ay.operation_type_id OR opy.language_parent_id = ay.operation_type_id) and opy.language_id =lay.id  AND opy.deleted =0 AND opy.active =0                    
		    INNER JOIN sys_specific_definitions sd15y ON sd15y.main_group = 15 AND sd15y.first_group= ay.deleted AND sd15y.language_id = lay.id AND sd15y.deleted =0 AND sd15y.active =0 
		    INNER JOIN sys_specific_definitions sd16y ON sd16y.main_group = 16 AND sd16y.first_group= ay.active AND sd16y.language_id = lay.id AND sd16y.deleted = 0 AND sd16y.active = 0
		    INNER JOIN sys_specific_definitions sd19y ON sd19y.main_group = 19 AND sd19y.first_group= ay.profile_public AND sd19y.language_id = lay.id AND sd19y.deleted = 0 AND sd19y.active = 0                    
		    INNER JOIN sys_machine_tools smty ON (smty.id = sys_machine_tool_id OR smty.language_parent_id = ay.sys_machine_tool_id) AND smty.active =0 AND smty.deleted = 0 AND smty.language_id = lay.id                   
                    INNER JOIN sys_specific_definitions sd119y ON sd119y.main_group = 19 AND sd119y.first_group=ay.availability_id AND sd119y.language_id = lay.id AND sd119y.deleted = 0 AND sd119y.active = 0                                        
		    " . $whereSQL2 . " 
		    ) AS deleted_count 
		FROM info_firm_machine_tool a    
                INNER JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0                      
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                
                INNER JOIN info_users u ON u.id = a.op_user_id
                INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 
                INNER JOIN info_users own ON own.id = fp.owner_user_id    
                INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.language_id = lx.id  AND a.cons_allow_id = sd14.first_group  AND sd14.deleted =0 AND sd14.active =0
                INNER JOIN sys_operation_types op ON (op.id = a.operation_type_id OR op.language_parent_id = a.operation_type_id) and op.language_id =lx.id  AND op.deleted =0 AND op.active =0                    
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = lx.id    AND sd15.deleted =0 AND sd15.active =0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = lx.id    AND sd16.deleted = 0 AND sd16.active = 0
                INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.language_id = lx.id    AND sd19.deleted = 0 AND sd19.active = 0                    
                INNER JOIN sys_machine_tools smt ON (smt.id = a.sys_machine_tool_id OR smt.language_parent_id = sys_machine_tool_id) AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = lx.id
                INNER JOIN sys_specific_definitions sd119 ON sd119.main_group = 19 AND sd119.first_group=a.availability_id  AND sd119.language_id = lx.id AND sd119.deleted = 0 AND sd119.active = 0                    
		     
                 " . $whereSQL . "'
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
     *  
     * @author Okan CIRAN
     * @ seçilmiş olan user_id nin sahip oldugu firmaları combobox a doldurmak için kayıtları döndürür   !!
     * @version v 1.0  18.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillComboBox($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (!\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                } else {
                    $languageIdValue = 647;
                }
                $sql = "            
                SELECT 
                    a.id,                     
                    COALESCE(NULLIF(a.firm_name, ''), a.firm_name_eng) AS name
                FROM info_firm_machine_tool  a               
                WHERE 
                    a.active =0 AND 
                    a.deleted = 0 AND 
                    a.language_id = " . intval($languageIdValue) . " AND 
                    a.owner_user_id = " . intval($opUserIdValue) . "             
                ORDER BY  name                
                                 ";
                $statement = $pdo->prepare($sql);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
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
     * usage     
     * @author Okan CIRAN
     * @ info_firm_machine_tool tablosuna aktif olan diller için ,tek bir kaydın tabloda olmayan diğer dillerdeki kayıtlarını oluşturur   !!
     * @version v 1.0  18.02.2016
     * @return array
     * @throws \PDOException
     */
    public function insertLanguageTemplate($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $statement = $pdo->prepare("                 
                    
                    INSERT INTO info_firm_machine_tool(
                        language_parent_id, firm_name,firm_name_eng, 
			profile_public, f_check, s_date, active, country_id, 
			operation_type_id,  web_address, tax_office, 
			tax_no, sgk_sicil_no, ownership_status_id, foundation_year,  
			act_parent_id, bagkur_sicil_no, deleted, 
			auth_allow_id, owner_user_id, firm_name_short ,op_user_id,   language_code)  
                    SELECT                          
			language_parent_id,  
                        firm_name,
                        firm_name_eng, 
			profile_public, 
                        f_check, 
                        s_date,                         
                        active, 
                        country_id, 
			operation_type_id,  
                        web_address, 
                        tax_office, 
			tax_no, 
                        sgk_sicil_no, 
                        ownership_status_id, 
                        foundation_year,  
			act_parent_id, 
                        bagkur_sicil_no, 
                        deleted, 
			auth_allow_id,  
                        owner_user_id, 
                        firm_name_short ,
                        op_user_id, 
                        language_main_code 
                    FROM ( 
                            SELECT 
				c.id AS language_parent_id,                                
				'' AS firm_name, 
                                c.firm_name_eng, 
                                c.profile_public, 
                                0 AS f_check, 
                                c.s_date,                                 
                                0 AS active, 
                                c.country_id, 
				1 AS operation_type_id,  
                                c.web_address, 
                                c.tax_office, 
				c.tax_no, 
                                c.sgk_sicil_no, 
                                c.ownership_status_id, 
                                c.foundation_year,  
				0 AS act_parent_id, 
                                c.bagkur_sicil_no, 
                                0 AS deleted, 
				c.auth_allow_id,  
                                c.owner_user_id, 
                                c.firm_name_short ,					 
                                c.op_user_id, 		                               
                                l.language_main_code
                            FROM info_firm_machine_tool c
                            LEFT JOIN sys_language l ON l.deleted =0 AND l.active =0 
                            WHERE c.id = " . intval($params['id']) . "
                    ) AS xy  
                    WHERE xy.language_main_code NOT IN 
                        (SELECT 
                            DISTINCT language_code 
                         FROM info_firm_machine_tool cx 
                         WHERE (cx.language_parent_id = " . intval($params['id']) . "
						OR cx.id = " . intval($params['id']) . "
					) AND cx.deleted =0 AND cx.active =0)

                            ");

            //   $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);

            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('info_firm_machine_tool_id_seq');
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
     * 
     * @author Okan CIRAN
     * @ text alanları doldurmak için info_firm_machine_tool tablosundan tek kayıt döndürür !! 
     * insertLanguageTemplate fonksiyonu ile oluşturulmuş kayıtları 
     * combobox dan çağırmak için hazırlandı.
     * @version v 1.0  18.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillTextLanguageTemplate($args = array()) {

        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $sql = "
                    SELECT 
                        a.id, 
                        a.profile_public, 
                        a.f_check, 
                        a.s_date, 
                        a.c_date, 
                        a.operation_type_id,
                        op.operation_name, 
                        a.firm_name, 
                        a.web_address,                     
                        a.tax_office, 
                        a.tax_no, 
                        a.sgk_sicil_no,
			a.bagkur_sicil_no,
			a.ownership_status_id,
                        sd4.description AS owner_ship,
			a.foundation_year,			
			a.act_parent_id,  
                        a.language_code, 
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                        
                        a.active, 
                        sd3.description AS state_active,  
                        a.deleted,
			sd2.description AS state_deleted, 
                        a.op_user_id,
                        u.username,                    
                        a.auth_allow_id, 
                        sd.description AS auth_alow ,
                        a.cons_allow_id,
                        sd1.description AS cons_allow,
                        a.language_parent_id,
                        a.owner_user_id,
                        u1.name as firm_owner_name,
                        u1.surname as firm_owner_surname,
                        a.firm_name_eng, 
                        a.firm_name_short
                    FROM info_firm_machine_tool a    
                    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id and  op.language_code = a.language_code  AND op.deleted =0 AND op.active =0
                    INNER JOIN sys_specific_definitions sd ON sd.main_group = 13 AND sd.language_code = a.language_code AND a.auth_allow_id = sd.first_group  AND sd.deleted =0 AND sd.active =0
                    INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 14 AND  sd1.language_code = a.language_code AND a.cons_allow_id = sd1.first_group  AND sd1.deleted =0 AND sd1.active =0
                    INNER JOIN sys_specific_definitions sd2 ON sd2.main_group = 15 AND sd2.first_group= a.deleted AND sd2.language_code = a.language_code AND sd2.deleted =0 AND sd2.active =0 
                    INNER JOIN sys_specific_definitions sd3 ON sd3.main_group = 16 AND sd3.first_group= a.active AND sd3.language_code = a.language_code AND sd3.deleted = 0 AND sd3.active = 0
                    LEFT JOIN sys_specific_definitions sd4 ON sd4.main_group = 1 AND sd4.first_group= a.active AND sd4.language_code = a.language_code AND sd4.deleted = 0 AND sd4.active = 0
                    INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active =0 
                    INNER JOIN info_users u ON u.id = a.op_user_id  
                    LEFT JOIN info_users u1 ON u1.id = a.owner_user_id  
                    WHERE 
                        a.language_code = :language_code AND 
                        a.language_parent_id = :language_parent_id AND
                        a.active = 0 AND 
                        a.deleted = 0

                    ";

            $statement = $pdo->prepare($sql);
            /**
             * For debug purposes PDO statement sql
             * uses 'Panique' library located in vendor directory
             */
            $statement->bindValue(':language_code', $args['language_code'], \PDO::PARAM_STR);
            $statement->bindValue(':language_parent_id', $args['id'], \PDO::PARAM_STR);


            //    echo debugPDO($sql, $parameters);

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
     * delete olayında önce kaydın active özelliğini pasif e olarak değiştiriyoruz. 
     * daha sonra deleted= 1 ve active = 1 olan kaydı oluşturuyor. 
     * böylece tablo içerisinde loglama mekanizması için gerekli olan kayıt oluşuyor.
     * @version 18.02.2016 
     * @param type $id
     * @param type $params
     * @return array
     * @throws PDOException
     */
    public function deletedAct($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (!\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];

                $this->makePassive(array('id' => $params['id']));

                $addSql = " op_user_id, ";
                $addSqlValue = " " . intval($opUserIdValue) . ",";
                $addSql .= " owner_user_id, ";
                $addSqlValue .= " owner_user_id,";
                $addSql .= " active,  ";
                $addSqlValue .= " 1,";
                $addSql .= " deleted,  ";
                $addSqlValue .= " 1,";
                $addSql .= " consultant_id,  ";
                $addSqlValue .= " consultant_id, ";
                $addSql .= " consultant_confirm_type_id,  ";
                $addSqlValue .= " consultant_confirm_type_id,  ";
                $addSql .= " confirm_id,  ";
                $addSqlValue .= " confirm_id,";


                $addSql .= " operation_type_id,  ";
                if ((isset($params['operation_type_id']) && $params['operation_type_id'] != "")) {
                    $addSqlValue .= " " . intval($params['operation_type_id']) . ",";
                } ELSE {
                    $addSqlValue .= " 3,";
                }


                $statement_act_insert = $pdo->prepare(" 
                 INSERT INTO info_firm_machine_tool(
                        profile_public, 
                        " . $addSql . "
                        country_id,                        
                        firm_name, 
                        web_address, 
                        tax_office, 
                        tax_no, 
                        sgk_sicil_no, 
                        ownership_status_id, 
                        foundation_year, 
                        language_code,                         
                        firm_name_eng, 
                        firm_name_short,
                        act_parent_id, 
                        auth_allow_id,
                        language_id,
                        description,
                        description_eng,
                        duns_number
                        )
                        SELECT  
                            profile_public, 
                            " . $addSqlValue . "
                            country_id,                             
                            firm_name, 
                            web_address, 
                            tax_office, 
                            tax_no, 
                            sgk_sicil_no, 
                            ownership_status_id, 
                            foundation_year, 
                            language_code,                             
                            firm_name_eng, 
                            firm_name_short,
                            act_parent_id,  
                            auth_allow_id,
                            language_id,
                            description, 
                            description_eng, 
                            duns_number                                              
                        FROM info_firm_machine_tool 
                        WHERE id =  " . intval($params['id']) . " 
                        ");

                $insert_act_insert = $statement_act_insert->execute();
                $affectedRows = $statement_act_insert->rowCount();
                $errorInfo = $statement_act_insert->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
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
     * Datagrid fill function used for testing
     * user interface datagrid fill operation   
     * @author Okan CIRAN
     * @ Gridi doldurmak için info_firm_machine_tool tablosundan kayıtları döndürür !!
     * @version v 1.0  09.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillSingularFirmMachineTools($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $ownerUser = $userId ['resultSet'][0]['user_id'];

                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                } else {
                    $languageIdValue = 647;
                }

                $sql = "
                    SELECT 
                        a.id,                        
                        a.firm_id,  
                        fp.firm_name,                                                                       
			a.s_date, 
                        a.c_date, 
			a.sys_machine_tool_id,
                        COALESCE(NULLIF(smt.machine_tool_name, ''), smt.machine_tool_name_eng) AS machine_tool_names,
                        smt.machine_tool_name_eng, 
			smt.model, 
			smt.model_year,
			smt.manufactuer_id,
			sm.manufacturer_name,
                        a.profile_public, 
                        sd19.description AS state_profile_public, 
                        a.operation_type_id,
                        op.operation_name, 					
                        a.language_code, 
                        a.language_id, 
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                        
                        a.active, 
                        sd16.description AS state_active,  
                        a.deleted,
			sd15.description AS state_deleted, 
                        a.op_user_id,
                        u.username as op_user_name,  
                        fp.owner_user_id AS owner_id ,
                        own.username AS owner_username, 
                        a.availability_id ,
                        sd119.description AS state_availability,
                        a.language_parent_id 
                    FROM info_firm_machine_tool a  
                    INNER JOIN sys_language lx ON lx.id =" . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0                      
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.owner_user_id = " . intval($ownerUser) . " AND (fp.language_parent_id =a.firm_id OR fp.id =a.firm_id)
                    INNER JOIN info_users own ON own.id = fp.owner_user_id    
                    INNER JOIN sys_machine_tools smt ON (smt.id = sys_machine_tool_id OR smt.language_parent_id = sys_machine_tool_id) AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = lx.id
                    INNER JOIN sys_manufacturer sm ON sm.id = smt.manufactuer_id AND (sm.id = smt.manufactuer_id OR sm.language_parent_id = smt.manufactuer_id)
		    INNER JOIN sys_operation_types op ON (op.id = a.operation_type_id OR op.language_parent_id = a.operation_type_id) and op.language_id =lx.id  AND op.deleted =0 AND op.active =0                    
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = lx.id AND sd15.deleted =0 AND sd15.active =0 
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = lx.id AND sd16.deleted = 0 AND sd16.active = 0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.language_id = lx.id AND sd19.deleted = 0 AND sd19.active = 0                    		    
                    INNER JOIN sys_specific_definitions sd119 ON sd119.main_group = 19 AND sd119.first_group=a.availability_id  AND sd119.language_id = lx.id AND sd119.deleted = 0 AND sd119.active = 0                    
		    ORDER BY l.priority                       
              
                ";
                $statement = $pdo->prepare($sql);
                //  echo debugPDO($sql, $parameters);                
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                $affectedRows = $statement->rowCount();       
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  user_id not_null_violation
                $errorInfoColumn = 'user_id';
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**
     * user interface datagrid fill operation get row count for widget
     * @author Okan CIRAN
     * @ Gridi doldurmak için info_firm_machine_tool tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  18.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillSingularFirmMachineToolsRtc($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $ownerUser = $userId ['resultSet'][0]['user_id'];
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                } else {
                    $languageIdValue = 647;
                }
                $whereSQL = "   ";
                $whereSQL1 = " WHERE ax.deleted = 1 ";
                $whereSQL2 = " WHERE ay.deleted = 0 ";

                $sql = "
                 SELECT 
                    COUNT(a.id) AS COUNT , 
                    (SELECT COUNT(ax.id) FROM info_firm_machine_tool ax    
                    INNER JOIN sys_language lax ON lax.id = " . intval($languageIdValue) . " AND lax.deleted =0 AND lax.active =0                      
                    INNER JOIN sys_language lx ON lx.id = ax.language_id AND lx.deleted =0 AND lx.active =0                
                    INNER JOIN info_users ux ON ux.id = ax.op_user_id                    
                    INNER JOIN info_firm_profile fpx ON fpx.id = ax.firm_id AND fpx.active = 0 AND fpx.deleted = 0 AND fpx.owner_user_id = " . intval($ownerUser) . "
                    INNER JOIN info_users ownx ON ownx.id = fpx.owner_user_id    
		    INNER JOIN sys_specific_definitions sd14x ON sd14x.main_group = 14 AND sd14x.language_id = lax.id  AND ax.cons_allow_id = sd14x.first_group AND sd14x.deleted =0 AND sd14x.active =0
		    INNER JOIN sys_operation_types opx ON (opx.id = ax.operation_type_id OR opx.language_parent_id = ax.operation_type_id) and opx.language_id =lax.id AND opx.deleted =0 AND opx.active =0                    
		    INNER JOIN sys_specific_definitions sd15x ON sd15x.main_group = 15 AND sd15x.first_group= ax.deleted AND sd15x.language_id = lax.id AND sd15x.deleted =0 AND sd15x.active =0 
		    INNER JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.first_group= ax.active AND sd16x.language_id = lax.id AND sd16x.deleted = 0 AND sd16x.active = 0
		    INNER JOIN sys_specific_definitions sd19x ON sd19x.main_group = 19 AND sd19x.first_group= ax.profile_public AND sd19x.language_id = lax.id AND sd19x.deleted = 0 AND sd19x.active = 0                    
		    INNER JOIN sys_machine_tools smtx ON (smtx.id = sys_machine_tool_id OR smtx.language_parent_id = ax.sys_machine_tool_id) AND smtx.active =0 AND smtx.deleted = 0 AND smtx.language_id = lax.id                   
                    INNER JOIN sys_specific_definitions sd119x ON sd119x.main_group = 19 AND sd119x.first_group=ax.availability_id AND sd119x.language_id = lax.id AND sd119x.deleted = 0 AND sd119x.active = 0                    
                    " . $whereSQL1 . "
		  ) AS undeleted_count,
                    (SELECT COUNT(ay.id) FROM info_firm_machine_tool ay    
                    INNER JOIN sys_language lay ON lay.id = " . intval($languageIdValue) . " AND lay.deleted =0 AND lay.active =0                      
                    INNER JOIN sys_language ly ON ly.id = ay.language_id AND ly.deleted =0 AND ly.active =0                
                    INNER JOIN info_users uy ON uy.id = ay.op_user_id
                    INNER JOIN info_firm_profile fpy ON fpy.id = ay.firm_id AND fpy.active = 0 AND fpy.deleted = 0 AND fpy.owner_user_id = " . intval($ownerUser) . "
                    INNER JOIN info_users owny ON owny.id = fpy.owner_user_id    
		    INNER JOIN sys_specific_definitions sd14y ON sd14y.main_group = 14 AND sd14y.language_id = lay.id  AND ay.cons_allow_id = sd14y.first_group AND sd14y.deleted =0 AND sd14y.active =0
		    INNER JOIN sys_operation_types opy ON (opy.id = ay.operation_type_id OR opy.language_parent_id = ay.operation_type_id) and opy.language_id =lay.id  AND opy.deleted =0 AND opy.active =0                    
		    INNER JOIN sys_specific_definitions sd15y ON sd15y.main_group = 15 AND sd15y.first_group= ay.deleted AND sd15y.language_id = lay.id AND sd15y.deleted =0 AND sd15y.active =0 
		    INNER JOIN sys_specific_definitions sd16y ON sd16y.main_group = 16 AND sd16y.first_group= ay.active AND sd16y.language_id = lay.id AND sd16y.deleted = 0 AND sd16y.active = 0
		    INNER JOIN sys_specific_definitions sd19y ON sd19y.main_group = 19 AND sd19y.first_group= ay.profile_public AND sd19y.language_id = lay.id AND sd19y.deleted = 0 AND sd19y.active = 0                    
		    INNER JOIN sys_machine_tools smty ON (smty.id = sys_machine_tool_id OR smty.language_parent_id = ay.sys_machine_tool_id) AND smty.active =0 AND smty.deleted = 0 AND smty.language_id = lay.id                   
                    INNER JOIN sys_specific_definitions sd119y ON sd119y.main_group = 19 AND sd119y.first_group=ay.availability_id AND sd119y.language_id = lay.id AND sd119y.deleted = 0 AND sd119y.active = 0                                        
		    " . $whereSQL2 . " 
		    ) AS deleted_count 
		FROM info_firm_machine_tool a    
                INNER JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0                      
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                
                INNER JOIN info_users u ON u.id = a.op_user_id                
                INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.owner_user_id = " . intval($ownerUser) . "
                INNER JOIN info_users own ON own.id = fp.owner_user_id    
                INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.language_id = lx.id AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0
                INNER JOIN sys_operation_types op ON (op.id = a.operation_type_id OR op.language_parent_id = a.operation_type_id) and op.language_id =lx.id AND op.deleted =0 AND op.active =0                    
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = lx.id AND sd15.deleted =0 AND sd15.active =0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = lx.id AND sd16.deleted = 0 AND sd16.active = 0
                INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.language_id = lx.id AND sd19.deleted = 0 AND sd19.active = 0                    
                INNER JOIN sys_machine_tools smt ON (smt.id = a.sys_machine_tool_id OR smt.language_parent_id = sys_machine_tool_id) AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = lx.id
                INNER JOIN sys_specific_definitions sd119 ON sd119.main_group = 19 AND sd119.first_group=a.availability_id  AND sd119.language_id = lx.id AND sd119.deleted = 0 AND sd119.active = 0                    
                " . $whereSQL . " 
                    ";
                $statement = $pdo->prepare($sql);
                //   echo debugPDO($sql, $params);  
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  user_id not_null_violation
                $errorInfoColumn = 'user_id';
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
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
    public function fillFirmMachineToolGroups($params = array()) {
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
            $statement = $pdo->prepare("                
                SELECT                    
                    a.id,                     
                    COALESCE(NULLIF(a.group_name, ''), a.group_name_eng) as name ,
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
                INNER JOIN sys_language lx ON lx.id = a.language_id AND lx.deleted =0 AND lx.active =0 
                WHERE                    
                    a.parent_id = " . intval($parentId) . " AND 
                    a.deleted = 0 AND a.language_id = " . intval($languageIdValue) . "  
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

    /**
     * user interface fill operation   
     * @author Okan CIRAN
     * @ tree doldurmak için sys_machine_tool tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  19.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUsersFirmMachines($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $ownerUser = $userId ['resultSet'][0]['user_id'];
                $addSql="";

                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                } else {
                    $languageIdValue = 647;
                }
                
                if (isset($params['id'])) {
                    $addSql .= " AND a.sys_machine_tool_id = " . intval($params['id']) . " ";
                }

                $sql = " 
               SELECT 
                    a.id,  
                    cast(a.sys_machine_tool_id as text) AS machine_id,
		    m.manufacturer_name ,  
		    COALESCE(NULLIF(mtg.group_name, ''), mtg.group_name_eng) AS machine_tool_grup_names ,  
                    COALESCE(NULLIF(mt.machine_tool_name, ''), mt.machine_tool_name_eng) AS machine_tool_names,
                    mt.model, 
                    cast(mt.model_year as text) AS model_year                                  
                FROM info_firm_machine_tool a
		INNER JOIN sys_language lx ON lx.id =" . intval($languageIdValue) . "  AND lx.deleted =0 AND lx.active =0                      
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  		
		INNER JOIN info_firm_users ifu on ifu.user_id = " . intval($ownerUser) . "  AND ifu.language_id = l.id 
                INNER JOIN info_firm_profile ifp on (ifp.id = ifu.firm_id OR ifp.language_parent_id = ifu.firm_id)  AND ifp.active =0 AND ifp.deleted =0  AND ifp.language_id = l.id
                INNER JOIN sys_machine_tools mt ON (mt.id = a.sys_machine_tool_id OR mt.language_parent_id = a.sys_machine_tool_id )AND mt.language_id = lx.id
                INNER JOIN sys_machine_tool_groups mtg ON (mtg.id = mt.machine_tool_grup_id OR mtg.language_parent_id = mt.machine_tool_grup_id )AND mtg.language_id = lx.id
                INNER JOIN sys_manufacturer m ON (m.id = mt.manufactuer_id OR m.language_parent_id = mt.manufactuer_id) AND m.language_id = lx.id             
                WHERE a.deleted =0 AND a.active =0                
                AND a.language_parent_id =0 
                ".$addSql."
                                 ";
                $statement = $pdo->prepare($sql);
                // echo debugPDO($sql, $params);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);              
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  user_id not_null_violation
                $errorInfoColumn = 'pk';
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    
    /**
     * user interface fill operation   
     * @author Okan CIRAN
     * @ treeyi dolduran servisde sys_machine_tool tablosundan çekilen kayıt sayısını döndürür !!
     * @version v 1.0  25.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUsersFirmMachinesRtc($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $ownerUser = $userId ['resultSet'][0]['user_id'];
                $addSql="";

                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                } else {
                    $languageIdValue = 647;
                }
                
                if (isset($params['id'])) {
                    $addSql .= " AND a.sys_machine_tool_id = " . intval($params['id']) . " ";
                }

                $sql = " 
               SELECT 
                    COUNT(a.id ) as COUNT                      
                FROM info_firm_machine_tool a
		INNER JOIN sys_language lx ON lx.id =" . intval($languageIdValue) . "  AND lx.deleted =0 AND lx.active =0                      
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  		
		INNER JOIN info_firm_users ifu on ifu.user_id = " . intval($ownerUser) . "  AND ifu.language_id = l.id 
                INNER JOIN info_firm_profile ifp on (ifp.id = ifu.firm_id OR ifp.language_parent_id = ifu.firm_id)  AND ifp.active =0 AND ifp.deleted =0  AND ifp.language_id = l.id
                INNER JOIN sys_machine_tools mt ON (mt.id = a.sys_machine_tool_id OR mt.language_parent_id = a.sys_machine_tool_id )AND mt.language_id = lx.id
                INNER JOIN sys_machine_tool_groups mtg ON (mtg.id = mt.machine_tool_grup_id OR mtg.language_parent_id = mt.machine_tool_grup_id )AND mtg.language_id = lx.id
                INNER JOIN sys_manufacturer m ON (m.id = mt.manufactuer_id OR m.language_parent_id = mt.manufactuer_id) AND m.language_id = lx.id             
                WHERE a.deleted =0 AND a.active =0                
                AND a.language_parent_id =0 
                ".$addSql."
                                 ";
                $statement = $pdo->prepare($sql);
                // echo debugPDO($sql, $params);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);              
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  user_id not_null_violation
                $errorInfoColumn = 'pk';
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
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
    public function fillUsersFirmMachineProperties($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $ownerUser = $userId ['resultSet'][0]['user_id'];
                $addSql="";

                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                } else {
                    $languageIdValue = 647;
                }
                
                if (isset($params['machine_id'])) {
                    $addSql .= " AND a.sys_machine_tool_id = " . intval($params['machine_id']) . " ";
                }

                $sql = " 
               	SELECT 
                    mtp.id, 
                    cast(a.sys_machine_tool_id as text) as machine_id ,		   
		     COALESCE(NULLIF(pd.property_name, ''), pd.property_name_eng) AS property_names,
                     pd.property_name_eng,
		     mtp.property_value, 
		     u.id AS unit_id,
                    COALESCE(NULLIF(u.unitcode, ''), u.unitcode_eng) AS unitcodes                  
                FROM info_firm_machine_tool a
		INNER JOIN sys_language lx ON lx.id =" . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0                      
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  		
		INNER JOIN info_firm_users ifu ON ifu.user_id = " . intval($ownerUser) . " AND ifu.language_id = l.id 
                INNER JOIN info_firm_profile ifp ON (ifp.id = ifu.firm_id OR ifp.language_parent_id = ifu.firm_id)  AND ifp.active =0 AND ifp.deleted =0  AND ifp.language_id = l.id
                INNER JOIN sys_machine_tools mt ON (mt.id = a.sys_machine_tool_id OR mt.language_parent_id = a.sys_machine_tool_id )AND mt.language_id = lx.id
                LEFT JOIN sys_machine_tool_properties mtp ON mtp.machine_tool_id = a.sys_machine_tool_id AND mtp.language_id = lx.id
                LEFT JOIN sys_machine_tool_property_definition pd ON (pd.id = mtp.machine_tool_property_definition_id OR pd.language_parent_id = mtp.machine_tool_property_definition_id) AND pd.language_id = lx.id             
                LEFT JOIN sys_units u ON (u.id = mtp.unit_id OR u.language_parent_id = mtp.unit_id) AND u.language_id = lx.id
                WHERE a.deleted =0 AND a.active =0  
                AND a.language_parent_id =0 
                ".$addSql."
                                 ";
                $statement = $pdo->prepare($sql);
            //    echo debugPDO($sql, $params);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC); 
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  user_id not_null_violation
                $errorInfoColumn = 'pk';
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

}
