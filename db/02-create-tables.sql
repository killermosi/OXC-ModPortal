/*
 * Copyright © 2016-2017 OpenXcom Mod Portal Developers
 *
 * This file is part of OpenXcom Mod Portal.
 *
 * OpenXcom Mod Portal is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OpenXcom Mod Portal is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OpenXcom Mod Portal. If not, see <http://www.gnu.org/licenses/>.
 */

create table user (
    user_id char(36) not null comment 'Internal identifier',
    is_orphan tinyint(1) not null default 0 comment 'If the user is orphan - it does not exist on the forum anymore',
    member_id int(10) not null unique comment 'Forum member identifier',
    authentication_token varchar(64) default null comment 'Forum authentication token',
    real_name varchar(128) default null comment 'Display name',
    personal_text varchar(128) default null comment 'Personal text',
    is_administrator tinyint(1) default 0 not null comment 'If the user is an administrator',
    avatar_url varchar(256) default null comment 'URL pointing to the member avatar',
    last_token_check_date datetime not null default '1970-01-01' comment 'The last date and time when the authentication token was validated',
    last_detail_update_date datetime not null default '1970-01-01' comment 'The last date and time when the member details were updated',
    primary key (user_id)
) engine=InnoDB default charset=utf8 comment 'Users in the system';

create table mod_data (
    mod_id char(36) not null comment 'The internal identifier',
    user_id char(36) not null comment 'The user identifier',
    is_published tinyint(1) not null default 0 comment 'If the mod is published',
    title varchar(64) not null comment 'Mod title',
    summary varchar(128) default null comment 'Mod summary',
    description text default null comment 'Mod description, compiled to HTML',
    description_raw text null default null comment 'Mod description, as entered by the owner',
    slug varchar(128) not null unique comment 'A web-friendly URL identifier',
    date_created datetime not null comment 'The date and time when the mod was created',
    date_updated datetime not null comment 'The date and time when the mod was updated',
    downloads int(10) not null default 0 comment 'Completed downloads for the mod',
    primary key (mod_id),
    index idx_slug(slug),
    index idx_is_published(is_published)
) engine=InnoDB default charset=utf8 comment 'Mods list';

create table mod_file (
    file_id char(36) not null comment 'The internal identifier',
    mod_id char(36) not null comment 'The mod identifier',
    type tinyint(1) not null comment 'The file purpose: 0 - downloadable resource, 1 - gallery image, 2 - background image',
    file_order tinyint(2) default 0 comment 'File order, for gallery images and resources',
    file_version varchar(64) default null comment 'File version, for resources',
    name varchar(128) not null comment 'The original file name, unique per mod_id and type',
    description varchar(512) null comment 'A short file description, used for images as caption and for resources as details',
    date_added datetime not null comment 'The date and time when the file was added',
    downloads int(10) not null default 0 comment 'Completed downloads for the file',
    size int(10) not null default 0 comment 'File size, in bytes',
    primary key (file_id),
    index idx_mod_id (mod_id),
    unique unique_mod_id_type_name(mod_id, type, name)
) engine=InnoDB default charset=utf8 comment 'Mod associated files';

create table mod_vote (
    mod_id char(36) not null comment 'The mod identifier - UUID',
    user_id char(36) not null comment 'The user identifier',
    vote tinyint(1) not null comment  'The vote type: 0 - negative, 1 - positive',
    date datetime not null comment 'Date and time when the vote was cast',
    primary key (mod_id, user_id),
    index idx_mod_id(mod_id)
) engine=InnoDB default charset=utf8 comment 'Mod votes';

create table mod_tag (
    mod_id char(36) not null comment 'The mod identifier - UUID',
    tag varchar(32) not null comment 'The tag',
    primary key (mod_id, tag)
) engine=InnoDB default charset=utf8 comment 'Mod associated tags';

create table tag (
    tag varchar(32) not null comment 'The tag',
    primary key (tag)
) engine=InnoDB default charset=utf8 comment 'Available tags';