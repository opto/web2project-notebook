<?php /* $Id: view.php 374 2012-06-26 07:35:45Z caseydk $ $URL: svn+ssh://caseydk@svn.code.sf.net/p/web2project-mod/code/notebook/trunk/view.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$note_id = (int) w2PgetParam($_GET, 'note_id', 0);

// check permissions for this record
$perms = &$AppUI->acl();
$canEdit = $perms->checkModule($m, 'edit');
if (!$canEdit) {
	$AppUI->redirect('m=public&a=access_denied');
}

// load the companies class to retrieved denied companies
require_once ($AppUI->getModuleClass('projects'));
print '<script type="text/javascript" src="' . w2PgetConfig('base_url') . '/lib/tiny_mce/tiny_mce.js"></script>';
print '
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		// General options
		mode : "textareas",
		theme : "advanced",
		readonly : true
	});
</script>
';

$note_task = (int) w2PgetParam($_GET, 'task_id', 0);
$note_parent = (int) w2PgetParam($_GET, 'note_parent', 0);
$note_project = (int) w2PgetParam($_GET, 'project_id', 0);
$note_company = (int) w2PgetParam($_GET, 'company_id', 0);

$q = new w2p_Database_Query();
$q->addQuery('notes.*');
$q->addQuery('u.user_username');
$q->addQuery('c.contact_first_name, c.contact_last_name');
$q->addQuery('cm.contact_first_name AS modified_first_name, cm.contact_last_name AS modified_last_name');
$q->addQuery('project_id');
$q->addQuery('task_id, task_name');
$q->addQuery('company_id, company_name');
$q->addTable('notes');
$q->leftJoin('users', 'u', 'note_creator = u.user_id');
$q->leftJoin('contacts', 'c', 'u.user_contact = c.contact_id');
$q->leftJoin('users', 'um', 'note_modified_by = um.user_id');
$q->leftJoin('contacts', 'cm', 'um.user_contact = cm.contact_id');
$q->leftJoin('companies', 'co', 'company_id = note_company');
$q->leftJoin('projects', 'p', 'project_id = note_project');
$q->leftJoin('tasks', 't', 'task_id = note_task');
$q->addWhere('note_id = ' . (int)$note_id);

// check if this record has dependancies to prevent deletion
$msg = '';
$obj = new CNotebook();
$canDelete = $obj->canDelete($msg, $note_id);

$obj = null;
$q->loadObject($obj);
// load the record data
if (!$obj && $note_id > 0) {
	$AppUI->setMsg('Note');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
}

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$note_created = new w2p_Utilities_Date($obj->note_created);
$note_modified = new w2p_Utilities_Date($obj->note_modified);

// setup the title block
$ttl = 'View Note';
$titleBlock = new CTitleBlock($ttl, 'notebook.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=' . $m, 'notes list');
$titleBlock->addCrumb('?m=' . $m . '&a=addedit&note_id=' . $note_id, 'edit note');
$canDelete = $perms->checkModule($m, 'delete');
if ($canDelete && $note_id > 0) {
	$titleBlock->addCrumbDelete('delete note', $canDelete, $msg);
}
$titleBlock->show();

if ($obj->note_project) {
	$note_project = $obj->note_project;
}
if ($obj->note_task) {
	$note_task = $obj->note_task;
	$task_name = $obj->task_name;
} elseif ($note_task) {
	$q->clear();
	$q->addQuery('task_name');
	$q->addTable('tasks');
	$q->addWhere('task_id = ' . (int)$note_task);
	$task_name = $q->loadResult();
} else {
	$task_name = '';
}

if ($obj->note_company) {
	$note_company = $obj->note_company;
	$company_name = $obj->company_name;
} elseif ($note_company) {
	$q->clear();
	$q->addQuery('company_name');
	$q->addTable('companies');
	$q->addWhere('company_id = ' . (int)$note_company);
	$company_name = $q->loadResult();
} else {
	$company_name = '';
}

if (intval(w2PgetParam($_GET, 'company_id', 0))) {
	$extra = array('where' => 'project_active = 1 AND project_company = ' . $note_company);
} else {
	$extra = array('where' => 'project_active = 1');
}
$project = new CProject();
$projects = $project->getAllowedRecords($AppUI->user_id, 'projects.project_id,project_name', 'project_name', null, $extra, 'projects');
$projects = arrayMerge(array('0' => $AppUI->_('All', UI_OUTPUT_JS)), $projects);

$categories = w2PgetSysVal('NoteCategory');
$status = w2PgetSysVal('NoteStatus');
?>

<table width="100%" border="0" cellpadding="3" cellspacing="3" class="std">
<tr>
	<td width="100%" valign="top" align="center">
		<table cellspacing="1" cellpadding="2" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Note Title'); ?>:</td>
			<td align="left" class="hilite"><?php echo $obj->note_title; ?></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Private'); ?>:</td>
			<td>
				<input type="checkbox" disabled="disabled" name="note_private" <?php echo ($obj->note_private ? 'checked="checked"' : ''); ?> />
			</td>
		</tr>
	<?php if ($note_id) { ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Created By'); ?>:</td>
			<td align="left" class="hilite"><?php echo $obj->contact_first_name . ' ' . $obj->contact_last_name; ?>, <?php echo $note_created->format($df . ' ' . $tf); ?></td>
		</tr>
	<?php } ?>
	<?php if ($obj->note_modified_by) { ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Modified By'); ?>:</td>
			<td align="left" class="hilite"><?php echo $obj->modified_first_name . ' ' . $obj->modified_last_name; ?>, <?php echo $note_modified->format($df . ' ' . $tf); ?></td>
		</tr>
	<?php } ?>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Category'); ?>:</td>
			<td align="left" class="hilite">
            	<?php echo $categories[$obj->note_category]; ?>
            </td>
		</tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Status'); ?>:</td>
			<td align="left" class="hilite">
            	<?php echo $status[$obj->note_status]; ?>
            </td>
		</tr>
	<?php if ($company_name) { ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
			<td align="left" class="hilite">
				<?php echo $company_name; ?>
            </td>
		</tr>
	<?php } ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project'); ?>:</td>
			<td align="left" class="hilite">
				<?php echo $projects[$note_project]; ?>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task'); ?>:</td>
			<td align="left" class="hilite">
				<?php echo $task_name; ?>
			</td>
		</tr>

		<tr>
			<td align="right" valign="top" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:</td>
			<td align="left">
				<textarea class="text" style="width:100%;height:320px"><?php echo $obj->note_body; ?></textarea>
			</td>
		</tr>

	<?php if (mb_trim($obj->note_doc_url)) { ?>
		<tr>
			<td align="right" nowrap="nowrap">&nbsp;</td>
			<td align="left" nowrap="nowrap"><a href="<?php echo $obj->note_doc_url; ?>" target="_blank"><?php echo $AppUI->_('Note Document'); ?></a></td>
		</tr>
	<?php } ?>
		</table>
	</td>
</tr>
</table>