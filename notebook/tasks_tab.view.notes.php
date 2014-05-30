<?php /* $Id: tasks_tab.view.notes.php 181 2010-12-29 16:20:58Z caseydk $ $URL: svn+ssh://caseydk@svn.code.sf.net/p/web2project-mod/code/notebook/trunk/tasks_tab.view.notes.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

global $AppUI, $m, $obj, $task_id, $showCompany;
if (canView('notebook')) {
    if (canEdit('notebook')) {
		echo '<a href="./index.php?m=notebook&a=addedit&project_id=' . $obj->task_project . '&task_id=' . $task_id . '">' . $AppUI->_('Add Note') . '</a>';
		echo w2PshowImage('stock_attach-16.png', 16, 16, '', '', $m);
	}
	$showCompany = false;
	$project_id = $obj->task_project;
	include (W2P_BASE_DIR . '/modules/notebook/index_table.php');
}