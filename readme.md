# Gravity Forms Intercom
 
### Description 

Lets you create new Intercom conversations from Gravity Forms submissions.

### Credits

This is largely based on the Gravity Forms Help Scout integration (which was used as a starting point), and leverages the [Intercom PHP API client](https://github.com/intercom/intercom-php).

### Requirements

 - Gravity Forms 1.9.15 or newer
 - WordPress 4.4 or newer
 - PHP 5.6 or newer
 
This repository **is not a stand-alone plugin**. You must pull in the required dependencies with composer (which will install the Intercom API library in the correct path). Run `$ composer install` in the plugin directory to install dependencies.

### Setup and plugin info

1. You need an **extended access token** with full read permissions for all admins, users, etc., in order to use this plugin.
1. Gravity Forms feed settings let you use leads or users for the created conversation, and you can also use conditional form feed processing to choose when to use a user vs a lead.
1. You can't assign a conversation with initial creation; you must add a note to assign conversations to an Intercom user. This is an Intercom API limitation. Because the initial conversation must be user-generated, it does not accept assignment as part of this request.
1. You can't add attachments within Intercom for form fields, given the file needs to be at a public URL to import to Intercom, and this won't work with the GF form attachment URL. Only image-type attachments are supported in Intercom regardless, so we've omitted attachment support in this plugin.

### Changelog

**2018-02-10 - version 1.0.0**   
 * Initial Release
