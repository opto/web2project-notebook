<?php
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

<<<<<<< HEAD

=======
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
<script language="javascript" type="text/javascript">
    function delIt() {
        if (confirm("' . $AppUI->_('doDelete') . ' note?")){
            document.frmDelete.submit();
        }
    }
</script>
';
>>>>>>> remotes/origin/master

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

//$obj = null;
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


print '

';




?>


<<<<<<< HEAD











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
				<div id= "note_body" name = "note_body"  style="width:100%;height:320px"><?php echo $obj->note_body; ?></div>
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
<tr>
<textarea id="pasteArea" placeholder="Paste Image Here"></textarea>
<img id="pastedImage">  </img>

         <script language="javascript" type="text/javascript">


document.getElementById("pasteArea").onpaste = function(event) {
  // use event.originalEvent.clipboard for newer chrome versions
  var items = (event.clipboardData || event.originalEvent.clipboardData).items;
  var blob = null;
  alert("paste");
  alert(items.length);
   var dt = event.clipboardData.dataTransfer;
  var files = dt.files;

  var count = event.clipboardData.dataTransfer.types.length;    alert(count);
  for (var i = 0; i < items.length; i++) {
   alert(items[i].type);
   
      if (items[i].type.indexOf("image") === 0) {
      blob = items[i].getAsFile();
    }
  }

  if (blob !== null) {
    var reader = new FileReader();
    reader.onload = function(event) {
      document.getElementById("pastedImage").src = event.target.result;
    };
    reader.readAsDataURL(blob);
  }
}

</script>

}</tr>
</table>
=======
<form name="frmDelete" action="?m=notebook" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_note_aed" />
    <input type="hidden" name="del" value="1" />
    <input type="hidden" name="note_id" value="<?php echo $note_id; ?>" />
</form>

<?php

$view = new w2p_Output_HTML_ViewHelper($AppUI);

?>

<div class="std view notebook">
    <div class="column left" style="width: 25%">
        <p><?php $view->showLabel('Note Title'); ?>
            <?php $view->showField('note_name', $obj->note_name); ?>
        </p>
        <p><?php $view->showLabel('Company'); ?>
            <?php $view->showField('note_company', $obj->note_company); ?>
        </p>
        <p><?php $view->showLabel('Project'); ?>
            <?php $view->showField('note_project', $obj->note_project); ?>
        </p>
        <p><?php $view->showLabel('Task'); ?>
            <?php $view->showField('note_task', $obj->note_task); ?>
        </p>
        <p><?php $view->showLabel('Created By'); ?>
            <?php $view->showField('note_owner', $obj->note_creator); ?>
        </p>
        <p><?php $view->showLabel('Created At'); ?>
            <?php $view->showField('_datetime', $obj->note_created); ?>
        </p>
        <p><?php $view->showLabel('Modified By'); ?>
            <?php $view->showField('note_owner', $obj->note_modified_by); ?>
        </p>
        <p><?php $view->showLabel('Modified At'); ?>
            <?php $view->showField('_datetime', $obj->note_modified); ?>
        </p>
    </div>
    <div class="column right" style="width: 70%;">
        <p><?php $view->showLabel('Private'); ?>
            <input type="checkbox" disabled="disabled" name="note_private" <?php echo ($obj->note_private ? 'checked="checked"' : ''); ?> />
        </p>
        <p><?php $view->showLabel('Category'); ?>
            <?php $view->showField('note_category', $categories[$obj->note_category]); ?>
        </p>
        <p><?php $view->showLabel('Status'); ?>
            <?php $view->showField('note_status', $status[$obj->note_status]); ?>
        </p>
        <p><?php $view->showLabel('URL'); ?>
            <?php $view->showField('note_doc_url', $obj->note_doc_url); ?>
        </p>
        <p><?php $view->showLabel('Description'); ?>
            <?php $view->showField('_description', $obj->note_body); ?>
        </p>
    </div>
</div>
>>>>>>> remotes/origin/master
