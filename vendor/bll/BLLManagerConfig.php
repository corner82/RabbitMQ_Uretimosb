<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 * Ğİ
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace BLL;

/**
 * Business LAyer MAnager config class
 * Uses zend Framework manager config infrastructure
 */
class BLLManagerConfig{
    
    /**
     * constructor
     */
    public function __construct() {
        
    }
    
    /**
     * config array for zend service manager config
     * @var array
     */
    protected $config= array(
        // Initial configuration with which to seed the ServiceManager.
        // Should be compatible with Zend\ServiceManager\Config.
         'service_manager' => array(
             'invokables' => array(
                 'test2' => 'BLL\Test',
                 //'test' => 'Utill\BLL\Test\Test'
                 'reportConfigurationBLL' => 'BLL\BLL\ReportConfiguration',
                 'cmpnyEqpmntBLL' => 'BLL\BLL\CmpnyEqpmnt',
                 'sysNavigationLeftBLL' => 'BLL\BLL\SysNavigationLeft',
                 'sysSectorsBLL' => 'BLL\BLL\SysSectors',
                 'infoUsersBLL' => 'BLL\BLL\InfoUsers',
                 'sysCountrysBLL' => 'BLL\BLL\SysCountrys',
                 'sysCityBLL' => 'BLL\BLL\SysCity',
                 'sysLanguageBLL' => 'BLL\BLL\SysLanguage',
                 'sysBoroughBLL' => 'BLL\BLL\SysBorough',
                 'sysVillageBLL' => 'BLL\BLL\SysVillage',
                 'blLoginLogoutBLL' => 'BLL\BLL\BlLoginLogout',
                 'infoFirmProfileBLL' => 'BLL\BLL\InfoFirmProfile',
                 'sysAclRolesBLL' => 'BLL\BLL\SysAclRoles',
                 'sysAclResourcesBLL' => 'BLL\BLL\SysAclResources',
                 'sysAclPrivilegeBLL' => 'BLL\BLL\SysAclPrivilege',
                 'sysAclRrpMapBLL' => 'BLL\BLL\SysAclRrpMap',  
                 'sysSpecificDefinitionsBLL' => 'BLL\BLL\SysSpecificDefinitions',   
                 'infoUsersCommunicationsBLL' => 'BLL\BLL\InfoUsersCommunications',   
                 'infoUsersAddressesBLL' => 'BLL\BLL\InfoUsersAddresses',   
                 'blActivationReportBLL' => 'BLL\BLL\BlActivationReport',
                 'sysOsbConsultantsBLL' => 'BLL\BLL\SysOsbConsultants',                 
                 'sysOsbBLL' => 'BLL\BLL\SysOsb',
                 'sysOperationTypesBLL' => 'BLL\BLL\SysOperationTypes',
                 'sysOperationTypesToolsBLL' => 'BLL\BLL\SysOperationTypesTools',
                 'infoErrorBLL' => 'BLL\BLL\InfoError',
                 'sysMachineToolGroupsBLL' => 'BLL\BLL\SysMachineToolGroups',
                 'sysMachineToolsBLL' => 'BLL\BLL\SysMachineTools',
                 'sysMachineToolPropertyDefinitionBLL' => 'BLL\BLL\SysMachineToolPropertyDefinition',
                 'sysMachineToolPropertiesBLL' => 'BLL\BLL\SysMachineToolProperties',
                 'sysUnitsBLL' => 'BLL\BLL\SysUnits',
                 'infoFirmMachineToolBLL' => 'BLL\BLL\InfoFirmMachineTool',
                 'sysNaceCodesBLL' => 'BLL\BLL\SysNaceCodes',
                 'hstryLoginBLL' => 'BLL\BLL\HstryLogin',
                 'logConnectionBLL' => 'BLL\BLL\LogConnection',
                 'serviceConnectionBLL' => 'BLL\BLL\LogServices',
                                  
             ),
             'factories' => [
                 //'reportConfigurationPDO' => 'BLL\BLL\ReportConfiguration',
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




