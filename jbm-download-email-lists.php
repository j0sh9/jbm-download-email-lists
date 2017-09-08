<?php

/*
Plugin Name: _Email List
Description: Download Email Lists
Version: 1.0
*/

add_action('init','jbm_transfer_download_email_list');

function jbm_transfer_download_email_list() {
	if ( isset( $_POST['download'] ) ) {
		$list = jbm_get_download_email_list();
		if ( !empty($list) ) {
			array_to_csv_download($list);
		}
	}
}

function array_to_csv_download($list) {
	$delimiter=";";
	$filename = get_bloginfo()."-email-".date('Ymd').".csv";
	$upload_dir = wp_upload_dir();
	$upload_dir = $upload_dir['basedir'].'/email-lists/';
    if (! is_dir($upload_dir)) {
       mkdir( $upload_dir, 0755 );
    }
	if ( isset( $_POST['filename'] ) && !empty( $_POST['filename'] ) )
		$filename = get_bloginfo().'-'.$_POST['filename']."-".date('Ymd').".csv";
	$file = $upload_dir.$filename;

	$f = fopen($file, 'w');

	foreach ($list as $line) {
		fputcsv($f, $line, $delimiter);
	}
	fclose($f);

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="'.$filename.'";');

	readfile($file);
	
	header("Refresh:0");
	exit();

	// open the "output" stream
	// see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
} 

function jbm_get_download_email_list() {
	global $wpdb;
	$table = $wpdb->prefix."users";
	$all_users = $wpdb->get_results( "SELECT ID FROM $table" );

	$list = array();
	$list[] = array('Email','First Name','Last Name','Phone','State','Country','Type','User ID');
	foreach ( $all_users as $base ) {
		$user = get_userdata($base->ID);
		$role = reset($user->roles);
		if ( isset( $_POST['types'] ) && !in_array($role, $_POST['types']) ) continue;
		if ( isset( $_POST['states'] ) && !empty( $_POST['states'] ) && strpos(strtoupper($_POST['states']), strtoupper($user->billing_state)) === FALSE ) continue;
		$list[] = array(
			$user->user_email,
			$user->first_name,
			$user->last_name,
			$user->billing_phone,
			$user->billing_state,
			$user->billing_country,
			$role,
			$user->ID
		);
	}
	return $list;
}

function jbm_output_download_lists() {
	$list = jbm_get_download_email_list();	
	
	if ( empty($list) ) return;
	
	$cell = 'th';
	$rows .= '<p><strong>'.(count($list)-1).' Results</strong></p>';
	$rows .= '<table id="userList">';
	foreach ( $list as $line ) {
		$row = "<tr>";
		foreach ( $line as $value ) {
			$row .= "<$cell>$value</$cell>";
		}
		$row .= "</tr>";
		$cell = 'td';
		$rows .= $row;
	}
	$rows .= '</table>';
	return $rows;
}

add_action( 'admin_menu', 'jbm_download_email_lists' );
function jbm_download_email_lists() {
	$jb_page_title = 'Email List Export';
	$jb_menu_title = 'Email List Export';
	$jb_capability = 'manage_options';
	$jb_menu_slug = 'jb-email-list-export';
	$jb_callback = 'jbm_download_email_lists_html';
	$jb_icon_url = 'dashicons-email-alt';
	$jb_menu_position = 130;
	add_menu_page(  $jb_page_title,  $jb_menu_title,  $jb_capability,  $jb_menu_slug,  $jb_callback,  $jb_icon_url,  $jb_menu_position );
}

function jbm_download_email_lists_html() {
	if ( ! current_user_can('manage_options') ) {
		die('You do not have permission to view this page');
	}

?>
<style>
	#userList, #userList th, #userList td {
		border: 1px solid #cdcdcd;
		border-collapse: collapse;
	}
	#userList {
		width: 97%;
		margin: 3vw 1vw;
	}
	#userList th, #userList td {
		padding: 4px 8px;
	}
	.basic_order_search_form label {
		padding: 3px;
	}
</style>

<form action="" method="post">
	<p><button name="view" value="1" class="button">View List</button></p>
	<p><button class="button" name="download" value="1">Download List</button></p>
	<p><strong>Filename:</strong> <input name="filename" type="text" value="<?php echo (isset( $_POST['filename'] )?$_POST['filename']:'');?>" /></p>
	<p><strong>States:</strong> <input name="states" type="text" value="<?php echo (isset( $_POST['states'] )?$_POST['states']:'');?>" /> *csv - AZ,CA,MN</p>
	<p><strong>Customer Type:</strong> Blank will return all results.<br>
<?php
	$all_roles = get_editable_roles();
	foreach($all_roles as $role_slug => $role_array) {
		$checked = in_array($role_slug, $_POST['types'])?'checked':'';
		echo "<label><input type='checkbox' name='types[]' value='$role_slug' $checked /> ".$role_array['name']."</label><br>";
	}
?>
	</p>
	
	
</form>
<?php
	//Get all the existing files and make them available for download at some point
	$upload_dir = wp_upload_dir();
	$file_path = $upload_dir['baseurl'].'/email-lists';
?>
<!--
<a href="<?php echo $file_path.'/test-20170907.csv';?>" download>Download</a>
-->

	

<?php
	if ( isset($_POST['view'] ) ) {
		echo jbm_output_download_lists();
	}
?>

<?php
}
?>
