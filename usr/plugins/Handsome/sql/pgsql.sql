CREATE TABLE typecho_links
(
    "lid"       SERIAL PRIMARY KEY,
    "name"      varchar(200) default NULL,
    url         varchar(200) default NULL,
    sort        varchar(200) default NULL,
    image       varchar(200) default NULL,
    description varchar(200) default NULL,
    "user"      varchar(200) default NULL,
    "order"     INTEGER      default '0'
);
