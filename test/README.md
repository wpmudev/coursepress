## Install test environment

### PHP

Obtain and install PHPUnit: https://github.com/sebastianbergmann/phpunit#installation
    
We will need create the default test environment and set it up. First up, we will need a shell opened up in a proper location.

- *nix: `cd tests/php/bin` 
- Win: right-click `tests\php\bin` -> "Git Bash here"

To install in a place other than default (`/tmp/wp-tests/*`), do the following (this is optional):

	export WP_TESTS_DIR="<path-to-your-preferred-destination-1>"
	export WP_CORE_DIR="<path-to-your-preferred-destination-2>"

Now that we have all this, run the install script:

- *nix: `bash install-wp-tests.sh <db-name> <db-user> <db-pass>`
- Win: `bash install-wp-tests.sh <db-name> <db-user> <db-pass>`

(the database shouldn't exist)

It might be necessary to do some post-install steps, such as adjusting your ABSPATH on Windows. This can be done by editing your `WP_TESTS_DIR/wp-tests-config.php` file. Also, if you encounter error running tests that mentions missing `WP_REST_Server` class, open up the `WP_TESTS_DIR/includes/spy-rest-server.php` and comment out the `extends` part so it looks like this: `class Spy_REST_Server /* extends WP_REST_Server */ {`

### Javascript

`npm install`

## Running tests

- To run both JS and PHP tests: `npm test`
- To run JS tests only: `npm run test-js`
- To run PHP tests only: `npm run test-php`

If you installed PHP tests in a place other than default, you will need to set the `WP_TESTS_DIR` environment variable before issuing the command. Example for Windows:

`SET WP_TESTS_DIR=d:/tmp/wp-tests/coursepress-tests-lib`

## Writing tests

### PHP

PHP tests are located in `test/php`, and they are standard PHPUnit tests. For more info: https://phpunit.de/manual/4.8/en/

### Javascript

JS tests are located in `test/js`. For more info, see example tests there.
