=== WP Auto Columns ===
Contributors: spectraweb
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Z5XN794FWSSZW
Tags: column, columns, layout, magazine, newspaper, pages, posts, multicolumn, automatic
Requires at least: 3.1.0
Tested up to: 3.3.1
Stable tag: 1.0.0

Wrap block of text with shortcode. It will be split into columns. Automagically.

== Description ==

I needed automated, tag-aware column splitter for one of my projects. So I have created one.

= Features =

*	fully automatic splitter;
*	tag-aware;
*	splits long paragraphs and unordered lists;
*	keeps text with headers;
*	does not split ordered lists to keep right order.

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

= 1.0 =
* First public release

== Upgrade Notice ==

Plugin does not require any special steps to upgrade
