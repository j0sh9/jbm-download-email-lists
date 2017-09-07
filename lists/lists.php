<?php

/*
Plugin Name: _Email List
Description: Download Email Lists
Version: 1.0
*/

if ( ! current_user_can('manage_options') ) {
	die('You do not have permission to view this page');
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

<form action="" method="post"><button name="list" value="1">Generate List</button></form>
<table id="userList">
	<tr>
		<th>Email</th>
		<th>First Name</th>
		<th>Last Name</th>
		<th>Phone</th>
		<th>State</th>
		<th>Country</th>
		<th>Type</th>
		<th>User ID</th>
	</tr>
<?php
	if ( isset($_POST['list'] ) ) {
		global $wpdb;
		$table = $wpdb->prefix."users";
		$all_users = $wpdb->get_results( "SELECT ID FROM $table" );
		foreach ( $all_users as $base ) {
			$user = get_userdata($base->ID);
			$row = "<tr><td>";
			$row .= $user->user_email;
			$row .= "<td>$user->first_name</td>";
			$row .= "<td>$user->last_name</td>";
			$row .= "<td>$user->billing_phone</td>";
			$row .= "<td>$user->billing_state</td>";
			$row .= "<td>$user->billing_country</td>";
			$row .= "<td>".reset($user->roles)."</td>";
			$row .= "<td>$user->ID</td>";
			$row .= "</td></tr>";
			echo $row;
		}
	}
?>
</table>
<?php
}
?>