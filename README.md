# OpenXcom MOD Portal

## Introduction

This portal is a repository for OpenXcom modifications.

## Features list

### Application

* An open source, community-powered repository that anyone can improve
* Built on the "standard" LAMP stack
* Uses proven technologies, like Zend Framework and Bootstrap

### QOL

- [ ] Common users roster with the OpenXcom forum - no need to create a separate account
- [ ] File versions history for modifications
- [ ] Comments and rating system for modifications (thumbs up/down)
- [ ] Responsive theme for mobile users
- [ ] Large file uploads
- [ ] Search in archives content
- [ ] Markdown and BBCode for comments and descriptions
- [ ] Moderate comments for own mods
- [ ] Advanced features for administrators

## Installation

### Requirements

* PHP
* MySQL
* Apache
* [Git](https://git-scm.com/)
* [Composer](https://getcomposer.org/)

### Getting the source

Just clone this repository in a directory of your choosing:

```bash
$ git clone https://github.com/killermosi/OXC-ModPortal.git
```

### Additional libraries

As mentioned before, some external libraries are used, which need to be added to the project:

```bash
$ cd path/to/project
$ composer install
```


### Database and Configuration

First, create a database, and use the `install/install.sql` file to set it up

Finally, you will need to configure the application. To do so,
open the `config/config.ini` file and fill in the required values.