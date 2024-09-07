## Pages

`/admin/pages`

Pages are the main way to publish static content on your website. They are usually used for the "About" or "Contact" pages.

Pages use HTML as the markup language.

A page can have its content defined in the WYSIWYG editor in the admin or in a static file within the `app/views/themes/[theme]` directory (where `[theme]` refers to the theme choosen in the /admin/settings page).

_The static file content will take precedence over the WYSIWYG editor content when the 'Static page' option is on._

Pages are only ever published on the slug which is given to them, and do not automatically appear anywhere on the website. The only way people can find them is if you create a [link](#links) to them.

## Posts

`/admin/posts`

Posts are the primary entry-type and represent the majority of data in the website.

Posts use HTML as the markup language and can be assigned to a single user and/or multiple tags.

Posts have a "Publish date" which is not only used for sorting when listing them, but also to set the date they will be made available on the website.

## Media

`/admin/media`

Media refers to all files within the content directory, by default it is `public/content`.

The content directory is not versioned and is supposed to have public files like images, audios and videos used by pages and posts in the website.

When uploading new images for posts or pages via the WSYSIWYG editor, they will be saved within the content directory in a subdirectory that follows the pattern `[year]/[month]` where year refers to the current year and month refers to the current month.

## Links

`/admin/links`

Links are the options that show up in your website header.

These are commonly used to let users navigate between the different pages.

A link can point to a relative path (e.g., `/about`) or an absolute/external path (e.g., `https://www.google.com`).

Each link has an order which defines its position in the header, it must be an integer number. Links are ordered from lowest to highest.

## Tags

`/admin/tags`

Tags are the primary taxonomy for categorizing posts.

Each post can have multiple tags and each tag can have multiple posts.

The tags are displayed at the top of each post and can be clicked to view all posts with that tag.

The tags url is `/[blog_url]/tags/[tag_slug]`.

## Users

`/admin/users`

Users have access to the admin area. Each user has a role, which determines what they can do.

Disabled users cannot log in to the admin area or do anything.

Users that are logged in can access the public website even if it's in maintenance mode and view draft content like pages and posts.

### Roles

The roles and their default permissions are:

- **Contributor** - Can only create and edit its own posts but not publish them.

- **Editor** - Can create, edit, delete and publish links, media files, tags, pages and posts.

- **Admin** - Same as editor but also can create, edit, delete and impersonate users.

- **Owner** - Same as admin but also can edit the website settings and update Aurora.

### Changing permissions

You can change the permissions of each role in the admin area by going to **Settings** > **Permissions** (/admin/settings#permissions).

