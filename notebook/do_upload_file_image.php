<?php
//echo $_POST["upload"].name;
//phpinfo();
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$note_id = (int) w2PgetParam($_GET, 'note_id', 99999999);   //intentional, to be able later to delete
                                         // all files with file_note_id !=0 when deinstalling notebook


// check permissions for this record
//already checked in addedit, we come from there

$obj = new CFile();
$nb_prefs = &$AppUI->loadPrefs(0,true);

$upload = null;
if (isset($_FILES['upload'])   && isset($_FILES['upload']['name']) && $_FILES['upload']['name']!='') {

            $file_obj = new CFile();
            $file_info=array();
            $acl =& $AppUI->acl();
            if ( ! $acl->checkModule('files', 'add')) {
                $AppUI->setMsg($AppUI->_( "noDeletePermission" ));
                $AppUI->redirect(ACCESS_DENIED);
            }
            $file_obj->_message = 'added';
            $file_info['file_version'] = 1.0;
            $file_info['file_category'] = 0;
            $file_info['file_parent'] = 0;
            $file_info['file_folder'] =   $nb_prefs['notebook_file_folder_id']  ;
            $file_info['file_project'] =99999999;
 //           if (!$new_item) {
            $file_info['file_description'] = $AppUI->_('This file is associated with notebook item') . ' ' .$note_id ;
            $file_info['file_note_id'] = $note_id;
 //           }
            $file_info['file_owner']=$AppUI->user_id;

            if (!$file_obj->bind( $file_info )) {
                $AppUI->setMsg( $file_obj->getError(), UI_MSG_ERROR );
                $AppUI->redirect();
            }








	$upload = $_FILES['upload'];
 
 
 
	if ($upload['size'] < 1) {
		if (!$file_id) {
			$AppUI->setMsg('Upload file size is zero. Process aborted.', UI_MSG_ERROR);
            $AppUI->holdObject($obj);
			$AppUI->redirect('m=files&a=addedit');
		}
	} else {

		// store file with a unique name
		$file_obj->file_name = $upload['name'];
		$file_obj->file_type = $upload['type'];
		$file_obj->file_size = $upload['size'];
        //now in hook  $file_obj->file_date = str_replace("'", '', $db->DBTimeStamp(time()));
		$file_obj->file_project = 99999999;//corresponds to notebook
//		$file_obj->_filepath = "tt";
        //now in hook??  $file_obj->file_real_filename = uniqid( rand() );

		$res = $file_obj->moveTemp($upload);
//		if (!$res) {			$AppUI->redirect($redirect);		}
            if (($msg = $file_obj->store()) !== true) {
                $AppUI->setMsg( $msg, UI_MSG_ERROR );
            }
 
	}
}


//         $file->_filepath = W2P_BASE_DIR . '/files/' . (int) $file->file_project . '/' . $file->file_real_filename;
 
$fname=W2P_BASE_URL. '/index.php?m=files&a=view&file_id=' . (int) $file_obj->file_id;// . '/' . $file_obj->file_real_filename;

  echo '{
    "uploaded": 1,
    "fileName": "'. "$file_obj->file_name". ' ",
    "url": "'. "$fname"   .' "

}';  
/*
echo "{
    'uploaded': 1,
    'fileName': '$file_obj->file_real_filename',
    'url':  '$fname'

}";
*/
?>