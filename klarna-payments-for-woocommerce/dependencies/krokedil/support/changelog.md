# Changelog

All notable changes of krokedil/support are documented in this file.

The format is based on [Keep a Changelog](https://kepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

------------------

## [1.1.2] - 2026-11-03

### Changed

* The "Export order for support" JSON export now applies the `krokedil_wp_api_mask_log_data` filter from krokedil/wp-api, using the log context as its slug. The same filter can disable masking for both the plugin's logs and the export.

## [1.1.1] - 2026-10-27

### Fixed

* The "Export order for support" button now includes the plugin's log entries for the order, as intended.

## [1.1.0] - 2026-10-16

### Added

* Added an "Export order for support" button for the admin order page. It downloads a JSON file with the order data, order notes, the WooCommerce system status report and any log entries that match the order. Plugins add the button with `OrderSupport::add_export_order_button()`.

### Fixed

* Viewing the WooCommerce system status report no longer writes a PHP deprecation notice (or an undefined array key warning) to the error log when a request log entry has no error message.

## [1.0.4] - 2026-03-30

### Fixed

* The $form_field variable can sometimes be missing keys that are available in the $setting array when "MU"-plugins are used. Added a check to ensure that the keys exist in both arrays before attempting to access them, preventing potential undefined key errors.

## [1.0.3] - 2026-03-30

### Changed
* Explicitly disabled autoloading the system report option to prevent it from being loaded on every request, as it is only needed when issuing a request, and viewing the system report.

## [1.0.2] - 2025-10-08

### Fixed

* A fatal error occurred due to illegal array offset when attempting to delete outdated system report in the woocommerce_cleanup_logs loop.

## [1.0.1] - 2025-06-10

### Fixed

* The JSON encoded report in the system report view was decoded twice: once when retrieved, and once when displayed which caused a fatal error. This has been corrected to ensure the report is only decoded once, only when it is about to be displayed.

## [1.0.0] - 2025-05-28

### Added

* Initial release of the package.