=== Arconix Testimonials ===
Contributors: jgardner03
Donate link: http://arcnx.co/actdonation
Tags: arconix, testimonials, quotes, feedback
Requires at least: 3.8
Tested up to: 3.9
License: GPLv2 or later
Stable Tag: 1.1.1

Easily showcase what your customers or users are saying about you or your business.

== Description ==

Arconix Testimonials is an easy-to-use plugin that allows you to showcase the great things your customers or users are saying about you or your business.

**Features**

* Base stylesheet is responsive and is looks good out of the box with almost any theme, but supports upgrade-safe customizations if a tighter integration is desired.

* Integrates into the new dashboard design with WordPress 3.8

* Add testimonials to your site via a widget, shortcode or template tag

* Displays [Gravatars](http://gravatar.com) when available

[Live Demo](http://demo.arconixpc.com/arconix-testimonials)

== Installation ==

You can download and install Arconix Testimonials using the built in WordPress plugin installer. If you download the plugin manually, make sure the files are uploaded to `/wp-content/plugins/arconix-testimonials/`.

Activate Arconix Testimonials in the "Plugins" admin panel using the "Activate" link.

== Upgrade Notice ==

Upgrade normally via your WordPress admin -> Plugins panel.

== Frequently Asked Questions ==

= How can I show my testimonials?  =

* Place the Arconix - Testimonials widget in the desired widget area
* Use the shortcode `[ac-testimonials]` on a post, page or other area
* Place `<?php echo do_shortcode( "[ac-testimonials]" ); ?>` in the desired page template

= Why is the plugin basically unstyled? =

With no 2 themes exactly alike, it's impossible to style a plugin that seamlessly integrates without issue. That's why I made the plugin flexible -- copy `includes/arconix-testimonials.css` to the root of your theme's folder and make your desired modifications. My plugin will automatically load that file instead.

= Is there any other documentation? =

* Visit the plugin's [Wiki Page](http://arcnx.co/atwiki) for all the plugin's documentation
* Tutorials on advanced plugin usage can be found at [Arconix Computers](http://arconixpc.com/tag/arconix-testimonials)

= I have a problem or a bug =

* Check out the WordPress [support forum](http://arcnx.co/athelp) or the [Issues section on Github](http://arcnx.co/atissues)

= I have a great idea for your plugin! =

That's fantastic! Feel free to submit a pull request over at [Github](http://arcnx.co/atsource), add an idea to the [Trello Board](http://arcnx.co/attrello), or you can contact me through [Twitter](http://arcnx.co/twitter), [Facebook](http://arcnx.co/facebook) or my [Website](http://arcnx.co/1)

== Screenshots ==

1. Adding a new Testimonial
2. Testimonial list in the Admin
3. Shortcode output with default styling
4. Shortcode metabox on Testimonial creation screen


== Changelog ==
= 1.1.1 =
Fixed a text formatting bug

= 1.1.0 =
* Added a metabox to the testimonial edit screen that provides the Post ID. Good for when a specific testimonial is needed to display
* Fixed a bug where some users were getting an error message about a missing file
* Enhanced the testimonial column display on the admin side to be more flexible

= 1.0.1 =
Fixed a bug where sometimes empty testimonial html was showing up on non-testimonial pages.

= 1.0.0 =
Initial release