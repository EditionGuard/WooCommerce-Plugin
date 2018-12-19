=== EditionGuard for WooCommerce - eBook Sales with DRM  ===
Contributors: EditionGuard
Donate link: https://www.editionguard.com
Tags: ebook,drm,epub,pdf,sales,woocommerce,e-book
Requires at least: 3.0.1
Tested up to: 5.0.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This Wordpress plugin allows you to sell eBooks using WooCommerce 2.1+, while protecting them with EditionGuard DRM.

== Description ==

[Watch Screencast on Youtube for Setup Instructions](http://www.youtube.com/watch?v=vpZv8miQPdM "EditionGuard WooCommerce eBook DRM Plugin Screencast")

This plugin is a complete eBook sales solution, which is fully integrated with our cost-effective EditionGuard eBook DRM service.

By following just a few simple steps, you can immediately start selling your eBooks on your WooCommerce site with industry standard eBook DRM protection based on Adobe Content Server.

1. First make sure you have WooCommerce installed and properly configured. This plugin will not work without WooCommerce!
2. Login to your admin interface and install the plugin
3. Enter your EditionGuard credentials under the WooCommerce > EditionGuard menu. Don't have an EditionGuard account yet? [Click here to try our service for 30 days, free of charge](http://www.editionguard.com/?action=trial "Start selling eBooks on your WooCommerce site with Adobe DRM").
4. Create a downloadable product, with the "Use EditionGuard DRM" option checked.
5. Upload your eBook in ePub or PDF format, or choose an existing eBook on your EditionGuard account.
6. Start making money!

Once your customers buy one of your eBooks and make payment for it, they’ll automatically receive a secure download link for the eBook through email, which will also be displayed on the transaction result page. This download link can then be opened using [Adobe Digital Editions](http://www.adobe.com/products/digital-editions.html "Adobe Digital Editions") on your Mac or PC. It can also be opened on your iOS or Android mobile device using [one of the apps listed here](http://blogs.adobe.com/digitalpublishing/supported-devices "EditionGuard DRM Supported Devices and Apps").

[Please see this visual download guide](http://www.editionguard.com/images/download-guide.jpg "EditionGuard DRM Download Guide") for a step by step capture of the download process. You are free to use this visual on your websites.

For more information about our EditionGuard DRM Service, please visit http://www.editionguard.com

== Installation ==

1. Download the plugin
2. Unzip the file and upload the `woocommerce-editionguard` directory to the `/wp-content/plugins` directory.
3. Activate the plugin through the plugins menu in WordPress.
4. Start selling!

[Watch Screencast on Youtube for Setup Instructions](http://www.youtube.com/watch?v=vpZv8miQPdM "EditionGuard WooCommerce eBook DRM Plugin Screencast")

== Frequently Asked Questions ==

= What is EditionGuard?  =

EditionGuard is a secure and robust eBook DRM (digital rights management) service, based on the industry standard Adobe Content Server.

The aim of our service is to provide publishers of all sizes with a cost-effective and easy to use DRM system without the usually large capital and time investments required.

= Why would I need to use EditionGuard? =

Typically, eBook DRM systems have very high capital and operational costs associated with them. Not only would you have to pay for costly server software licenses, you would also need to setup and manage your own IT infrastructure consisting of various expensive server hardware and software software.

Also, the software components of a DRM system are usually quite complex and low-level. What this means is that you’re expected to develop and integrate your own management and business logic layers on top of the DRM system to utilize it comprehensively. While this structure allows for lots of flexibility, we found that most of our clients really want to perform some simple tasks easily, without having to dabble in all the technical details involved.

These are the main reasons we created EditionGuard. We want to provide you with a cost-effective and easy to manage DRM system, while still maintaining the robust and flexible infrastructure present in its core by basing the system on industry standard technologies.

= Who is using EditionGuard? =

Our service is being used by a wide range of eBook publishers, distributors and authors. Examples of some of our client profiles are as follows;

* Bookstore chains who wish to offer eBooks securely to their clients through the web,
* Self publishers who want to put up their eBooks on their blogs securely,
* E-Commerce sites with a wide range of products wanting to include eBooks in their product range,
* Publishing Houses of all sizes wanting to publish and distribute their titles online,
* Government institutions with a desire to sell their publications digitally through their portals

= More questions? =

Please visit the [our official FAQ](http://www.editionguard.com/help#faq "EditionGuard FAQ") or feel free to [contact us](mailto:support.editionguard.com "Contact EditionGuard Support").

== Screenshots ==

1. General settings page.
2. Downloadable product addition with EditionGuard DRM option.
3. Download page for a successful order.

== Changelog ==
= 3.3.0 =
Wordpress 5.0.1 Compatibility Check.
Added Header identifier in API requests to EditionGuard.
Increased page_size of book list for larger accounts.
= 3.2.0 =
E-book selection has become a combo box for easier use.
= 3.1.1 =
Dropdown list showing e-books will now show all books on account. (Temporary fix, this will become a combobox shortly.)
= 3.1.0 =
WooCommerce 3.2.x compatibility update.
= 3.0.2 =
Generate download links for manual orders through WooCommerce admin.
= 3.0.1 =
Download links are now visible under Account > My Downloads page.
= 3.0.0 =
WooCommerce 3.x compatibility update.
= 2.0.3 =
Updated reference for Social DRM.
= 2.0.2 =
Get book list should receive all books on account.
= 2.0.1 =
Fixed API url.
= 2.0.0 =
Added support for Social DRM (watermarking) enabled books.
Plugin now uses our V2 Rest API for shorter, better formatted links.
Removed book upload functionality.
All book uploads need to be done on the EditionGuard app going forward.
= 1.1.4 =
Fixed a referencing issue with post urls.
= 1.1.3 =
Fixed a bug where the settings page in some cases saved with a warning message.
= 1.1.2 =
jQuery added as a dependency for js script.
= 1.1.1 =
Removed temporary debug logging.
= 1.1.0 =
Compatibility upgrade with WooCommerce 2.1+ and some minor visual fixes.
= 1.0.7 =
Check if link is to be managed by plugin.
= 1.0.6 =
Added a special fix for emails with invalid download links.
= 1.0.5 =
Support for eBook sales with Quantity > 1
= 1.0.4 =
Made sure each download link order id is unique by appending product/variant id.
= 1.0.3 =
Another order id bug fix for download links when order set as completed through admin.
= 1.0.2 =
Fixed a bug where some download links within e-mails were missing the order id.
= 1.0.1 =
Fixed a bug which prevented running from a sub folder.
= 1.0 =
Initial Release.

== Upgrade Notice ==
None as of yet.
