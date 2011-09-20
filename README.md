# My Profile Picture

My Profile Picture is a Moodle block that interfaces with a web service to import profile pictures from the Blackboard eCommerce Server.

## Features

* PHP webservice based
* Imports photos via cron 
* Automated
* Users can reprocess their photos from the official pool
* Stops users from uploading their own pictures

## Download

Visit [My Profile Picture's Github page][quickmail_my_picture] to either download a package or clone the git repository.

## Installation

* The My Profile Picture block should be installed like any other block. See [the Moodle Docs page on block installation][block_doc].
* The webservice should be copied to any php server that can mount the Blackboard Ecommerce Server's data folder.
 * The web server the webservice resides on should allow https communications.
 * The web server the webservice resides on should be locked down to only allow connections from the Moodle server.

## Contributions

Contributions of any form are welcome. Github pull requests are preferred.

File any bugs, improvements, or feature requiests in our [issue tracker][issues].

## License

My Profile Picture adopts the same license that Moodle does.

## Screenshots


---


---


---


[quickmail_github]: https://github.com/lsuits/my_picture
[block_doc]: http://docs.moodle.org/20/en/Installing_contributed_modules_or_plugins#Block_installation
