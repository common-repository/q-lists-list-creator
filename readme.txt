=== Plugin Name ===
Contributors: Ali Sipahioglu
Tags: posts, pages, pagination
Requires at least: 2.0.0
Tested up to: 2.9
Stable tag: 1.0


Create a list on your website and let members to vote the items of the list.

== Description ==

Q-List is a Wordpress Plug-in that lets you create a list on your Wordpress website and your members can vote yes/no to the items on your list.
Features:

    * Add items to the list
    * Remove items from the list
    * Edit items in the list
    * Members can vote Yes/No to multiple items in the list. (Uses cookies so your users must have cookies enabled otherwise the poll won't let them vote)
    * Can be included in a post, page or in a php page.
    * Abilty to show the checked ones only. Requires Jquery


== Installation ==

1. Upload everything to the `/wp-content/plugins/q-list-list-creator` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Now you will see the Q-List under Settings in the admin panel.

 If you want to include the list into your post or a page all you need to include is `[qlist]`

 If you want to include it in your php files the function is ’show_questions’. Usage: `<?php show_questions();?>`

== Frequently Asked Questions ==

Coming up features:
    * Custom styles. If you need to change anything you can do so by editing the q-list.css in the plugin folder as of now.


You need to have jquery in your page for the switch view link to work. It hides the unchecked boxes.

Any problems? Please leave a comment at http://www.weblimner.com/plugin/q-lists-list-creator/

== Screenshots ==

1. First screen shot is a sample list
2. This is what your admin menu looks like.

== Changelog ==

= 1.0 =
In this version, users can vote without being a member of your website. Cookies are used. Design is changed. Percentages are shown for the results.

= 0.6 = 
Now you have the ability to show the results of the votes.

= 0.5 =
First Release

