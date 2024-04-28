<h1 align="center">
  <img src="public/assets/logo.svg" alt="Aurora logo" width="140">
  <br>
  Aurora
  <br>
</h1>

<h4 align="center">Made for Developers, Designed for Users</h4>

<p align="center">
<img src="https://img.shields.io/github/actions/workflow/status/Usbac/aurora/ci.yml"/>
<img src="https://img.shields.io/badge/stable-0.1.10-blue.svg">
<img src="https://img.shields.io/badge/license-MIT-orange.svg">
</p>

Aurora is a modern and lightweight CMS made in PHP, focused on both developers and content managers. Extremely simple to install, use and modify.

## Features

- ✨ **Simple**: Easy to set up and use. No complicated settings or configurations. Aurora can be installed by simply moving its files to your web server root!

- 🗜️ **Compact**: The Aurora codebase is extremely small and simple to understand, having less than 8k loc in total. In addition, it does not rely on any single external dependency other than the default WYSIWYG editor.

- 🦄 **Elegant**: Aurora is written in clean, easy-to-understand and modern PHP code. It follows the best coding practices and standards, making it extremely easy for developers to understand and modify.

- 🛠️ **Comprenhensive**: Its admin panel (and CLI) offers you everything you may ever need to manage your next website without touching any code. With it you can create posts, modify pages, manage files, export/import backups, handle several users with their roles and much more.

- 📦 **Modular**: Aurora allows you to modify not only the default DBMS but also things like the WYSIWYG editor used in the admin panel.

## Requirements

- [PHP 8.0](https://www.php.net) or higher

- [Apache 2.4](https://httpd.apache.org) or higher

- [Composer](https://getcomposer.org) (only for installation)

## Install

[Composer](https://getcomposer.org/) is required to install Aurora, once you got it...

Go to your web server's root directory and run the following command:

```bash
composer create-project usbac/aurora
```

Done! You can now access your Aurora panel by going to `/admin` in your localhost (e.g., http://localhost/admin).

_The admin user created by default is `john.doe@mail.com` with password `12345678`. Be sure to change it._

## Documentation

- [**Architecture**](https://github.com/Usbac/aurora/wiki/Architecture)

- [**Install**](https://github.com/Usbac/aurora/wiki/Install)

- [**Config**](https://github.com/Usbac/aurora/wiki/Config)

- [**Settings**](https://github.com/Usbac/aurora/wiki/Settings)

- [**Content**](https://github.com/Usbac/aurora/wiki/Content)

- [**Command Line Interface**](https://github.com/Usbac/aurora/wiki/CLI)

- [**Testing**](https://github.com/Usbac/aurora/wiki/Testing)

- [**FAQ**](https://github.com/Usbac/aurora/wiki/FAQ)

## Captures

<div align="center">
  <img src="https://github.com/Usbac/aurora/assets/38147742/b48cb613-8ddc-47be-bd5b-19f7ed06fb01" width="800"/>

  <img src="https://github.com/Usbac/aurora/assets/38147742/7e293216-8da6-46f7-a4db-0de988826a1f" width="800"/>
  
  <img src="https://github.com/Usbac/aurora/assets/38147742/90849968-f2f6-49a1-8a69-a0dcb665a33b" width="800"/>
  
  <img src="https://github.com/Usbac/aurora/assets/38147742/fd769057-2a3e-485f-98a7-6f16c660f503" width="800"/>
</div>

## Contributing

Any contribution or support to this project will be highly appreciated. ❤️

Please review the [contribution guide](CONTRIBUTING.md) before you open issues or send pull requests.
