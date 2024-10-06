# Housekeeping Plugin #

## Description ##

This plugin provides a page that performs housekeeping on various tables to help reduce the size of the phplist database.

## Installation ##

### Dependencies ###

Requires the Common Plugin version 3.6.7 or later to be installed.
phplist now includes Common Plugin so you should only need to enable it on the Manage Plugins page.

See <https://github.com/bramley/phplist-plugin-common>

### Installation through phplist ###

Install on the Plugins page (menu Config > Plugins) using the package URL `https://github.com/bramley/phplist-plugin-housekeeping/archive/master.zip`

## Usage ##

For guidance on usage see the plugin page within the phplist documentation site <https://resources.phplist.com/plugin/housekeeping>

## Support ##

Please raise any questions or problems in the user forum <https://discuss.phplist.org/>.

## Donation ##

This plugin is free but if you install and find it useful then a donation to support further development is greatly appreciated.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=W5GLX53WDM7T4)

## Version history ##

    version     Description
    1.4.0+20241013  Delete rows from the user_message_view table
    1.3.5+20240316  Delete user_message_bounce rows that do not have a related bounce row
    1.3.4+20240315  Delete bounces for subscribers who are blacklisted
    1.3.3+20231028  Allow the process page to be run as a remote page
    1.3.2+20231020  Add link to configuration settings to the housekeeping page
    1.3.1+20230524  Use MessageTrait instead of Message DAO
    1.3.0+20210718  Spanish translation from Elías Torres. Fixes #1.
    1.2.1+20201017  Remove query to delete duplicate bounces from user_message_bounce
    1.2.0+20200627  Use core phplist page lock
    1.1.3+20200514  Delete duplicate bounces from user_message_bounce
    1.1.2+20200513  Change way of deleting bounces
    1.1.1+20200420  Rework dependency container
    1.1.0+20200419  Add page to delete all bounces
    1.0.4+20171116  Made text translateable
    1.0.3+20170930  Add setting to delete user history
    1.0.2+20170911  Correct use of wrong number of records deleted
    1.0.1+20170911  Enter interval in full
    1.0.0+20170911  First version
