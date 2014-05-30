<?php /* $Id: admin_tab.viewuser.notes.php 181 2010-12-29 16:20:58Z caseydk $ $URL: svn+ssh://caseydk@svn.code.sf.net/p/web2project-mod/code/notebook/trunk/admin_tab.viewuser.notes.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

global $AppUI, $m, $obj, $user_id, $company_id, $showCompany;
if (canView('notebook')) {
	if (canEdit('notebook')) {
		echo '<a href="./index.php?m=notebook&a=addedit">' . $AppUI->_('Add Note') . '</a>';
		echo w2PshowImage('stock_attach-16.png', 16, 16, '', '', $m);
	}
	$showCompany = true;
	$company_id = 0;
	include (W2P_BASE_DIR . '/modules/notebook/index_table.php');
}