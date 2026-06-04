Aurora has been built with testing in mind. [PHPUnit](https://phpunit.de) is the library used for this.

## Running tests

First make sure you have PHPUnit installed, you can install it with the command:

```bash
composer install
```

Then, to run the automated tests, just execute the following command:

```bash
composer test
```

Or directly:

```bash
vendor/bin/phpunit -c phpunit.xml.dist
```
