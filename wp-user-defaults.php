<?php
/*
Plugin Name: WP User Defaults
Plugin URI: http://jerodsanto.net/src/wp-user-defaults
Description: Allows Administrator to set default values for new user profiles
Author: Jerod Santo
Author URI: http://jerodsanto.net
Version: 0.1
*/

// disallow direct access to the plugin file
if (basename($_SERVER['PHP_SELF']) == basename (__FILE__)) {
die("Sorry, but you can't access this page directly.");
}

// called when new user is registered
function apply_user_defaults($user_id) {
  
  // fetch current user meta information
  $first   = get_usermeta($user_id,'first_name');
  $last    = get_usermeta($user_id, 'last_name');
  $nick    = get_usermeta($user_id, 'nickname');
  $display = "";
  
  // set the default display name
  $display_type = get_option('user_defaults_display_name');
  switch ($display_type) {
    case "first_last":
      $display = $first . " " . $last;
      break;
    case "last_first":
      $display = $last . " " . $first;
      break;
    case "first":
      $display = $first;
      break;
    case "last":
      $display = $last;
      break;
    case "nick":
      $display = $nick;
      break;
  }
  wp_update_user(array("ID" => $user_id, "display_name" => $display));
  
  // set the administrative interface color
  $admin_color = get_option('user_defaults_admin_color');
  wp_update_user(array("ID" => $user_id, "admin_color" => $admin_color ));
  
  // set the rich editor default (true = disabled)
  if ((bool) get_option('user_defaults_rich_editing')) {
    update_usermeta($user_id, 'rich_editing','false');
  } else {
    update_usermeta($user_id, 'rich_editing','true');
  }
  
  // set the keyboard shortcuts 
  if ((bool) get_option('user_defaults_comments_shortcuts')) {
    update_usermeta($user_id, 'comment_shortcuts','true');
  } else {
   update_usermeta($user_id, 'comment_shortcuts','false'); 
  }
}

function add_pages() {
  add_options_page('Default User Settings','Default User Settings',10,__FILE__,'the_options_page');
}

// draw the actual options page loaded above
function the_options_page() {
  $rich_editing = (bool) get_option('user_defaults_rich_editing');
  $admin_color  = get_option('user_defaults_admin_color');
  $shortcuts    = (bool) get_option('user_defaults_comments_shortcuts');
  $display_name = get_option('user_defaults_display_name');
?>

<div class="wrap">
  <h2>Default User Settings</h2>
  <h3>Applies to newly registered users, not existing users.</h3>
  <form method="post" action="options.php">
  
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="page_options" value="user_defaults_rich_editing,user_defaults_admin_color,user_defaults_comments_shortcuts,user_defaults_display_name" />
    <?php wp_nonce_field('update-options'); ?>
    
    <table class="form-table">
      <tr valign="top">
        <th scope="row"><?php _e('Visual Editor')?></th>
        <td><label for="user_defaults_rich_editing"><input type="checkbox" name="user_defaults_rich_editing" <?php if ($rich_editing) echo'checked="checked""' ?> value="1" />
        <?php _e('Disable the visual editor when writing'); ?></label></td>
      </tr>
      <tr valign="top">
        <th scope="row"><?php _e('Keyboard Shortcuts')?></th>
        <td><label for="user_defaults_comments_shortcuts"><input type="checkbox" name="user_defaults_comments_shortcuts" <?php if ($shortcuts) echo'checked="checked""' ?> value="1" />
        <?php _e('Enable keyboard shortcuts for comment moderation'); ?></label></td>
      </tr>
      <tr valign="top">
        <th scope="row"><?php _e('Admin Color Scheme')?></th>
        <td><label for="user_defaults_admin_color">
          <select name="user_defaults_admin_color">
            <option value="fresh" <?php if ($admin_color == "fresh") echo 'SELECTED'; ?>>gray</option>
            <option value="classic" <?php if ($admin_color == "classic") echo 'SELECTED'; ?>>blue</option>
          </select></label>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row"><?php _e('Display name publicly as')?></th>
        <td><label for="user_defaults_display_name">
          <select name="user_defaults_display_name">
            <option value="first_last" <?php if ($display_name == "first_last") echo 'SELECTED'; ?>>First Last</option>
            <option value="last_first" <?php if ($display_name == "last_first") echo 'SELECTED'; ?>>Last First</option>
            <option value="first" <?php if ($display_name == "first") echo 'SELECTED'; ?>>First</option>
            <option value="last" <?php if ($display_name == "last") echo 'SELECTED'; ?>>Last</option>
            <option value="nick" <?php if ($display_name == "nick") echo 'SELECTED'; ?>>Nickname</option>
          </select></label>
        </td>
      </tr>
    </table>
    
    <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
    </p>
  </form>
</div>
    
  
<?php
}

// hooks into wordpress api
add_option('user_defaults_rich_editing','1');
add_option('user_defaults_admin_color', "fresh");
add_option('user_defaults_comments_shortcuts',false);
add_option('user_defaults_display_name','first_last'); // first_last, last_first, first, last, nick
add_action('admin_menu','add_pages');
add_action('user_register','apply_user_defaults');

?>