<?php
if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('NO direct script access allowed'); }

/**
 * Class defining the Certs Config object stored on EE_Registry::instance->CFG
 *
 * @since 1.0.0
 *
 * @package EE Certs
 * @subpackage config
 * @author Kinna Thompson
 */
class EE_Cert_Config extends EE_Config_Base {

    public $has_credits;

    public $ce_credits;

    public $basic;

    public $display;

    public $style;

    public $fields;

    /**
     * constructor
     * @since 1.0.0
     */
    public function __construct() {
        $this->has_credits = false;
        $this->ce_credits = '';
        $this->basic = new EE_Cert_Config_Basic();
        $this->display = new EE_Cert_Config_Display();
        $this->style = new EE_Cert_Config_Style();
        $this->fields = new EE_Cert_Config_Fields();
    }

    /**
    *     to_flat_array
    *
    * All nested config classes properties are 'flattened'.
    * Eg, $this->basic->enable becomes array key 'basic_enable' in the newly formed array
    *
    * @return array
    */
    public function to_flat_array(){
        $flattened_vars = array();
        $properties = get_object_vars($this);
        foreach($properties as $name => $property){
            if($property instanceof EE_Config_Base){
                $sub_config_properties = get_object_vars($property);
                foreach($sub_config_properties as $sub_config_property_name => $sub_config_property){
                    $flattened_vars[$name."_".$sub_config_property_name] = $sub_config_property;
                }
            }else{
                $flattened_vars[$name] = $property;
            }
        }
        return $flattened_vars;
    }
    }

class EE_Cert_Config_Basic extends EE_Config_Base {

        public $enable_certs;
        public $default_credits;

        public function __construct(){
            $this->enable_certs = 'true';
            $this->default_credits = '';
        }
}

class EE_Cert_Config_Display extends EE_Config_Base {

        public $cert_company;
        public $company_phone;

        public function __construct(){
            $this->cert_company = '<b>Example Company</b><br/>100 Street Address<br/>City, State Zip';
            $this->company_phone = '123-456-7890';
        }
}

 //end EE_Certs_Config
