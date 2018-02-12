<?php /* $Id: addedit.php 374 2012-06-26 07:35:45Z caseydk $ $URL: svn+ssh://caseydk@svn.code.sf.net/p/web2project-mod/code/notebook/trunk/addedit.php $ */
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

print '
       <script language="javascript" type="text/javascript">
$(document).ready(function(){ CKEDITOR.replace( "note_body",
{
			extraPlugins: "uploadimage",
			uploadUrl : "?m=notebook&a=do_upload_file_image&suppressHeaders=true&note_id='.$note_id.'",
			height: 900,
} ); 
editor=CKEDITOR.instances.note_body;
editor.on( "paste", function( evt ) {
 //   alert(evt.data.dataTransfer.isEmpty());
} );
//CKEDITOR.config.extraPlugins = "uploadimage";
//CKEDITOR.config.uploadUrl = "/do_upload_file_image.php";
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

//if ($note_id > 0) {
	$note_created = new w2p_Utilities_Date($obj->note_created);
	$note_modified = new w2p_Utilities_Date($obj->note_modified);
	//$obj->note_modified_by=$AppUI->user_id;
/*	}
	else 
	{
		
	$note_created = ($q->dbfnNowWithTZ());
	$note_modified = new w2p_Utilities_Date($q->dbfnNowWithTZ()  );
	
	}
*/
// setup the title block
$ttl = $note_id ? 'Edit Note' : 'Add Note';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'notebook.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=' . $m, 'notes list');
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

?>
<script language="javascript" type="text/javascript">
function submitIt() {
	var f = document.uploadFrm;
	f.submit();
}
function delIt() {
	if (confirm( "<?php echo $AppUI->_('notesDelete', UI_OUTPUT_JS); ?>" )) {
		var f = document.uploadFrm;
		f.del.value='1';
		f.submit();
	}
}
function popTask() {
    var f = document.uploadFrm;
    if (f.note_project.selectedIndex == 0) {
        alert( "<?php echo $AppUI->_('Please select a project first!', UI_OUTPUT_JS); ?>" );
    } else {
        window.open('./index.php?m=public&a=selector&dialog=1&callback=setTask&table=tasks&task_project='
            + f.note_project.options[f.note_project.selectedIndex].value, 'task','left=50,top=50,height=250,width=400,resizable')
    }
}

// Callback function for the generic selector
function setTask( key, val ) {
    var f = document.uploadFrm;
    if (val != '') {
        f.note_task.value = key;
        f.task_name.value = val;
    } else {
        f.note_task.value = '0';
        f.task_name.value = '';
    }
}
</script>

<form name="uploadFrm" action="?m=notebook" method="post">
	<input type="hidden" name="dosql" value="do_note_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="note_id" value="<?php echo $note_id; ?>" />
	<input type="hidden" name="note_creator" value="<?php echo ($note_id ? $obj->note_creator : $AppUI->user_id); ?>" />
	<input type="hidden" name="note_company" value="<?php echo $note_company; ?>" />
<table width="100%" border="0" cellpadding="3" cellspacing="3" class="std">
<tr>
	<td width="100%" valign="top" align="center">
		<table cellspacing="1" cellpadding="2" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Note Title'); ?>:</td>
			<td align="left"><input type="text" style="width:400px" class="text" name="note_name" value="<?php echo $obj->note_name; ?>" /></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Private'); ?>:</td>
			<td>
				<input type="checkbox" value="1" name="note_private" <?php echo ($obj->note_private ? 'checked="checked"' : ''); ?> />
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
            <td align="left">
                    <?php echo arraySelect(w2PgetSysVal('NoteCategory'), 'note_category', 'class="text"', $obj->note_category, true); ?>
            </td>
		</tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Status'); ?>:</td>
            <td align="left">
                    <?php echo arraySelect(w2PgetSysVal('NoteStatus'), 'note_status', 'class="text"', $obj->note_status, true); ?>
            </td>
		</tr>
	<?php if ($company_name) { ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
			<td align="left" class="hilite"><?php echo $company_name; ?></td>
		</tr>
	<?php } ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project'); ?>:</td>
			<td align="left">
			<?php
echo arraySelect($projects, 'note_project', 'size="1" class="text" style="width:270px"', $note_project);
?>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task'); ?>:</td>
			<td align="left" colspan="2" valign="top">
				<input type="hidden" name="note_task" value="<?php echo $note_task; ?>" />
				<input type="text" class="text" name="task_name" value="<?php echo $task_name; ?>" size="40" disabled="disabled" />
				<input type="button" class="button" value="<?php echo $AppUI->_('select task'); ?>..." onclick="popTask()" />
			</td>
		</tr>

		<tr>
			<td align="right" valign="top" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:</td>
			<td align="left">
				<textarea class="text" name="note_body"  id="note_body"><?php echo $obj->note_body; ?></textarea>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Note Doc URL'); ?>:</td>
			<td align="left"><input type="field" class="text" name="note_doc_url" style="width:400px" value="<?php echo $obj->note_doc_url ?>" /></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td>
		<input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" onclick="javascript:if(confirm('<?php echo $AppUI->_('Are you sure you want to cancel?', UI_OUTPUT_JS); ?>')){location.href = './index.php?m=notebook';}" />
	</td>
	<td align="right">
		<input type="button" class="button" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt()" />
	</td>
</tr>
</form>

</table>
