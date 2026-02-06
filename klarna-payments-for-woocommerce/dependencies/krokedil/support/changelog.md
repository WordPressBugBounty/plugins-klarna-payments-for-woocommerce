# Changelog

All notable changes of krokedil/support are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

------------------
## [1.0.2] - 2025-10-08

### Fixed

* A fatal error occurred due to illegal array offset when attempting to delete outdated system report in the woocommerce_cleanup_logs loop.

## [1.0.1] - 2025-06-10

### Fixed

* The JSON encoded report in the system report view was decoded twice: once when retrieved, and once when displayed which caused a fatal error. This has been corrected to ensure the report is only decoded once, only when it is about to be displayed.

## [1.0.0] - 2025-05-28

### Added

* Initial release of the package.