=== Gravity Forms Intercom ===
Contributors: skyverge, jilt
Tags: gravity-forms, gravityforms, intercom
Requires at least: 4.4
Tested up to: 4.9.4
Stable Tag: 1.0.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Lets you create new Intercom conversations from Gravity Forms submissions.

== Description ==

Lets you connect to Intercom and post new form submission as Intercom conversations. Set up notes:

1. You need and extended access token with full read permissions for all admins, users, etc
2. This lets you support using leads vs users for the conversation (can also use these with form conditional feed processing)
3. You can't assign a conversation with initial creation; you must add a note to assign conversations. This is an Intercom API limitation, because the initial conversation must be user-generated, which doesn't allow assignment.
4. You can't add attachments within Intercom for form fields, given the file needs to be at a public URL to import to Intercom, and Gravity Forms attachment URLs are private. Only image attachments are supported in Intercom anyway.

== Installation ==

1. Be sure you're running Gravity Forms 1.9.15 or newer
2. Upload the entire `gravityformsintercom` folder to the `/wp-content/plugins/` directory, or upload the .zip file with the plugin under **Plugins &gt; Add New &gt; Upload**
3. Activate the plugin through the **Plugins** menu in WordPress
4. Go to Forms &gt; Settings &gt; Intercom and enter your extended access token to connect.

== Frequently Asked Questions ==

TODO

== Changelog ==

= 2018-02-10 - version 1.0.0 =
 * Initial Release
