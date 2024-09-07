The CLI allows you to manage all of your website right from your terminal.

It has all of the features available in the admin interface, you can create backups, manage users, edit settings, create and edit posts and much more.

_The only limitation the CLI has in comparison to the admin interface, is that the body of posts and pages cannot be edited._

## Commands

### Database

Create a backup of the database in a json file.

```bash
php aurora db:backup [file_path]
```

Restore the database from a json file.

```bash
php aurora db:restore [file_path]
```

### Links

Create a new link.

```bash
php aurora links:create
```

Delete a link.

```bash
php aurora links:delete [id_or_slug]
```

Edit a link.

```bash
php aurora links:edit [id_or_slug]
```

List the links.

```bash
php aurora links:list
```

### Pages

Create a new page.

```bash
php aurora pages:create
```

Delete a page.

```bash
php aurora pages:delete [id_or_slug]
```

Edit a page.

```bash
php aurora pages:edit [id_or_slug]
```

List the pages.

```bash
php aurora pages:list
```

### Posts

Create a new post.

```bash
php aurora posts:create
```

Delete a post.

```bash
php aurora posts:delete [id_or_slug]
```

Edit a post.

```bash
php aurora posts:edit [id_or_slug]
```

List the posts.

```bash
php aurora posts:list
```

### Tags

Create a new tag.

```bash
php aurora tags:create
```

Delete a tag.

```bash
php aurora tags:delete [id_or_slug]
```

Edit a tag.

```bash
php aurora tags:edit [id_or_slug]
```

List the tags.

```bash
php aurora tags:list
```

### Users

Create a new user.

```bash
php aurora users:create
```

Delete a user.

```bash
php aurora users:delete [id_or_slug]
```

Edit a user.

```bash
php aurora users:edit [id_or_slug]
```

List the users.

```bash
php aurora users:list
```

### Settings

List the system settings.

```bash
php aurora settings:list
```

Set the value of a system setting.

```bash
php aurora settings:set [name] [value]
```

### Update

Update Aurora to the latest compatible version.

```bash
php aurora update
```