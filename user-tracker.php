<?php

/*
Plugin Name: User Tracker
Description: Track page visited by users and view in table
Plugin URI:  http://www.decristofano.it/
Version:     0.1.1
Author:      lucdecri

Author URI:  http://www.decristofano.it/
*/


/*
 @todo inserire un'opzione "visualizza amministrazione" per visualizzare/nascondere in tabella le righe che contengono nell'url 'wp-admin'
 @todo	creare uno shortcode per visualizzare gli utenti online, ovvero con attivitˆ da meno di X tempo(impostato nello short code) e nella Y pagina (impostata nello shortcode);
 @todo 	creare uno shortcode per visualizzare il numero o il nome degli utenti che hanno avuto accesso ad una pagina X (impostata nello shortcode) negli ultimi Y giorni (impostato da short code)
 @todo	invece dell'url della pagina dovrebbe visualizzare il titolo o lo slug
*/

require_once "admin_helper.php";

function usertracker_track() {
global $current_user, $wpdb;

	$user = $current_user->id;

	$page = $_SERVER["REQUEST_URI"];
	$table = $wpdb->prefix.'userlogs';

	$now = current_time('mysql');

	// verifica se l'ultimo giorno si  giˆ connesso 

	$query = "SELECT id, `time` FROM $table WHERE user=$user AND url='$page' ORDER BY `time` DESC LIMIT 1";
	//$last_connect = $wpdb->get_var($query,'ARRAY_A');

	$row = $wpdb->get_row($query,'ARRAY_A');

	$last_connect=$row['time'];
	$id = $row['id'];

	if ($row==null) {
		$time_enlapsed=strtotime($now);
		$id = null;
	} else {
		$time_enlapsed = strtotime($now)-strtotime($row['time']);
		$id = $row['id'];
	}

	if ($time_enlapsed>24*60*60)   $rows_affected = $wpdb->insert( 
								$table, 
								array(  'time' => $now, 
									'user' => $user, 
									'url' =>  $page ) 
									);	
	else				$rows_affected = $wpdb->update( 
								$table, 
								array(  'time' => $now, 
									'user' => $user, 
									'url' =>  $page ),
									array( 'id' => $id )
									);
}

function usertracker_activate() {
// effettua la creazione delle tabelle, se non  giˆ presente
global $wpdb;

	if (get_option('usertracker_set',false)==false) {
		add_option('usertracker_set','ok','',true);
		$sql = "CREATE TABLE " . $wpdb->prefix."userlogs (
	  		id mediumint(9) NOT NULL AUTO_INCREMENT,
	  		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  		user mediumint(9) DEFAULT 0 NOT NULL,
	  		url VARCHAR(55) DEFAULT ' ' NOT NULL,
	  		UNIQUE KEY id (id)
			);";
		if ($wp_db_version >= 5540)
			$page = 'wp-admin/includes/upgrade.php';
		else
			$page = 'wp-admin/upgrade-functions.php';
		require_once(ABSPATH . $page);
		dbDelta($sql);
	}
}


function usertracker_admin() {
global $wpdb;

		admin_panel('usertracker_options',
                'users.php?page=user-tracker.php',
                'Track User',
                'Informazioni sulle pagine visitate dagli utenti loggati',
                'ver. 00',
                'usertracker');

        if ( (isset($_POST['page'])) && ($_POST['page']=='usertracker_options') ) {
			// visualizza il dettaglio
			$keys = array_keys($_POST);
			foreach ($keys as $key) {
				if (substr($key,0,5)=='user-') $selected_user=substr($key,5);
			}
			admin_field('','page',get_userdata($selected_user)->user_login.' detail','','usertracker','Visited pages');
			admin_table(array('Pagina','Ultimo Accesso','Numero Visite'));
			$query = "Select url, MAX(time) as last, count(url) as visit FROM ".$wpdb->prefix."userlogs WHERE user='".$selected_user."' GROUP BY url ORDER BY last DESC";
			$rows=$wpdb->get_results($query, 'ARRAY_A');
			
			foreach($rows as $row) {
				$name = $row['url'];
				admin_table_row(array($name,$row['last'],$row['visit']));
			}
			admin_table_close(array('Pagina','Ultimo Accesso','Numero Visite'));
	}

	admin_field('','page','Summary','','usertracker','All users Access');
	admin_table(array('Nome utente','Ultimo accesso','Pagine visitate',''));
	$query = "Select user, MAX(time) as last, count(url) as visit FROM ".$wpdb->prefix."userlogs GROUP BY user";
	$rows=$wpdb->get_results($query, 'ARRAY_A');
	foreach($rows as $row) {
		$name = get_userdata($row['user'])->user_login;
		if ($name=='') $name = '(non loggato)';
		$link = "<input type='submit' id='user-{$row['user']}' name='user-{$row['user']}' value='Visualizza' />";
		admin_table_row(array($name,$row['last'],$row['visit'],$link));
	}
	admin_table_close(array('Nome utente','Ultimo accesso','Pagine visitate',''));
        admin_panel_close(); 

}

function usertracker_menu() {
	add_submenu_page( 'users.php', 'User Tracker', 'Tracker', 'edit_plugins', 'user-tracker.php', 'usertracker_admin' );
}

//@WIP attivazione degli hook

add_action('init', 'usertracker_track'); // quando inizializzo la pagina

add_action('activate_plugin', 'usertracker_activate'); // quando attivo il plugin

add_action('admin_menu', 'usertracker_menu'); // quando genero il men di amministrazione

register_activation_hook(__FILE__, 'usetracker_activate');  // non ho ben capito, ma serve per i permalink

?>