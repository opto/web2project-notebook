<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

global $AppUI, $m, $obj, $user_id, $company_id, $showCompany;

if (canView('notebook')) {
	if (canEdit('notebook')) {
		echo '<a href="./index.php?m=notebook&a=addedit">' . $AppUI->_('Add Note') . '</a>';
		echo w2PshowImage('stock_attach-16.png', 16, 16, '', '', $m);
	}
	$company_id = 0;
	include (W2P_BASE_DIR . '/modules/notebook/index_table.php');
}