<?php

defined('MOODLE_INTERNAL') || die();


class block_latestaritems extends block_base {
    
    public function init() {
        $this->title = get_string('pluginname', 'block_latestaritems');
    }

    function applicable_formats() {
        return array('all' => true);
    }
    
    
    /***
     * 
     * Define the content of the block here
     * 
     */
    public function get_content() {
        global $DB;
        
        if ($this->content !== null) {
          return $this->content;
        }

        

        
        
        $this->content=  new stdClass;
        
        $arlems = $DB->get_records('arete_allarlems', null, 'timecreated DESC');
        
        $rowNumber = count($arlems) >= 10 ? 10 : count($arlems);
        for ($x = 1; $x <= $rowNumber; $x++) {
             $this->content->text .=  strval($x) . '- ' . pathinfo($arlems[$x]->filename, PATHINFO_FILENAME) . '<br>';
        }
        
//        $this->content->footer = 'Footer here...';

        if (! empty($this->config->text)) {
        $this->content->text = $this->config->text;
        }


        return $this->content;
    }
    
    
    /*
     * Return an object containing all the block content to be returned by external functions.
     * If your block is returning formatted content or provide files for download, 
     * you should override this method to use the external_format_text, external_format_string functions for formatting or external_util::get_area_files for files.
     */
    public function get_content_for_external($output) {

        $bc = new stdClass;

        return $bc;
    }
    
    
    public function specialization() {
        if (isset($this->config)) {
            if (empty($this->config->title)) {
                $this->title = get_string('pluginname', 'block_latestaritems');            
            } else {
                $this->title = $this->config->title;
            }

        }
    }
    
    
    //after delete the block
    function instance_delete() {

        return true;
    }
    
    ///allow to add multiple blocks of this type to a single course "false means not allow"
    public function instance_allow_multiple() {
        return false;
    }
    
    
    //if true header will be removed
    public function hide_header() {
        return false;
    }
}