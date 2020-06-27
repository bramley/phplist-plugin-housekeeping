# Housekeeping Plugin #

## Description ##

This plugin provides a page that performs housekeeping on various tables to help reduce the size of the phplist database.

## Installation ##

### Dependencies ###

Requires php version 5.4 or later.

Requires the Common Plugin version 3.6.7 or later to be installed. You should install or upgrade to the latest version.

See <https://github.com/bramley/phplist-plugin-common>

### Installation through phplist ###

Install on the Plugins page (menu Config > Plugins) using the package URL `https://github.com/bramley/phplist-plugin-housekeeping/archive/master.zip`.

## Usage ##

For guidance on usage see the plugin page within the phplist documentation site <https://resources.phplist.com/plugin/housekeeping>

## Support ##

Please raise any questions or problems in the user forum <https://discuss.phplist.org/>.

## Donation ##

This plugin is free but if you install and find it useful then a donation to support further development is greatly appreciated.

[![Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=W5GLX53WDM7T4)

## Version history ##

    version     Description
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
