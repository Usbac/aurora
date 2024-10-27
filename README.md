<h1 align="center">
  <img src="public/assets/logo.svg" alt="Aurora logo" width="140">
  <br>
  Aurora
  <br>
</h1>

<h4 align="center">Made for Developers, Designed for Users</h4>

<p align="center">
<img src="https://img.shields.io/github/actions/workflow/status/Usbac/aurora/ci.yml"/>
<img src="https://img.shields.io/badge/stable-0.6.0-blue.svg">
<img src="https://img.shields.io/badge/license-MIT-orange.svg">
</p>

Aurora is a cutting-edge CMS crafted in PHP, tailored for developers and content managers alike. It boasts a sleek and lightweight design, ensuring seamless installation, effortless usability, and convenient customization.

## Features

- ✨ **User-Friendly**: Aurora is designed to be simple and straightforward, making it a breeze to set up and use. Just move its files to your web server's root folder to get started!

- 🗜️ **Lightweight**: The Aurora codebase is incredibly compact, totaling less than 8k lines of code (loc), making it easy to grasp and work with. It doesn't rely on any external dependencies apart from the default WYSIWYG editor.

- 🦄 **Stylish**: Written in clean, modern PHP, Aurora adheres to the highest coding standards, ensuring it's both elegant and easy for developers to understand and customize.

- 🛠️ **Comprehensive**: With its intuitive admin panel and command-line interface (CLI), Aurora offers everything you need to manage your website without delving into the code. From creating posts and editing pages to handling user roles and backups.

- 📦 **Modular**: Aurora's modular design allows you to customize not only the default database management system (DBMS) but also elements like the WYSIWYG editor in the admin panel.

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
  <img src="https://github.com/user-attachments/assets/a43f4f4e-48fb-48fe-97af-6b13af1d1ce4" width="800"/>
  
  <img src="https://github.com/user-attachments/assets/d3ca710f-ccac-4fae-8a84-0607442c9250" width="800"/>
  
  <img src="https://github.com/user-attachments/assets/d39ee11f-9686-4fdd-a6ef-bd241170f585" width="800"/>
  
  <img src="https://github.com/user-attachments/assets/c89c8ebb-aac5-4931-84f4-490dd027b108" width="800"/>
</div>

## Contributing

Any contribution or support to this project will be highly appreciated. ❤️

Please review the [contribution guide](CONTRIBUTING.md) before you open issues or send pull requests.

## License

Aurora is open-source software licensed under the [MIT license](https://github.com/Usbac/aurora/blob/master/LICENSE).
