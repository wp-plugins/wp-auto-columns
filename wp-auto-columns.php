<?php

/*
  Plugin Name: WP Auto Columns
  Plugin URI: http://wordpress.org/extend/plugins/wp-auto-columns/
  Description: Wrap block of text with shortcode. It will be split into columns. Automagically.
  Author: Spectraweb s.r.o.
  Author URI: http://www.spectraweb.cz
  Version: 1.0.1
 */

load_plugin_textdomain('wp-auto-columns', false, dirname(plugin_basename(__FILE__)) . '/languages');

register_activation_hook(__FILE__, array('WPAutoColumns', 'on_activation'));
register_deactivation_hook(__FILE__, array('WPAutoColumns', 'on_deactivation'));

add_filter('init', array('WPAutoColumns', 'on_init'));

require_once('include/HTMLSplitter.php');

class WPAutoColumns
{

	/**
	 * plugin activation hook
	 */
	public static function on_activation()
	{

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

}