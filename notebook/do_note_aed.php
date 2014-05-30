<?php /* $Id: do_note_aed.php 374 2012-06-26 07:35:45Z caseydk $ $URL: svn+ssh://caseydk@svn.code.sf.net/p/web2project-mod/code/notebook/trunk/do_note_aed.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

//addnote sql
$note_id = (int) w2PgetParam($_POST, 'note_id', 0);
$del = (int) w2PgetParam($_POST, 'del', 0);

$obj = new CNote();
if ($note_id) {
	$obj->_message = 'updated';
} else {
	$obj->_message = 'added';
}
$now = new CDate();
$obj->note_category = intval(w2PgetParam($_POST, 'note_category', 0));
$note_body = stripslashes(w2PgetParam($_POST, 'note_body', 0));
if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('Note');
// delete the note
if ($del) {
	$obj->load($note_id);
	if (($msg = $obj->delete())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
		$AppUI->redirect();
	} else {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
		$AppUI->redirect('m=notebook');
	}
}

if (!$note_id) {
	$obj->note_creator = $AppUI->user_id;
	$obj->note_created = $now->format(FMT_DATETIME_MYSQL);
} else {
	$obj->note_modified_by = $AppUI->user_id;
	$obj->note_modified = $now->format(FMT_DATETIME_MYSQL);
}

if ($obj->note_project) {
	$q = new w2p_Database_Query;
	$q->addTable('projects');
	$q->addQuery('project_company');
	$q->addWhere('project_id = ' . (int)$obj->note_project);
	$obj->note_company = $q->loadResult();
} elseif (!$obj->note_company) {
	$obj->note_company = 0;
}

if (($msg = $obj->store())) {
	$AppUI->setMsg($msg, UI_MSG_ERROR);
} else {
	$q = new w2p_Database_Query;
	$q->addTable('notes');
	$q->addUpdate('note_body', $note_body);
	$q->addWhere('note_id = ' . $obj->note_id);
	$q->exec();
	$AppUI->setMsg($note_id ? 'updated' : 'added', UI_MSG_OK, true);
}

$AppUI->redirect();