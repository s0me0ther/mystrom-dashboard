CREATE TABLE IF NOT EXISTS "switch"
(
    id          INTEGER             not null primary key autoincrement,
    name        varchar(255)        not null,
    ip          varchar(255)        not null,
    active      bool default false  not null,
    sort        integer             not null,
    is_input    bool default 0      not null
);

CREATE TABLE IF NOT EXISTS "switch_data"
(
    id          INTEGER not null primary key autoincrement,
    switch_id   integer not null,
    power       REAL    not null,
    ws          REAL    not null,
    temperature REAL    not null,
    created     integer not null
);

CREATE INDEX switch_data_created_index
    on switch_data (created);
CREATE INDEX switch_data_switch_id_index
    on switch_data (switch_id);

CREATE TABLE IF NOT EXISTS "migrate"
(
    name    varchar(255)    not null,
    created integer         not null
);
