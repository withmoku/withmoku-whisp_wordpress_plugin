<?php
/**
 * Plugin Name:       WHISP
 * Plugin URI:        https://blog.whisp.io/whisp-plugin/
 * Description:       Easy Way to Capture New Leads.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Todd Westra
 * Author URI:        https://team.whisp.io/todd-westra
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       whisp
 * Domain Path:       /whisp
*/

if(!defined('ABSPATH')) {
  die('Do not open this file directly.');
}

class WhispPlugin {

  function whisp_activate() {
    global $wpdb;
    global $table_prefix;
    $table = $table_prefix.'whisp';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      product_identifier varchar(55) DEFAULT '' NOT NULL,
      type varchar(55) DEFAULT '' NOT NULL,
      source varchar(55) DEFAULT '' NOT NULL,
      campaign varchar(55) DEFAULT '' NOT NULL,
      other varchar(55) DEFAULT '' NOT NULL,
      code varchar(500) DEFAULT '' NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
  }

  function whisp_deactivate() {
    global $wpdb;
    global $table_prefix;
    $table = $table_prefix.'whisp';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "DROP TABLE IF EXISTS wp_whisp";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
   
  }


  function whisp_remove_database() {
       global $wpdb;
       $table_name = $wpdb->prefix . 'whisp';
       $sql = "DROP TABLE IF EXISTS $table_name";
       $wpdb->query($sql);
  }


  function whisp_add_pages() {
       add_menu_page(
          __( 'WHISP', 'textdomain' ),
          __( 'WHISP','textdomain' ),
          'manage_options',
          'whisp_form',
            array( $this, 'whisp_page_callback'),
          'dashicons-wordpress-alt'
      );
  }


  function load_scripts() {
      wp_register_style('custom.css', plugins_url('whisp/inc/custom.css'));
      wp_enqueue_style('custom.css'); 
      wp_register_style('bootstrap.css', plugins_url('whisp/inc/bootstrap.css'));
      wp_enqueue_style('bootstrap.css'); 
      wp_register_script ('bootstrap.js', plugins_url('whisp/inc/bootstrap.js'));
      wp_enqueue_script ('bootstrap.js' );
      wp_register_script ('custom.js', plugins_url('whisp/inc/custom.js'));
      wp_enqueue_script ('custom.js' );
  }


   function whisp_page_callback() {
    global $session;
    echo '<h1>WHISP Custom Code </h1><button style="border: 1px solid #2271b1; font-weight: bold; color: #2271b1" type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#myModal">Add New</button><br /><br />';
    echo '  <div class="modal fade" id="myModal" role="dialog">
      <div class="modal-dialog">
      
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button><br />
            <p class="modal-title">Add Custom Code</p>
          </div>
          <div class="modal-body">
  <form id="myForm" name="myform" action="' . esc_attr( admin_url('admin-post.php') ).'" method="POST" onsubmit="return validateForm()">
    <input type="hidden" name="action" value="save_my_custom_form" />
      <table>
        <tr>
          <td style="padding-bottom: 20px;">Product Identifier</td>
          <td style="padding-left: 10px;"><input name="whisp_pid" type="text" required/><br /><br /></td>
        </tr>
        <tr>
          <td style="padding-bottom: 20px;">WHISP Type</td>
          <td style="padding-left: 10px;">
           <select id="name" name="whisp_type" size="1" required>
              <option selected="selected" value="">-- Select --</option>
              <option value="text">Text</option>
              <option value="wheel">Wheel</option>
          </select><br /><br />
          </td>
        </tr>
        <tr>
          <td style="padding-bottom: 20px;">SOURCE</td>
          <td style="padding-left: 10px;"><input type="text" name="whisp_source" required/><br /><br /></td>
        </tr>
        <tr>
          <td style="padding-bottom: 20px;">CAMPAIGN</td>
          <td style="padding-left: 10px;"><input type="text" name="whisp_campaign" required/><br /><br /></td>
        </tr>
        <tr>
          <td style="padding-bottom: 20px;">OTHER</td>
          <td style="padding-left: 10px;"><input type="text" name="whisp_other" required/><br /><br /></td>
        </tr>
        <tr>
          <td></td>
          <td style="padding-left: 10px;"><input type="submit" value="ADD" /><br /><br /></td>
        </tr>
      </table>
  </form><br /> <br />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>';

    global $wpdb;
    echo "<table class='table'>";
    echo "<th>Product Identifier</th>";
    echo "<th>Whisp Type</th>";
    echo "<th>Source</th>";
    echo "<th>Campaign</th>";
    echo "<th>Other</th>";
    echo "<th>Copy and paste this code</th>";
    $result = $wpdb->get_results ( "SELECT * FROM wp_whisp ORDER BY ID DESC");
    foreach ( $result as $print ) {
      echo "<tr>";
      echo "<td>".esc_attr($print->product_identifier)."</td>";
      echo "<td>".esc_attr($print->type)."</td>";
      echo "<td>".esc_attr($print->source)."</td>";
      echo "<td>".esc_attr($print->campaign)."</td>";
      echo "<td>".esc_attr($print->other)."</td>";
      echo "<td>".esc_attr($print->code)."</td>";
      echo "<td><button 
      onclick='whisp_delete(".esc_attr($print->id).")' class='btn btn-danger btn-sm'>
      Delete
    </button></td>";
      echo "</tr>";
     }
     echo "</table>";
    }


   function whisp_save_form() {
      global $wpdb;
      $pid = sanitize_text_field($_POST['whisp_pid']);
      $whisptype = sanitize_text_field($_POST['whisp_type']);
      $source = sanitize_text_field($_POST['whisp_source']);
      $campaign = sanitize_text_field($_POST['whisp_campaign']);
      $other = sanitize_text_field($_POST['whisp_other']);
      $wcode01 = "[WHISP product_identifier=";
      $wcode02 = "]";
      $whisp_shortcode = $wcode01.'"'.$pid.'" button_type="'.$whisptype.'"'.$wcode02 ;

      if((!empty($pid)) && (!empty($whisptype)) && (!empty($source)) && (!empty($campaign)) && (!empty($other))){
        $wpdb->insert( 'wp_whisp', array( 'product_identifier' => $pid, 'type' => $whisptype, 'source' => $source, 'campaign' => $campaign, 'other' => $other,'code' => $whisp_shortcode), array( '%s', '%s', '%s', '%s', '%s', '%s' ) );
          wp_redirect( site_url('/wp-admin/admin.php?page=whisp_form') ); 
          die;
      } else {
          wp_redirect( site_url('/wp-admin/admin.php?page=whisp_form') );
          die;
      }
  }


  function whisp_shortcode_function($atts) {
    $a = shortcode_atts(array(
      'product_identifier' => '',
      'button_type' => ''
    ), $atts);
   $product_id = $a['button_type'];
    switch( $product_id ){
      case 'wheel': 
          $output =  '<div id="button_wtf"><img width="150px" class="taptext-wheel btn-tap-text" src="'.esc_url(plugin_dir_url( __FILE__ ).'assets/spinthewheel.png').'"  /></div>';
          echo wp_get_script_tag( array(
              'type' => 'text/javascript',
              'id' => 'taptext-lib',
              'data-chatbtn' => 'true',
              'data-productidentifer' => esc_attr($a['product_identifier']),
              'src' => esc_url('https://hub.taptext.com/scripts/taptext_lib.js')
          ) );
          break;

      case 'text': 
          $output =  '<div id="button_wtf">
      <a href="javascript:" class="pink-btn" data-itemurl="" data-utm_source="" data-utm_medium="" data-utm_campaign="" data-utm_term="" data-utm_content="" data-productidentifer="' . esc_attr($a['product_identifier']) . '" onclick="onTapTextClick(this)" style="float: left; cursor:pointer"><img src="'.esc_url(plugin_dir_url( __FILE__ ).'assets/taptext.png').'" width="150px" style="float: left;" /></a></div>';
          echo wp_get_script_tag( array(
              'type' => 'text/javascript',
              'id' => 'taptext-lib',
              'data-chatbtn' => 'true',
              'data-productidentifer' => esc_attr($a['product_identifier']),
              'src' => esc_url('https://hub.taptext.com/scripts/taptext_lib.js')
          ) );
          break;

      default:
          $output = '<div>&nbsp;</div>';
          break;
    }
     return $output;
  ?>

  <?php
  }
}


if(isset($_GET['whisp_delete'])){
    $id = $_GET['whisp_delete'];
    $table = 'wp_whisp';
    $wpdb->delete( $table, array( 'id' => $id ) );
}


if( class_exists('WhispPlugin')) {
  $whispplugin = new WhispPlugin();
}


register_activation_hook( __FILE__, array( $whispplugin, 'whisp_activate'));
register_deactivation_hook( __FILE__, array( $whispplugin, 'whisp_remove_database'));
add_action('admin_menu', array( $whispplugin, 'whisp_add_pages'));
add_action('wp_enqueue_scripts', array( $whispplugin, 'load_scripts'));    
add_action('admin_enqueue_scripts', array( $whispplugin, 'load_scripts'));
add_action( 'admin_post_nopriv_save_my_custom_form', array( $whispplugin, 'whisp_save_form'));
add_action( 'admin_post_save_my_custom_form', array( $whispplugin, 'whisp_save_form'));
add_shortcode('WHISP', array( $whispplugin, 'whisp_shortcode_function'));