## General

### Logo

The logo of the website shown in places like the header and footer.

### Title

The title of the website. Shown in the browser tab and other places.

### Blog url

The url of the blog relative to the website. By default it's `/blog`.

It's recommended to have a link to this url in the header so users can easily access it.

The url of pages related to the blog will follow the following patterns:

- `[blog_url]` - List of posts.

- `[blog_url]/[post_slug]` - Post page.

- `[blog_url]/tag/[tag_slug]` - Tag page.

- `[blog_url]/author/[user_slug]` - Author page.

### RSS feed URL

The url of the RSS feed relative to the website. When empty the RSS will be disabled.

### Theme

The theme that will be used to render the website. Relative to the views directory.

### Items per page

The number of items that will be displayed per page in the blog.

### System language

The language that will be used to render the website (including the admin panel). It must be a valid iso 639-1 code (e.g., `en` or `es`).

### Date format

The format that will be used to render dates in the website. It must be a valid [ICU Date](https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax) format.

### Timezone

Server's timezone.

### Maintenance mode

If enabled, the website will be in maintenance mode. This means that only users logged in the admin panel will be able to access the website, other users will see a maintenance page when accessing the website.

## Meta

### Meta Title

The meta title of the website. Shown in search engines.

### Description

The description of the website. Shown in search engines.

### Meta description

The meta description of the website. Shown in search engines.

### Meta keywords

The meta keywords of the website. Used by search engines.

## Permissions

This section can be used to modify the default permissions of the admin roles.

## Data

### Download database

Download the database as a json file. This is useful to make backups of the website.

### Upload database

Upload a json file containing the new database data. This is useful to restore backups of the website.

### Views counter

When disabled, the views counter will not be updated when a post or page is visited. This means that the views counter will always be 0.

The `Reset views count` button will reset the views counter of all posts and pages to 0.

## Advanced

### Session lifetime

PHP Session lifetime in seconds (e.g. 3600 = 1 hour).

### Session SameSite cookie

Value for the PHP session SameSite cookie. This controls whether or not a cookie is sent with cross-site requests, providing some protection against cross-site request forgery attacks (CSRF).

### Display errors

Display PHP errors on the website. It's recommended to enable this option ONLY in non-production environments. For it to work properly, the Log errors option must also be enabled.

### Log errors

Log PHP errors into the defined log file or not.

### Error log filename

Path of the file where logs will be written. Relative to the Aurora root directory (e.g. app/aurora.log).

## Code

The fields in this section can contain JS, CSS, HTML and even PHP code.

Fields in this section are not sanitized, so it's recommended to only add code from trusted sources.

### Site header

Code that will be added to the header of the website. This is useful to add custom css or js.

### Site footer

Code that will be added to the footer of the website. This is useful to add custom css or js.

### Posts code

Code that will be added at the bottom of all posts pages. This is useful to add something like a comments section via js. The `$post` PHP variable will be available in this code and will contain all the post data.

## Update

This section can be used to update Aurora to the latest compatible version.