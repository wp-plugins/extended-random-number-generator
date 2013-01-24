=== Extended Random Number Generator ===
Contributors: rtddev, Whiler
Donate link: TBD
Tags: random, random numbers, generate random numbers
Requires at least: 2.7
Tested up to: 3.5
Stable tag: 1.0

This plugin extends the capability of the original Random Number Generator plugin so that the short code may be used within custom text/html fields native to many themes, such as those with special ad placements.

== Description ==

By default, the original <a href="http://wordpress.org/extend/plugins/random-number-generator/">Random Number Generator</a> plugin short code works within widgets, posts, and pages. We have extended the core capability of the plugin so that it also works within text/html fields, such as those used for advertising purposes.

One primary use: It can be used to <a href="http://support.google.com/dfp_premium/bin/answer.py?hl=en&answer=1116933">defeat browser caching</a> by inserting a random number. The most common implementation of this is within Google DoubleClick ads where they require an 'ord=' within the script to ensure that ad creative is not cached by the browers.

Use Example 1: 
input: http://my_url?ord=[random-number]" 
output: http://my_url?ord=134548.

Use Example 2:
input: [random-number from="2" to="72" format="%b"]%d minutes[/random-number]
output: an integer value between 2 & 72 followed by the word 'minutes'

== Supported languages ==

* English
* French
* Russian
* All languages supported based on orginal Random Number Generator plugin

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload folders and files  to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `[random-number]` in a post (if you haven't changed the tag caption in the options)

== Frequently Asked Questions ==

Does it work with WordPress MU?

- Yes

Which languages are included when I install the plugin?

* English
* French
* Russian

== Screenshots ==

1. Options in English
1. Options in French
1. Options in Russian
1. Sample


== Changelog ==

= 1.0 =
* Extended capability to work within text/html fields outside of posts and pages.
* First release
* Plugin started/forked from version 1.3.2 of Original Random Number Generator plugin

