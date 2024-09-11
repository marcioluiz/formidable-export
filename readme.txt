=== Formidable Export ===
Contributors: Márcio Luiz
Tags: formidable forms, export, wp-cli, csv
Requires at least: 6.6
Tested up to: 6.6.1
Requires PHP: 8.3
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin to export Formidable Form entries to a CSV file via WP-CLI, with optional date range filtering and customizable file paths.

== Description ==

Formidable Export CLI Plugin allows you to export entries from Formidable Forms into a CSV file using WP-CLI. You can specify the form ID, custom file path, and filter entries by a date range.

**Key Features:**
- Export form entries to a CSV file via WP-CLI.
- Support for custom file paths.
- Filter entries by date range (start and end dates).

== Installation ==

1. **Upload the Plugin:**
   - Download or clone the repository.
   - Upload it to your WordPress `/wp-content/plugins/` directory.

2. **Activate the Plugin:**
   - Go to the WordPress admin dashboard.
   - Navigate to **Plugins** -> **Installed Plugins**.
   - Activate the **Formidable Export CLI** plugin.

3. **Ensure Correct Permissions:**
   - Ensure that the directory where you want to save CSV files is writable by the `www-data` user.
   - Set permissions using:
     ```
     sudo chown -R www-data:www-data /path/to/directory
     sudo chmod -R 755 /path/to/directory
     ```

== Usage ==

This plugin adds a new WP-CLI command to export entries from Formidable Forms.

### Exporting All Form Entries
```bash
wp formidable export_csv --form_id=<form_id>

**Example:**

```bash
wp formidable export_csv --form_id=123

### Export with Custom File Path
```bash
wp formidable export_csv --form_id=<form_id> --file_path=<file_path>

**Example:**

```bash
wp formidable export_csv --form_id=123 --file_path=/var/www/html/wp-content/uploads/formidable-export.csv

### Export with Date Range Filtering

Filter entries by start and/or end date in YYYY-mm-dd format.

#### Export with Start and End Date:

```bash
wp formidable export_csv --form_id=<form_id> --start-date=<start-date> --end-date=<end-date>

**Example:**

```bash
wp formidable export_csv --form_id=123 --start-date=2023-01-01 --end-date=2023-12-31

#### Export with Start Date Only:

```bash
wp formidable export_csv --form_id=<form_id> --start-date=<start-date>

**Example:**

```bash
wp formidable export_csv --form_id=123 --start-date=2023-01-01

#### Export with End Date Only:

```bash
wp formidable export_csv --form_id=<form_id> --end-date=<end-date>

**Example:**

```bash
wp formidable export_csv --form_id=123 --end-date=2023-12-31


== Frequently Asked Questions ==

= Do I need any special permissions to use this plugin? =

Yes, make sure the directory where the CSV file is being written is writable by the www-data user. If you encounter a “Permission Denied” error, verify the permissions using:

```bash
sudo chown -R www-data:www-data /path/to/directory
sudo chmod -R 755 /path/to/directory

= What happens if the file cannot be created? =

If the file cannot be created (due to permission issues or an invalid file path), the plugin will display an error message and the process will terminate.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==

= 0.1.2 =

• Added support for --start-date and --end-date parameters.

= 0.1.1 =

• Added support for custom file paths using the --file_path parameter.

= 0.1.0 =

• Initial release with basic export functionality.

== License ==

This plugin is licensed under the GPLv2 or later. See the GPLv2 License for details.
