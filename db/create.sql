create table user (
    user_id int(10) not null auto_increment comment 'Internal identifier',
    member_id int(10) not null unique comment 'Forum member identifier',
    authentication_token varchar(64) default null comment 'Forum authentication token',
    real_name varchar(128) default null comment 'Display name',
    personal_text varchar(128) default null comment 'Personal text',
    is_administrator tinyint(1) default 0 not null comment 'If the user is an administrator',
    avatar_url varchar(256) default null comment 'URL pointing to the member avatar',
    last_token_check_date datetime not null default '1970-01-01' comment 'The last date and time when the authentication token was validated',
    last_detail_update_date datetime not null default '1970-01-01' comment 'The last date and time when the member details were updated',
    primary key (user_id),
    index idx_member_id (member_id)
) engine=InnoDB default charset=utf8 comment 'Users in the system';