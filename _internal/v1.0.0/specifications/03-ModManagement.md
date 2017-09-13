# Mod Management

## Overview

Mod management is the pivotal feature of the Portal.

Since mods are the primary resource offered by (and sought on) the Portal,
mod management must be easy and intuitive for the mod authors.

## Mod description

A mod is composed of several elements, some being editable, and some not. They
are detailed below.

### Editable properties
* **Name**: This is the mod name, and the standard searches are done on this
  field only.
* **Summary**: A brief description of the mod.
* **Slug**: The unique "friendly URL" of the mod, used to differentiate
  one mod from another. It is automatically derived from the title when the mod
  is created, but can be changed as needed.
* **Published**: If the mod is published. Mods that are not published will be
  visible only to their authors. Useful when making large changes to a mod.
* **Base Game**: The base game for which the mod was made.
* **Description**: This is pretty self-describing.
* **Gallery**: A visual presentation of the mod. Optional.
* **Tags**: A list of tags associated with the mod, used to place it in one or
  more categories.
* **Files**: One or more files that compose the actual mod, which can be
  downloaded.
* **Changelog**: Details the changes between versions.
* **Homepage**: Where the author provides support for the mod and replies
  to questions and suggestions. Optional.
* **Background**: Customized background that provides some eye-candy for the
  mod. Template provided. Optional.
* **Tags**: Tags are associated with the mod.
* **Order**: The order in which images listed. Only gallery images can
  be re-ordered, standard files are shown in their upload order


### Non-editable properties
The values below are not directly editable by the mod author, though some are
automatically adjusted as the author and/or other users interact with a mod.

* **UUID**: The mod's universal unique identifier, used to aggregate mod data.
* **User ID**: Who owns the mod.
* **Date created**: Date and time when the mod was created.
* **Date updated**: Date and time when the mod was updated.
* **Rating Up**: The number of positive ratings the mod has received.
* **Rating Down**: The number of negative ratings the mod has received.
* **Downloads**: The total number of completed downloads for the mod in general
  and for each individual file

## Resources

Being a complex entity, a mod is described internally as a sum of several
resources, split among the database and the file system.

### Database

In the database, the mod is primarily stored in the `mod_data` table.

```mysql
create table mod_data (
    mod_id varchar(36) not null comment 'The internal identifier',
    user_id int(10) not null comment 'The user identifier',
    is_published tinyint(1) not null default 0 comment 'If the mod is published',
    base_game tinyint(1) not null default 0 comment 'Base game for the mod: 0 - UFO, 1 - TFTD',
    title varchar(128) not null comment 'Mod title',
    summary varchar(256) not null commend 'Mod summary',
    description text null default null comment 'Mod description',
    slug varchar(128) not null unique comment 'A web-friendly URL identifier',
    date_created datetime not null comment 'The date and time when the mod was created',
    date_updated datetime not null comment 'The date and time when the mod was updated',
    downloads int(10) not null default 0 comment 'Completed downloads for the mod',
    primary key (mod_id),
    index idx_slug(slug),
    index idx_is_published(is_published)
) engine=InnoDB default charset=utf8 comment 'Mods list';
```

Additional tables are used to store the associated resources:

```mysql
create table mod_file (
    file_id varchar(36) not null comment 'The internal identifier',
    mod_id varchar(36) not null comment 'The mod identifier',
    type tinyint(1) not null comment 'The file purpose: 0 - downloadable resource, 1 - gallery image, 2 - background image',
    image_order tinyint(2) default 0 comment 'File order, for gallery images',
    name varchar(128) not null comment 'The original file name, must be unique per mod_id and type',
    date_added datetime not null comment 'The date and time when the file was added',
    downloads int(10) not null default 0 comment 'Completed downloads for the file',
    primary key (file_id),
    index idx_mod_id_type (mod_id, type),
    unique unique_mod_id_type_name(mod_id, type, name)
) engine=InnoDB default charset=utf8 comment 'Mod associated files';

create table mod_vote (
    mod_id varchar(36) not null comment 'The mod identifier - UUID',
    user_id int(10) not null comment 'The user identifier',
    vote tinyint(1) not null comment  'The vote type: 0 - negative, 1 - positive',
    primary key (mod_id, user_id)
) engine=InnoDB default charset=utf8 comment 'Mod votes';

create table mod_tag (
    mod_id varchar(36) not null comment 'The mod identifier - UUID',
    tag varchar(32) not null comment 'The tag',
    primary key (mod_id, tag)
) engine=InnoDB default charset=utf8 comment 'Mod associated tags';
```

The associated tag names are stored directly in the `mod_tag` table, to avoid
an additional `join` query.

The list of available tags is stored in a separate table:

```mysql
create table tag (
    tag varchar(32) not null comment 'The tag',
    primary key (tag)
) engine=InnoDB default charset=utf8 comment 'Available tags';
```

When the `tag` table is edited, the `mod_tag` will be updated too.

### Files

The files associated with a mod are stored on disk using their UUID, to avoid
collisions and other issues with the file names themselves.

`/path/to/storage/<mod_uuid>/<file_uuid>`

When served back to the user, the original file name will be presented, with the
exception of the background image which will use the generic name
 `background.png` at all times.

`portal.url/mod/<mod_slug>/file/<file_name.ext>`
`portal.url/mod/<mod_slug>/file/<background.png>`

## Mod management

The `My Mods` page will list a user's mods and allow the owner to
create/update/delete a mod.

`portal.url/my-mods`

### Mod editing

Due to the complexity of a mod, editing will be done in a dedicated page.

`portal.url/edit-mod/<mod_slug>`

The mod slug will be used here to reference the mod. When the mod slug is
changed during an editing session, the owner will be redirected to the new URL.

#### Files management

To allow some flexibility during an edit session, any changes that are done to
the mod files are to be applied only when the user submits the edit form.

* During edit:
    * Deleted files are removed only visually from the edit form.
    * Added files will be uploaded to a temporary location on the server.
    * Changed files will show their changes only visually on the edit form.
* On submit, all the operations will be applied to the actual files and mod
  data:
    * Files marked as deleted will be removed from the database and storage
    * Files uploaded will be moved from the temporary storage location to the
      normal storage location and added to the database
    * Changed files will have their properties updated

### Files upload

Since the mod files are potentially very large, they will be uploaded in chunks.