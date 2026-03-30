# VideoShare v1

VideoShare is a lightweight PHP + MySQL video sharing prototype with:

- Bootstrap-based responsive UI
- Dark/light theme switch
- Danish/English language support
- User login/register
- Comment system with replies
- AJAX voting for comments (upvote / dislike)
- Admin panel for comment moderation and video management
- Video overview with pagination
- Sorting by most comments + likes first (dislikes lower priority)
- robots.txt and sitemap.xml included
- Auto-generated meta tags from video title and description
- Optional thumbnail upload, with ffmpeg-based thumbnail generation if available

## Default admin account

- Username: `admin`
- Password: `password`

Change this immediately in production.

## Requirements

- PHP 8.1+
- MySQL / MariaDB
- Apache or Nginx
- mod_rewrite recommended
- `pdo_mysql` enabled
- Optional: `ffmpeg` installed on the server for automatic thumbnail generation

## Installation

1. Create a MySQL database.
2. Import `database.sql`.
3. Copy the project to your web root.
4. Edit `includes/config.php` and set your database credentials.
5. Ensure these folders are writable:
   - `uploads/videos/`
   - `uploads/thumbs/`
6. Open the site in your browser.
7. Log in with the default admin account and change the password.

## Folder overview

```text
videoshare_v1/
├─ admin/
├─ ajax/
├─ assets/
├─ includes/
├─ lang/
├─ uploads/
├─ videos/
├─ index.php
├─ video.php
├─ login.php
├─ register.php
├─ logout.php
├─ upload.php
├─ database.sql
├─ robots.txt
└─ sitemap.xml
```

## Notes

- The code aims to be understandable and modifiable rather than framework-heavy.
- Thumbnail auto-generation attempts to use ffmpeg if available. If not, a JPG/PNG upload can be used instead.
- The sitemap is static by default. You can extend `sitemap.php` later to generate dynamically.

## Security notes

This is a strong prototype, not a fully hardened production platform. Before public deployment, you should:

- move config secrets out of the web root
- force HTTPS
- add CSRF protection more broadly
- add rate limiting and stronger validation
- rotate the default admin password immediately
- review upload restrictions carefully

## License

No license file is included.
