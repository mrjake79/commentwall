commentwall
===========

Comment wall exercise.

Author: Jake Bahnsen (mrjake79@gmail.com)
Copyright 2014.

This is my implementation of the comment wall as outlined in the specifications for the developer test.  Database
connection information should be adjusted in config.php and install.sql should be manually run in the MySQL
database.

If the database connection fails or the table does not exist, it should fall back to "test mode" where the
comments can be posted and will be displayed, but will not be persisted between page loads.

The following JavaScript libraries are used in this implementation:

jQuery 2.1.1 (http://jquery.com)
jquery.formatDateTime (http://plugins.jquery.com/formatDateTime/)
jQuery-MD5 (http://github.com/gabrieleromanato/jQuery-MD5)
