<?php

/**
 * Configure the BKSB block
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

$view = optional_param('view', 'main', PARAM_ALPHA);

$access = $ELBP->getCoursePermissions(1);
if (!$access['god']){
    print_error( get_string('invalidaccess', 'block_elbp') );
}

// Need to be logged in to view this page
require_login();

$BKSB = \ELBP\Plugins\Plugin::instaniate("elbp_bksblive");
$TPL = new \ELBP\Template();

// Submitted
if (!empty($_POST))
{
    $BKSB->saveConfig($_POST);
    $MSGS['success'] = get_string('settingsupdated', 'block_elbp');
    $TPL->set("MSGS", $MSGS);
}

// Set up PAGE
$PAGE->set_context( context_course::instance(1) );
$PAGE->set_url($CFG->wwwroot . '/blocks/elbp_bksblive/config.php');
$PAGE->set_title( get_string('bksbconfig', 'block_elbp_bksblive') );
$PAGE->set_heading( get_string('bksbconfig', 'block_elbp_bksblive') );
$PAGE->set_cacheable(true);
$ELBP->loadJavascript();
$ELBP->loadCSS();

// If course is set, put that into breadcrumb
$PAGE->navbar->add( get_string('bksbconfig', 'block_elbp_bksblive'), $CFG->wwwroot . '/blocks/elbp_bksblive/config.php', navigation_node::TYPE_CUSTOM);

echo $OUTPUT->header();

$hooks = $ELBP->getAllPossibleHooks();


$TPL->set("view", $view);
$TPL->set("BKSB", $BKSB);
$TPL->set("hooks", $hooks);

try {
    $TPL->load( $CFG->dirroot . '/blocks/elbp_bksblive/tpl/config.html' );
    $TPL->display();
} catch (\ELBP\ELBPException $e){
    echo $e->getException();
}

echo $OUTPUT->footer();