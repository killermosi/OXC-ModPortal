# Version Overview

## Features

This version contains only the basic features required for a functional mod
portal:

* Users system
* Mod management
* Mod search

## Page structure

The mod portal page structure is as follows (from top to bottom):

 * Top menu
 * Notifications
 * See-through area
 * Page content
 * Footer

### Top menu

Doubling as header, the menu contains links to the actions available to the
currently logged in user.

### Notifications

A small zone in which flash notifications will be displayed.

### See-through area

Since mods may specify a custom background image for the page, this eye candy
feature allows the user to see the page background, so that the mod makers may
present their work in a more artistic manner.

### Page content

The page content is displayed here. This is the only vertically-flexible area
of the page.

### Footer

Several not-that-important links reside here, as well as a link to the project
page.

## Varnish cache

To accommodate the Varnish caching system, several architectural elements are
needed:

 1. Static domain for the resources: **static.openxcom.org**
 1. Normal domain for the application: **mods.openxcom.org**

The static domain will operate in a cookie-less mode, meaning that cookies are
neither expected, nor returned when requesting resources using this domain.

### Page load

The application will load in two stages:

 1. When the user loads the portal for the first time in a browser session,
    no "page content" is loaded
 1. The application automatically  makes an AJAX request to the static domain
    to load the actual page content.

After the page is completely loaded, when the user clicks a link to another
static page (like a mod):

 1. The application will intercept the link, and will make an AJAX request to
    the static domain to get the page data.
 1. The URL will be updated to accommodate for page history navigation

