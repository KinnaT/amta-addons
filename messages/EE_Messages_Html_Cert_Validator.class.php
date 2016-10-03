<?php
/**
 * This contains the class for the Html+Certificate Message Template Validator.
 *
 * @since 4.5.0
 * @package Event Espresso
 * @subpackage messages
 */
if (!defined('EVENT_ESPRESSO_VERSION') ) exit('NO direct script access allowed');

/**
 *
 * EE_Messages_Html_Certificate_Validator
 *
 * This is a child class for the EE_Messages_Validator. Holds any special validation rules for template fields with Html Messenger and Certificate Message Type.
 *
 *
 * @since 1.0.0
 *
 * @package            Event Espresso
 * @subpackage        messages
 * @author            Kinna Thompson
 */
class EE_Messages_Html_Certificate_Validator extends EE_Messages_Validator {

    public function __construct( $fields, $context ) {
        $this->_m_name = 'html';
        $this->_mt_name = 'cert';

        parent::__construct( $fields, $context );
    }

    /**
     * custom validator (restricting what was originally set by the messenger).
     * Note nothing is currently done for this messenger and message type.
     */
    protected function _modify_validator() {
        $this->_specific_shortcode_excludes['content'] = array('[DISPLAY_HTML_URL]');
        return;
    }
}
