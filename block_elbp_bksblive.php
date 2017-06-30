<?php
/**
 * BKSB class
 * 
 * This plugin is in no way related to the BKSB company. It was written by Conn Warwicker as part of Bedford College's ELBP block
 * 
 * @copyright 2012 Bedford College
 * @package Bedford College Electronic Learning Blue Print (ELBP)
 * @version 1.0
 * @author Conn Warwicker <cwarwicker@bedford.ac.uk> <conn@cmrwarwicker.com>
 * 
 */
require_once 'elbp_bksblive.class.php';

class block_elbp_bksblive extends block_base
{
        
    var $BKSB;
    var $ELBP;
    var $DBC;
    var $blockwww;
    
    public function init()
    {
        $this->title = get_string('pluginname', 'block_elbp_bksblive');     
    }
    
    public function get_content()
    {
        
        global $SITE, $CFG, $COURSE, $USER;
        
        // Must be logged in
        if (!$USER->id){
            return false;
        }
        
        $this->blockwww = $CFG->wwwroot . '/blocks/elbp_bksblive/';
        
        // If we are tryint to add the BKSB block before it has been installed as a plugin, install it
        try {
            $this->BKSB = \ELBP\Plugins\Plugin::instaniate("elbp_bksblive", '/blocks/elbp_bksblive/');
        } catch (\ELBP\ELBPException $e){
            echo $e->getException();
            return false;
        }
        
        $context = context_course::instance($COURSE->id);
        
        $this->BKSB->loadStudent($USER->id);
        $this->ELBP = ELBP\ELBP::instantiate();
        $this->DBC = new ELBP\DB();
        
        $access = $this->ELBP->getCoursePermissions($COURSE->id);
                
        $this->content = new \stdClass();
        
        $this->content->text = '';
        $this->content->footer = '';
        
        // Check version has been set - bksblive1 or bksblive2
        $version = $this->BKSB->getBKSBLiveVersion();
        if (!$version){
            
            $this->content->text = get_string('missingversion', 'block_elbp_bksblive');
            
            if ($access['god'])
            {
                $this->content->text .= '<li><a href="'.$this->blockwww.'config.php"><img src="'.$this->blockwww.'pix/cog.png" alt="Img" class="icon" /> '.get_string('config', 'block_elbp_bksblive').'</a></li>';
            }
            
            return $this->content;
            
        }
        
        // Check URL to their site has been defined
        $site = $this->BKSB->getSiteURL();
        if (!$site)
        {
            $this->content->text = get_string('missingsiteurl', 'block_elbp_bksblive');
            
            if ($access['god'])
            {
                $this->content->text .= '<li><a href="'.$this->blockwww.'config.php"><img src="'.$this->blockwww.'pix/cog.png" alt="Img" class="icon" /> '.get_string('config', 'block_elbp_bksblive').'</a></li>';
            }
            
            return $this->content;
        }
        
        
        
        $userField = $this->BKSB->getUserMapping();
        
        $this->content->text .= '<div style="text-align:center;"><img src="'.$this->blockwww.'pix/BKSB_Small.png" style="height:25px;" /><br><br></div>';
        $this->content->text .= '<div style="width:90%;margin:auto;"><ul class="list">';
                        
        // Staff links
        if (\has_capability('block/elbp_bksblive:view_as_staff', $context))
        {
            $this->content->text .= '<li><a href="'.$this->blockwww.'mystudents.php"><img src="'.$this->blockwww.'pix/table.png" alt="Img" class="icon" /> '.get_string('bksbresults', 'block_elbp_bksblive').'</a></li>';
        }
        
        // Student links
        elseif (\has_capability('block/elbp_bksblive:view', $context))
        {
            
            $this->content->text .= '<li><a href="'.$this->blockwww.'myresults.php"><img src="'.$this->blockwww.'pix/page.png" alt="Img" class="icon" /> '.get_string('viewmy', 'block_elbp_bksb').'</a></li>';
            $this->content->text .= '<li><br></li>';
            $this->content->text .= '<li style="font-weight:bold;">'.get_string('initassessments', 'block_elbp_bksb').':</li>';
            
            // English
            if ($this->BKSB->getAssessmentID('IA', 'ENG')){
                $this->content->text .= '<li><a href="'.$this->BKSB->getInitialAssessmentURL( $this->BKSB->getAssessmentID('IA', 'ENG') ).'&username='.$USER->$userField.'" target="_blank"><img src="'.$this->blockwww.'pix/page_white_go.png" alt="Img" class="icon" /> '.get_string('initeng', 'block_elbp_bksb').'</a></li>';
            }

            // Maths
            if ($this->BKSB->getAssessmentID('IA', 'MATHS')){
                $this->content->text .= '<li><a href="'.$this->BKSB->getInitialAssessmentURL( $this->BKSB->getAssessmentID('IA', 'MATHS') ).'&username='.$USER->$userField.'" target="_blank"><img src="'.$this->blockwww.'pix/page_white_go.png" alt="Img" class="icon" /> '.get_string('initmath', 'block_elbp_bksb').'</a></li>';
            }

            // ICT
            if ($this->BKSB->getAssessmentID('IA', 'ICT')){
                $this->content->text .= '<li><a href="'.$this->BKSB->getInitialAssessmentURL( $this->BKSB->getAssessmentID('IA', 'ICT') ).'&username='.$USER->$userField.'" target="_blank"><img src="'.$this->blockwww.'pix/page_white_go.png" alt="Img" class="icon" /> '.get_string('initict', 'block_elbp_bksb').'</a></li>';
            }
            
        }
        
        
        // External link
        $this->content->text .= '<li><br></li>';
        $this->content->text .= '<li><a href="'.$this->BKSB->getSiteLoginURL().'" target="_blank"><img src="'.$this->blockwww.'pix/door_in.png" alt="Img" class="icon" /> '.get_string('externalsite', 'block_elbp_bksblive').'</a></li>';
        
        if ($access['god'])
        {
            $this->content->text .= '<li><br></li>';
            $this->content->text .= '<li><a href="'.$this->blockwww.'config.php"><img src="'.$this->blockwww.'pix/cog.png" alt="Img" class="icon" /> '.get_string('config', 'block_elbp_bksblive').'</a></li>';
        }

        
        $this->content->text .= '</ul></div>';
        
        return $this->content;
        
    }
    
}