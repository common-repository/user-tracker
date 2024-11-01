<?php
/*
 * some function to create admin panel for plugin
 * 
 * rev.02 
 * 
 */
if (!function_exists('admin_panel')) {

add_action('wp_print_styles', 'admin_css');
 

function admin_css() {
	
	
	$url = plugins_url( 'admin_helper.css' , __FILE__ );
	wp_register_style('admin_helper', $url);
	wp_enqueue_style('admin_helper');
}

function admin_menu($parent, $page_title, $menu_title, $function_name, $menu_slug, $position='', $capability='edit_plugins', $icon_url='') {
	// add a panel in wordpres menu
	
	//@FIXME pur passando gli stessi parametri non funziona!!!
	
	switch ($parent) {
		case 'Dashboard': $parent='index.php'; break;
		case 'Posts': $parent='edit.php'; break;
		case 'Media': $parent='upload.php'; break;
		case 'Links': $parent='link-manager.php'; break;
		case 'Pages': $parent='edit.php?post_type=page'; break;
		case 'Comments': $parent='edit-comments.php'; break;
		case 'Appearance': $parent='themes.php'; break;
		case 'Plugins': $parent='plugins.php'; break;
		case 'Users': $parent='users.php'; break;
		case 'Tools': $parent='tools.php'; break;
		case 'Settings': $parent='options-general.php'; break;
		default :	$parent='options-general.php'; break;
	}
	//$menu_slug= strtolower(str_replace(array(' ','@', 'ˆ','', '˜', 'Ž', '', '“', '¡'), array('_','_','a','e','o','e','u','i','_'), $menu_title)).'___x';
	if ($parent=='') 
		add_menu_page   (          $page_title, $menu_title, $capability, $menu_slug, $function_name, $icon_url, $position );
	else
		add_submenu_page( $parent, $page_title, $menu_title, $capability, $menu_slug, $function ); 
}

function admin_panel($name, $action, $title, $description, $info='', $localization='') {
  // create a form for admin panel
    echo '<div class="wrap">
    		<h2>'.$title.'</h2>
    		<h5>'.$info.'</h5>
    		<p>'.$description.'</p>
			<p><form name="'. $name.'" action="'.$action.'" method="post" id="'.$name.'">
			<fieldset>
    			<div class="UserOption">
   					<input type="hidden" name="page" value="'.$name.'" />';
}

function admin_field($id, $type, $text, $default, $localization='', $message='' ) {
  // add a field in form for admin panel
  //   type is the field type :
  //      littlenumber
  //      text
  //      color
  //      page : a page-break in the admin panel
  //      longtext
  //	  checkbox
  //	  hidden
  //	  button
	
	
	@list($text,$text2) = @explode('|',$text,2);
    echo '<div class="field_wrapper">';
    switch($type) {
        case 'color':
        	echo '
          <label for="'.$id.'" class="label">'._e($text,$localization).'</label>  :
          #<input type="text" id="'.$id.'" maxlength="6" name="'.$id.'" value="'.$default.'"
	      size="6" onchange="ChangeColor(\''.$id.'_box\',this.value)" onkeyup="ChangeColor(\''.$id.'_box\',this.value)"/>
	      <span id="'.$id.'_box" style="background:#'.$default.'">&nbsp;&nbsp;&nbsp;</span>
	      <div class="field_caption">'._e($message,$localization).'</div>';
        break;
        case 'page':
    ?>
             	</div>
            	</fieldset>
	            <br />
                <fieldset>
	            <legend><b><?php _e($text,$localization);?></b></legend>
	            <div class="UserOption">
                <p><i><?php _e($message,$localization);?></i></p>
<?php
        break;
        case 'select':
    ?>
          <label for="<?php echo $id; ?>" class="label"><?php echo _e($text,$localization); ?></label>  :
          <select class="field_select" id="<?php echo $id; ?>" name="<?php echo $id; ?>" >
          <?php
          	$options = explode(",",$text2);
          	foreach($options as $opt) {
          			if ($default==$opt) $d = ' selected="selected" ';
          			else				$d = ' ';
          			echo "<option value='$opt' $d >";
          			_e($opt,$localization);
          			echo "</option>";
          	}
          ?>
          </select>
          <div class="field_caption"><?php  _e($message,$localization); ?></div>
<?php
        break;
        case 'littlenumber':
        ?>
          <label class="label" for="<?php echo $id; ?>"><?php echo _e($text,$localization); ?></label>  :
          <input type="text" maxlength="3" name="<?php echo $id; ?>" value="<?php echo $default; ?>" size="3" /> <?php _e($text2,$localization);?><br />
	      <div class="field_caption"><?php  _e($message,$localization); ?></div>
<?php
        break;
        case 'text':
        ?>
          <label class="label" for="<?php echo $id; ?>"><?php echo _e($text,$localization); ?></label>  :
          <input type="text" maxlength="100" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo $default; ?>" size="20" /> <?php _e($text2,$localization);?><br />
	  <div class="field_caption"><?php  _e($message,$localization); ?></div>
<?php
        break;
        case 'hidden':
        ?>
          <input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo $default; ?>" />
<?php
        break;
        case 'button':
        	// @TODO button non funziona
         echo '<input type="button" name="'.$id.'" id="'.$id.'" value="'.$text.'" />';
        break;
        case 'longtext':
			if ($text!='') {        
        ?>
          <label for="<?php echo $id; ?>" class="label"><?php echo _e($text,$localization); ?></label>  :<br />
<?php     	} ?>
          <textarea  name="<?php echo $id; ?>" id="<?php echo $id; ?>" cols="40" rows="5" ><?php echo $default; ?></textarea> <?php _e($text2,$localization);?><br />
	      <div class="field_caption"><?php  _e($message,$localization); ?></div>
<?php
        break;
        
        case 'checkbox':
        ?>
          <label for="<?php echo $id; ?>" class="label"><?php echo _e($text,$localization); ?></label>  :
          <input type="checkbox" name="<?php echo $id; ?>" id="<?php echo $id; ?>" <?php if($default == '1') { echo ' checked="checked" '; } ?>/><br />
	      <div class="field_caption"><?php  _e($message,$localization); ?></div>
<?php
        break;
    }
	echo '</div>';


}

function admin_field_save($name, $val=null) {
// save option named $name, if $val is defined
	$old = get_option($name);
	if ($val!=null) {
				if (get_option($name,'')!='') {
							update_option($name,$val);
				} else {
							add_option($name,$val,'',true);
				}
	}
}

function admin_table($columns_name) {
// create a table with specified columns
	echo '<table class="wp-list-table widefat fixed users" cellspacing="0">
		<thead>
			<tr>';
	foreach($columns_name as $col) echo '	<th>'.$col.'</th>';
	echo '
				</thead>
				<tbody>';

}

function admin_table_row($row) {
// add row to table
	echo '<tr>';
	foreach($row as $td) echo '	<td>'.$td.'</td>';
	echo '</tr>';
}

function admin_table_close($columns_name) {
// add footer to table and close it
	echo '			</tbody>
				<tfoot>';
	foreach($columns_name as $col) echo '	<th>'.$col.'</th>';
	echo '
				</tfoot>
	     <table>';
}

function admin_panel_close() {
  // close a form of admin panel
  echo '
    </fieldset>
    '.submit_button().'	
    </form>
    </p>
    <script>
	function ChangeColor(id,color) {
		jQuery(id).css("background-color","#"+color);
	}
	</script>
</div>
  ';
}


} // chiude if(!function_exist...
