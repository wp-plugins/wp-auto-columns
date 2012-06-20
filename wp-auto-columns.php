<?php

/*
  Plugin Name: WP Auto Columns
  Plugin URI: http://wordpress.org/extend/plugins/wp-auto-columns/
  Description: Wrap block of text with shortcode. It will be split into columns. Automagically.
  Author: Spectraweb s.r.o.
  Author URI: http://www.spectraweb.cz
  Version: 1.0.4
 */

load_plugin_textdomain('wp-auto-columns', false, dirname(plugin_basename(__FILE__)) . '/languages');

require_once('include/HTMLSplitter.php');

/**
 *
 * @todo Size modifiers settings
 *
 */
class WPAutoColumns
{

	/**
	 * plugin activation hook
	 */
	public static function on_activation()
	{
		// check plugin requirements
		if (!class_exists('tidy'))
		{
			WPAutoColumns::trigger_error(__('This plugin requires Tidy (<a href="http://www.php.net/manual/en/book.tidy.php" target="_blank">more info</a>)', 'wp-auto-columns'), E_USER_ERROR);
		}

		if (!class_exists('DOMDocument'))
		{
			WPAutoColumns::trigger_error(__('This plugin requires DOM API (<a href="http://www.php.net/manual/en/book.dom.php" target="_blank">more info</a>)', 'wp-auto-columns'), E_USER_ERROR);
		}
	}

	/**
	 * plugin deactivation hook
	 */
	public static function on_deactivation()
	{

	}

	/**
	 * plugin initialization
	 */
	public static function on_init()
	{
		if (is_admin())
		{
			add_action('admin_init', array('WPAutoColumns', 'on_admin_init'));
			add_action('admin_menu', array('WPAutoColumns', 'on_admin_menu'));

			if (current_user_can('edit_posts') || current_user_can('edit_pages'))
			{
				// Add only in Rich Editor mode
				if (get_user_option('rich_editing') == 'true')
				{
					add_filter("mce_external_plugins", array('WPAutoColumns', 'tinymce_plugin'));
					add_filter('mce_buttons', array('WPAutoColumns', 'buttons'));
				}
			}
		}
		else
		{
			wp_enqueue_style('auto_columns', plugin_dir_url(__FILE__) . 'css/auto-columns.css', array(), '1.0');

			add_shortcode('auto_columns', array('WPAutoColumns', 'shortcode'));
		}
	}

	/**
	 *
	 */
	public static function on_admin_init()
	{
		//register_setting('wp_fetch_page_settings', 'wp_fetch_tm_currency');
	}

	/**
	 *
	 */
	public static function on_admin_menu()
	{
		//add_options_page(__('Auto Columns Options', 'theme'), __('Auto Columns', 'theme'), 'manage_options', basename(__FILE__), array('WPAutoColumns', 'on_settings'));
	}

	/**
	 *
	 * @param type $atts
	 * @param type $content
	 */
	public static function shortcode($atts, $content = '')
	{
		extract(shortcode_atts(array('columns' => 2), $atts));

		$splitter = new HTMLSplitter();

		$col_array = $splitter->split($content, $columns);

		if (!is_array($col_array))
		{
			// could not split
			return $content;
		}

		// construct container
		$class = array('auto-columns-container', 'columns-' . $columns);
		$ret = '<div class="' . implode(' ', $class) . '">';

		for ($i = 0; $i < count($col_array); $i++)
		{
			// construct column class
			$class = array('auto-columns-column', 'column-' . ($i + 1));
			if ($i == 0)
			{
				$class[] = 'first-column';
			}
			if ($i == count($col_array) - 1)
			{
				$class[] = 'last-column';
			}
			// add column
			$ret .= '<div class="' . implode(' ', $class) . '">';
			foreach ($col_array[$i] as $tag)
			{
				$ret .= $tag;
			}
			$ret .= '</div>';
		}

		$ret .= '<div class="auto-columns-clear"></div>';
		$ret .= '</div>';

		return $ret;
	}

	/**
	 *
	 */
	public static function on_settings()
	{
		echo 'settings';
	}

	/**
	 *
	 * @param type $message
	 * @param type $errno
	 */
	public static function trigger_error($message, $errno)
	{
		if (isset($_GET['action']) && $_GET['action'] == 'error_scrape')
		{
			echo '<strong>' . $message . '</strong>';
			exit;
		}
		else
		{
			trigger_error($message, $errno);
		}
	}

	/**
	 * Load the TinyMCE plugin : editor_plugin.js (wp2.5)
	 *
	 * @param type $plugin_array
	 */
	public static function tinymce_plugin($plugin_array)
	{
		$plugin_array['autocolumns'] = plugins_url('tinymce/plugins/editor_plugin.js', __FILE__);
		return $plugin_array;
	}

	/**
	 *
	 * @param type $buttons
	 * @return type
	 */
	public static function buttons($buttons)
	{
		array_push($buttons, 'separator', 'auto-columns');
		return $buttons;
	}

	/**
	 *
	 */
	public static function footer_admin()
	{
		echo '<script type="text/javascript">' . "\n";
		readfile(plugin_dir_path(__FILE__) . 'js/footer_admin.js');
		echo '</script>' . "\n";
	}

}

// activation hook
register_activation_hook(__FILE__, array('WPAutoColumns', 'on_activation'));
// deactivation hook
register_deactivation_hook(__FILE__, array('WPAutoColumns', 'on_deactivation'));

add_action('init', array('WPAutoColumns', 'on_init'));

add_action('admin_footer-post-new.php', array('WPAutoColumns', 'footer_admin'));
add_action('admin_footer-post.php', array('WPAutoColumns', 'footer_admin'));
add_action('admin_footer-page-new.php', array('WPAutoColumns', 'footer_admin'));
add_action('admin_footer-page.php', array('WPAutoColumns', 'footer_admin'));
