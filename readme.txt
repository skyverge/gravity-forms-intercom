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

Lets you connect to Intercom and post new form submission as Intercom conversations.

Set up notes:

1. You need and extended access token with full read permissions for all admins, users, etc., in order to use this plugin.
2. Feed settings let you use leads or users for the created conversation, and you can also use conditional form feed processing to choose when to use a user vs a lead.
3. You can't assign a conversation with initial creation; you must add a note to assign conversations to an Intercom user. This is an Intercom API limitation. Because the initial conversation must be user-generated, it does not accept assignment as part of this request.
4. You can't add attachments within Intercom for form fields, given the file needs to be at a public URL to import to Intercom, which won't work with the form attachment URL. Only image-type attachments are supported in Intercom regardless, so we've omitted attachment support.

== Installation ==

1. Be sure you're running Gravity Forms 1.9.15 or newer
2. Upload the entire `gravityformsintercom` folder to the `/wp-content/plugins/` directory, or upload the .zip file with the plugin under **Plugins &gt; Add New &gt; Upload**
3. Activate the plugin through the **Plugins** menu in WordPress
4. Go to Forms &gt; Settings &gt; Intercom and enter your extended access token to connect.

== Frequently Asked Questions ==

None so far :)

== Changelog ==

= 2018-02-10 - version 1.0.0 =
 * Initial Release
