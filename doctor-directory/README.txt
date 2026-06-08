=== Doctor Directory ===
Version: 1.0.0
Author: Cesar Bolivar
License: GPL-2.0+

== Description ==

WordPress plugin for managing a national doctor directory. Administrators
can add, edit, deactivate and delete doctor records from the WP admin panel.
A shortcode displays the active directory on any page.

== Requirements ==

- WordPress 6.0+
- PHP 7.4+
- MySQL 5.7+

== Installation ==

1. Upload the doctor-directory folder to /wp-content/plugins/
2. Activate the plugin from the Plugins screen

The database table and 5 sample records are created automatically on activation.

== Usage ==

Go to Doctor Directory in the admin sidebar to manage records.

To display the public directory on any page, use the shortcode:
[doctor_directory]

Only active doctors are shown in the public directory.
Deactivating a doctor hides them from the shortcode without deleting the record.

== Database ==

Table: {prefix}dd_doctors
Fields: id, full_name, email (unique), address, status, created_at, updated_at

See sql/schema.sql for the full schema and sample data.

== Uninstall ==

Deactivating the plugin keeps all data intact.
Deleting the plugin permanently removes the table and all records.

== Author ==

Cesar Bolivar
cesarbolivar.bt@gmail.com
linkedin.com/in/cesarbolivardev
