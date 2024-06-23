The configuration file is located at `/app/bootstrap/config.php`. It must return an array containing the configuration values.

_All configuration values are mandatory._

_Configuration values related to paths must be relative to the Aurora root directory._

## Configuration values

### `bootstrap`

Closure that will be executed when the web app is initialized.

It can receive an instance of the `\Aurora\System\Kernel` class as argument.

#### Example

```php
function (\Aurora\System\Kernel $kernel) {
}
```

### `db`

Database connection.

#### Example

```php
new \Aurora\System\DB("sqlite:app/database/db.sqlite");
```

### `content`

Path to the content directory within the public directory.

#### Example

```php
'public/content'
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
