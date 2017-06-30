<?php
namespace ELBP\Plugins;

require_once $CFG->dirroot . '/blocks/elbp/lib.php';
require_once 'Assessment.class.php';

/**
 * Description of elbp_bksblive
 *
 * @author cwarwicker
 */
class elbp_bksblive extends \ELBP\Plugins\Plugin {
    
    public $supportedHooks;

    private $assessmentIDs = array(
        'IA' => array(
            'ENG' => 50001,
            'MATHS' => 50000,
            'ENG_OLD' => 40001,
            'MATHS_OLD' => 40000,
            'ICT' => 468
        ),
        'DIAG' => array(),
        'GCSE' => array(
            'ENG' => 15001,
            'MATHS' => 15000
        )
    );
    
     /**
     * Construct bksb plugin object
     * @param type $install
     */
    public function __construct($install = false) {
        
        if ($install){
            parent::__construct( array(
                "name" => strip_namespace(get_class($this)),
                "title" => "BKSB Live",
                "path" => "/blocks/elbp_bksblive/",
                "version" => \ELBP\ELBP::getBlockVersionStatic()
            ) );
        }
        else
        {
            parent::__construct( strip_namespace(get_class($this)) );
        }
        
        $this->supportedHooks = array(
            'elbp_prior_learning' => array(
                'English GCSE',
                'Maths GCSE'
            ),
            'elbp_gt_prior_learning' => array(
                'English GCSE',
                'Maths GCSE'
            )
        );
        

    }
    
    /**
     * Connect to MIS
     */
    public function connect(){
        
        $this->loadMISConnection();
        if ($this->connection && $this->connection->connect()){
            $core = $this->getMainMIS();
            if ($core){
                $pluginConn = new \ELBP\MISConnection($core->id);
                if ($pluginConn->isValid()){
                    $this->useMIS = true;
                    $this->plugin_connection = $pluginConn;
                }
            }
        }
        
    }
    
    /**
     * Install the plugin
     */
    public function install()
    {
        
        global $DB;
        
        $return = true;
        $this->id = $this->createPlugin();
        $return = $return && $this->id;
        
        // This is a core ELBP plugin, so the extra tables it requires are handled by the core ELBP install.xml
        
        
        // Hooks that can be used in other plugins
        $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "English IA"));
        $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "Maths IA"));
        $DB->insert_record("lbp_hooks", array("pluginid" => $this->id, "name" => "ICT IA"));
        
        
        // Reporting elements for bc_dashboard wizard
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:percentwithengia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:percentwithmthia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:percentwithictia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:avgengia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:avgmthia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:avgictia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:numwithengia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:numwithmthia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:numwithictia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:numwithoutengia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:numwithoutmthia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:numwithoutictia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:datelastengia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:datelastmthia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:datelastictia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:numwithanyia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:percentwithanyia", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:avgenggcsetest", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:avgmthgcsetest", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:numwithenggcsetest", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:numwithmthgcsetest", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:numwithoutenggcsetest", "getstringcomponent" => "block_elbp_bksblive"));
        $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:numwithoutmthgcsetest", "getstringcomponent" => "block_elbp_bksblive"));
       
        
        return $return;
    }
    
    /**
     * Upgrade the plugin from an older version to newer
     */
    public function upgrade(){
        
        global $DB;
        
        $result = true;
        $version = $this->version; # This is the current DB version we will be using to upgrade from     
        
        if ($version < 2014090300)
        {
            
            $DB->execute("UPDATE {lbp_plugin_report_elements}
                          SET getstringname = REPLACE(getstringname, ?, ?)
                          WHERE getstringcomponent = 'block_elbp_bksblive'", array('reports:bksb:', 'reports:bksblive:'));
            
            $this->version = 2014090300;
            $this->updatePlugin();
            \mtrace("## Fixed plugin_report_elements data for plugin: {$this->title}");
            
        }
        
        if ($version < 2014100200)
        {
            
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:avgenggcsetest", "getstringcomponent" => "block_elbp_bksblive"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:avgmthgcsetest", "getstringcomponent" => "block_elbp_bksblive"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:numwithenggcsetest", "getstringcomponent" => "block_elbp_bksblive"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:numwithmthgcsetest", "getstringcomponent" => "block_elbp_bksblive"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:numwithoutenggcsetest", "getstringcomponent" => "block_elbp_bksblive"));
            $DB->insert_record("lbp_plugin_report_elements", array("pluginid" => $this->id, "getstringname" => "reports:bksblive:numwithoutmthgcsetest", "getstringcomponent" => "block_elbp_bksblive"));

            $this->version = 2014100200;
            $this->updatePlugin();
            \mtrace("## Inserted plugin_report_element data for plugin: {$this->title}");
            
        }
        
        
    }
    
    /**
     * Handle ajax requests sent to the plugin
     * @param type $action
     * @param type $params
     * @param type $ELBP
     * @return boolean
     * @throws \ELBP\ELBPException
     */
     public function ajax($action, $params, $ELBP){
                          
        switch($action)
        {
            
            case 'load_display_type':
                                                                              
                // Correct params are set?
                if (!$params || !isset($params['studentID']) || !$this->loadStudent($params['studentID'])) return false;
                                
                 // We have the permission to do this?
                $access = $ELBP->getUserPermissions($params['studentID']);
                if (!$ELBP->anyPermissionsTrue($access)) return false;
                
                // Student's courses
                $TPL = new \ELBP\Template();
                $TPL->set("obj", $this);
                $TPL->set("student", $this->student);
                
                $this->connect();
                
                try {
                    
                    // If no connection, just stop
                    if (!$this->connection){
                        throw new \ELBP\ELBPException( get_string('plugin', 'block_elbp'), get_string('nomisconnection', 'block_elbp'), false, get_string('admin:setupmisconnectionplugin', 'block_elbp'));
                    }
                    
                    $method = 'ajax_'.$params['type'];
                    $this->$method($TPL);
                    $TPL->load( $this->CFG->dirroot . '/'.$this->path.'/tpl/'.$params['type'].'.html' );
                    $TPL->display();
                } catch (\ELBP\ELBPException $e){
                    echo $e->getException();
                }
                
                return;
                
            break;
            
            case 'load_learning_plan':
                
                // Correct params are set?
                if (!$params || !isset($params['studentID']) || !$this->loadStudent($params['studentID']) || !isset($params['subject']) || !isset($params['level'])) return false;
                                
                 // We have the permission to do this?
                $access = $ELBP->getUserPermissions($params['studentID']);
                if (!$ELBP->anyPermissionsTrue($access)) return false;
                
                // Student's courses
                $TPL = new \ELBP\Template();
                $TPL->set("obj", $this);
                $TPL->set("student", $this->student);
                
                $this->connect();
                
                try {
                    
                    // If no connection, just stop
                    if (!$this->connection){
                        throw new \ELBP\ELBPException( get_string('plugin', 'block_elbp'), get_string('nomisconnection', 'block_elbp'), false, get_string('admin:setupmisconnectionplugin', 'block_elbp'));
                    }
                    
                    $topics = array();
                    $topics['NP'] = $this->getUserProgressTopics($params['subject'], $params['level'], 'NP');
                    $topics['TC'] = $this->getUserProgressTopics($params['subject'], $params['level'], 'TC');
                    $topics['VG'] = $this->getUserProgressTopics($params['subject'], $params['level'], 'VG');
                    
                    $TPL->set("topics", $topics);
                    $TPL->set("progress", $this->calculateTopicProgress( $topics ));
                    
                    $TPL->load( $this->CFG->dirroot . '/'.$this->path.'/tpl/topics.html' );
                    $TPL->display();
                    
                } catch (\ELBP\ELBPException $e){
                    echo $e->getException();
                }
                
                return;
                
            break;
            
        }
         
     }
     
     
     /**
      * The "all" page
      * @param type $TPL
      */
     private function ajax_all($TPL)
     {
         $TPL->set("iaresults", $this->getAllInitialAssessmentResults());
         $TPL->set("diagresults", $this->getAllDiagnosticAssessmentResults());
     }
     
     /**
      * The "best" page
      * @param type $TPL
      */
     private function ajax_best($TPL)
     {
                  
         $TPL->set("iaresults", $this->getBestInitialAssessmentResults());
         $TPL->set("diagresults", $this->getBestDiagnosticAssessmentResults());

     }
     
     private function ajax_plan($TPL)
     {
         
         $TPL->set("tabs", $this->getUserProgressTabs());
         
     }
     
     
     public function getSiteName(){
         return $this->getSetting('bksblive_site_name');
     }
     
     /**
      * Get the URL to take an initial assessment on the bksblive
      * @param type $id
      * @return boolean
      */
    public function getInitialAssessmentURL($id){
        
         $version = $this->getBKSBLiveVersion();
         $url = $this->getSiteURL();
         if (!$url || !$version) return false;
         
         if ($version == 1){
             return $url . '/runFlash.aspx?a='. $id;
         } elseif ($version == 2){
             return $url . '/PlayAssessment.aspx?ID=' . $id;
         }
         
         return false;
         
    }
     
    /**
     * Get the URL to a session's printable result on the bksblive
     * @param type $id
     * @return boolean
     */
    public function getSessionResultURL($id){
         
        $version = $this->getBKSBLiveVersion();
        $url = $this->getSiteURL();
        if (!$url || !$version) return false;
        
        // Supposedly the same
        return $url . '/EOAReport.aspx?ID=' . $id . '&username=' . $this->getMyFieldValue();
         
    }
     
     /**
      * Get the site login url, depending on which version you are using
      * @global type $USER
      * @return boolean
      */
     public function getSiteLoginURL(){
         
         global $USER;
         
         $version = $this->getBKSBLiveVersion();
         $url = $this->getSiteURL();
         if (!$url || !$version) return false;
         
         $userField = $this->getUserMapping();

         if ($version == 1){
             return $url . '/default.aspx?username='.$USER->$userField;
         } elseif ($version == 2){
             return $url . '/Login.aspx?username='.$USER->$userField;
         }
         
         return false;
         
     }
     
     /**
      * Get the site's URL
      * @return type
      */
     public function getSiteURL(){
         
         $url = $this->getSetting('bksblive_url');         
         return (filter_var($url, FILTER_VALIDATE_URL)) ? rtrim($url, '/') : false;
     }
     
     
     public function getAssessmentID($type, $subject){
         
         // FIrst check if we defined something else
         $setting = $this->getSetting('assessment_id_'.$type.'_'.$subject);
         if ($setting)
         {
             return $setting;
         }
         
         // If not, check for a default
         if (isset($this->assessmentIDs[$type][$subject]))
         {
             return $this->assessmentIDs[$type][$subject];
         }
         
         return false;         
         
     }
     
     
     
      /**
     * Load the summary box
     * @return type
     */
    public function getSummaryBox(){
        
        $this->connect();

        $ia = $this->getMostRecentInitialAssessments();
        
        $TPL = new \ELBP\Template();
                
        $TPL->set("obj", $this);
        $TPL->set("ia", $ia);
                
        try {
            return $TPL->load($this->CFG->dirroot . $this->path . '/tpl/summary.html');
        }
        catch (\ELBP\ELBPException $e){
            return $e->getException();
        }
        
    }
    
     /**
     * Get the expanded view
     * @param type $params
     * @return type
     */
    public function getDisplay($params = array()){
                
        $output = "";
        
        $this->connect();
        
        $ia = $this->getMostRecentInitialAssessments();
        
        $TPL = new \ELBP\Template();
        
        try {
            $output .= $TPL->load($this->CFG->dirroot . $this->path . '/tpl/expanded.html');
        } catch (\ELBP\ELBPException $e){
            $output .= $e->getException();
        }

        return $output;
        
    }
    
    /**
     * Get the version they are using
     * @return type
     */
    public function getBKSBLiveVersion(){
        $version = $this->getSetting('bksblive_version');
        return ($version == 1 || $version == 2) ? $version : false;
    }
    
    public function displayConfig() {
        
        parent::displayConfig();
        
        $output = "";
        
        
        // Version
        $output .= "<h2>".get_string('bksblive', 'block_elbp_bksblive')."</h2>";
        $output .= "<small><strong>".get_string('bksbliveversion', 'block_elbp_bksblive')."</strong> - ".get_string('bksbliveversion:desc', 'block_elbp_bksblive')."</small><br>";
        $output .= "<input type='radio' name='bksblive_version' value='1' ".( ($this->getSetting('bksblive_version') == 1) ? 'checked' : '' )." /> " . get_string('bksblive1', 'block_elbp_bksblive');
        $output .= "&nbsp;&nbsp;&nbsp;&nbsp;";
        $output .= "<input type='radio' name='bksblive_version' value='2' ".( ($this->getSetting('bksblive_version') == 2) ? 'checked' : '' )." /> " . get_string('bksblive2', 'block_elbp_bksblive');
        $output .= "<br><br>";
        
        // URLS
        $output .= "<h2>".get_string('weblinks', 'block_elbp_bksblive')."</h2>";
        $output .= "<small><strong>".get_string('bksbliveurl', 'block_elbp_bksblive')."</strong> - ".get_string('bksbliveurl:desc', 'block_elbp_bksblive')."</small><br>";
        $output .= "<input type='text' name='bksblive_url' value='{$this->getSetting('bksblive_url')}' />";
        
        $output .= "<br><br>";
        
        // User mapping
        $output .= "<h2>".get_string('usermapping', 'block_elbp_bksblive')."</h2>";
        
        $output .= "<small><strong>".get_string('usermapping', 'block_elbp_bksblive')."</strong> - ".get_string('usernameoridnumber:desc', 'block_elbp_bksblive')."</small><br>";
        $output .= "<select name='username_or_idnumber'>";
            $output .= "<option value=''></option>";
            $output .= "<option value='username' ".( ($this->getUserMapping() == 'username') ? 'selected' : '') ." >username</option>";
            $output .= "<option value='idnumber' ".( ($this->getUserMapping() == 'idnumber') ? 'selected' : '') ." >idnumber</option>";
        $output .= "</select>";
        $output .= "<br><br>";
        
        
        
        echo $output;
        
    }
    
    public function saveConfig($settings) {
        
        global $MSGS, $DB;
        
        // Hook links
        if(isset($_POST['submit_hooks']))
        {
                        
            $hooks = (isset($_POST['hooks'])) ? $_POST['hooks'] : false;
            
            // Clear all records - We could just check afterwards which ones are in the DB that weren't specified here and delete those, but cba
            $DB->delete_records("lbp_plugin_hooks", array("pluginid" => $this->id));
                        
            if($hooks)
            {
                foreach($hooks as $hook)
                {

                    $data = new \stdClass();
                    $data->pluginid = $this->id;
                    $data->hookid = $hook;
                    $DB->insert_record("lbp_plugin_hooks", $data);

                }
            }
            
            return true;
            
        }
        
        
         parent::saveConfig($settings);
        
        
    }
    
    public function getUserMapping(){
        
        $setting = $this->getSetting('username_or_idnumber');
        
        return ($setting) ? $setting : 'username';
        
    }
    
    public function getStudentFieldValue(){
        
        $field = $this->getUserMapping();
        return $this->student->$field;
        
    }
    
    public function getMyFieldValue(){
        
        global $USER;
        
        $field = $this->getUserMapping();
        return $USER->$field;
        
    }
    
    /**
     * Get the URL for a topic
     * @global \ELBP\Plugins\type $USER
     * @param type $topic
     * @return boolean|string
     */
    public function getTopicURL($topic){
        
        global $USER;
        
        $version = $this->getBKSBLiveVersion();
        $url = $this->getSiteURL();
        if (!$url || !$version) return false;
                
        if ($USER->id == $this->student->id){
            
            if ($version == 1){
                return $url . "/studentCourse.aspx?c=FS_{$topic['Subject']}_IA_DIAG_R&username={$this->getStudentFieldValue()}";
            } elseif ($version == 2){
                return $url . "/Student/StudentLevel.aspx?CourseID=FS_{$topic['Subject']}&LevelID=LVL_{$topic['Subject']}_{$topic['Level']}";
            }
            
            return "#";
            
        } else {
            
            // If we've got far enough to be able to see it we must have permissions as a staff member
            if ($version == 1){
                return $url . "/tutor_studentProgress.aspx?c=FS_{$topic['Subject']}_IA_DIAG_R&un={$this->getStudentFieldValue()}";
            } elseif ($version == 2){
                $bksbLiveUser = $this->getBKSBLiveUser( $this->getStudentFieldValue() );
                if ($bksbLiveUser)
                {
                    return $url . "/Staff/TutorStudentCourseView.aspx?ID={$bksbLiveUser['UserID']}&CourseID=FS_{$topic['Subject']}";
                }
            }
            
            return "#";
            
        }
        
    }
    
    public function getBKSBLiveUser($username){
        
        if (!$this->student) return false;
        $this->connection->connect();
                
        $results = $this->connection->query("SELECT * 
                                             FROM [bksb_bksbLiveUsers]
                                             WHERE [Username] LIKE ?", array($username));
        
        $user = $this->connection->fetch($results);
        return $user;       
        
    }
    
    
    /**
     * Get the student's most recent initial assessment results for each of the three types
     */
    private function getMostRecentInitialAssessments($filters = false)
    {
                
        if (!$this->student) return false;
        
        $this->connection->connect();
        
        $AssessmentObj = new \ELBP\Plugins\elbp_bksblive\Assessment();
        $AssessmentObj->loadConnection($this->connection);
                
        $english = false;
        $maths = false;
        $ict = false;
                
        // English
        if ($this->getAssessmentID('IA', 'ENG')){
            $english = $AssessmentObj->getUserLatestIA($this->getStudentFieldValue(), $this->getAssessmentID('IA', 'ENG'), $filters);
        }
        
        // If no English, try the old assessment id
        if (!$english && $this->getAssessmentID('IA', 'ENG_OLD'))
        {
            $english = $AssessmentObj->getUserLatestIA($this->getStudentFieldValue(), $this->getAssessmentID('IA', 'ENG_OLD'), $filters);
        }
                
        // Maths
        if ($this->getAssessmentID('IA', 'MATHS')){
            $maths = $AssessmentObj->getUserLatestIA($this->getStudentFieldValue(), $this->getAssessmentID('IA', 'MATHS'), $filters);
        }
        
        // If no Maths, try the old assessment id
        if (!$maths && $this->getAssessmentID('IA', 'MATHS_OLD'))
        {
            $maths = $AssessmentObj->getUserLatestIA($this->getStudentFieldValue(), $this->getAssessmentID('IA', 'MATHS_OLD'), $filters);
        }
        
        // ICT
        if ($this->getAssessmentID('IA', 'ICT')){
            $ict = $AssessmentObj->getUserLatestICTIA($this->getStudentFieldValue(), $this->getAssessmentID('IA', 'ICT'), $filters);
        }
        
        
        
         // English GCSE Screening
        if ($this->getAssessmentID('GCSE', 'ENG')){
            $englishGCSE = $AssessmentObj->getUserLatestIA($this->getStudentFieldValue(), $this->getAssessmentID('GCSE', 'ENG'), $filters);
        }
        
        // Maths GCSE Screening
        if ($this->getAssessmentID('GCSE', 'MATHS')){
            $mathsGCSE = $AssessmentObj->getUserLatestIA($this->getStudentFieldValue(), $this->getAssessmentID('GCSE', 'MATHS'), $filters);                $results['MGCSE'] = $mathsGCSE;
        }
        
        
       
        $results = array();
        $results['E'] = $english;
        $results['M'] = $maths;
        $results['I'] = $ict;
        $results['EGCSE'] = $englishGCSE;
        $results['MGCSE'] = $mathsGCSE;
                
        $result = $this->convertInitialAssessmentResult($results);
        
        return $result;
        
        
    }
    
    
    public function getBestGCSEScreeningAssessmentResults($filters = false)
    {
        
        if (!$this->student) return false;
        
        $this->connection->connect();
        
        $results = array();
        
        $AssessmentObj = new \ELBP\Plugins\elbp_bksblive\Assessment();
        $AssessmentObj->loadConnection($this->connection);
                
        $english = false;
        $maths = false;
        
        // English
        if ($this->getAssessmentID('GCSE', 'ENG')){
            
            $english = $AssessmentObj->getUserBestIA($this->getStudentFieldValue(), $this->getAssessmentID('GCSE', 'ENG'), $filters);
            
            if ($english){
                $results['E'] = $english;
            }
            
        }
               
        
        // Maths
        if ($this->getAssessmentID('GCSE', 'MATHS')){
            
            $maths = $AssessmentObj->getUserBestIA($this->getStudentFieldValue(), $this->getAssessmentID('GCSE', 'MATHS'), $filters);            
            
            if ($maths){
                $results['M'] = $maths;
            }
            
        }
                
        
        // Sort all by date in desc order
        if ($results){
            uasort($results, function($a, $b){
                return ($a->ts < $b->ts);
            });
        }
        
        return $results;
        
    }
    
    
    /**
     * Get the student's best results for each IA
     */
    public function getBestInitialAssessmentResults($filters = false)
    {
        
        if (!$this->student) return false;
        
        $this->connection->connect();
        
        $results = array();
        
        $AssessmentObj = new \ELBP\Plugins\elbp_bksblive\Assessment();
        $AssessmentObj->loadConnection($this->connection);
                
        $english = false;
        $maths = false;
        $ict = false;
        
        // English
        if ($this->getAssessmentID('IA', 'ENG')){
            
            // If we might have results from the old system
            if ($this->getAssessmentID('IA', 'ENG_OLD')){
                $english = $AssessmentObj->getUserBestIA($this->getStudentFieldValue(), array($this->getAssessmentID('IA', 'ENG'), $this->getAssessmentID('IA', 'ENG_OLD')), $filters);
            } else {
                $english = $AssessmentObj->getUserBestIA($this->getStudentFieldValue(), $this->getAssessmentID('IA', 'ENG'), $filters);
            }
            
            if ($english){
                $results['E'] = $english;
            }
            
        }
               
        
        // Maths
        if ($this->getAssessmentID('IA', 'MATHS')){
            
            // If we might have results from the old system
            if ($this->getAssessmentID('IA', 'MATHS_OLD')){
                $maths = $AssessmentObj->getUserBestIA($this->getStudentFieldValue(), array($this->getAssessmentID('IA', 'MATHS'), $this->getAssessmentID('IA', 'MATHS_OLD')), $filters);
            } else {
                $maths = $AssessmentObj->getUserBestIA($this->getStudentFieldValue(), $this->getAssessmentID('IA', 'MATHS'), $filters);
            }
            
            if ($maths){
                $results['M'] = $maths;
            }
            
        }
        

        // ICT
        if ($this->getAssessmentID('IA', 'ICT')){
            
            $ict = $AssessmentObj->getUserBestICTIA($this->getStudentFieldValue(), $this->getAssessmentID('IA', 'ICT'), $filters);
            if ($ict){
                $results['I'] = $ict;
            }
            
        }
        
        
        
        
        // English GCSE Screening
        if ($this->getAssessmentID('GCSE', 'ENG')){
            
            $englishGCSE = $AssessmentObj->getUserBestIA($this->getStudentFieldValue(), $this->getAssessmentID('GCSE', 'ENG'), $filters);
            if ($englishGCSE){
                $results['EGCSE'] = $englishGCSE;
            }
            
        }
        
        // Maths GCSE Screening
        if ($this->getAssessmentID('GCSE', 'MATHS')){
            
            $mathsGCSE = $AssessmentObj->getUserBestIA($this->getStudentFieldValue(), $this->getAssessmentID('GCSE', 'MATHS'), $filters);
            if ($mathsGCSE){
                $results['MGCSE'] = $mathsGCSE;
            }
            
        }
        
        
        
        
        // Sort all by date in desc order
        if ($results){
            uasort($results, function($a, $b){
                return ($a->ts < $b->ts);
            });
        }
        
        return $results;
        
    }
    
    
    
    
    
    
    /**
     * Get all of the student's IA results
     * @return array
     */
    private function getAllInitialAssessmentResults()
    {
        
        if (!$this->student) return false;
        
        $this->connection->connect();
        
        $AssessmentObj = new \ELBP\Plugins\elbp_bksblive\Assessment();
        $AssessmentObj->loadConnection($this->connection);
        
        $results = array();
        
        // English
        if ($this->getAssessmentID('IA', 'ENG')){
            
            if ($this->getAssessmentID('IA', 'ENG_OLD')){
                $english = $AssessmentObj->getUserAllIA($this->getStudentFieldValue(), array($this->getAssessmentID('IA', 'ENG'), $this->getAssessmentID('IA', 'ENG_OLD')));
            } else {
                $english = $AssessmentObj->getUserAllIA($this->getStudentFieldValue(), $this->getAssessmentID('IA', 'ENG'));
            }
            
            if ($english)
            {
                foreach($english as $result)
                {
                    $results[] = (object)$result;
                }
            }
            
        }
        
        
        // Maths
        if ($this->getAssessmentID('IA', 'MATHS')){
            
            // If we might have results from the old system
            if ($this->getAssessmentID('IA', 'MATHS_OLD'))
            {
                $maths = $AssessmentObj->getUserAllIA($this->getStudentFieldValue(), array($this->getAssessmentID('IA', 'MATHS'), $this->getAssessmentID('IA', 'MATHS_OLD')));
            }
            else
            {
                $maths = $AssessmentObj->getUserAllIA($this->getStudentFieldValue(), $this->getAssessmentID('IA', 'MATHS'));
            }
            
            if ($maths)
            {
                foreach($maths as $result)
                {
                    $results[] = (object)$result;
                }
            }
            
        }
        
        
        
        // ICT - Full list so we will show all of them, not just the average
        if ($this->getAssessmentID('IA', 'ICT')){
            
            $ict = $AssessmentObj->getUserAllIA($this->getStudentFieldValue(), $this->getAssessmentID('IA', 'ICT'));
            if ($ict)
            {
                foreach($ict as $result)
                {
                    $results[] = (object)$result;
                }
            }
            
        }
        
        
        
        // English GCSE Screening
        if ($this->getAssessmentID('GCSE', 'ENG')){
            
            $englishGCSE = $AssessmentObj->getUserAllIA($this->getStudentFieldValue(), $this->getAssessmentID('GCSE', 'ENG'));
            if ($englishGCSE)
            {
                foreach($englishGCSE as $result)
                {
                    $results[] = (object)$result;
                }
            }
            
            
        }
        
        // Maths GCSE Screening
        if ($this->getAssessmentID('GCSE', 'MATHS')){
            
            $mathsGCSE = $AssessmentObj->getUserAllIA($this->getStudentFieldValue(), $this->getAssessmentID('GCSE', 'MATHS'));
            if ($mathsGCSE)
            {
                foreach($mathsGCSE as $result)
                {
                    $results[] = (object)$result;
                }
            }
            
        }
        
        
        
        
        // Sort all by date in desc order
        usort($results, function($a, $b){
            return ($a->ts < $b->ts);
        });
        
        
        return $results;
        
    }
    
    
    
    
    
    /**
     * Get all of the student's diag results
     * @return array
     */
    private function getAllDiagnosticAssessmentResults()
    {
        
        if (!$this->student) return false;
        
        $this->connection->connect();
        
        $return = array();
        
        $AssessmentObj = new \ELBP\Plugins\elbp_bksblive\Assessment();
        $AssessmentObj->loadConnection($this->connection);
        
        $results = $AssessmentObj->getUserAllDiag($this->getStudentFieldValue());
        
        if ($results)
        {
            foreach($results as $result)
            {
                $return[] = (object)$result;
            }
        }
        
        return $return;
        
    }
    
    
     /**
     * Get all of the student's diag results
     * @return array
     */
    public function getBestDiagnosticAssessmentResults()
    {
        
        if (!$this->student) return false;
        
        $this->connection->connect();
        
        $results = array();
        
        $AssessmentObj = new \ELBP\Plugins\elbp_bksblive\Assessment();
        $AssessmentObj->loadConnection($this->connection);
        
        $results['English E2 Diagnostic'] = $AssessmentObj->getUserBestDiag($this->getStudentFieldValue(), "English E2");
        $results['English E3 Diagnostic'] = $AssessmentObj->getUserBestDiag($this->getStudentFieldValue(), "English E3");
        $results['English L1 Diagnostic'] = $AssessmentObj->getUserBestDiag($this->getStudentFieldValue(), "English L1");
        $results['English L2 Diagnostic'] = $AssessmentObj->getUserBestDiag($this->getStudentFieldValue(), "English L2");
        $results['English L3 Diagnostic'] = $AssessmentObj->getUserBestDiag($this->getStudentFieldValue(), "English L3");

        $results['Maths E2 Diagnostic'] = $AssessmentObj->getUserBestDiag($this->getStudentFieldValue(), "Maths E2");
        $results['Maths E3 Diagnostic'] = $AssessmentObj->getUserBestDiag($this->getStudentFieldValue(), "Maths E3");
        $results['Maths L1 Diagnostic'] = $AssessmentObj->getUserBestDiag($this->getStudentFieldValue(), "Maths L1");
        $results['Maths L2 Diagnostic'] = $AssessmentObj->getUserBestDiag($this->getStudentFieldValue(), "Maths L2");
        $results['Maths L3 Diagnostic'] = $AssessmentObj->getUserBestDiag($this->getStudentFieldValue(), "Maths L3");

        return $results;
        
    }
    
    
    
    
    
     /**
     * Given a results object of initial assessments, convert it to something which can be displayed
     * @param type $result
     */
    private function convertInitialAssessmentResult($results)
    {
        
        $result = new \stdClass();
        
        $result->EnglishResult = (isset($results['E']->Result)) ? $results['E']->Result : false;
        $result->EnglishDate = (isset($results['E']->Date)) ? $results['E']->Date : false;
        
        $result->MathsResult = (isset($results['M']->Result)) ? $results['M']->Result : false;
        $result->MathsDate = (isset($results['M']->Date)) ? $results['M']->Date : false;
        
        $result->ICTResult = (isset($results['I']->Result)) ? $results['I']->Result : false;
        $result->ICTDate = (isset($results['I']->Date)) ? $results['I']->Date : false;

        $result->EnglishGCSEResult = (isset($results['EGCSE']->Result)) ? $results['EGCSE']->Result : false;
        $result->EnglishGCSEDate = (isset($results['EGCSE']->Date)) ? $results['EGCSE']->Date : false;
        
        $result->MathsGCSEResult = (isset($results['MGCSE']->Result)) ? $results['MGCSE']->Result : false;
        $result->MathsGCSEDate = (isset($results['MGCSE']->Date)) ? $results['MGCSE']->Date : false;
        
        return $result;
        
    }
    
    
    public function getShortResult($result){
        
        $result = str_replace( array("English", "Maths", "ICT"), "", $result );
        $result = str_replace("Entry ", "E", $result );
        $result = str_replace("Below ", "B", $result );
        return $result;
        
    }
    
    /**
     * Call all hook data
     * @param type $params
     * @return type
     */
    public function callHooks($params)
    {
        return $this->callAllHooks($params);
    }
    
    
    
    /**
     * For the bc_dashboard reporting wizard - get all the data we can about Targets for these students,
     * then return the elements that we want.
     * @param type $students
     * @param type $elements
     */
    public function getAllReportingData($students, $elements, $filters = false)
    {
        
        global $DB;
                
        if (!$students || !$elements) return false;
        
        $this->connect();
        
        $elementNames = array();
        foreach($elements as $elementID){
            $elementNames[] = $this->getReportingElementName($elementID);
        }
        
        
        // IA elements
        $iaElementNames = array(
            'reports:bksblive:percentwithengia',
            'reports:bksblive:percentwithmthia',
            'reports:bksblive:percentwithictia',
            'reports:bksblive:avgengia',
            'reports:bksblive:avgmthia',
            'reports:bksblive:avgictia',
            'reports:bksblive:numwithengia',
            'reports:bksblive:numwithmthia',
            'reports:bksblive:numwithictia',
            'reports:bksblive:numwithoutengia',
            'reports:bksblive:numwithoutmthia',
            'reports:bksblive:numwithoutictia',
            'reports:bksblive:datelastengia',
            'reports:bksblive:datelastmthia',
            'reports:bksblive:datelastictia',
            'reports:bksblive:numwithanyia',
            'reports:bksblive:percentwithanyia'
        );
        
        // GCSE Screening elements
        $gcseElementNames = array(
            'reports:bksblive:avgenggcsetest',
            'reports:bksblive:avgmthgcsetest',
            'reports:bksblive:numwithenggcsetest',
            'reports:bksblive:numwithmthgcsetest',
            'reports:bksblive:numwithoutenggcsetest',
            'reports:bksblive:numwithoutmthgcsetest',
        );
        
                
        $data = array();
        
        // Variables for totals
        $totalStudents = count($students);
        $totalStudentsEngIA = 0;
        $totalStudentsMthIA = 0;
        $totalStudentsICTIA = 0;
        
        $totalStudentsEngGCSE = 0;
        $totalStudentsMthGCSE = 0;
        
        $bestResults = array();
        $bestResults['eng'] = array();
        $bestResults['mth'] = array();
        $bestResults['ict'] = array();
        
        $bestResultsGCSE = array();
        $bestResultsGCSE['eng'] = array();
        $bestResultsGCSE['mth'] = array();
        
        $studsWithAnyIA = array();
        
        $engDate = '-';
        $mathsDate = '-';
        $ictDate = '-';
        
        
        // Loop through students and get data
        foreach($students as $student)
        {
            
            $this->loadStudent($student->id);
            
            // Are we looking for any IA related ele,ents?
            if ( array_intersect($elementNames, $iaElementNames) ){
            
                $initials = $this->getBestInitialAssessmentResults($filters);

                $bestEng = isset($initials['E']) ? $initials['E'] : false;
                $bestMth = isset($initials['M']) ? $initials['M'] : false;
                $bestICT = isset($initials['I']) ? $initials['I'] : false;

                if (isset($bestEng->Result)){
                    if (!isset($bestResults['eng'][$bestEng->Result_Rank])){
                        $bestResults['eng'][$bestEng->Result_Rank] = 0;
                    }
                    $bestResults['eng'][$bestEng->Result_Rank]++;
                    $totalStudentsEngIA++;
                    $studsWithAnyIA[$student->id] = $student->id;
                }

                if (isset($bestMth->Result)){
                    if (!isset($bestResults['mth'][$bestMth->Result_Rank])){
                        $bestResults['mth'][$bestMth->Result_Rank] = 0;
                    }
                    $bestResults['mth'][$bestMth->Result_Rank]++;
                    $totalStudentsMthIA++;
                    $studsWithAnyIA[$student->id] = $student->id;
                }

                if (isset($bestICT->Result)){
                    if (!isset($bestResults['ict'][$bestICT->Rank])){
                        $bestResults['ict'][$bestICT->Rank] = 0;
                    }
                    $bestResults['ict'][$bestICT->Rank]++;
                    $totalStudentsICTIA++;
                    $studsWithAnyIA[$student->id] = $student->id;
                }


                // If not an individual student, don't bother
                if (count($students) == 1)
                {
                    // Get latest assessments as well
                    $recent = $this->getMostRecentInitialAssessments($filters);
                    $engDate = (!empty($recent->EnglishDate)) ? $recent->EnglishDate : '-';
                    $mathsDate = (!empty($recent->MathsDate)) ? $recent->MathsDate : '-';
                    $ictDate = (!empty($recent->ICTDate)) ? $recent->ICTDate : '-';
                }            
            
            }
            
            
            
            // GCSE Screening
            if ( array_intersect($elementNames, $gcseElementNames) ){
                
                $gcses = $this->getBestGCSEScreeningAssessmentResults($filters);
                
                $bestEng = isset($gcses['E']) ? $gcses['E'] : false;
                $bestMth = isset($gcses['M']) ? $gcses['M'] : false;
                
                if (isset($bestEng->Result)){
                    if (!isset($bestResults['eng'][$bestEng->Result_Rank])){
                        $bestResultsGCSE['eng'][$bestEng->Result_Rank] = 0;
                    }
                    $bestResultsGCSE['eng'][$bestEng->Result_Rank]++;
                    $totalStudentsEngGCSE++;
                }

                if (isset($bestMth->Result)){
                    if (!isset($bestResults['mth'][$bestMth->Result_Rank])){
                        $bestResultsGCSE['mth'][$bestMth->Result_Rank] = 0;
                    }
                    $bestResultsGCSE['mth'][$bestMth->Result_Rank]++;
                    $totalStudentsMthGCSE++;
                }
                
                
                
            }
            
            
            
        }
        
        // IA
        
        // Work out the avgs. Will have to give levels points
        $points = array();
        $points[0] = '-';
        $points[1] = 'L3';
        $points[2] = 'L2';
        $points[3] = 'L1';
        $points[4] = 'E3';
        $points[5] = 'E2';
        $points[6] = 'E1';
        $points[7] = 'PE';
        
        $maxPoints = 7;
        
        $ictPointsArray = array();
        $ictPointsArray[0] = '-';
        $ictPointsArray[1] = 'L2';
        $ictPointsArray[2] = 'L1';
        $ictPointsArray[3] = 'E3';
        $ictPointsArray[4] = 'PE';
        
        $maxIctPoints = 4;
        
        $englishPoints = 0;
        $mathsPoints = 0;
        $ictPoints = 0;
        
        // Loop through results for each
            // English
            foreach($bestResults['eng'] as $rank => $cnt)
            {
                $englishPoints += ( $rank * $cnt );
            }
            
            // If there were people without results, add to the calculation otherwise avg is wrong
            $totalEngResults = array_sum($bestResults['eng']);
            if ($totalEngResults < $totalStudents)
            {
                $diff = ($totalStudents - $totalEngResults);
                $englishPoints += ($diff * $maxPoints);
            }
                        
            $avgEnglishPoints = round( $englishPoints / $totalStudents );
            
            // SHould still be fine, as if the student row doesn't have a result, will display as - instead of the calculated value based on the highest points
            if (count($bestResults['eng']) == 0) $data['reports:bksblive:avgengia'] = '-';
            else $data['reports:bksblive:avgengia'] = $points[$avgEnglishPoints];
            
            
            
            
            // Maths
            foreach($bestResults['mth'] as $rank => $cnt)
            {
                $mathsPoints += ( $rank * $cnt );
            }
            
            // If there were people without results, add to the calculation otherwise avg is wrong
            $totalMathsResults = array_sum($bestResults['mth']);
            if ($totalMathsResults < $totalStudents)
            {
                $diff = ($totalStudents - $totalMathsResults);
                $mathsPoints += ($diff * $maxPoints);
            }
                                    
            $avgMathsPoints = round( $mathsPoints / $totalStudents );
            
            if (count($bestResults['mth']) == 0) $data['reports:bksblive:avgmthia'] = '-';
            else $data['reports:bksblive:avgmthia'] = $points[$avgMathsPoints];
            
            
            
            // ICT
            foreach($bestResults['ict'] as $rank => $cnt)
            {
                $ictPoints += ( $rank * $cnt );
            }
                        
            // If there were people without results, add to the calculation otherwise avg is wrong
            $totalICTPoints = array_sum($bestResults['ict']);
            if ($totalICTPoints < $totalStudents)
            {
                $diff = ($totalStudents - $totalICTPoints);
                $ictPoints += ($diff * $maxIctPoints);
            }
                        
            $avgIctPoints = round( $ictPoints / $totalStudents );
            
            
            if (count($bestResults['ict']) == 0) $data['reports:bksblive:avgictia'] = '-';
            else $data['reports:bksblive:avgictia'] = $ictPointsArray[$avgIctPoints];
            
            
            
        // GCSE Screening 
            $gcseScreeningPoints = array();
            $gcseScreeningPoints[1] = 'GCSE %s';
            $gcseScreeningPoints[2] = 'Functional Skills';
            
            $maxGCSEPoints = 2;
            
            $englishGCSEPoints = 0;
            $mathsGCSEPoints = 0;
            
            // English
                foreach($bestResultsGCSE['eng'] as $rank => $cnt)
                {
                    $englishGCSEPoints += ( $rank * $cnt );
                }

                // If there were people without results, add to the calculation otherwise avg is wrong
                $totalEngResults = array_sum($bestResultsGCSE['eng']);
                if ($totalEngResults < $totalStudents)
                {
                    $diff = ($totalStudents - $totalEngResults);
                    $englishGCSEPoints += ($diff * $maxGCSEPoints);
                }

                $avgEnglishGCSEPoints = round( $englishGCSEPoints / $totalStudents );

                // SHould still be fine, as if the student row doesn't have a result, will display as - instead of the calculated value based on the highest points
                if (count($bestResultsGCSE['eng']) == 0) $data['reports:bksblive:avgenggcsetest'] = '-';
                else $data['reports:bksblive:avgenggcsetest'] = sprintf($gcseScreeningPoints[$avgEnglishGCSEPoints], 'English');
            
            
            
            // Maths
                foreach($bestResultsGCSE['mth'] as $rank => $cnt)
                {
                    $mathsGCSEPoints += ( $rank * $cnt );
                }

                // If there were people without results, add to the calculation otherwise avg is wrong
                $totalMthResults = array_sum($bestResultsGCSE['mth']);
                if ($totalMthResults < $totalStudents)
                {
                    $diff = ($totalStudents - $totalMthResults);
                    $mathsGCSEPoints += ($diff * $maxGCSEPoints);
                }

                $avgMathshGCSEPoints = round( $mathsGCSEPoints / $totalStudents );

                // SHould still be fine, as if the student row doesn't have a result, will display as - instead of the calculated value based on the highest points
                if (count($bestResultsGCSE['mth']) == 0) $data['reports:bksblive:avgmthgcsetest'] = '-';
                else $data['reports:bksblive:avgmthgcsetest'] = sprintf($gcseScreeningPoints[$avgMathshGCSEPoints], 'Maths');
            
          
                
                
                
                
                
                
                
            
            
        // Percentages
            $data['reports:bksblive:percentwithengia'] = round( ($totalStudentsEngIA / $totalStudents) * 100, 2 ) . "%";
            $data['reports:bksblive:percentwithmthia'] = round( ($totalStudentsMthIA / $totalStudents) * 100, 2 ) . "%";
            $data['reports:bksblive:percentwithictia'] = round( ($totalStudentsICTIA / $totalStudents) * 100, 2 ) . "%";
            $data['reports:bksblive:percentwithanyia'] = round( (count($studsWithAnyIA) / $totalStudents) * 100, 2 ) . "%";

        // Totals
            $data['reports:bksblive:numwithengia'] = $totalStudentsEngIA;
            $data['reports:bksblive:numwithmthia'] = $totalStudentsMthIA;
            $data['reports:bksblive:numwithictia'] = $totalStudentsICTIA;
            $data['reports:bksblive:numwithanyia'] = count($studsWithAnyIA);
            
            $data['reports:bksblive:numwithoutengia'] = $totalStudents - $totalStudentsEngIA;
            $data['reports:bksblive:numwithoutmthia'] = $totalStudents - $totalStudentsMthIA;
            $data['reports:bksblive:numwithoutictia'] = $totalStudents - $totalStudentsICTIA;
            
        // Dates
            $data['reports:bksblive:datelastengia'] = $engDate;
            $data['reports:bksblive:datelastmthia'] = $mathsDate;
            $data['reports:bksblive:datelastictia'] = $ictDate;
            
        // GCSE Screening
            $data['reports:bksblive:numwithenggcsetest'] = $totalStudentsEngGCSE;
            $data['reports:bksblive:numwithmthgcsetest'] = $totalStudentsMthGCSE;
            $data['reports:bksblive:numwithoutenggcsetest'] = $totalStudents - $totalStudentsEngGCSE;
            $data['reports:bksblive:numwithoutmthgcsetest'] = $totalStudents - $totalStudentsMthGCSE;
            
            
        $names = array();
        $els = array();
        
        foreach($elements as $element)
        {
            $record = $DB->get_record("lbp_plugin_report_elements", array("id" => $element));
            $names[] = $record->getstringname;
            $els[$record->getstringname] = $record->getstringcomponent;
        }
            
            
        $return = array();
        if ($names)
        {
            foreach($names as $name)
            {
                if (isset($data[$name])){
                    $newname = \get_string($name, $els[$name]);
                    $return["{$newname}"] = $data[$name];
                }
            }
        }
                
        return $return;
        
    }
    
    private function calculateTopicProgress($topics){
        
        $cnt = count($topics['NP']) + count($topics['TC']) + count($topics['VG']);
        $comp = 0;
                
        if ($topics['NP']){
            foreach($topics['NP'] as $topic){
                if ($topic['TutorMarkComplete'] == 1){
                    $comp++;
                }
            }
        }
        
        if ($topics['TC']){
            foreach($topics['TC'] as $topic){
                if ($topic['TutorMarkComplete'] == 1){
                    $comp++;
                }
            }
        }
        
        if ($topics['VG']){
            foreach($topics['VG'] as $topic){
                if ($topic['TutorMarkComplete'] == 1){
                    $comp++;
                }
            }
        }
                
        return round( ( $comp / $cnt ) * 100 );
        
        
    }
    
    public function getUserProgressTabs(){
        
        $sql = "SELECT DISTINCT cc.[Subject], cc.[Level]
                FROM [bksb_ModuleProgressRecords] mpr
                INNER JOIN [bksb_CurricCodes] cc ON cc.curric_ref = mpr.ModuleID
                WHERE mpr.username LIKE ?
                ORDER BY cc.[Subject], cc.[Level]";
        
        $result = $this->connection->query($sql, array($this->getStudentFieldValue()));
        $result = $this->connection->fetchAll($result);
        
        return $result;
        
    }
    
    public function getUserProgressTopics($subject, $level, $status){
        
        $sql = "SELECT cc.[Title], cc.[Subject], cc.[Level], mpr.[ModuleDiagnosticComment], mpr.[TutorMarkComplete], mpr.[DisplayLong]
                FROM [bksb_ModuleProgressRecords] mpr
                INNER JOIN [bksb_CurricCodes] cc ON cc.curric_ref = mpr.ModuleID
                WHERE mpr.username LIKE ? AND cc.[Subject] LIKE ? 
                AND cc.[Level] LIKE ? AND mpr.[ModuleDiagnosticComment] LIKE ?
                ORDER BY mpr.[TutorMarkComplete] DESC, cc.[Subject], cc.[Level], cc.[Title]";
        
        $result = $this->connection->query($sql, array($this->getStudentFieldValue(), $subject, $level, $status));
        $result = $this->connection->fetchAll($result);
        
        return $result;
        
    }
    
    
    /**
     * Hook to be called from another plugin
     * Get the student's best English IA result
     * @global \ELBP\Plugins\type $DB
     * @param type $obj
     * @return boolean
     */
    public function _callHook_English_IA($obj)
    {
        
        global $DB;
        
        if (!$this->isEnabled()) return false;
        if (!isset($obj->student->id)) return false;
                        
        // Load student
        $this->loadStudent($obj->student->id);
        $this->connect();
        
        $result = $this->getBestInitialAssessmentResults();        
        return (array_key_exists('E', $result)) ? $result['E'] : false;
        
    }
    
    
    /**
     * Hook to be called from another plugin
     * Get the student's best English IA result
     * @global \ELBP\Plugins\type $DB
     * @param type $obj
     * @return boolean
     */
    public function _callHook_Maths_IA($obj)
    {
        
        global $DB;
        
        if (!$this->isEnabled()) return false;
        if (!isset($obj->student->id)) return false;
                        
        // Load student
        $this->loadStudent($obj->student->id);
        $this->connect();
        
        $result = $this->getBestInitialAssessmentResults();        
        return (array_key_exists('M', $result)) ? $result['M'] : false;
        
    }
    
    
    /**
     * Hook to be called from another plugin
     * Get the student's best English IA result
     * @global \ELBP\Plugins\type $DB
     * @param type $obj
     * @return boolean
     */
    public function _callHook_ICT_IA($obj)
    {
        
        global $DB;
        
        if (!$this->isEnabled()) return false;
        if (!isset($obj->student->id)) return false;
                        
        // Load student
        $this->loadStudent($obj->student->id);
        $this->connect();
        
        $result = $this->getBestInitialAssessmentResults();        
        return (array_key_exists('I', $result)) ? $result['I'] : false;
        
    }
    
    
    
    
}
