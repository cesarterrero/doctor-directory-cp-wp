# Doctor Directory

WordPress plugin for managing a national doctor directory. Built as part of a technical assessment for a Web Programmer position.

Administrators can create, edit, deactivate, and delete doctor records from the WP admin panel. A shortcode displays the public directory on any page.

## Stack

PHP, MySQL, jQuery, WordPress

## Installation

Upload the plugin folder to `/wp-content/plugins/` and activate it from the WordPress admin. The database table and sample data are created automatically on activation.

## Shortcode

Place `[doctor_directory]` on any page to display the list of active doctors.

## Features

- Full CRUD with server-side and client-side validation
- Activate/deactivate doctors without deleting records
- AJAX delete with modal confirmation
- Live search with match highlighting
- Auto-seeding on fresh install

## Security

Nonces on all forms, parameterized queries via `$wpdb->prepare()`, input sanitization, output escaping, and capability checks on every admin action.

## Author

Cesar Bolivar — [linkedin.com/in/cesarbolivardev](https://linkedin.com/in/cesarbolivardev)
