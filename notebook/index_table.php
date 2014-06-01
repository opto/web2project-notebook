<?php /* $Id: index_table.php 374 2012-06-26 07:35:45Z caseydk $ $URL: svn+ssh://caseydk@svn.code.sf.net/p/web2project-mod/code/notebook/trunk/index_table.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $deny1, $canRead, $canEdit;

// modified later by Pablo Roca (proca) in 18 August 2003 - added page support
// Files modules: index page re-usable sub-table
$m = 'notebook';

// ****************************************************************************
// Page numbering variables
// Pablo Roca (pabloroca@Xmvps.org) (Remove the X)
// 19 August 2003
//
// $tab             - file category
// $page            - actual page to show
// $xpg_pagesize    - max rows per page
// $xpg_min         - initial record in the SELECT LIMIT
// $xpg_totalrecs   - total rows selected
// $xpg_sqlrecs     - total rows from SELECT LIMIT
// $xpg_total_pages - total pages
// $xpg_next_page   - next pagenumber
// $xpg_prev_page   - previous pagenumber
// $xpg_break       - stop showing page numbered list?
// $xpg_sqlcount    - SELECT for the COUNT total
// $xpg_sqlquery    - SELECT for the SELECT LIMIT
// $xpg_result      - pointer to results from SELECT LIMIT
function shownavbar_notes($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page)
{

	global $AppUI, $m;
	$xpg_break = false;
	$xpg_prev_page = $xpg_next_page = 0;

	$s = '<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr>';

	if ($xpg_totalrecs > $xpg_pagesize) {
		$xpg_prev_page = $page - 1;
		$xpg_next_page = $page + 1;
		// left buttoms
		if ($xpg_prev_page > 0) {
			$s .= '<td align="left" width="15%"><a href="./index.php?m=' . $m . 'notebook&amp;page=1"><img src="' . w2PfindImage('navfirst.gif') . '" border="0" Alt="First Page"></a>&nbsp;&nbsp;';
			$s .= '<a href="./index.php?m=' . $m . '&amp;page=' . $xpg_prev_page . '"><img src="' . w2PfindImage('navleft.gif') . '" border="0" Alt="Previous page (' . $xpg_prev_page . ')"></a></td>';
		} else {
			$s .= '<td width="15%">&nbsp;</td>';
		}

		// central text (files, total pages, ...)
		$s .= '<td align="center" width="70%">' . $xpg_totalrecs . ' ' . $AppUI->_('Note(s)') . ' (' . $xpg_total_pages . ' ' . $AppUI->_('Page(s)') . ')</td>';

		// right buttoms
		if ($xpg_next_page <= $xpg_total_pages) {
			$s .= '<td align="right" width="15%"><a href="./index.php?m=' . $m . '&amp;page=' . $xpg_next_page . '"><img src="' . w2PfindImage('navright.gif') . '" border="0" Alt="Next Page (' . $xpg_next_page . ')"></a>&nbsp;&nbsp;';
			$s .= '<a href="./index.php?m=' . $m . '&amp;page=' . $xpg_total_pages . '"><img src="' . w2PfindImage('navlast.gif') . '" border="0" Alt="Last Page"></a></td>';
		} else {
			$s .= '<td width="15%">&nbsp;</td></tr>';
		}
		// Page numbered list, up to 30 pages
		$s .= '<tr><td colspan="3" align="center"> [ ';

		for ($n = $page > 16 ? $page - 16 : 1; $n <= $xpg_total_pages; $n++) {
			if ($n == $page) {
				$s .= '<b>' . $n . '</b></a>';
			} else {
				$s .= '<a href="./index.php?m=' . $m . '&amp;page=' . $n . '">' . $n . '</a>';
			}
			if ($n >= 30 + $page - 15) {
				$xpg_break = true;
				break;
			} elseif ($n < $xpg_total_pages) {
				$s .= ' | ';
			}
		}

		if (!isset($xpg_break)) { // are we supposed to break ?
			if ($n == $page) {
				$s .= '<' . $n . '</a>';
			} else {
				$s .= '<a href="./index.php?m=' . $m . '&amp;page=' . $xpg_total_pages . '">';
				$s .= $n . '</a>';
			}
		}
		$s .= ' ] </td></tr>';
	} else { // or we dont have any files..
		$s .= '<td align="center">';
		if ($xpg_next_page > $xpg_total_pages) {
			$s .= $xpg_sqlrecs . ' ' . $m . ' ';
		}
		$s .= '</td></tr>';
	}
	$s .= '</table>';
	echo $s;
}

$tab = $AppUI->getState('NoteIdxTab') !== null ? $AppUI->getState('NoteIdxTab') : 0;
$page = (int) w2PgetParam($_GET, 'page', 1);
$search = w2PgetParam($_REQUEST, 'search', '');

global $company_id, $project_id, $task_id, $user_id, $note_status, $showCompany;
if (!isset($project_id)) {
	$project_id = w2PgetParam($_REQUEST, 'project_id', 0);
}
if (!isset($showCompany)) {
	$showCompany = true;
}

$xpg_pagesize = 30;
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from

$project = new CProject();
$task = new CTask();

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$note_types = w2PgetSysVal('NoteCategory');
$note_statuses = w2PgetSysVal('NoteStatus');
if ($tab <= 0) {
	$catsql = '';
} else {
	$catsql = 'note_category = ' . --$tab;
}

// SETUP FOR NOTE LIST
$q = new w2p_Database_Query();
$q->addQuery('notes.*');
$q->addQuery('contact_first_name, contact_last_name');
$q->addQuery('project_name, pr.project_id, project_color_identifier, project_status');
$q->addQuery('task_name, task_id');
$q->addQuery('con.company_name, con.company_id');

$q->addTable('notes');

$q->leftJoin('companies', 'con', 'con.company_id = note_company');
$q->leftJoin('users', 'u', 'user_id = note_creator');
$q->leftJoin('contacts', 'c', 'user_contact = contact_id');

if (!empty($search)) {
	$q->addWhere('(note_title LIKE \'%' . $search . '%\' OR note_body LIKE \'%' . $search . '%\')');
}
if ($company_id) { // Company
	$q->addWhere('(note_company = ' . (int)$company_id . ' OR note_company = 0)');
}
if ($project_id) { // Project
	$q->addWhere('(note_project = ' . (int)$project_id . ' OR note_project = 0)');
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

if ($catsql) { // Category
	$q->addWhere($catsql);
}
// Permissions
$project->setAllowedSQL($AppUI->user_id, $q, 'note_project');
$task->setAllowedSQL($AppUI->user_id, $q, 'note_task and task_project = note_project');
$q->addOrder('company_name, note_title');

//LIMIT ' . $xpg_min . ', ' . $xpg_pagesize ;
//if ($canRead) {
	$notes = $q->loadList();
//} else {
//	$AppUI->redirect('m=public&a=access_denied');
//}
// counts total recs from selection
$xpg_totalrecs = count($notes);

// How many pages are we dealing with here ??
$xpg_total_pages = ($xpg_totalrecs > $xpg_pagesize) ? ceil($xpg_totalrecs / $xpg_pagesize) : 0;

shownavbar_notes($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page);

?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th nowrap="nowrap">&nbsp;</th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Note Title'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Category'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Status'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Project'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Task'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Creator'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Date'); ?></th>
</tr>
<?php
$fp = -1;

$id = 0;
for ($i = ($page - 1) * $xpg_pagesize; $i < $page * $xpg_pagesize && $i < $xpg_totalrecs; $i++) {
	$row = $notes[$i];
	$note_created = new CDate($row['note_created']);

	if ($fp != $row['note_company']) {
		if (!$row['company_name']) {
			$row['company_name'] = $AppUI->_('All Companies');
		}
		if ($showCompany) {
			$s = '<tr>';
			$s .= '<td colspan="10" style="border: outset 2px #eeeeee">';
			if ($row['company_id'] > 0) {
				$s .= '<a href="?m=companies&a=view&company_id=' . $row['company_id'] . '">' . $row['company_name'] . '</a>';
			} else {
				$s .= $row['company_name'];
			}
			$s .= '</td></tr>';
			echo $s;
		}
	}
	$fp = $row['note_company'];
?>
<tr>
	<td nowrap="nowrap" align="center" width="20">
	<?php if ($canEdit) {
		echo '<a href="./index.php?m=' . $m . '&a=addedit&note_id=' . $row['note_id'] . '">' . w2PshowImage('icons/stock_edit-16.png', '16', '16') . '</a>';
	}
?>
	</td>
	<td nowrap="8%">
		<?php
	echo '<a href="./index.php?m=' . $m . '&a=view&note_id=' . $row['note_id'] . '">' . $row['note_title'] . '</a>';
	if (mb_trim($row['note_doc_url'])) {
		echo '<a href="' . $row['note_doc_url'] . '" target="_blank">' . w2PshowImage('clip.png', '16', '16') . '</a>';
	}
?>
	</td>
    <td width="10%" nowrap="nowrap"><?php echo $note_types[$row['note_category']]; ?></td> 
    <td width="10%" nowrap="nowrap"><?php echo $note_statuses[$row['note_status']]; ?></td> 
	<td width="10%" align="left"><a href="./index.php?m=projects&a=view&project_id=<?php echo $row['project_id']; ?>"><?php echo $row['project_name']; ?></a></td>
	<td width="10%" align="left"><a href="./index.php?m=tasks&a=view&task_id=<?php echo $row['task_id']; ?>"><?php echo $row['task_name']; ?></a></td>
	<td width="15%" nowrap="nowrap"><?php echo $row['contact_first_name'] . ' ' . $row['contact_last_name']; ?></td>
	<td width="15%" nowrap="nowrap" align="center"><?php echo $note_created->format($df . ' ' . $tf); ?></td>
</tr>
<?php } ?>
</table>
<?php
shownavbar_notes($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page);