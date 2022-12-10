<?php
namespace tool_orm;

//TO DO: Change this into a Singleton and get rid of static functions
class Base {

    /**
     * Creates the Moodle page header
     * @global \stdClass $CFG
     * @global \moodle_database $DB
     * @global \moodle_page $PAGE
     * @global \stdClass $SITE
     * @param string $url Current page url
     * @param string $pagetitle  Page title
     * @param string $pageheading Page heading (Note hard coded to site fullname)
     * @param array $context The page context (SYSTEM, COURSE, MODULE etc)
     * @param string $pagelayout The page context (SYSTEM, COURSE, MODULE etc)
     * @return HTML Contains page information and loads all Javascript and CSS
     */
    public static function page($url, $pagetitle, $pageheading, $context = null, $pagelayout = 'base') {
        global $CFG, $PAGE, $SITE;

        if ($context == null) {
            $context = \context_system::instance();
        }

        $stringman = get_string_manager();
        $strings = $stringman->load_component_strings('tool_orm', current_language());
        $PAGE->set_url($url);
        $PAGE->set_title($pagetitle);
        $PAGE->set_heading($pageheading);
        $PAGE->set_pagelayout($pagelayout);
        $PAGE->set_context($context);
        $PAGE->requires->strings_for_js(array_keys($strings), 'tool_orm');
    }

    /**
     * Sets filemanager options for mforms
     * @global \stdClass $CFG
     * @param \stdClass $context
     * @param int $maxfiles
     * @return array
     */
    public static function getFileManagerOptions($context, $maxfiles = 1) {
        global $CFG;
        return array('subdirs' => 0, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => $maxfiles);
    }

   /**
    * Used in mforms
    * @global \stdClass $CFG
    * @param \stdClass $context
    */
    public static function getEditorOptions($context) {
        global $CFG;
        return array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1,
            'changeformat' => 1, 'context' => $context, 'noclean' => 1, 'trusttext' => 0);
    }
}
