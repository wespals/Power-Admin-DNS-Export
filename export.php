<?php
require_once("inc/toolkit.inc.php");

$zone_id = "-1";
if (isset($_GET['id']) && v_num($_GET['id'])) {
	$zone_id = $_GET['id'];
}

if ($zone_id == "-1") {
	error(ERR_INV_INPUT);
	exit;
}


/*
Check permissions
*/
if (verify_permission('zone_content_view_others')) { $perm_view = "all" ; }
elseif (verify_permission('zone_content_view_own')) { $perm_view = "own" ; }
else { $perm_view = "none" ; }

if (verify_permission('zone_content_edit_others')) { $perm_content_edit = "all" ; }
elseif (verify_permission('zone_content_edit_own')) { $perm_content_edit = "own" ; }
else { $perm_content_edit = "none" ; }

if (verify_permission('zone_meta_edit_others')) { $perm_meta_edit = "all" ; }
elseif (verify_permission('zone_meta_edit_own')) { $perm_meta_edit = "own" ; }
else { $perm_meta_edit = "none" ; }

verify_permission('zone_master_add') ? $perm_zone_master_add = "1" : $perm_zone_master_add = "0" ;
verify_permission('zone_slave_add') ? $perm_zone_slave_add = "1" : $perm_zone_slave_add = "0" ;

$user_is_zone_owner = verify_user_is_owner_zoneid($zone_id);
if ( $perm_meta_edit == "all" || ( $perm_meta_edit == "own" && $user_is_zone_owner == "1") ) {
	$meta_edit = "1";
}
else {
        $meta_edit = "0";
}

(verify_permission('user_view_others')) ? $perm_view_others = "1" : $perm_view_others = "0" ;


if ( $perm_view == "none" || $perm_view == "own" && $user_is_zone_owner == "0" ) {
	error(ERR_PERM_VIEW_ZONE);
} else {

	if (zone_id_exists($zone_id) == "0") {
		error(ERR_ZONE_NOT_EXIST);
	} else  {
		$domain_type=get_domain_type($zone_id);
		$record_count=count_zone_records($zone_id);
                $zone_templates = get_list_zone_templ($_SESSION['userid']);
                $zone_template_id = get_zone_template($zone_id);

		$records = get_records_from_domain_id($zone_id,ROWSTART,$record_count,RECORD_SORT_BY);

		$filename = get_zone_name_from_id($zone_id) . "_zone_record.csv";
	    header("Pragma: public");
	    header("Expires: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Content-Type: application/force-download");
	    header("Content-Type: application/octet-stream");
	    header("Content-Type: application/download");
	    header("Content-Disposition: attachment;filename={$filename}");
	    header("Content-Transfer-Encoding: binary");

	   if (count($records) == 0) {
	     return null;
	   }

	   ob_start();
	   $df = fopen("php://output", 'w');

	   // Format some dns data fields
	   for($i=0; $i<count($records); $i++){
	      unset($records[$i]['id']);
	      unset($records[$i]['domain_id']);
	      unset($records[$i]['change_date']);
	      $records[$i]['priority'] = $records[$i]['prio'];
	      unset($records[$i]['prio']);
	   }

	   fputcsv($df, array_keys(reset($records)));
	   foreach ($records as $row) {
	      fputcsv($df, $row);
	   }
	   fclose($df);
	   echo ob_get_clean();
	}
}

?>
