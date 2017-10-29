# laravel-std-admin
Admin panel backbone for standard laravel projects.

**Disclaimer:** this package and readme file are both incomplete beyond wildest imagination available in our multiverse. Abundant amounts of discretion is advised.

# Goals

- **standard:** standard tasks will completely be handled by the package, in a standard way.
- **fallback:** painless and partial fallback to manual mode.
    - instead of trying to cover all the edge cases, package aims to cover the basics and provide excellent partial fallback options.
    - disabling auto generated content of your choice and go with manual implementation for any choosen module.
        - routes
        - views
        - controller methods
        - whole sections
- **agnosticity:** provide module-agnostic helpers for common CMS functions.
    - package relies on these module-aware and module-agnostic utilities to provide auto generated content.
    - but another important role of the utilities is to help you override parts of your choice much easier.
    - utilities would also help you to develop reusable partials and modules.


# Capabilities

## Current


- route generation
    - function based route generation
        - index
        - create
        - update
        - delete
        - restore



- standard index pages
    - sorting
    - one-column text filtering
    - boolean toggles
    - deletables



- deletables
    - deleting from index pages
    - standard trashed pages
        - standard-column indexing
        - restoring

## Planned

- generic
    - ability to use either config files or controller constructs for module settings



- index generator
    - relationship columns
    - bool labels
        - on text, off-text, on-style, off-style
    - manual injection of $rows


# Default routes

- user
    - login
    - logout
- utilities
    - sorting
    - editable


# Module structure

## models

### model sturctures


- index pages
    - filtering: models *must* have;
        - `filter` scope
    - ordering: models *may* have;
        - `order` field
        - `order()` scope



- deleting
    - soft deletes