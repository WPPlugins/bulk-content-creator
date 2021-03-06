<?php
/**
 *
 * Plugin Name: Bulk Content Creator
 * Plugin URI: https://github.com/luk3thomas/wpBulkContentCreator 
 * Description: Quickly create multiple posts, pages, or other custom post types from a single interface. Helpful for WordPress developers during the initial site setup.
 * Version: 1.2.1
 * Author: @luk3thomas
 * Author URI: https://twitter.com/luk3thomas
 * 
 * Inspired by DaganLev and the Bulk Page Creator plugin 
 * 
 * Copyright 2012 @luk3thomas  (email : luke@vewebsites.com)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * -------------------------------------------- */


class VE_Content_Creator {

  var $slug = 've_bulk_content_creator';

  function __construct() {

    add_action('admin_menu', array($this, 'create_page'));

  }

  function create_page() {

    add_management_page('Bulk Content Creator' , 'Bulk Content Creator' , 'manage_options' , $this->slug , array($this, 'show_page'));

  }

  function pt_select() {

    $types = get_post_types(array('public' => true));
    $out = '';

    foreach( $types as $k => $v ) {

      if($v != 'attachment')
        $out .= '<option value="' . $k . '">' . $v . '</option>';

    }

    return $out;

  }
  
  function status_select() {

    $types = array(
        "publish" => "Published" ,
        "pending" => "Pending Review" ,
        "draft" => "Draft" ,
    );
    $out = '';

    foreach( $types as $k => $v ) {

        $out .= '<option value="' . $k . '">' . $v . '</option>';

    }

    return $out;

  }
  
  function show_page() {

    if( isset($_POST['ve_set']) && $_POST['ve_set']=='set' ){
      echo "<div id='message' class='updated'>";
      foreach( $_POST['ve_post'] as $new ) {
        if(! empty($new['name'])) {
          $menu_order = $new['menu_order'] ? $new['menu_order'] : 0;
          $post_parent = $new['post_parent'] ? $new['post_parent'] : 0;
            
          $params = array( 
            'post_type' => $new['type'],
            'post_title' => $new['name'],
            'post_parent' => $post_parent,
            'menu_order' => $menu_order,
            'post_status' => $new['post_status'],
            'post_content' => $new['content'],
          );
          
          global $wpdb;
          
          $new_id = wp_insert_post($params);

          if($new_id && ! empty($new['thumbnail'])) {
            update_post_meta($new_id, '_thumbnail_id', $new['thumbnail']);
            $id = wp_update_post(array('ID' => $new['thumbnail'], 'post_parent' => $new_id), true);
          }

          if($new_id) {
              printf(' <p> Added new %s: <a href="%s">%s</a> </p> '
              , $new["type"]
              , get_edit_post_link($new_id)
              , $new["name"]
              );
          }

        }

      }
      echo "</div>";
      //form submitted
        }
    ?>
    <style>
      .ve_table {
        width:100%;
      }
      .ve_table td {
        vertical-align:top;
      }
      #message {
        margin-left:0;
      }
    </style>
    <h1>Create Content</h2>
    <h3>Input the items below</h3>
    <form action="<?php bloginfo('wpurl') ?>/wp-admin/tools.php?page=<?php echo $this->slug ?>" method="post">
      <input type="hidden" name="ve_set" value="set" />
      <table class="ve_table">
        <thead>
          <tr>
            <td>Name</td>
            <td>Post Type</td>
            <td>Content</td>
            <td>Thumbnail ID</td>
            <td>Post Parent ID</td>
            <td>Menu Order</td>
            <td>Post Status</td>
        </thead>
        <tbody>
          <tr>
            <td><input class="widefat" type="text" name="ve_post[post_1][name]" value=""  /></td>
            <td><select class="widefat pt-select" name="ve_post[post_1][type]"><?php echo $this->pt_select() ?></select></td>
            <td><textarea class="widefat" name="ve_post[post_1][content]"></textarea></td>
            <td><input type="text" name="ve_post[post_1][thumbnail]" value="" size="2" /></td>
            <td><input type="text" name="ve_post[post_1][post_parent]" value="" size="2" /></td>
            <td><input type="text" name="ve_post[post_1][menu_order]" value="" size="2" /></td>
            <td><select class="widefat" name="ve_post[post_1][post_status]"><?php echo $this->status_select() ?></select></td>
            <td><span class="button secondary ve_rm">Remove</span></td>
          </tr>
        </tbody>
      </table>      

      <p> <span class="button secondary ve_add">+ Add Row</span> </p>

      Change post type of all posts to : <select id="set_all"><?php echo $this->pt_select() ?></select>

      <p> <input type="submit" value="Submit" class="button-primary" /> </p>

    </form>
    <script type="text/javascript">
      jQuery(document).ready(function($){
        (function(){

            function ve_renumber() {
              var start = 1;
              $('.ve_table tbody tr').each(function(i, el){

                var repl = 'post_' + start ;
                
                $(this).find('input, select, textarea').each(function(i, el){

                  var name = $(this).attr('name'),
                      n    = name.replace(/post_[0-9]+/, repl) ;

                  $(this).attr('name', n);

                });

                start += 1;
              });
            }

            function set_all() {
                var $sel = $(this),
                    val = $sel.val() ;

                $('.pt-select').val( val );
            }

            $('.ve_add').live('click', function() {
              $('.ve_table tbody').append('<tr>' + $('.ve_table tbody tr').slice(-1).html() + '</tr>');
              ve_renumber();
            });

            $('.ve_rm').live('click', function(){
                if($('.ve_rm').length > 1) {
                    $(this).parent().parent().remove();
                }
            });

            $('#set_all').change( set_all );

        })(this.jQuery);

      });
    </script>
    <?php
  }

}

new VE_Content_Creator();
