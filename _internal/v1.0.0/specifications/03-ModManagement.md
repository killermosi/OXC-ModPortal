# Mod Management

## Overview

Mod management is the pivotal feature of the Portal.

Since mods are the primary resource offered by (and sought on) the Portal,
mod management must be easy and intuitive for the mod authors.

## Mod system

A mod is composed of several elements:

* Name
* Description
* Gallery
* Versions
    * Files
    * Changelog

### Mod versions

A mod entry may contain multiple versions of the mod it represents, for both
practical and historical reasons.

Each mod version contains one or more files for download (usually ZIP archive)
and a changelog indicating what changed in the respective version.

## Mod editing

There is no separate editing interface, editing will be done "in place", using
Bootstrap pop-ups to present editors for various elements of a mod

## URL format

The URL format for a mod is as follows:

`portal.url/mod/<slug>`

The `<slug>` is a unique text value generated from the mod title when the mod
is created. It identifies the mod while presenting a mod summary (the title)
to a person viewing the URL, without having to open it in a browser.

Notes:

* The slug is not editable (either by the author or by an administrator)
* The slug will not change if the mod title is changed
