<?php
/**
Plugin Name: slem
Plugin URI: http://slem.ir/
Description: Convert All Your Post Links To Short Links wth Slem.ir Api
Version: 1.0
Author: <a href="http://ctboard.com/">Mostafa Shiraali</a>
Author URI: http://ctboard.com/
License: A "Slug" license name e.g. GPL2
Text Domain: slem
Domain Path: /languages
 */
slem::init();
class slem
{
	public static function init()
	{
	add_action('admin_init',array(__CLASS__,'registersetting'));
	add_action('admin_menu', array(__CLASS__,'menu'));
	add_action('init',array(__CLASS__,'lang_init'));
	add_action('admin_init',array(__CLASS__,'lang_init'));
	register_activation_hook( __FILE__, array(__CLASS__,'active'));
	register_deactivation_hook( __FILE__, array(__CLASS__,'deactivate'));
	add_filter('the_content',array(__CLASS__,'content_filter') );
	}
 public static function active()
 {
 add_option('slem_email');
 add_option('slem_pass');
 }
 public static function registersetting()
 {
 register_setting('slem_opt','slem_email');
 register_setting('slem_opt','slem_pass');
 }
  public static function deactivate()
 {
 delete_option('slem_email');
 delete_option('slem_pass');
 }
 public static function lang_init()
 {
   load_plugin_textdomain( 'slem', false,dirname( plugin_basename( __FILE__ ) ) .'/languages/' );
 }
 public static function menu() {
	add_options_page(__("Slem URL Shortener","slem"),__("Slem URL Shortener","slem"), 10, __FILE__,array(__CLASS__,"display_options"));
}
public static function display_options()
{
?>
	<div class="wrap">
	<h2><?php _e("Slem Options","slem")?></h2>        
	<form method="post" action="options.php">
	<?php settings_fields('slem_opt'); ?>
	<table class="form-table">
		<tr valign="top">
            <th scope="row"><label><?php _e("Email Of Account","slem");?></label></th>
			<td><input type="text" name="slem_email" value="<?php echo get_option('slem_email'); ?>" /> </td>
        </tr>		
		<tr valign="top">
            <th scope="row"><label><?php _e("Password Of Account","slem");?></label></th>
			<td><input type="text" name="slem_pass" value="<?php echo get_option('slem_pass'); ?>" /> </td>
        </tr>	
	</table>
<?php submit_button(); ?>
		</form><br/><br/>		
	</div>
<?php
}
public static function content_filter($content)
{	
     $content = preg_replace_callback('#href="(.*?)"#s',function ($matches)
	{
	if (strpos($matches[1],'slem.ir') !== false)
	{
	return 'href="'.$matches[1].'"';
	}
	else
	{
	$postdata = http_build_query(array('url' => urlencode($matches[1]), 'email' => ''.get_option('slem_email').'', 'password' => ''.get_option('slem_pass').''));
	$opts = array('http' =>
    array(
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => $postdata
    )
	);
	$context  = stream_context_create($opts);
	$resp = file_get_contents('http://slem.ir/link/api', false, $context);
	$response=json_decode($resp);
	if ($response->status==1)
	{
	return 'href="'.$response->result.'"';
	}
	else
	{
	return 'href="'.$matches[1].'"';	
	}
	}
	
	}
	 
	 , $content);
     return $content;
}


}
?>