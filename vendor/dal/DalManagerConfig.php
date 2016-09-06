<?php

/**
 * OSTİM TEKNOLOJİ Framework 
 * Ğİ
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace DAL;

/**
 * class called for DAL manager config 
 * DAL manager uses Zend Service manager and 
 * config class is compliant zend service config structure
 * @author Mustafa Zeynel Dağlı
 */
class DalManagerConfig {

    /**
     * constructor
     */
    public function __construct() {
        
    }

    /**
     * config array for zend service manager config
     * @var array
     */
    protected $config = array(
        // Initial configuration with which to seed the ServiceManager.
        // Should be compatible with Zend\ServiceManager\Config.
        'service_manager' => array(
            'invokables' => array(
            //'test' => 'Utill\BLL\Test\Test'
            ),
            'factories' => [
                'reportConfigurationPDO' => 'DAL\Factory\PDO\ReportConfigurationFactory',
                'cmpnyEqpmntPDO' => 'DAL\Factory\PDO\CmpnyEqpmntFactory',
                'sysNavigationLeftPDO' => 'DAL\Factory\PDO\SysNavigationLeftFactory',
                'sysSectorsPDO' => 'DAL\Factory\PDO\SysSectorsFactory',
                'infoUsersPDO' => 'DAL\Factory\PDO\InfoUsersFactory',
                'sysCountrysPDO' => 'DAL\Factory\PDO\SysCountrysFactory',
                'sysCityPDO' => 'DAL\Factory\PDO\SysCityFactory',
                'sysLanguagePDO' => 'DAL\Factory\PDO\SysLanguageFactory',
                'sysBoroughPDO' => 'DAL\Factory\PDO\SysBoroughFactory',
                'sysVillagePDO' => 'DAL\Factory\PDO\SysVillageFactory',      
                'blLoginLogoutPDO' => 'DAL\Factory\PDO\BlLoginLogoutFactory',   
                'infoFirmProfilePDO' => 'DAL\Factory\PDO\InfoFirmProfileFactory',   
                'sysAclRolesPDO' => 'DAL\Factory\PDO\SysAclRolesFactory',   
                'sysAclResourcesPDO' => 'DAL\Factory\PDO\SysAclResourcesFactory',   
                'sysAclPrivilegePDO' => 'DAL\Factory\PDO\SysAclPrivilegeFactory',   
                'sysAclRrpMapPDO' => 'DAL\Factory\PDO\SysAclRrpMapFactory',  
                'sysSpecificDefinitionsPDO' => 'DAL\Factory\PDO\SysSpecificDefinitionsFactory', 
                'infoUsersCommunicationsPDO' => 'DAL\Factory\PDO\InfoUsersCommunicationsFactory', 
                'infoUsersAddressesPDO' => 'DAL\Factory\PDO\InfoUsersAddressesFactory', 
                'blActivationReportPDO' => 'DAL\Factory\PDO\BlActivationReportFactory', 
                'sysOsbConsultantsPDO' => 'DAL\Factory\PDO\SysOsbConsultantsFactory', 
                'sysOsbPDO' => 'DAL\Factory\PDO\SysOsbFactory', 
                'sysOperationTypesPDO' => 'DAL\Factory\PDO\SysOperationTypesFactory',
                'sysOperationTypesToolsPDO' => 'DAL\Factory\PDO\SysOperationTypesToolsFactory', 
                'infoErrorPDO' => 'DAL\Factory\PDO\InfoErrorFactory', 
                'sysMachineToolGroupsPDO' => 'DAL\Factory\PDO\SysMachineToolGroupsFactory', 
                'sysMachineToolsPDO' => 'DAL\Factory\PDO\SysMachineToolsFactory',
                'sysMachineToolPropertyDefinitionPDO' => 'DAL\Factory\PDO\SysMachineToolPropertyDefinitionFactory',
                'sysMachineToolPropertiesPDO' => 'DAL\Factory\PDO\SysMachineToolPropertiesFactory',
                'sysUnitsPDO' => 'DAL\Factory\PDO\SysUnitsFactory',
                'infoFirmMachineToolPDO' => 'DAL\Factory\PDO\InfoFirmMachineToolFactory',
                'sysNaceCodesPDO' => 'DAL\Factory\PDO\SysNaceCodesFactory',
                'hstryLoginPDO' => 'DAL\Factory\PDO\HstryLoginFactory',
                'logConnectionPDO' => 'DAL\Factory\PDO\LogConnectionFactory',
                'logServicePDO' => 'DAL\Factory\PDO\LogServiceFactory',
                
                  
            ],
        ),
    );

    /**
     * return config array for zend service manager config
     * @return array | null
     * @author Mustafa Zeynel Dağlı
     */
    public function getConfig() {
        return $this->config['service_manager'];
    }

}
