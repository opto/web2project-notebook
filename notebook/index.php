<?php /* $Id: index.php 181 2010-12-29 16:20:58Z caseydk $ $URL: svn+ssh://caseydk@svn.code.sf.net/p/web2project-mod/code/notebook/trunk/index.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// retrieve any state parameters
if (isset($_REQUEST['note_status'])) {
	$AppUI->setState('NoteIdxStatus', w2PgetParam($_REQUEST, 'note_status', null));
}

$note_status = $AppUI->getState('NoteIdxStatus') !== null ? $AppUI->getState('NoteIdxStatus') : -1;


$company_id = $AppUI->processIntState('NoteIdxCompany', $_POST, 'company_id', 0);

if (isset($_REQUEST['project_id'])) {
	$AppUI->setState('NoteIdxProject', w2PgetParam($_REQUEST, 'project_id', null));
}

$project_id = $AppUI->getState('NoteIdxProject') !== null ? $AppUI->getState('NoteIdxProject') : 0;

if (w2PgetParam($_GET, 'tab', -1) != -1) {
	$AppUI->setState('NoteIdxTab', w2PgetParam($_GET, 'tab'));
}
$tab = $AppUI->getState('NoteIdxTab') !== null ? $AppUI->getState('NoteIdxTab') : 0;
$active = intval(!$AppUI->getState('NoteIdxTab'));

// get the list of visible companies
$extra = array('from' => 'notes', 'where' => 'companies.company_id = note_company');

$company = new CCompany();
$companies = $company->getAllowedRecords($AppUI->user_id, 'companies.company_id,company_name', 'company_name', null, $extra, 'companies');
$companies = arrayMerge(array('0' => $AppUI->_('All', UI_OUTPUT_JS)), $companies);

// get the list of visible companies
$extra = array('from' => 'notes', 'where' => 'projects.project_id = note_project');

$project = new CProject();
$projects = $project->getAllowedRecords($AppUI->user_id, 'projects.project_id,project_name', 'project_name', null, $extra, 'projects');
$projects = arrayMerge(array('0' => $AppUI->_('All', UI_OUTPUT_JS)), $projects);

$status = w2PgetSysVal('NoteStatus');
$status = arrayMerge(array('-1' => $AppUI->_('All', UI_OUTPUT_JS)), $status);

$search_string = w2PgetParam($_POST, 'search_string', '');
$search_string = w2PformSafe($search_string, true);

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Notebook', 'notebook.png', $m, $m . '.' . $a);
$titleBlock->addSearchCell($search_string);
$titleBlock->addFilterCell('Company', 'company_id', $companies, $company_id);
$titleBlock->addCell($AppUI->_('Project') . ':');
$titleBlock->addCell(arraySelect($projects, 'project_id', 'onchange="document.pickProject.submit()" size="1" class="text"', $project_id), '', '<form name="pickProject" action="?m=notebook" method="post">', '</form>');
$titleBlock->addCell($AppUI->_('Status') . ':');
$titleBlock->addCell(arraySelect($status, 'note_status', 'onchange="document.pickStatus.submit()" size="1" class="text"', $note_status), '', '<form name="pickStatus" action="?m=notebook" method="post">', '</form>');
if ($canEdit) {
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new note') . '">', '', '<form action="?m=notebook&a=addedit" method="post">', '</form>');
}
$titleBlock->show();

$note_types = w2PgetSysVal('NoteCategory');
if ($tab != -1) {
	array_unshift($note_types, 'All Notes');
}
array_map(array($AppUI, '_'), $note_types);

$tabBox = new CTabBox('?m=notebook', W2P_BASE_DIR . '/modules/notebook/', $tab);

$i = 0;

foreach ($note_types as $note_type) {
	$tabBox->add('index_table', $note_type);
	++$i;
}

$tabBox->show();