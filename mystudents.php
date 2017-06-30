<?php

/**
 * List of students' LBPs.
 * Can be by course (students on any course teacher is assigned to), or by mentees (any students personal tutor is assigned to)
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
$BKSB = \ELBP\Plugins\Plugin::instaniate("elbp_bksblive");

$site = $BKSB->getSiteURL();
if (!$site){
    print_error( get_string('missingsiteurl', 'block_elbp_bksblive') );
}

$courseID = optional_param('courseid', SITEID, PARAM_INT);
$childID = optional_param('child', false, PARAM_INT);
$groupID = optional_param('groupid', false, PARAM_INT);
$view = optional_param('view', null, PARAM_ALPHA);

// Page in query string
$page = optional_param('page', 1, PARAM_INT);

// Display pages
$perpage = ELBP\Setting::getSetting('list_stud_per_page', $USER->id);
if (!$perpage) $perpage = 15;

// URL of current page
$pageURL = $_SERVER['REQUEST_URI'];

$records = array();

// Check course context is valid
$courseContext = context_course::instance($courseID);
if (!$courseContext){
    print_error( get_string('invalidcourse', 'block_elbp') );
}

// If we're not looking at the front page course (as this would bring back every student probably), then create course object
$course = false;

if ($courseID <> SITEID){
    // THis shouldn't be able to fail in theory, if courseContext hasn't failed
    $course = $DBC->getCourse(array("type" => "id", "val" => $courseID));
}

// Need to be logged in to view this page
require_login();

// Set up PAGE
$PAGE->set_context( context_course::instance($courseID) );
$PAGE->set_url($CFG->wwwroot . '/blocks/elbp_bksblive/mystudents.php?id='.$courseID);
$PAGE->set_title( get_string('mystudents', 'block_elbp') );
$PAGE->set_heading( get_string('mystudents', 'block_elbp') );
$PAGE->set_cacheable(true);

// If course is set, put that into breadcrumb
if ($course) $PAGE->navbar->add( $course->shortname , $CFG->wwwroot . "/course/view.php?id={$course->id}", navigation_node::TYPE_COURSE);
$PAGE->navbar->add( get_string('pluginname', 'block_elbp_bksblive') , null, navigation_node::TYPE_CUSTOM);
$PAGE->navbar->add( get_string('mystudents', 'block_elbp') , null, navigation_node::TYPE_CUSTOM);

echo $OUTPUT->header();

// Define variables to be used in heredocs
$vars = array();
$vars['string_courses'] = get_string('courses', 'block_elbp');
$vars['string_mentees'] = get_string('mentees', 'block_elbp');
$vars['link_class']['courses'] = '';
$vars['link_class']['mentees'] = '';

// If view is "course" and courseID is defined, then we are looking at a course
if ($view == 'course')
{
    
    // Check access permissions on the specified course ID
    $access = $ELBP->getCoursePermissions($courseID);
    
    // Need to be a teacher on the course (or admin) if we can view the students on the course
    if ( !isset($access['teacher']) && !isset($access['god']) ){
        print_error( get_string('nopermissionscourse', 'block_elbp') );
    }
    
    // If $course is not false, then we are looking at a valid course that is NOT the front page
    if ($course)
    {
        
        // Before we build the list of students, work out the LIMIT based on our limit setting perpage and the page number
                        
        // Work out the LIMIT based on these things
        $limitMin = ($page - 1) * $perpage;
        $limitMax = $perpage;
                
        // Get all the students on this course
        if ($childID > 0){
            $records = $DBC->getStudentsOnCourse($childID, null, false, array($limitMin, $limitMax));
        } else {
            $records = $DBC->getStudentsOnCourse($course->id, null, false, array($limitMin, $limitMax));
        }
        
    }
        
    
    // At this point we must have the correct permissions then
    $vars['link_class']['courses'] = 'selected';
    
}
elseif ($view == 'mentees')
{
    
    // Work out the LIMIT based on these things
    $limitMin = ($page - 1) * $perpage;
    $limitMax = $perpage;
    
    // Get all mentees associated with tutor
    
    $records = $DBC->getMenteesOnTutor($USER->id, null, false, array($limitMin, $limitMax));
    
    
    $vars['link_class']['mentees'] = 'selected';
    
}


// Navigation tabs - Courses, Mentees
$html = <<<HTML

   <ul class="elbp_tabrow">
        <li class="{$vars['link_class']['courses']}"><a href="mystudents.php?view=course">{$vars['string_courses']}</a></li>
        <li class="{$vars['link_class']['mentees']}"><a href="mystudents.php?view=mentees">{$vars['string_mentees']}</a></li>
    </ul>

HTML;

// Heading
if ($view == 'course'){
    $html .= "<h2 class='elbp_h2 elbp_centre'>".get_string('yourcourses', 'block_elbp')."</h2>";
    if ($course){
        
        $html .= "<h3 class='elbp_h3 elbp_centre'>({$course->shortname})</h3>";
        
        // Child courses
        $children = $DB->get_records_sql("SELECT c.*
                                          FROM {course} c
                                          INNER JOIN {enrol} e ON e.customint1 = c.id
                                          WHERE e.enrol = 'meta' AND e.courseid = ?", array($course->id));
        
        if ($children)
        {
            
            $html .= "<p class='c'>".get_string('childcourse', 'block_bksblive').": <select name='child' onchange='window.location=\"{$CFG->wwwroot}/blocks/elbp_bksblive/mystudents.php?view=course&courseid={$course->id}&child=\"+this.value;return false;'>";
                $html .= "<option value=''></option>";
                foreach($children as $child)
                {
                    $sel = ($childID == $child->id) ? 'selected' : '';
                    $html .= "<option value='{$child->id}' {$sel}>{$child->fullname}</option>";
                }
            $html .= "</select></p>";
            
        }
        
    }
}
elseif ($view == 'mentees'){
    $html .= "<h2 class='elbp_h2 elbp_centre'>".get_string('yourmentees', 'block_elbp')."</h2>";
}
        
        
// If not a valid view        
if ($view != 'course' && $view != 'mentees')
{
    $html .= get_string('choosevalid', 'block_elbp');
}
        
        
// If we're on view=course but no courseid is set, we need to choose frmo a list of course
elseif ($view == 'course' && !$course)
{
    
    // Display list of courses
    $html .= "<div class='elbp_centre'>";
    $teachersCourses = $DBC->getTeachersCourses($USER->id);
    if ($teachersCourses)
    {
        foreach($teachersCourses as $teachersCourse)
        {
            $html .= "<a href='mystudents.php?view=course&courseid={$teachersCourse->id}'>{$teachersCourse->fullname}</a><br>";
        }
    }
    else
    {
        $html .= "<p>".get_string('nocourses', 'block_elbp')."</p>";
    }
    $html .= "</div>";
    
}
        

// Otherwise:
else
{
        
    // Filter block
    $html .= $ELBP->buildStudentListFilter();

    // There should be a $records variable now if they are looking @ a valid course or are looking at mentees, if not can't display a table
            
    if ($view == 'mentees')
    {

        // Refine mentee search by course & then possibly further by group
        // Get all courses tutor is assigned to (this will be with courseid = (int) and studentid null
        $tutorsCourses = $DBC->getTutorsAssignedCourses($USER->id);

        // If they have any courses, show a select menu
        if ($tutorsCourses)
        {
            $url = strip_from_query_string("courseid", $pageURL);
            $url = strip_from_query_string("groupid", $url);
            $url = strip_from_query_string("page", $url);
            $html .= "<div class='elbp_centre'>";

            $html .= "<select onchange='window.location=\"".  append_query_string($url, 'courseid=')."\"+this.value;'>";
                $html .= "<option value='1'>".get_string('filterbycourse', 'block_elbp')."</option>";
                foreach($tutorsCourses as $tutorsCourse)
                {
                    $selected = ($course && $course->id == $tutorsCourse->id) ? "selected" : "";
                    $html .= "<option value='{$tutorsCourse->id}' {$selected}>{$tutorsCourse->fullname}</option>";
                }
            $html .= "</select> ";

            // If a course has been selected, display any groups that course has (which the tutor is linked to)
            if ($course)
            {

                $courseGroups = $DBC->getTutorsAssignedGroups($USER->id, $course->id);
                $url = strip_from_query_string("groupid", $pageURL);

                 $html .= "<select onchange='window.location=\"".  append_query_string($url, 'groupid=')."\"+this.value;'>";
                     $html .= "<option value='0'>".get_string('filterbygroup', 'block_elbp')."</option>";
                     foreach($courseGroups as $group)
                     {
                         $selected = ($groupID && $groupID == $group->id) ? "selected" : "";
                         $html .= "<option value='{$group->id}' {$selected}>{$group->name}</option>";
                     }
                 $html .= "</select> ";

            }

            $html .= "</div>";
            $html .= "<br>";
        }


    }
    
    if ($records)
    {

        // Results
        $params = array();

        if ($view == 'course' && $course){
            $params['course'] = true;
            $params['courseID'] = $course->id;
        }
        if ($view == 'mentees'){
            $params['mentees'] = true;

        }
        
        $params['viewtext'] = get_string('viewbksb', 'block_elbp_bksblive');
        $params['viewfile'] = 'myresults.php';
        
        // Extra column headers
        $headers = array( get_string('engia', 'block_elbp_bksblive'),
                          get_string('mathsia', 'block_elbp_bksblive'),
                          get_string('ictia', 'block_elbp_bksblive'),
                          get_string('enge2', 'block_elbp_bksblive'),
                          get_string('enge3', 'block_elbp_bksblive'),
                          get_string('engl1', 'block_elbp_bksblive'),
                          get_string('engl2', 'block_elbp_bksblive'),
                          get_string('engl3', 'block_elbp_bksblive'),
                          get_string('mthe2', 'block_elbp_bksblive'),
                          get_string('mthe3', 'block_elbp_bksblive'),
                          get_string('mthl1', 'block_elbp_bksblive'),
                          get_string('mthl2', 'block_elbp_bksblive'),
                          get_string('mthl3', 'block_elbp_bksblive')
                        );
        
        // Prior Learning Hooks
        if ($BKSB->hasHookEnabled("elbp_gt_prior_learning/English GCSE"))
        {
            $headers[] = get_string('enggcse', 'block_bcgt');
        }
        elseif ($BKSB->hasHookEnabled("elbp_prior_learning/English GCSE"))
        {
            $headers[] = get_string('enggcse', 'block_bcgt');
        }
        
        if ($BKSB->hasHookEnabled("elbp_gt_prior_learning/Maths GCSE"))
        {
            $headers[] = get_string('mathsgcse', 'block_bcgt');
        }
        elseif ($BKSB->hasHookEnabled("elbp_prior_learning/Maths GCSE"))
        {
            $headers[] = get_string('mathsgcse', 'block_bcgt');
        }
        
                    
        $BKSB->connect();
        
        $cols = array();
        foreach ((array)$records as $record)
        {
            $cols[$record->id] = array();
            $BKSB->loadStudent($record->id);
            $hookData = $BKSB->callHooks(null);
                            
            // Initial
            $initial = $BKSB->getBestInitialAssessmentResults();
                                    
            $englishIA = (isset($initial['E'])) ? $initial['E'] : false;
            $mathsIA = (isset($initial['M'])) ? $initial['M'] : false;
            $ictIA = (isset($initial['I'])) ? $initial['I'] : false;

            $cols[$record->id][get_string('engia', 'block_elbp_bksblive')] = (isset($englishIA->SessionID)) ? "<a href='".$BKSB->getSessionResultURL($englishIA->SessionID)."' target='_blank' title='{$englishIA->Result}'>{$BKSB->getShortResult($englishIA->Result)}</a>" : '-';
            $cols[$record->id][get_string('mathsia', 'block_elbp_bksblive')] = (isset($mathsIA->SessionID)) ? "<a href='".$BKSB->getSessionResultURL($mathsIA->SessionID)."' target='_blank' title='{$mathsIA->Result}'>{$BKSB->getShortResult($mathsIA->Result)}</a>" : '-';
            $cols[$record->id][get_string('ictia', 'block_elbp_bksblive')] = (isset($ictIA->SessionID))     ? "<a href='".$BKSB->getSessionResultURL($ictIA->SessionID)."' target='_blank' title='{$ictIA->Result}'>{$BKSB->getShortResult($ictIA->Result)}</a>" : '-';

            // Diagnostic
            $diagnostic = $BKSB->getBestDiagnosticAssessmentResults();
            
            $engE2 = $diagnostic['English E2 Diagnostic'];
            $engE3 = $diagnostic['English E3 Diagnostic'];
            $engL1 = $diagnostic['English L1 Diagnostic'];
            $engL2 = $diagnostic['English L2 Diagnostic'];
            $engL3 = $diagnostic['English L3 Diagnostic'];
            $mthE2 = $diagnostic['Maths E2 Diagnostic'];
            $mthE3 = $diagnostic['Maths E3 Diagnostic'];
            $mthL1 = $diagnostic['Maths L1 Diagnostic'];
            $mthL2 = $diagnostic['Maths L2 Diagnostic'];
            $mthL3 = $diagnostic['Maths L3 Diagnostic'];

            $cols[$record->id][get_string('enge2', 'block_elbp_bksblive')] = isset($engE2->Session_ID) ? "<a href='".$BKSB->getSessionResultURL($engE2->Session_ID)."' target='_blank'>{$engE2->Result}%</a>" : '-';
            $cols[$record->id][get_string('enge3', 'block_elbp_bksblive')] = isset($engE3->Session_ID) ? "<a href='".$BKSB->getSessionResultURL($engE3->Session_ID)."' target='_blank'>{$engE3->Result}%</a>" : '-';
            $cols[$record->id][get_string('engl1', 'block_elbp_bksblive')] = isset($engL1->Session_ID) ? "<a href='".$BKSB->getSessionResultURL($engL1->Session_ID)."' target='_blank'>{$engL1->Result}%</a>" : '-';
            $cols[$record->id][get_string('engl2', 'block_elbp_bksblive')] = isset($engL2->Session_ID) ? "<a href='".$BKSB->getSessionResultURL($engL2->Session_ID)."' target='_blank'>{$engL2->Result}%</a>" : '-';
            $cols[$record->id][get_string('engl3', 'block_elbp_bksblive')] = isset($engL3->Session_ID) ? "<a href='".$BKSB->getSessionResultURL($engL3->Session_ID)."' target='_blank'>{$engL3->Result}%</a>" : '-';
            $cols[$record->id][get_string('mthe2', 'block_elbp_bksblive')] = isset($mthE2->Session_ID) ? "<a href='".$BKSB->getSessionResultURL($mthE2->Session_ID)."' target='_blank'>{$mthE2->Result}%</a>" : '-';
            $cols[$record->id][get_string('mthe3', 'block_elbp_bksblive')] = isset($mthE3->Session_ID) ? "<a href='".$BKSB->getSessionResultURL($mthE3->Session_ID)."' target='_blank'>{$mthE3->Result}%</a>" : '-';
            $cols[$record->id][get_string('mthl1', 'block_elbp_bksblive')] = isset($mthL1->Session_ID) ? "<a href='".$BKSB->getSessionResultURL($mthL1->Session_ID)."' target='_blank'>{$mthL1->Result}%</a>" : '-';
            $cols[$record->id][get_string('mthl2', 'block_elbp_bksblive')] = isset($mthL2->Session_ID) ? "<a href='".$BKSB->getSessionResultURL($mthL2->Session_ID)."' target='_blank'>{$mthL2->Result}%</a>" : '-';
            $cols[$record->id][get_string('mthl3', 'block_elbp_bksblive')] = isset($mthL3->Session_ID) ? "<a href='".$BKSB->getSessionResultURL($mthL3->Session_ID)."' target='_blank'>{$mthL3->Result}%</a>" : '-';

            // Prior Learning Hooks
            if ($BKSB->hasHookEnabled("elbp_gt_prior_learning/English GCSE"))
            {
                $data = $hookData['elbp_gt_prior_learning/English GCSE'];
                $cols[$record->id][get_string('enggcse', 'block_bcgt')] = $data;
            }
            elseif ($BKSB->hasHookEnabled("elbp_prior_learning/English GCSE"))
            {
                $data = $hookData['elbp_prior_learning/English GCSE'];
                $cols[$record->id][get_string('enggcse', 'block_bcgt')] = $data;
            }

            if ($BKSB->hasHookEnabled("elbp_gt_prior_learning/Maths GCSE"))
            {
                $data = $hookData['elbp_gt_prior_learning/Maths GCSE'];
                $cols[$record->id][get_string('mathsgcse', 'block_bcgt')] = $data;
            }
            elseif ($BKSB->hasHookEnabled("elbp_prior_learning/Maths GCSE"))
            {
                $data = $hookData['elbp_prior_learning/Maths GCSE'];
                $cols[$record->id][get_string('mathsgcse', 'block_bcgt')] = $data;
            }
            
            
        }
        
        $html .= $ELBP->buildListOfStudents($records, $params, array($headers, $cols));
    
    }
    else
    {
        $html .= "<p class='elbp_centre'>".get_string('nostudents', 'block_elbp')."</p>";
    }


}

echo $html;

echo $OUTPUT->footer();