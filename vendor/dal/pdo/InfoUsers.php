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
 * example DAL layer class for test purposes
 * @author Mustafa Zeynel Dağlı
 */
class InfoUsers extends \DAL\DalRabbitMQ {

    
    public function test() {
        $pdo = $this->getServiceLocator()->get('pgConnectFactory');
    }


    /**
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
                    UPDATE info_users 
                    SET deleted = 1, active =1,
                    user_id = " . $userIdValue . "                     
                    WHERE id = :id
                    ");
                $update = $statement->execute();
                $affectedRows = $statement->rowCount();
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
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
     * @param array | null $args
     * @return type
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $statement = $pdo->prepare(" 
                    SELECT
                        a.id, 
                        ad.profile_public, 
                        ad.f_check, 
                        a.s_date, 
                        a.c_date, 
                        a.operation_type_id,
                        op.operation_name, 
                        ad.name, 
                        ad.surname, 
                        a.username, 
                        a.password, 
                        ad.auth_email,                   
                        ad.language_code, 
                        ad.language_id, 
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,
                        sd2.description AS state_deleted, 
                        a.active, 
                        sd3.description AS state_active,  
                        a.deleted, 
                        a.op_user_id,
                        u.username AS kaydi_yaratan ,
                        u1.username AS enson_islem_yapan ,
                        ad.act_parent_id, 
                        ad.auth_allow_id, 
                        sd.description AS auth_alow ,
                        a.cons_allow_id,
                        sd1.description AS cons_allow,                     
                        ad.root_id,
                        a.consultant_id,
                        cons.name AS cons_name, 
                        cons.surname AS cons_surname                        
                    FROM info_users a    
                    inner join info_users_detail ad  on ad.language_id = a.language_id AND ad.deleted =0 AND ad.active =0 and ad.root_id = a.id 
                    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id and  op.language_id = a.language_id
                    INNER JOIN sys_specific_definitions sd ON sd.main_group = 13 AND sd.language_id = a.language_id AND ad.auth_allow_id = sd.first_group 
                    INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 14 AND  sd1.language_id = a.language_id AND ad.cons_allow_id = sd1.first_group 
                    INNER JOIN sys_specific_definitions sd2 ON sd2.main_group = 15 AND sd2.first_group= a.deleted AND sd2.language_id = a.language_id AND sd2.deleted =0 AND sd2.active =0 
                    INNER JOIN sys_specific_definitions sd3 ON sd3.main_group = 16 AND sd3.first_group= a.active AND sd3.language_id = a.language_id AND sd3.deleted = 0 AND sd3.active = 0                    
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                    INNER JOIN info_users u ON u.id = a.op_user_id  
                    INNER JOIN info_users u1 ON u1.id = ad.op_user_id   
                    LEFT JOIN info_users_detail cons ON cons.root_id = a.consultant_id AND cons.active=0 AND cons.deleted = 0 
                    ORDER BY ad.name, ad.surname
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
     * @ info_users_details tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif yapar. !!
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
                UPDATE info_users
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
     * @ info_users tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif
     *  ve deleted 1 = silinmiş yapar. !!
     * @version v 1.0  09.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makeUserDeleted($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');        
            $statement = $pdo->prepare(" 
                UPDATE info_users 
                SET                         
                    c_date =  timezone('Europe/Istanbul'::text, ('now'::text)::timestamp(0) with time zone) ,                     
                    active = 1 ,
                    deleted= 1 
                WHERE id = :id");
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            $update = $statement->execute();
            $afterRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);            
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
        } catch (\PDOException $e /* Exception $e */) {            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ info_users tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0 20.01.2016
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
                username AS username , 
                '" . $params['username'] . "' AS value , 
                username ='" . $params['username'] . "' AS control,
                CONCAT(username , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message                             
            FROM info_users                
            WHERE   
                LOWER(username) = LOWER('" . $params['username'] . "') "
                    . $addSql . " 
               AND active =0         
               AND deleted =0   
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
     * @author Okan CIRAN
     * @ info_users tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0 20.01.2016
     * @return array
     * @throws \PDOException
     */
    public function haveEmail($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $addSql = "";
            if (isset($params['id'])) {
                $addSql = " AND id != " . intval($params['id']) . " ";
            }
            $sql = " 
            SELECT  
                auth_email AS auth_email , 
                '" . $params['auth_email'] . "' AS value , 
                auth_email ='" . $params['auth_email'] . "' AS control,
                CONCAT(auth_email , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message                             
            FROM info_users_detail                
            WHERE   
                LOWER(auth_email) = LOWER('" . $params['auth_email'] . "') "
                    . $addSql . " 
               AND active =0         
               AND deleted =0   
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

    /**
     * info_users tablosundaki kullanıcı kaydı oluşturur  !!
     * @author Okan CIRAN
     * @version v 1.0  26.01.2016
     * @param array | null $args
     * @return array
     * @throws PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();            
            $kontrol = $this->haveRecords($params); // username kontrolu
            if (!\Utill\Dal\Helper::haveRecord($kontrol)) { 
                $userId = $this->getUserId(array('pk' => $params['pk']));// bı pk var mı  
                if (!\Utill\Dal\Helper::haveRecord($userId)) {
                    $userIdValue = $userId ['resultSet'][0]['user_id'];
                    $addSql = " op_user_id,";
                    $addSqlValue = $userIdValue . ',';
                    
                    /// languageid sini alalım 
                    $addSql .= " language_id, ";
                    if (isset($params['preferred_language'])) {
                        $languageIdValue = $params['preferred_language'];
                    } else {
                        $languageIdValue = 647;
                    }
                    $addSqlValue = $languageIdValue . ',';
                    
                   //uzerinde az iş olan consultantı alalım.  
                   $getConsultant = SysOsbConsultants::getConsultantIdForUsers();              
                    if (\Utill\Dal\Helper::haveRecord($getConsultant)) {
                        $ConsultantId = $getConsultant ['resultSet'][0]['consultant_id'];
                    } else {
                        $ConsultantId = 1001;
                    } 
                    $sql = " 
                    INSERT INTO info_users(                           
                               operation_type_id, 
                               username, 
                               password, 
                               active,
                               " . $addSql . " 
                               role_id,
                               consultant_id
                                )
                    VALUES (  :operation_type_id, 
                              :username,
                              :password,                      
                              :active,                                          
                              " . $addSqlValue . "  
                              :role_id ,
                              ".  intval($ConsultantId)."
                        )";

                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':operation_type_id', $params['operation_type_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':username', $params['username'], \PDO::PARAM_STR);
                    $statement->bindValue(':password', $params['password'], \PDO::PARAM_STR);
                    $statement->bindValue(':role_id', $params['role_id'], \PDO::PARAM_INT);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('info_users_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);


                    /*
                     * kullanıcı için gerekli olan private key ve value değerleri yaratılılacak.  
                     * kullanıcı için gerekli olan private key temp ve value temp değerleri yaratılılacak.  
                     */
                    $this->setPrivateKey(array('id' => $insertID));
                   
                    /*
                     * kullanıcı bilgileri info_users_detail tablosuna kayıt edilecek.   
                     */
                    $this->insertDetail(
                            array(
                                'id' => $insertID,
                                'op_user_id' => $userIdValue,
                                'role_id' => 5,
                                'active' => $params['active'],
                                'operation_type_id' => $params['operation_type_id'],
                                'language_id' => $params['preferred_language'],
                                'profile_public' => $params['profile_public'],
                                'f_check' => 0,
                                'name' => $params['name'],
                                'surname' => $params['surname'],
                                'auth_email' => $params['auth_email'],
                                'act_parent_id' => $params['act_parent_id'],
                                'auth_allow_id' => 0,
                                'cons_allow_id' => 0,
                                'root_id' => $insertID,
                                'password' => $params['password'],
                    ));

                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23502';   // 23502  not_null_violation
                    $errorInfoColumn = 'pk';
                    $pdo->rollback();
                    return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
            } else {
                $errorInfo = '23505';   // 23505  unique_violation
                $errorInfoColumn = 'username';
                 $pdo->rollback();
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * info_users tablosundaki kullanıcı kaydı oluşturur  !!
     * @author Okan CIRAN
     * @version v 1.0  26.01.2016
     * @param array | null $args
     * @return array
     * @throws PDOException
     */
    public function insertDetail($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');                  
            $kontrol = $this->haveRecords($params);
            if (\Utill\Dal\Helper::haveRecord($kontrol)) {
                $sql = " 
                INSERT INTO info_users_detail(                           
                            profile_public,   
                            f_check,                          
                            operation_type_id, 
                            name, 
                            surname, 
                            auth_email, 
                            active,                
                            act_parent_id,  
                            auth_allow_id, 
                            cons_allow_id,
                            language_id,                             
                            root_id, 
                            op_user_id,
                            password,
                            consultant_id)
                VALUES (    :profile_public,   
                            :f_check,                          
                            :operation_type_id, 
                            :name, 
                            :surname, 
                            :auth_email, 
                            :active,                
                            :act_parent_id,  
                            :auth_allow_id, 
                            :cons_allow_id,
                            :language_id,                             
                            :root_id, 
                            :op_user_id ,
                            :password,
                            (SELECT consultant_id FROM info_users WHERE id = ".  intval($params['root_id'])." )                                
                    )";
                $statement = $pdo->prepare($sql);
                $statement->bindValue(':profile_public', $params['profile_public'], \PDO::PARAM_INT);
                $statement->bindValue(':f_check', $params['f_check'], \PDO::PARAM_INT);
                $statement->bindValue(':operation_type_id', $params['operation_type_id'], \PDO::PARAM_INT);
                $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
                $statement->bindValue(':surname', $params['surname'], \PDO::PARAM_STR);
                $statement->bindValue(':auth_email', $params['auth_email'], \PDO::PARAM_STR);
                $statement->bindValue(':active', $params['active'], \PDO::PARAM_INT);
                $statement->bindValue(':act_parent_id', $params['act_parent_id'], \PDO::PARAM_INT);
                $statement->bindValue(':auth_allow_id', $params['auth_allow_id'], \PDO::PARAM_INT);
                $statement->bindValue(':cons_allow_id', $params['cons_allow_id'], \PDO::PARAM_INT);
                $statement->bindValue(':password', $params['password'], \PDO::PARAM_STR);
                $statement->bindValue(':language_id', $params['language_id'], \PDO::PARAM_INT);
                $statement->bindValue(':root_id', $params['root_id'], \PDO::PARAM_INT);
                $statement->bindValue(':op_user_id', $params['op_user_id'], \PDO::PARAM_INT);
               // echo debugPDO($sql, $params);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('info_users_detail_id_seq');
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);              
                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
            } else {
                $errorInfo = '23502';   // 23502 info_users tablosunda bulunamadı. not_null_violation   
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '');
            }
        } catch (\PDOException $e /* Exception $e */) {         
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * Kullanıcı ilk kayıt ta "pk" sız olarak  cagırılacak servis.
     * Kullanıcıyı kaydeder. pk, pktemp, privatekey degerlerinin olusturur.  
     * @author Okan CIRAN
     * @version v 1.0 27.01.2016
     * @param array | null $args
     * @return array
     * @throws PDOException
     */
    public function insertTemp($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction(); 
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $getConsultant = SysOsbConsultants::getConsultantIdForUsers(array('category_id' => 0));              
                    if (\Utill\Dal\Helper::haveRecord($getConsultant)) {
                        $ConsultantId = $getConsultant ['resultSet'][0]['consultant_id'];
                    } else {
                        $ConsultantId = 1001;
                    }
                    
                    $sql = " 
                INSERT INTO info_users(                           
                        operation_type_id, 
                        username, 
                        password,                         
                        op_user_id,                            
                        language_id, 
                        role_id,
                        consultant_id
                              )      
                VALUES (1,
                        :username,
                        :password,
                        (SELECT last_value FROM info_users_id_seq),
                        :language_id,
                        5 ,
                       ".intval( $ConsultantId)."
                    )";
                    
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':username', $params['username'], \PDO::PARAM_STR);
                    $statement->bindValue(':password', $params['password'], \PDO::PARAM_STR);
                    $statement->bindValue(':language_id', $params['preferred_language'], \PDO::PARAM_INT);
                  //echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('info_users_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);

                    /*
                     * kullanıcı için gerekli olan private key ve value değerleri yaratılılacak.  
                     * kullanıcı için gerekli olan private key temp ve value temp değerleri yaratılılacak.  
                     */
                    $this->setPrivateKey(array('id' => $insertID));
                    /*
                     * kullanıcının public key temp değeri alınacak..  
                     */
                    $publicKeyTemp = $this->getPublicKeyTemp(array('id' => $insertID));             
                    if (\Utill\Dal\Helper::haveRecord($publicKeyTemp)) {
                        $publicKeyTempValue = $publicKeyTemp ['resultSet'][0]['pk_temp'];
                    } else {
                        $publicKeyTempValue = NULL;
                    }
                
                    /*
                     * kullanıcı bilgileri info_users_detail tablosuna kayıt edilecek.   
                     */
                    $this->insertDetail(
                            array(
                                'op_user_id' => $insertID,
                                'role_id' => 5,
                                'active' => 0,
                                'operation_type_id' => 1,
                                'language_id' => $params['preferred_language'],
                                'profile_public' => $params['profile_public'],
                                'f_check' => 0,
                                'name' => $params['name'],
                                'surname' => $params['surname'],
                                'username' => $params['username'],
                                'auth_email' => $params['auth_email'],
                                'act_parent_id' => 0,
                                'auth_allow_id' => 0,
                                'cons_allow_id' => 0,
                                'root_id' => $insertID,
                                'password' => $params['password'],
                                'consultant_id'=> $ConsultantId
                    ));

                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID, "pktemp" => $publicKeyTempValue);
                } else {
                    $errorInfo = '23505';   // 23505  unique_violation
                    $errorInfoColumn = 'username';
                     $pdo->rollback();
                    $result = $kontrol;
                    return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }             
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * parametre olarak gelen array deki 'id' li kaydın update ini yapar  !!
     * @author Okan CIRAN
     * @version v 1.0  26.01.2016     
     * @param array | null $args
     * @param type $params
     * @return array
     * @throws PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = $this->getUserId(array('pk' => $params['pk'], 'id' => $params['id']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $userIdValue = $userId ['resultSet'][0]['user_id'];
                $kontrol = $this->haveRecords($params);
                if ( \Utill\Dal\Helper::haveRecord($kontrol)) {
                    $addSql = " ";
                    $addSqlValue = "";
                    if (isset($params['f_check'])) {
                        $addSql .= " f_check, ";
                        $addSqlValue .= intval($params['f_check']) . ", ";
                    }
                    if (isset($params['auth_allow_id'])) {
                        $addSql .= " auth_allow_id, ";
                        $addSqlValue .= intval($params['auth_allow_id']) . ", ";
                    }
                    $consultantId = 0;
                    if (isset($params['consultant_id'])) {
                        $addSql .= " consultant_id, ";
                        $addSqlValue .= intval($params['consultant_id']) . ", ";
                        $consultantId = $params['consultant_id'];
                    }
                    /*
                     * parametre olarak gelen array deki 'id' li kaydın, info_users tablosundaki 
                     * alanlarını update eder !! username update edilmez.  
                     */
                    $this->updateInfoUsers(array('id' => $userIdValue,
                        'op_user_id' => $userIdValue,
                        'role_id' => $params['role_id'],
                        'active' => $params['active'],
                        'operation_type_id' => $params['operation_type_id'],
                        'language_id' => $params['preferred_language'],
                        'password' => $params['password'],
                        'consultant_id' => $consultantId,
                    ));
                    /*
                     *  parametre olarak gelen array deki 'id' li kaydın, info_users_details tablosundaki 
                     * active = 0 ve deleted = 0 olan kaydın active alanını 1 yapar  !!
                     */
                    $this->setUserDetailsDisables(array('id' => $userIdValue));
                    $sql = " 
                    INSERT INTO info_users_detail(
                           profile_public,                            
                           operation_type_id, 
                           " . $addSql . "
                           name, 
                           surname,                           
                           auth_email,                            
                           language_id,                           
                           op_user_id,                                                       
                           root_id,
                           act_parent_id,
                           password,
                           auth_allow_id,
                           cons_allow_id
                            ) 
                           SELECT 
                                " . intval($params['profile_public']) . " AS profile_public,                           
                                " . intval($params['operation_type_id']) . " AS operation_type_id, 
                                " . $addSqlValue . "
                                '" . $params['name'] . "' AS name, 
                                '" . $params['surname'] . "' AS surname,                                 
                                '" . $params['auth_email'] . "' AS auth_email,   
                                '" . $params['preferred_language'] . "' AS language_id,   
                                " . intval($userIdValue) . " AS user_id,
                                COALESCE(NULLIF(a.root_id, 0), " . intval($params['id']) . " ),
                                a.act_parent_id,
                                '" . $params['password'] . "' AS password ,
                                a.auth_allow_id,
                                a.cons_allow_id
                            FROM info_users_detail a
                            WHERE a.root_id  =" . intval($params['id']) . " 
                                AND a.active =1 AND a.deleted =0 AND 
                                a.c_date = (SELECT MAX(b.c_date)  
						FROM info_users_detail b WHERE b.root_id =a.root_id
						AND b.active =1 AND b.deleted =0)  
                    ";
                    $statementActInsert = $pdo->prepare($sql);
                    //   echo debugPDO($sql, $params);
                    $insertAct = $statementActInsert->execute();
                    $insertID = $pdo->lastInsertId('info_users_detail_id_seq');
                    $errorInfo = $statementActInsert->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows, "newId" => $insertID);
                } else {
                    $errorInfo = '23505';  // 23505  unique_violation 
                    $pdo->rollback();
                    $result = $kontrol;
                    return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '');
                }
            } else {
                $errorInfo = '23502';  /// 23502 user_id not_null_violation
                $pdo->rollback();
                $result = $kontrol;
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '');
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * parametre olarak gelen array deki 'id' li kaydın update ini yapar  !!
     * @author Okan CIRAN
     * @version v 1.0  26.01.2016     
     * @param array | null $args
     * @param type $params
     * @return array
     * @throws PDOException
     */
    public function updateTemp($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = $this->getUserIdTemp(array('pktemp' => $params['pktemp']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $userIdValue = $userId ['resultSet'][0]['user_id'];
                $kontrol = $this->haveRecords($params);
                if (\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $addSql = " ";
                    $addSqlValue = "";
                    $active = 0;
                    $operationTypeId = 2 ; 
                    $roleId = 5;
                    $consultantId = 0 ;                   
                    
                    if ((isset($params['active']) && $params['active'] != "")) {
                        $active= " " . intval($params['active']) ; 
                        $addSql .= " active,  ";
                        $addSqlValue .= " " . $active . ",";                       
                    }
                    $addSql .= " operation_type_id,  ";                    
                    if ((isset($params['operation_type_id']) && $params['operation_type_id'] != "")) {
                        $operationTypeId = intval($params['operation_type_id']) ;
                    }  
                    $addSqlValue .= $operationTypeId . ",";
                    
                    $addSql .= " role_id,  ";                    
                    if ((isset($params['role_id']) && $params['role_id'] != "")) {
                        $roleId = intval($params['role_id']) ;
                    }  
                    $addSqlValue .= $roleId . ",";
                    
                    /*
                     * parametre olarak gelen array deki 'id' li kaydın, info_users tablosundaki 
                     * alanlarını update eder !! username update edilmez.  
                     */
                    $this->updateInfoUsers(array('id' => $userIdValue,
                        'op_user_id' => $userIdValue,
                        'role_id' => 5,
                        'active' => $active,
                        'operation_type_id' => $operationTypeId,
                        'language_id' => $params['preferred_language'],
                        'password' => $params['password'],
                        'consultant_id' => $consultantId,
                    ));

                    /*
                     *  parametre olarak gelen array deki 'id' li kaydın, info_users_details tablosundaki 
                     * active = 0 ve deleted = 0 olan kaydın active alanını 1 yapar  !!
                     */
                    $this->setUserDetailsDisables(array('id' =>$userIdValue));

                    $sql = " 
                    INSERT INTO info_users_detail(
                           profile_public,  
                           " . $addSql . "
                           name, 
                           surname,                           
                           auth_email,                            
                           language_id,                           
                           op_user_id,                                                       
                           root_id,
                           act_parent_id,
                           password,
                           auth_allow_id,
                           cons_allow_id
                            ) 
                           SELECT 
                                " . intval($params['profile_public']) . " AS profile_public, 
                                " . $addSqlValue . "
                                '" . $params['name'] . "' AS name, 
                                '" . $params['surname'] . "' AS surname,                                 
                                '" . $params['auth_email'] . "' AS auth_email,   
                                '" . $params['preferred_language'] . "' AS language_id,   
                                " . intval($userIdValue) . " AS user_id,
                                COALESCE(NULLIF(root_id, 0), " . intval($userIdValue) . " ),
                                act_parent_id,
                                '" . $params['password'] . "' AS password ,
                                auth_allow_id,
                                cons_allow_id
                            FROM info_users_detail a
                            WHERE root_id  =" . intval($userIdValue) . "                               
                                AND active =1 AND deleted =0 and 
                                c_date = (SELECT MAX(c_date)  
						FROM info_users_detail WHERE root_id =a.root_id
						AND active =1 AND deleted =0) 
 
                    ";
                    $statementActInsert = $pdo->prepare($sql);
                  //  echo debugPDO($sql, $params);
                    $affectedRows1 = 1 ; 
                    $insertAct = $statementActInsert->execute();
                    $insertID = $pdo->lastInsertId('info_users_detail_id_seq');
                    $errorInfo = $statementActInsert->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows1);
                } else {
                    $errorInfo = '23505';  // 23505  unique_violation 
                    $pdo->rollback();
                    $result = $kontrol;
                    return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '');
                }
            } else {
                $errorInfo = '23502';  /// 23502 user_id not_null_violation
                $pdo->rollback();
                $result = $kontrol;
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '');
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     *       
     * parametre olarak gelen array deki 'id' li kaydın, info_users_details tablosundaki 
     * active = 0 ve deleted = 0 olan kaydın active alanını 1 yapar  !!
     * @author Okan CIRAN
     * @version v 1.0  29.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function setUserDetailsDisables($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            // $pdo->beginTransaction();           
            $sql = "
                UPDATE info_users_detail
                    SET
                        c_date =  timezone('Europe/Istanbul'::text, ('now'::text)::timestamp(0) with time zone) , 
                        active = 1 
                    WHERE root_id = :id AND active = 0 AND deleted = 0 
                    ";
             $statement = $pdo->prepare($sql); 
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            echo debugPDO($sql, $params);
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);           
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {         
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     *       
     * parametre olarak gelen array deki 'id' li kaydın, info_users tablosundaki 
     * alanlarını update eder !! username update edilmez. 
     * @author Okan CIRAN
     * @version v 1.0  29.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function updateInfoUsers($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');          
            $addSql = " ";
            if (isset($params['consultant_id']) && $params['consultant_id'] > 0) {
                $addSql .= "consultant_id =" . intval($params['consultant_id']) . ", ";
            }

            $statement = $pdo->prepare("
                UPDATE info_users
                    SET
                        c_date = timezone('Europe/Istanbul'::text, ('now'::text)::timestamp(0) with time zone) , 
                        active = :active, 
                        operation_type_id = :operation_type_id,
                        password = :password, 
                        language_id = :language_id,
                        role_id = :role_id,
                        " . $addSql . "
                        op_user_id = :op_user_id  
                    WHERE root_id = :id  
                    ");

            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            $statement->bindValue(':active', $params['active'], \PDO::PARAM_INT);
            $statement->bindValue(':operation_type_id', $params['operation_type_id'], \PDO::PARAM_INT);
            $statement->bindValue(':password', $params['password'], \PDO::PARAM_STR);
            $statement->bindValue(':language_id', $params['language_id'], \PDO::PARAM_INT);
            $statement->bindValue(':role_id', $params['role_id'], \PDO::PARAM_INT);
            $statement->bindValue(':op_user_id', $params['op_user_id'], \PDO::PARAM_INT);
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);         
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {         
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * Datagrid fill function used for testing
     * user interface datagrid fill operation
     * @param array | null $args
     * @return Array
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
            $sort = "ad.name";
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
        }else {
            $languageIdValue = 647;
         }
        $whereSql .= " AND a.language_id= ".intval($languageIdValue) ;


        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $sql = "    
                   SELECT
                        a.id, 
                        ad.profile_public, 
                        ad.f_check, 
                        a.s_date, 
                        a.c_date, 
                        a.operation_type_id,
                        op.operation_name, 
                        ad.name, 
                        ad.surname, 
                        a.username, 
                        a.password, 
                        ad.auth_email,                   
                        ad.language_code, 
                        ad.language_id,
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,
                        sd2.description AS state_deleted, 
                        a.active, 
                        sd3.description AS state_active,  
                        a.deleted, 
                        a.op_user_id,
                        u1.username AS enson_islem_yapan ,
                        ad.act_parent_id, 
                        ad.auth_allow_id, 
                        sd.description AS auth_alow ,
                        a.cons_allow_id,
                        sd1.description AS cons_allow,                     
                        ad.root_id,
                        a.consultant_id,
                        cons.name as cons_name, 
                        cons.surname as cons_surname                        
                    FROM info_users a    
                    inner join info_users_detail ad on ad.language_id = a.language_id AND ad.deleted =0 AND ad.active =0 and ad.root_id = a.id 
                    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id and  op.language_id = a.language_id
                    INNER JOIN sys_specific_definitions sd ON sd.main_group = 13 AND sd.language_id = a.language_id AND ad.auth_allow_id = sd.first_group 
                    INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 14 AND  sd1.language_id = a.language_id AND ad.cons_allow_id = sd1.first_group 
                    INNER JOIN sys_specific_definitions sd2 ON sd2.main_group = 15 AND sd2.first_group= a.deleted AND sd2.language_id = a.language_id AND sd2.deleted =0 AND sd2.active =0 
                    INNER JOIN sys_specific_definitions sd3 ON sd3.main_group = 16 AND sd3.first_group= a.active AND sd3.language_id = a.language_id AND sd3.deleted = 0 AND sd3.active = 0                    
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0              
                    LEFT JOIN info_users u1 ON u1.id = ad.op_user_id AND u1.language_id = a.language_id  
                    LEFT JOIN info_users_detail cons ON cons.root_id  = a.consultant_id AND cons.active=0 AND cons.deleted = 0 
                    WHERE a.deleted =0  
                    ".$whereSql."                   
                    ORDER BY  " . $sort . " "
                    . "" . $order . " "
                    . "LIMIT " . $pdo->quote($limit) . " "
                    . "OFFSET " . $pdo->quote($offset) . " ";
             
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
     * @param array | null $params
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');            
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            }else {
                $languageIdValue = 647;
             }
            $whereSql .= "  a.language_id = ".intval($languageIdValue);
            $whereSql1 .= " WHERE a1.deleted = 0 AND a1.language_id = ".intval($languageIdValue);
            $whereSql2 .= " WHERE a2.deleted = 1 AND a2.language_id = ".intval($languageIdValue);
             
            $sql = "
                   SELECT 
                        count(a.id) as count ,
                        (SELECT count(a1.id) AS toplam FROM info_users a1  		   
                        INNER JOIN sys_operation_types op1 ON op1.id = a1.operation_type_id and op1.language_id = a1.language_id
                        INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 13 AND sd1.language_id = a1.language_id AND a1.auth_allow_id = sd1.first_group 
                        INNER JOIN sys_specific_definitions sd11 ON sd11.main_group = 14 AND  sd11.language_code = a1.language_code AND a1.cons_allow_id = sd11.first_group 
                        INNER JOIN sys_specific_definitions sd21 ON sd21.main_group = 15 AND sd21.first_group= a1.deleted AND sd21.language_id = a1.language_id AND sd21.deleted =0 AND sd21.active =0 
                        INNER JOIN sys_specific_definitions sd31 ON sd31.main_group = 16 AND sd31.first_group= a1.active AND sd31.language_id = a1.language_id AND sd31.deleted = 0 AND sd31.active = 0
                        INNER JOIN sys_specific_definitions sd41 ON sd41.main_group = 3 AND sd41.first_group= a1.active AND sd41.language_id = a1.language_id AND sd41.deleted = 0 AND sd41.active = 0
                        INNER JOIN sys_language l1 ON l1.id = a1.language_id AND l1.deleted =0 AND l1.active =0 
                        INNER JOIN info_users u1 ON u1.id = a1.user_id                           
                             " . $whereSql1 . ") AS undeleted_count,                         
                        (SELECT count(a2.id) AS toplam FROM info_users a2
                        INNER JOIN sys_operation_types op2 ON op2.id = a2.operation_type_id and op2.language_id = a2.language_id
                        INNER JOIN sys_specific_definitions sd2 ON sd2.main_group = 13 AND sd2.language_id = a2.language_id AND a2.auth_allow_id = sd2.first_group 
                        INNER JOIN sys_specific_definitions sd12 ON sd12.main_group = 14 AND sd12.language_id = a2.language_id AND a2.cons_allow_id = sd12.first_group 
                        INNER JOIN sys_specific_definitions sd22 ON sd22.main_group = 15 AND sd22.first_group = a2.deleted AND sd22.language_id = a2.language_id AND sd22.deleted =0 AND sd22.active =0 
                        INNER JOIN sys_specific_definitions sd32 ON sd32.main_group = 16 AND sd32.first_group = a2.active AND sd32.language_id = a2.language_id AND sd32.deleted = 0 AND sd32.active = 0
                        INNER JOIN sys_specific_definitions sd42 ON sd42.main_group = 3 AND sd42.first_group = a2.active AND sd42.language_id = a2.language_id AND sd42.deleted = 0 AND sd42.active = 0
                        INNER JOIN sys_language l2 ON l2.id = a2.language_id AND l2.deleted =0 AND l2.active =0 
                        INNER JOIN info_users u2 ON u2.id = a2.user_id                        
                             " . $whereSql2 . " ) AS deleted_count                  
                    FROM info_users a  		   
		    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id and  op.language_id = a.language_id
		    INNER JOIN sys_specific_definitions sd ON sd.main_group = 13 AND sd.language_id = a.language_id AND a.auth_allow_id = sd.first_group 
		    INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 14 AND  sd1.language_id = a.language_id AND a.cons_allow_id = sd1.first_group 
		    INNER JOIN sys_specific_definitions sd2 ON sd2.main_group = 15 AND sd2.first_group= a.deleted AND sd2.language_id = a.language_id AND sd2.deleted =0 AND sd2.active =0 
		    INNER JOIN sys_specific_definitions sd3 ON sd3.main_group = 16 AND sd3.first_group= a.active AND sd3.language_id = a.language_id AND sd3.deleted = 0 AND sd3.active = 0
		    INNER JOIN sys_specific_definitions sd4 ON sd4.main_group = 3 AND sd4.first_group= a.active AND sd4.language_id = a.language_id AND sd4.deleted = 0 AND sd4.active = 0
		    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
		    INNER JOIN info_users u ON u.id = a.user_id 		   
                    " . $whereSql . " 
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
     * @param type $id
     * @param type $params
     * @return array
     * @throws PDOException
     */
    public function deletedAct($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = $this->getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $userIdValue = $userId ['resultSet'][0]['user_id']; 
                $this->setUserDetailsDisables(array('id' => $userIdValue));
                $this->makeUserDeleted(array('id' => $userIdValue));
                $sql = " 
                    INSERT INTO info_users_detail(
                           profile_public,   
                           f_check,
                           s_date,
                           c_date,
                           operation_type_id, 
                           name,
                           surname,                                                                        
                           auth_email,  
                           act_parent_id,
                           auth_allow_id,
                           cons_allow_id,
                           language_code,
                           language_id,
                           root_id,
                           op_user_id,
                           language_id,
                           password,                           
                           active,
                           deleted,
                            ) 
                           SELECT 
                                profile_public,                           
                                f_check,   
                                s_date,                              
                                timezone('Europe/Istanbul'::text, ('now'::text)::timestamp(0) with time zone) , 
                                3,
                                name,
                                surname,                                                                        
                                auth_email,  
                                act_parent_id,
                                auth_allow_id,
                                cons_allow_id,
                                language_code,
                                language_id,
                                root_id,                               
                                " . intval($userIdValue) . " AS op_user_id,
                                language_id,
                                password,   
                               1,
                               1
                            FROM info_users_detail 
                            WHERE root_id  =" . intval($userIdValue) . " 
                                AND active =0 AND deleted =0 
 
                    "; 
                $insert_act_insert = $statement_act_insert->execute();
                $affectedRows = $statement_act_insert->rowCount();
                $errorInfo = $statement_act_insert->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
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
     *       
     * parametre olarak gelen array deki 'id' li kaydın, info_users tablosundaki private key ve value değerlerini oluşturur  !!
     * @author Okan CIRAN
     * @version v 1.0  26.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function setPrivateKey($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');                    
            $statement = $pdo->prepare("
                UPDATE info_users
                SET              
                    sf_private_key = armor( pgp_sym_encrypt (username , oid, 'compress-algo=1, cipher-algo=bf')) ,
                    sf_private_key_temp = armor( pgp_sym_encrypt (username , oid_temp, 'compress-algo=1, cipher-algo=bf'))                   
                WHERE                   
                    id = :id");
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            $statementValue = $pdo->prepare("
                UPDATE info_users
                SET              
                    sf_private_key_value = substring(sf_private_key,40,length( trim( sf_private_key))-140)   ,
                    sf_private_key_value_temp = substring(sf_private_key_temp,40,length( trim( sf_private_key_temp))-140)  
                WHERE                     
                    id = :id");
            $statementValue->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            $updateValue = $statementValue->execute();
            $affectedRows = $statementValue->rowCount();
            $errorInfo = $statementValue->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);         
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {         
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * parametre olarak gelen array deki 'id' li kaydın, info_users tablosundaki  public key temp değerini döndürür !!
     * @author Okan CIRAN
     * @version v 1.0  26.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function getPublicKeyTemp($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $sql = " 
                SELECT 
                    REPLACE(TRIM(SUBSTRING(crypt(sf_private_key_value_temp,gen_salt('xdes')),6,20)),'/','*') as pk_temp ,              
                    id =" .intval( $params['id']) . " AS control
                FROM info_users 
                WHERE 
                     id =" .intval( $params['id']) . "
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
     * parametre olarak gelen array deki 'pk' nın, info_users tablosundaki user_id si değerini döndürür !!
     * @author Okan CIRAN
     * @version v 1.0  26.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function getUserId($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $sql = "  
                 SELECT id AS user_id, 1=1 AS control FROM (
                            SELECT id , 	
                                CRYPT(sf_private_key_value,CONCAT('_J9..',REPLACE('" . $params['pk'] . "','*','/'))) = CONCAT('_J9..',REPLACE('" . $params['pk'] . "','*','/')) as pkey                                
                            FROM info_users WHERE active =0 AND deleted =0) AS logintable
                        WHERE pkey = TRUE 
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
     * parametre olarak gelen array deki 'pk' yada 'pktemp' için info_users tablosundaki user_id si değerini döndürür !!
     * @author Okan CIRAN
     * @version v 1.0  27.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function getUserIdTemp($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            $sql = "  
                 SELECT id AS user_id , 1=1 AS control FROM (
                            SELECT id, 	                              
                                CRYPT(sf_private_key_value_Temp,CONCAT('_J9..',REPLACE('" . $params['pktemp'] . "','*','/'))) = CONCAT('_J9..',REPLACE('" . $params['pktemp'] . "','*','/')) AS pkeytemp                                    
                            FROM info_users WHERE active =0 AND deleted =0) AS logintable
                        WHERE pkeytemp = TRUE 
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
     *       
     * parametre olarak gelen array deki 'id' li kaydın, act_users_rrpmap tablosunda "New User" rolu verilerek kaydı oluşturulur. !!
     * insertTemp ve insert fonksiyonlarında kullanılacak.  
     * @author Okan CIRAN
     * @version v 1.0  27.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function setNewUserRrpMap($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');
            // $pdo->beginTransaction();           
            $statement = $pdo->prepare("
                INSERT INTO act_users_rrpmap(
                    info_users_id, rrpmap_id, user_id)
                VALUES (
                    :id, 
                    8,
                    :user_id )
                    ");
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            if ($params['user_id'] == 0)
                $statement->bindValue(':user_id', $params['id'], \PDO::PARAM_INT);
            else
                $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);     
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {  
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     *       
     * parametre olarak gelen array deki 'id' li kaydın, act_users_rrpmap tablosunda "New User" rolu verilerek kaydı oluşturulur. !!
     * insertTemp ve insert fonksiyonlarında kullanılacak.  
     * @author Okan CIRAN
     * @version v 1.0  27.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function updateNewUserRrpMap($params = array()) {
        try {
            $pdo = $this->getServiceLocator()->get('pgConnectFactory');                  
            $statement = $pdo->prepare("
                INSERT INTO act_users_rrpmap(
                    info_users_id, rrpmap_id, user_id)
                VALUES (
                    :id, 
                    8,
                    :user_id )
                    ");
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            if ($params['user_id'] == 0)
                $statement->bindValue(':user_id', $params['id'], \PDO::PARAM_INT);
            else
                $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {   
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }



}
