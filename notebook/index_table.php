<?php /* $Id: index_table.php 374 2012-06-26 07:35:45Z caseydk $ $URL: svn+ssh://caseydk@svn.code.sf.net/p/web2project-mod/code/notebook/trunk/index_table.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $deny1, $canRead, $canEdit;
global $company_id, $project_id, $task_id, $user_id, $note_status, $showCompany, $m, $tab, $search_string;

$page = (int) w2PgetParam($_GET, 'page', 1);
$search = w2PgetParam($_REQUEST, 'search', '');

if (!isset($project_id)) {
	$project_id = w2PgetParam($_REQUEST, 'project_id', 0);
}
if (!isset($showCompany)) {
	$showCompany = true;
}

$project = new CProject();
$task = new CTask();

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

// SETUP FOR NOTE LIST
$q = new w2p_Database_Query();
$q->addQuery('notes.*');
$q->addQuery('contact_first_name, contact_last_name');
$q->addQuery('project_name, pr.project_id, project_color_identifier, project_status');
$q->addQuery('task_name, task_id');
$q->addQuery('con.company_name, con.company_id');

$q->addTable('notes');

$q->leftJoin('projects', 'pr', 'pr.project_id = note_project');
$q->leftJoin('tasks', 't', 't.task_id = note_task');
$q->leftJoin('companies', 'con', 'con.company_id = note_company');
$q->leftJoin('users', 'u', 'user_id = note_creator');
$q->leftJoin('contacts', 'c', 'user_contact = contact_id');

if ($m == 'notebook' && $tab) {
    $_tab = $tab - 1;
    $q->addWhere('(note_category = ' . (int) $_tab . ')');
}
if (!empty($search_string)) {
    $q->addWhere('note_title LIKE "%' . $search_string . '%" OR note_body LIKE "%' . $search_string . '%"' );
}
if ($company_id) { // Company
	$q->addWhere('(note_company = ' . (int)$company_id . ')');
}
if ($project_id) { // Project
	$q->addWhere('(note_project = ' . (int)$project_id . ')');
}
if ($task_id) { // Task
	$q->addWhere('note_task = ' . (int)$task_id);
}
if ($user_id) { // User
	$q->addWhere('note_creator = ' . (int)$user_id);
}
if (isset($note_status) && $note_status >= 0) { // Task
	$q->addWhere('note_status = ' . (int)$note_status);
}

$q->addWhere('(note_private = 0 OR note_creator = ' . (int)$AppUI->user_id . ')');

// Permissions
$project->setAllowedSQL($AppUI->user_id, $q, 'note_project');
$task->setAllowedSQL($AppUI->user_id, $q, 'note_task');
$q->addOrder('company_name, note_title');

$items = $q->loadList();

$module = new w2p_System_Module();
$fields = $module->loadSettings('notebook', 'index_list');

if (0 == count($fields)) {
    $fieldList = array('note_title', 'note_category', 'note_status', 'note_project', 'note_task', 'note_creator', 'note_created');
    $fieldNames = array('Note Title', 'Category', 'Status', 'Project', 'Task', 'Creator', 'Date');

    $fields = array_combine($fieldList, $fieldNames);
}

$note_category = w2PgetSysVal('NoteCategory');
$note_status = w2PgetSysVal('NoteStatus');
$customLookups = array('note_category' => $note_category, 'note_status' => $note_status);

include $AppUI->getTheme()->resolveTemplate('list');