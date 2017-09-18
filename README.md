# OpenXcom MOD Portal

## Introduction

This portal is a repository for OpenXcom modifications.

## Features list

### Application

* An open source, community-powered repository that anyone can improve.
* Built on the standard LAMP stack.
* Uses proven technologies, like Zend Framework and Bootstrap.

### QOL

* Common users roster with the OpenXcom forum.
* Comments and rating system.
* Responsive theme for mobile users.
* Large file uploads.
* Deep search in archives content.
* Markdown for comments and descriptions.
* Moderate comments for own mods.
* Advanced features for administrators.

## Installation

### Requirements

* PHP
* MySQL
* Apache
* Git
* Composer

### Getting the source

Just clone this repository in a directory of your choosing:

```bash
$ git clone https://github.com/killermosi/OXC-ModPortal.git
```

### Dependencies

Use composer to pull in all dependencies:

```bash
$ cd path/to/project
$ composer install
```

### Database

Create a MySQL database and an associated user. User the `db/create.sql` file
to set it up.

### Apache

Point Apache to the `public` directory to use for public access.

The application uses an `.htaccess` located in the `public` directory to
configure the Apache server. To make use of it, local overrides need to be
enabled in the configuration.

Optionally, since that is the only `.htaccess` file used, its content can be
placed directly in the apache configuration file, if possible. This will speed
up the application a tiny bit, but will require an extra step on subsequent
upgrades, if the `.htaccess` file was changed.

### Configuration

Before using it, you will need to configure the application. To do so,
open the `module/OxcMP/config/config.ini` file and adjust all values as needed.

