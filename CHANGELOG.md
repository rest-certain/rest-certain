# REST Certain Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.6.0 - TBD

### Added

- Nothing.

### Changed

- Rename `bodyPath()` to `path()` for clarity and consistency.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.5.0 - 2025-05-11

### Added

- Print HTTP request/response exchange along with test failure messages.

- Set the `Content-Length` header automatically, if not already set.

### Changed

- Disallow multiple header values for so-called "singleton fields."

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.4.0 - 2025-05-10

### Added

- Support ability to extract values after making assertions

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.0 - 2025-05-09

### Added

- Set JSON Schema configuration through a dedicated Config class

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Use instance property instead of static variable for HTTP factory on Config

- Properly load external JSON Schema URLs that are referenced in schema docs

## 0.2.0 - 2025-05-08

### Added

- Calculate request time in milliseconds

- Implement "pretty print" functionality for JSON response bodies

- Set a default `User-Agent` string, if not set by the user

- Support matching JSON Schemas

### Changed

- Mark all exception class as final

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.0 - 2025-05-06

Preview release.

### Added

- Port of basic functionality from [REST Assured](https://github.com/rest-assured/rest-assured).

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
