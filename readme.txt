=== WP Auto Columns ===
Contributors: spectraweb
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Z5XN794FWSSZW
Tags: column, columns, layout, magazine, newspaper, pages, posts, multicolumn, automatic
Requires at least: 3.1.0
Tested up to: 3.3.1
Stable tag: 1.0.6

Wrap block of text with shortcode. It will be split into columns. Automagically.

== Description ==

You need to display your articles in magazine or newspaper style. Use this plugin. You don't have
to change your theme files or add styles - just use "table" splitter.

= Features =

*	fully automatic splitter;
*	create columns in table or div's;
*	tag-aware;
*	splits long paragraphs and unordered lists;
*	keeps text with headers;

= Requirements =

The plugin requires DOM API (http://www.php.net/manual/en/book.dom.php) and Tidy (http://www.php.net/manual/en/book.tidy.php)

= Usage =

Wrap block of text with `[auto_columns]...[/auto_columns]` shortcode. It will produce markup like

	<div class="auto-columns-container columns-2">
	<div class="auto-columns-column column-1 first-column">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
	<div class="auto-columns-column column-2 last-column">Nam tincidunt gravida dui, at bibendum nisl lacinia nec.</div>
	<div class="auto-columns-clear"></div>
	</div>

In your theme use `do_shortcode` function:

	<?php echo do_shortcode('[auto_columns]' . $content . '[/auto_columns]'); ?>

You will have to define width for `.auto-columns-column` class in your theme, of course.

== Installation ==

1. Login to admin panel of your WP blog and go to `Plugins` → `Add New`
2. Enter `wp-auto-columns` and click on Search button
3. Click on link Install bellow plugin name `WP Auto Columns`
4. Activate plugin by clicking on link `Activate`

== Frequently Asked Questions ==

No questions yet

== Screenshots ==

1. Example of splitted text
2. Example of shortcode markup

== Changelog ==

= 1.0.6 =
* Added parameter `style="table|div"` to define desired split style.

= 1.0.5 =
* Added processing of "height" attribute for IMG tags
* Fixed behaviour of <br/> tags
* Settings page for height measurement fine tuning

= 1.0.4 =
* Added editor toolbar button

= 1.0.3 =
* Small fix in activation handler

= 1.0.2 =
* IMG tags are filtered
* Added check for installed DOM API on activation
* Added Russian translation

= 1.0.1 =
* Fixed `<ol>` behavior

= 1.0.0 =
* First public release

== Upgrade Notice ==

Plugin does not require any special steps to upgrade
