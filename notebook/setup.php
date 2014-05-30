<?php /* $Id: setup.php 183 2011-01-03 16:50:41Z caseydk $ $URL: svn+ssh://caseydk@svn.code.sf.net/p/web2project-mod/code/notebook/trunk/setup.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 *  Name: Notebook
 *  Directory: notebook
 *  Version 2.0
 *  Type: user
 *  UI Name: Notebook
 *  UI Icon: ?
 */

$config = array();
$config['mod_name'] = 'Notebook'; // name the module
$config['mod_version'] = '2.0'; // add a version number
$config['mod_directory'] = 'notebook'; // tell web2Project where to find this module
$config['mod_setup_class'] = 'CSetupNotebook'; // the name of the PHP setup class (used below)
$config['mod_type'] = 'user'; // 'core' for modules distributed with w2P by standard, 'user' for additional modules from dotmods
$config['mod_ui_name'] = 'Notebook'; // the name that is shown in the main menu of the User Interface
$config['mod_ui_icon'] = 'notebook.png'; // name of a related icon
$config['mod_description'] = 'User notes in a easy way'; // some description of the module
$config['mod_config'] = false; // show 'configure' notebook in viewmods
$config['mod_main_class'] = 'CNote'; // the name of the PHP class used by the module
$config['permissions_item_table'] = 'notes';
$config['permissions_item_field'] = 'note_id';
$config['permissions_item_label'] = 'note_title';

if ($a == 'setup') {
	echo w2PshowModuleConfig($config);
}

// TODO: To be completed later as needed.
class CSetupNotebook {

	function configure() {
		return true;
	}

	function remove() {
		global $AppUI;

        $q = new DBQuery();
		$q->dropTable('notes');
		$q->exec();

		$q->clear();
		$q->setDelete('sysvals');
		$q->addWhere('sysval_title = \'NoteCategory\'');
		$q->exec();

		$q->clear();
		$q->setDelete('sysvals');
		$q->addWhere('sysval_title = \'NoteStatus\'');
		$q->exec();

        $perms = $AppUI->acl();
        return $perms->unregisterModule('todos');
	}

	function upgrade($old_version) {
		return true;
	}

	function install() {
		global $AppUI;

        $q = new DBQuery();
		$q->createTable('notes');
		$q->createDefinition('(
								`note_id` int(10) unsigned NOT NULL auto_increment,
								`note_parent` int(10) unsigned NOT NULL default \'0\',
								`note_company` int(10) unsigned NOT NULL default \'0\',
								`note_department` int(10) unsigned NOT NULL default \'0\',
								`note_project` int(10) unsigned NOT NULL default \'0\',
								`note_task` int(10) unsigned NOT NULL default \'0\',
								`note_file` int(10) unsigned NOT NULL default \'0\',
								`note_module` int(10) unsigned NOT NULL default \'0\',
								`note_module_name` varchar(64) NOT NULL default \'\',
								`note_record_id` int(10) unsigned NOT NULL default \'0\',
								`note_category` int(3) unsigned NOT NULL default \'0\',
								`note_status` int(3) unsigned NOT NULL default \'0\',
								`note_title` varchar(255) NOT NULL default \'\',
								`note_body` text NOT NULL,
								`note_doc_url` varchar(255) NOT NULL default \'\',
								`note_private` int(1) unsigned NOT NULL default \'0\',
								`note_creator` int(10) unsigned NOT NULL default \'0\',
								`note_created` datetime NOT NULL default \'0000-00-00 00:00:00\',
								`note_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
								`note_modified_by` int(10) unsigned NOT NULL default \'0\',
								PRIMARY KEY  (`note_id`), 
								KEY idx_note_company ( note_company ) ,
								KEY idx_note_project ( note_project ) ,
								KEY idx_note_task ( note_task ) ,
								KEY idx_note_user ( note_creator ) ,
								KEY idx_note_parent ( note_parent ) 
								) TYPE = MYISAM ');

		$q->exec($sql);

		$q->clear();
		$q->addTable('sysvals');
		$q->addInsert('sysval_key_id', 1);
		$q->addInsert('sysval_title', 'NoteCategory');
		$q->addInsert('sysval_value', 'Unknown');
		$q->addInsert('sysval_value_id', '0');
		$q->exec();

		$q->clear();
		$q->addTable('sysvals');
		$q->addInsert('sysval_key_id', 1);
		$q->addInsert('sysval_title', 'NoteCategory');
		$q->addInsert('sysval_value', 'Idea');
		$q->addInsert('sysval_value_id', '1');
		$q->exec();

		$q->clear();
		$q->addTable('sysvals');
		$q->addInsert('sysval_key_id', 1);
		$q->addInsert('sysval_title', 'NoteCategory');
		$q->addInsert('sysval_value', 'Workflow');
		$q->addInsert('sysval_value_id', '2');
		$q->exec();

		$q->clear();
		$q->addTable('sysvals');
		$q->addInsert('sysval_key_id', 1);
		$q->addInsert('sysval_title', 'NoteCategory');
		$q->addInsert('sysval_value', 'Document');
		$q->addInsert('sysval_value_id', '3');
		$q->exec();

		$q->clear();
		$q->addTable('sysvals');
		$q->addInsert('sysval_key_id', 1);
		$q->addInsert('sysval_title', 'NoteStatus');
		$q->addInsert('sysval_value', 'Unknown');
		$q->addInsert('sysval_value_id', '0');
		$q->exec();

		$q->clear();
		$q->addTable('sysvals');
		$q->addInsert('sysval_key_id', 1);
		$q->addInsert('sysval_title', 'NoteStatus');
		$q->addInsert('sysval_value', 'Reference');
		$q->addInsert('sysval_value_id', '1');
		$q->exec();

		$q->clear();
		$q->addTable('sysvals');
		$q->addInsert('sysval_key_id', 1);
		$q->addInsert('sysval_title', 'NoteStatus');
		$q->addInsert('sysval_value', 'Read');
		$q->addInsert('sysval_value_id', '2');
		$q->exec();

		$q->clear();
		$q->addTable('sysvals');
		$q->addInsert('sysval_key_id', 1);
		$q->addInsert('sysval_title', 'NoteStatus');
		$q->addInsert('sysval_value', 'Review');
		$q->addInsert('sysval_value_id', '3');
		$q->exec();

		$q->clear();
		$q->addTable('sysvals');
		$q->addInsert('sysval_key_id', 1);
		$q->addInsert('sysval_title', 'NoteStatus');
		$q->addInsert('sysval_value', 'Do');
		$q->addInsert('sysval_value_id', '4');
		$q->exec();

		$q->clear();
		$q->addTable('sysvals');
		$q->addInsert('sysval_key_id', 1);
		$q->addInsert('sysval_title', 'NoteStatus');
		$q->addInsert('sysval_value', 'Important');
		$q->addInsert('sysval_value_id', '5');
		$q->exec();

		$q->clear();
		$q->addTable('sysvals');
		$q->addInsert('sysval_key_id', 1);
		$q->addInsert('sysval_title', 'NoteStatus');
		$q->addInsert('sysval_value', 'Requirement');
		$q->addInsert('sysval_value_id', '6');
		$q->exec();

        $perms = $AppUI->acl();
        return $perms->registerModule('Notebook', 'notebook');
	}
}