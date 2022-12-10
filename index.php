<?php
require_once("config.php");
require_once($CFG->dirroot . "/admin/tool/orm/classes/forms/create_orm.php");
require_once($CFG->dirroot . "/admin/tool/orm/classes/Orm.php");

use tool_orm\Base;

require_login(1, FALSE);

// Set globals
global $CFG, $DB, $OUTPUT;

$formdata = new stdClass();
$formdata->id = 0;

$page_header = get_string('pluginname', 'tool_orm');

$mform = new tool_orm\create_orm(null, array('formdata' => $formdata));

$context = CONTEXT_SYSTEM::instance();

if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    redirect($CFG->wwwroot . '/admin/search.php');
} else if ($data = $mform->get_data()) {
//    $columns = $DB->get_columns($data->tablename);
//    foreach ($columns as $col) {
//        \core\notification::success($col->name . ' ' . $col->type);
//    }

    if (!$data->classname) {
        $data->classname = $data->tablename;
    }

    $ORM = new Orm($data->tablename, $data->pluginname, $data->classname);
    $filename = $ORM->CreateClassesFiles();

    \core\notification::success("<a href='" . $CFG->wwwroot . "/admin/tool/orm/download.php?file=$filename'>Download</a>");

} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
    //Set default data (if any)
    $mform->set_data($mform);
}


echo Base::page('/admin/tool/orm/index.php', get_string('pluginname', 'tool_orm'), $page_header, $context);

echo $OUTPUT->header();
//**********************
//*** DISPLAY HEADER ***
//    
$mform->display();
//**********************
//*** DISPLAY FOOTER ***
//**********************
echo $OUTPUT->footer();
