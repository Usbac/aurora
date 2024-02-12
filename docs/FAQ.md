## What is the Aurora approach?

Simplify the process of creating small websites with a extremely small, fast and easy to use utility.

## What is the project's philosophy?

The project's philosophy is to keep its simplicity while following good practices, clean code and an object oriented approach. All of this while trying to keep a stable architecture without relying on 'magic'.

## What is the coding standard?

The coding standard is [PSR-2](https://www.php-fig.org/psr/psr-2).

## How is the release life cycle?

Aurora strictly follows [Semantic Versioning](https://semver.org).

## How long does the support last for each version?

The last 2 major releases are the only ones supposed to get support. This condition is ignored ONLY if the major version that is going to be deprecated has been released less than a year ago. 

But one goal of the CMS is to NOT release a major version unless there's a big issue in the current one that requires breaking backward compatibility.

## What is the database engine used?

Aurora uses [SQLite](https://www.sqlite.org/index.html) as its database engine, but it can be changed to any other engine supported by [PDO](https://www.php.net/manual/en/book.pdo.php) in the `/bootstrap/index.php` file.

## What are the recommended file permissions for Aurora?

The recommended permissions are `0755` for folders and `0655` for PHP files. For your safety, PHP files should be editable by the owner and readable by a group.

_Avoid at any cost complete permissions (e.g., `0777`) in a production environment._

## What is the WYSIWYG editor used in the admin panel?

The WYSIWYG editor used in the admin panel is [TinyMCE](https://www.tiny.cloud), but you can change it in the settings for any other editor you like.

## What format is the content written in?

The content for both posts and pages is written in HTML.

## What PHP modules are required for Aurora to work properly?

Aurora requires the following PHP modules to be installed and enabled:

- json - Used to handle the import and export of the database.

- PDO - Database functionality.

- pdo_sqlite - Default database functionality.

- sqlite3 - Default database functionality.

- zip - Used only to download the media content as a zip file.

## How can i update Aurora?

You can update Aurora to the latest compatible version by running the command:

```bash
php aurora update
```

Or in the admin panel, by going to **Settings** > **Update** (`/admin/settings#update`).