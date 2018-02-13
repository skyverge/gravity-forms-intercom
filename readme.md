# Gravity Forms Intercom
 
### Description 

Lets you create new Intercom conversations from Gravity Forms submissions.

### Requirements

 - Gravity Forms 1.9.15 or newer
 - WordPress 4.4 or newer

### Set up info

1. You need and extended access token with full read permissions for all admins, users, etc
2. This lets you support using leads vs users for the conversation (can also use these with form conditional feed processing)
3. You can't assign a conversation with initial creation; you must add a note to assign conversations. This is an Intercom API limitation, because the initial conversation must be user-generated, which doesn't allow assignment.
4. You can't add attachments within Intercom for form fields, given the file needs to be at a public URL to import to Intercom, and Gravity Forms attachment URLs are private. Only image attachments are supported in Intercom anyway.

### Changelog

**2018-02-10 - version 1.0.0**   
 * Initial Release
