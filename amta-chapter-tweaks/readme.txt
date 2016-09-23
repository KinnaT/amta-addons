=== AMTA Chapter Tweaks & Customizations ===
Contributors: Kinna28
Tags: AMTA, chapter, tweaks, customizations, fixes
Requires at least: 3.8
Tested up to: 4.2.1
License: GPL2
License URI: https://wordpress.org/about/gpl/

Fixes and tweaks for the plugins and content to work with the custom theme.  Includes templates for various used plugins and custom styling for others.

== Description ==
Stuff that allows me to clone these sites and have it still work with the heaviest, most obtrusive custom theme I\'ve ever encountered.  Creating this small group of files should help me pare down the pile of shit I\'ve been forced to wade through as I unhook functionality from the rigidity of the theme and move it to swappable and well-supported plugins.  Fuck this theme.

== Installation ==
Just drop it in your /plugins folder.  Helps to have things like Shortcodes Ultimate and Ninja Forms installed, or it doesn\'t serve much purpose.  Plus, half of the time it\'s calling to strange and confusing object names from the theme, which may or may not interfere with WP core.  Oh well.  Good luck and godspeed.

== Frequently Asked Questions ==
Why are you doing this?
Spite mostly.  And a senseless determination to get these tiny, unloved sites to work well.  Pride.

What happened with this theme?  It can\'t be that bad...
It can, and it is.  I inherited a PARENT theme to use across 20+ subdomain sites that has a screen.css in excess of 2200 lines.  It doesn\'t include the 350 line media-queries.css, 100 line layout.css, 100 line rtl.css, 100 line carousel.css and a second 750 line style.css buried in the /base/assets folder.  The child theme has just as much styling in just as many files.  Except the classes and IDs aren\'t always the same.  It\'s an adventure to try to figure out where something is handled.  Ctrl+F has been my best friend.

== Changelog ==
v1.0.3 - 5/1/15
* Combined multiple files and tweaks into one baby repo
* Considered torching the entire parent and child theme and rebuilding them.  Again.