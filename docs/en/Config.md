The configuration file is located at `/app/bootstrap/config.php`. It must return an array containing the configuration values.

_All configuration values are mandatory._

_Configuration values related to paths must be relative to the Aurora root directory._

## Configuration values

### `bootstrap`

Closure that will be executed when the web app is initialized.

It can receive an instance of the `\Aurora\Core\Kernel` class as argument.

#### Example

```php
function (\Aurora\Core\Kernel $kernel) {
}
```

### `date_format`

Default date format to use for dates. Used by default on all admin pages.

#### Example

```php
'dd MMM Y HH:mm'
```

### `db`

Database connection.

#### Example

```php
new \Aurora\Core\DB("sqlite:app/database/db.sqlite");
```

### `content`

Path to the content directory within the public directory.

#### Example

```php
'public/content'
```

### `max_active_sessions`

Number of maximum **active sessions** per user. When a new login (or any flow that issues a session token) would exceed this count, the **oldest** sessions for that user are removed first.

#### Example

```php
10
```

### `mail`

Mail function that will be used to send emails.

The function must take 3 parameters:

- `$to` - The recipient.

- `$subject` - The subject.

- `$message` - The message.

#### Example

```php
fn($to, $subject, $message) => mail($to, $subject, $message)
```

### `views`

Path to the views directory.

#### Example

```php
'app/views'
```
