=== Modular Custom CSS ===
Contributors: celloexpressions
Tags: Custom CSS, Customizer, Custom Design, CSS
Requires at least: 4.0
Tested up to: 4.4
Stable tag: 1.0
Description: Manage and live-preview theme-specific and plugin-specific Custom CSS in the Customizer.
License: GPLv2

== Description ==
Write custom CSS that stays specific to each theme; so you can easily switch themes without losing customizations. Includes a place for theme-specific CSS (which is stored with each theme) and a place for plugin-specific CSS (which is run on every theme).

This plugin is extremely simple; no validation is performed on the CSS added here and revision history is not saved (although it will be if/when Customizer setting revisions are added to core).

Please note that Modular Custom CSS requires WordPress 4.0 or higher.

== Frequently Asked Questions ==
= Where is the Custom CSS Stored? =
Theme CSS is stored as a `theme_mod`, meaning it is a theme-specific option, part of the theme_mod_$theme option in the database. Each theme has its own `theme_mod` for the custom CSS, so if you switch to a new theme, the theme-specific custom CSS will be empty. When you switch back to a previously customized theme, the CSS that you added to it will still be there.

Plugin CSS is stored as a regular `option` in the database. It is used for every theme, so it's best used for things that are plugin-related or anything else you want to persist between different themes. Most Custom CSS plugins store the CSS as an option, but that means that theme-specific CSS must be deleted and lost when you switch themes.

= Why is WordPress 4.0 required? =
The Customizer features many improvements in WordPress 4.0, including the textarea control type that this plugin uses.

== Screenshots ==
1. Custom CSS Section in the Customizer.

== Installation ==
1. Take the easy route and install through the WordPress plugin adder, OR
1. Download the .zip file and upload the unzipped folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the Customizer (Appearance -> Customize) to add custom CSS to your site.

== Changelog ==
= 1.0 =
* First publicly available version of the plugin.
* Requires WordPress 4.0+.

== Upgrade Notice ==
= 1.0 =
* Initial Public Release