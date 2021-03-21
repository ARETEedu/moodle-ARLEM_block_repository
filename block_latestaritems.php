<?php

require_once(dirname(__FILE__). '/../../config.php');

defined('MOODLE_INTERNAL') || die();


class block_latestaritems extends block_base {
    
    var $default_number_of_items = 9; //the lataset x activities will be shown
    
    
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
        global $DB,$CFG,$default_number_of_items;
        
        if ($this->content !== null) {
          return $this->content;
        }


        $fs = get_file_storage();

        $this->content=  new stdClass;
        
        //import css file
        $this->content->text = '<link rel="stylesheet" type="text/css" href="'. $CFG->wwwroot.'/blocks/latestaritems/css/styles.css' . '"></head>';

        
        $arlems = $this->getAllArlems();
        

        $top_ten = array_chunk($arlems, $this->config->numberofitem);
        
        $counter = 0;
        
        $instance = $DB->get_record('block_instances', array('id' => $this->context->instanceid));
        if($instance !== null && $instance->defaultregion == 'side-pre'){
            $container_css = 'activity-container-side';
            $thumb_css = 'ImgThumbnail-side';
            
        }else{
            $container_css = 'activity-container';
            $thumb_css = 'ImgThumbnail';
        }

        
        
        $this->content->text .= html_writer::start_tag('div' ,array( 'class' => $container_css));
        
        //create the cells
        foreach ($top_ten[0] as $activity) {
            
            $counter ++;
            
            $this->content->text .= html_writer::start_tag('div' ,array( 'class' => 'activity-item' ));
            
                $thumbnail = $fs->get_file(context_system::instance()->id, get_string('component', 'arete'), 'thumbnail', $activity->itemid, '/', 'thumbnail.jpg');


                 //if the thumbnail file exists
                 if($thumbnail){
                    $thumb_url = moodle_url::make_pluginfile_url($thumbnail->get_contextid(), $thumbnail->get_component(), $thumbnail->get_filearea(), $thumbnail->get_itemid(), $thumbnail->get_filepath(), $thumbnail->get_filename(), false);
                 }else{
                     $thumb_url= $CFG->wwwroot.'/blocks/latestaritems/pix/no-thumbnail.jpg';
                 }

                         
                 $url = $CFG->wwwroot. '/mod/arete/filedetail.php?itemid=' . $activity->itemid ;
                 $this->content->text .= html_writer::start_tag('a', array('href' => $url));
                 
                 $this->content->text .= html_writer::start_tag('div' ,array( 'class' => 'thumbnail-container' ));
                    $this->content->text .= html_writer::empty_tag('img', array('class' => $thumb_css , 'src' => $thumb_url, 'alt' => pathinfo($activity->filename, PATHINFO_FILENAME)));
                 $this->content->text .= html_writer::end_tag('div');
                 
                 $this->content->text .= html_writer::end_tag('a');
                 $this->content->text .= html_writer::start_tag('div' ,array( 'class' => 'arleminfo' ));
                    $this->content->text .= '<b>' . get_string('arlemtitle', 'block_latestaritems') . ': </b>' . pathinfo($activity->filename, PATHINFO_FILENAME);
                    $this->content->text .= '<br><b>' . get_string('arlemdate', 'block_latestaritems') . ': </b>' . date('m.d.Y H:i ', $activity->timecreated);
                    $this->content->text .= '<br><b>' . get_string('arlemsize', 'block_latestaritems') . ': </b>' . $this->get_file_size($activity->filesize);
                 $this->content->text .= html_writer::end_tag('div');
             
             $this->content->text .= html_writer::end_tag('div');
        }
        
        $this->content->text .= html_writer::end_tag('div');
        
                
        if (! empty($this->config->text)) {
        $this->content->text = $this->config->text;
        }
        
        if(empty($this->config->numberofitem)){
           $this->content->text = 'Please configure this block using the edit icon.';
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
        return true;
    }
    
    
    //if true header will be removed
    public function hide_header() {
        return false;
    }
    
    
    function get_file_size($size_in_bytes){
         //file size
        $size = $size_in_bytes;
        
        if($size > 1000000000){
            $size /= pow(1024 ,3);
            $size = round($size,2);
            $size .= ' GB';
        }
        else if($size > 1000000){
            $size /= pow(1024 ,2);
            $size = round($size,2);
            $size .= ' MB';
        }else if($size > 1024){
            $size /= 1024;
            $size = round($size,2);
            $size .= ' KB';
        }else{
            $size = $size/1024;
            $size = round($size,2);
            $size .= ' KB';
        }
        
        return $size;
    }

    
    //get an array of all files in plug in filearea
    function getAllArlems()
    {
    //    $fs = get_file_storage();
    //
    //    $files = $fs->get_area_files( 1 , get_string('component', 'arete'), get_string('filearea', 'arete'), false, 'timecreated DESC ', $emptyFiles);
        global $DB,$USER, $COURSE;

        //course context
        $context = context_course::instance($COURSE->id);

        //manager
        if(has_capability('mod/arete:manageall', $context)){
                $files = $DB->get_records('arete_allarlems' , null, 'timecreated DESC'); //all arlems
        }else //others
        {
               $files = $DB->get_records_select('arete_allarlems', 'upublic = 1 OR userid = ' . $USER->id  , null, 'timecreated DESC');  //only public and for the user
        }

        return $files;  
    }

}