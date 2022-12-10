<?php
defined('MOODLE_INTERNAL') || die;

$context = context_system::instance();

$ADMIN->add('development', new admin_externalpage('tool_orm', new lang_string('pluginname', 'tool_orm'), "$CFG->wwwroot/admin/tool/orm/index.php"));