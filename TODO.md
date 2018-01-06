# TODO

* Update the oAuth system to consider member banned status
* Investigate proper oAuth protocol
* Enable cache for Doctrine (for production only)
* Slim out bootstrap, by removing unused components and their dependencies (eg: dropdown requires popper.js)
* Set proper cache, etag and other headers for images/backgrounds/resources
* Keep cache directory in sync with the mod data: rename on mod slug change, update cached image names on image rename,
  delete cache directory on mod removal
* Implement Etag header for images