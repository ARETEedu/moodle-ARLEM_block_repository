<?php
 
defined('MOODLE_INTERNAL') || die();

class block_latestaritems_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
 
        // Section header title according to language file.
        $mform->addElement('header', 'config_header', get_string('header', 'block_latestaritems'));
        
        // Title setting
        $mform->addElement('text', 'config_title', get_string('title', 'block_latestaritems'));
        $mform->setDefault('config_title', get_string('pluginname', 'block_latestaritems'));
        $mform->setType('config_title', PARAM_TEXT);
        
        
         // Number of items
        $mform->addElement('text', 'config_numberofitem', get_string('numberofitems', 'block_latestaritems'));
        $mform->setDefault('config_numberofitem', 5);
        $mform->setType('config_numberofitem', PARAM_INT); 
    }
}