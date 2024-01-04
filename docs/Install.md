## Requisites

- [PHP 8.0](https://www.php.net) or higher (with the PDO and ZIP extensions enabled)

- [Apache 2.4](https://httpd.apache.org) or higher

- [Composer](https://getcomposer.org) (only for installation)

## Installation

Go to your web server's root directory and run the following command:

```bash
composer create-project usbac/aurora
```

It will download a fresh copy of Aurora and install all the dependencies. You can also download a zip file from the [releases page](https://github.com/Usbac/aurora/releases) and unzip it in your web server's root directory.

Done! You can now access your Aurora panel by going to `/admin` in your localhost (e.g., http://localhost/admin).

_The admin user created by default is `john.doe@mail.com` and the password is `12345678`. Be sure to change it._