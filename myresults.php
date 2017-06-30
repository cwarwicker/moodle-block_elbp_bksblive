<?php

/**
 * View a list of your BKSB results (same as the All Results view on the ELBP)
 * 
 * @copyright 2012 Bedford College
 * @package Bedford College Electronic Learning Blue Print (ELBP)
 * @version 1.0
 * @author Conn Warwicker <cwarwicker@bedford.ac.uk> <conn@cmrwarwicker.com>
 * 
 */

require_once '../../config.php';
require_once $CFG->dirroot . '/blocks/elbp/lib.php';

$ELBP = ELBP\ELBP::instantiate();
$DBC = new ELBP\DB();

$userID = optional_param('id', $USER->id, PARAM_INT);

// Need to be logged in to view this page
require_login();

// Check permissions
$access = $ELBP->getUserPermissions($userID);
if (!$ELBP->anyPermissionsTrue($access)) $userID = $USER->id;

$user = $DBC->getUser( array("type"=>"id", "val"=>$userID) );
if (!$user){
    print_error( get_string('invaliduser', 'block_elbp') );
}

// Set up PAGE
$PAGE->set_context( context_course::instance(1) );
$PAGE->set_url($CFG->wwwroot . '/blocks/elbp_bksblive/myresults.php?id='.$userID);
$PAGE->set_title( get_string('myresults', 'block_elbp_bksblive') );
$PAGE->set_heading( get_string('myresults', 'block_elbp_bksblive') );
$PAGE->set_cacheable(true);

// If course is set, put that into breadcrumb
$PAGE->navbar->add( get_string('fullbksbresults', 'block_elbp_bksblive'), $CFG->wwwroot . '/blocks/elbp_bksblive/myresults.php?id='.$userID, navigation_node::TYPE_CUSTOM);
$PAGE->navbar->add( fullname($user), null, navigation_node::TYPE_CUSTOM);

echo $OUTPUT->header();

$BKSB = \ELBP\Plugins\Plugin::instaniate("elbp_bksblive");

if ($BKSB)
{
    $BKSB->connect();
    // Can easily re-use the ajax function here as we want the exact same output as in the ELBP
    $BKSB->ajax("load_display_type", array("studentID"=>$userID, "type"=>"all"), $ELBP);
}

echo $OUTPUT->footer();