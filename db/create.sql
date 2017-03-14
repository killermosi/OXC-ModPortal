/*
 * Copyright © 2016-2017 OpenXcom Mod Portal Contributors
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
    user_id int(10) not null auto_increment comment 'Internal identifier',
    is_orphan tinyint(1) not null default 0 comment 'If this user is orphan - it does not exist on the forum',
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
    mod_id int(10) not null auto_increment comment 'Internal identifier',
    user_id int(10) not null comment 'The owner identifier',
    is_published tinyint(1) not null default 0 comment 'If this mod is published',
    base_game tinyint(1) not null default 0 comment 'Base game for this mod: 0 - UFO, 1 - TFTD',
    title varchar(128) not null comment 'Mod title',
    description text null default null comment 'Mod description',
    slug varchar(128) not null unique comment 'A web-friendly URL identifier',
    creation_date datetime not null comment 'The date and time when this mod was created',
    background varchar(32) null default null comment 'MD5 hash of the image to use for page background',
    primary key (mod_id)
) engine=InnoDB default charset=utf8 comment 'Basic mod data';