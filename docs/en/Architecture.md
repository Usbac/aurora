The Aurora codebase is structured in several directories.

- `app` - Contains the code related to the project configuration, database, controllers, languages and views.

- `bin` Contains the CLI code.

- `core` - Contains the core code of Aurora. Like the router, database abstraction layer, language manager, etc.

- `docs` - Contains the Aurora documentation.

- `public` - Contains the public files and resources, such as images, stylesheets and javascript files.

- `tests` - Contains all the code related to the automated tests.

## Stack

Aurora is built on top of the following technologies:

- [PHP](https://www.php.net) - The backend programming language.

- [HTML/CSS/JS](https://developer.mozilla.org/en-US/docs/Web) - The frontend languages.

- [SQLite](https://www.sqlite.org) - The database engine used by default.

- [Apache](https://httpd.apache.org) - The web server used by default.

## Workflow

Aurora is built with simplicity in mind. The workflow is very straightforward:

1. The user makes a request to the web server.

2. `/public/index.php` is executed and initializes the web app.

3. `/app/bootstrap/config.php` is requested and loads the configuration, routes and everything else related to the web app initialization in the `app/bootstrap` property.

4. The requested route is matched and the corresponding route defined in `/app/bootstrap/routes.php` is executed (each route normally calls the modules defined in `/app/controllers/modules`).