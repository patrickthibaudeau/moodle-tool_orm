<?php
/**
 * *************************************************************************
 * *                           YULearn ELMS                               **
 * *************************************************************************
 * @package     local                                                     **
 * @subpackage  yulearn                                                   **
 * @name        YULearn ELMS                                              **
 * @copyright   UIT - Innovation lab & EAAS                               **
 * @link                                                                  **
 * @author      Patrick Thibaudeau                                        **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  **
 * *************************************************************************
 * ************************************************************************ */

namespace tool_orm;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/lib/formslib.php");
require_once("$CFG->dirroot/admin/tool/orm/config.php");

class create_orm extends \moodleform {

    protected function definition() {
        global $USER, $CFG, $DB, $OUTPUT;

        $formdata = $this->_customdata['formdata'];

        $options = array(
            'multiple' => false,
            'noselectionstring' => get_string('select', 'tool_orm'),
        );

        //Get all tables
        $tables = [''];
        $tables = array_merge($tables, $DB->get_tables());
        $mform = & $this->_form;
        $mform->addElement('hidden', 'id');
        $mform->addElement('header', 'general', get_string('general'));
        $mform->addElement('text', 'pluginname', get_string('plugin_name', 'tool_orm'));
        $mform->addElement('text', 'classname', get_string('class_name', 'tool_orm'));
        $mform->addElement('autocomplete', 'tablename', get_string('table', 'tool_orm'),$tables, $options);
        


        $mform->setType('id', PARAM_INT);
        $mform->setType('pluginname', PARAM_TEXT);
        $mform->setType('classname', PARAM_TEXT);

        $mform->addRule('pluginname', get_string('required', 'tool_orm'), 'required');

        $this->add_action_buttons(false, get_string('create_class', 'tool_orm'));
        $this->set_data($formdata);
    }

}
