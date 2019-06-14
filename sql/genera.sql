-- ======================================================================
-- ===   Sql Script for Database : MySQL db
-- ===
-- === Build : 67
-- ======================================================================

SET GLOBAL innodb_flush_log_at_trx_commit=0;
-- ======================================================================

DROP TABLE IF EXISTS input_cuts;
DROP TABLE IF EXISTS inputs;
DROP TABLE IF EXISTS importance_cuts;
DROP TABLE IF EXISTS importances;
DROP TABLE IF EXISTS propierties;
DROP TABLE IF EXISTS cuts;
DROP TABLE IF EXISTS sets;
DROP TABLE IF EXISTS variables;
DROP TABLE IF EXISTS effects;
DROP TABLE IF EXISTS aggregation_cuts;
DROP TABLE IF EXISTS aggregations;
DROP TABLE IF EXISTS aggregators;
DROP TABLE IF EXISTS effect_propierties;
DROP TABLE IF EXISTS factors;
DROP TABLE IF EXISTS actions;
DROP TABLE IF EXISTS projects_users;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS groups_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS groups;
DROP TABLE IF EXISTS users;

-- ======================================================================

CREATE TABLE users
  (
    id             int            not null auto_increment,
    email_address  varchar(255)   unique not null,
    password       varchar(255)   not null,
    active         char(1)        not null,
    created        datetime       not null,
    modified       datetime       not null,
    firstname      varchar(255),
    lastname       varchar(255),

    primary key(id)
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE groups
  (
    id        int            not null auto_increment,
    name      varchar(255)   unique not null,
    created   datetime       not null,
    modified  datetime       not null,

    primary key(id)
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE permissions
  (
    id      int              not null auto_increment,
    name    varchar(255)     unique not null,
    action  enum('a', 'b')   not null,
    object  varchar(255)     not null,

    primary key(id)
  )
 ENGINE = InnoDB;

-- ----------------------------------------------------------------------

ALTER TABLE permissions MODIFY COLUMN action ENUM('CREATE','READ','UPDATE','DELETE');

-- ======================================================================

CREATE TABLE groups_permissions
  (
    id             int       not null auto_increment,
    group_id       int       not null,
    permission_id  int       not null,
    active         tinyint   not null default 0,

    primary key(id),

    foreign key(group_id) references groups(id) on update CASCADE on delete CASCADE,
    foreign key(permission_id) references permissions(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE projects
  (
    id           int            not null auto_increment,
    name         varchar(255)   not null,
    description  text,
    created      datetime       not null,
    modified     datetime       not null,

    primary key(id)
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE projects_users
  (
    id          int   not null auto_increment,
    project_id  int   not null,
    user_id     int   not null,
    group_id    int   not null,

    primary key(id),

    foreign key(project_id) references projects(id) on update CASCADE on delete CASCADE,
    foreign key(user_id) references users(id) on update CASCADE on delete CASCADE,
    foreign key(group_id) references groups(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE actions
  (
    id           int            not null auto_increment,
    name         varchar(255)   not null,
    description  text,
    level        int            not null,
    project_id   int            not null,
    action_id    int,

    primary key(id),

    foreign key(project_id) references projects(id) on update CASCADE on delete CASCADE,
    foreign key(action_id) references actions(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE factors
  (
    id             int            not null auto_increment,
    name           varchar(255)   not null,
    description    text,
    level          int            not null,
    weight         float          not null default 0.0,
    family_weight  float          not null default 0.0,
    project_id     int            not null,
    factor_id      int,

    primary key(id),

    foreign key(project_id) references projects(id) on update CASCADE on delete CASCADE,
    foreign key(factor_id) references factors(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE effect_propierties
  (
    id           int            not null auto_increment,
    name         varchar(255)   not null,
    description  text,
    nature       int            not null,
    weight       float          not null,
    theta        float          not null,
    project_id   int            not null,

    primary key(id),

    foreign key(project_id) references projects(id) on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE aggregators
  (
    id           int            not null auto_increment,
    name         varchar(255)   not null,
    description  text,
    importance   char(1)        not null,
    equation     varchar(255)   not null,
    project_id   int            not null,

    primary key(id),

    foreign key(project_id) references projects(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE aggregations
  (
    id             int    not null auto_increment,
    aggregator_id  int    not null,
    factor_id      int    not null,
    action_id      int    not null,
    description    text,

    primary key(id),

    foreign key(aggregator_id) references aggregators(id) on update CASCADE on delete CASCADE,
    foreign key(factor_id) references factors(id) on update CASCADE on delete CASCADE,
    foreign key(action_id) references actions(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE aggregation_cuts
  (
    id              int     not null auto_increment,
    alpha           float   not null,
    L               float   not null,
    R               float   not null,
    aggregation_id  int,

    primary key(id),

    foreign key(aggregation_id) references aggregations(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE effects
  (
    id           int            not null auto_increment,
    name         varchar(255)   not null,
    description  text,
    nature       tinyint,
    project_id   int            not null,
    action_id    int            not null,
    factor_id    int,

    primary key(id),

    foreign key(project_id) references projects(id) on update CASCADE on delete CASCADE,
    foreign key(action_id) references actions(id) on update CASCADE on delete CASCADE,
    foreign key(factor_id) references factors(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE variables
  (
    id                   int            not null auto_increment,
    name                 varchar(255)   not null,
    description          text,
    minimum              float          not null,
    maximum              float          not null,
    aggregator_id        int,
    effect_propierty_id  int,

    primary key(id),

    foreign key(aggregator_id) references aggregators(id) on update CASCADE on delete CASCADE,
    foreign key(effect_propierty_id) references effect_propierties(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE sets
  (
    id           int            not null auto_increment,
    label        varchar(255)   not null,
    variable_id  int,

    primary key(id),

    foreign key(variable_id) references variables(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE cuts
  (
    id      int     not null auto_increment,
    alpha   float   not null,
    L       float   not null,
    R       float   not null,
    set_id  int     not null,

    primary key(id),

    foreign key(set_id) references sets(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE propierties
  (
    id                   int   not null auto_increment,
    effect_id            int,
    effect_propierty_id  int,

    primary key(id),

    foreign key(effect_id) references effects(id) on update CASCADE on delete CASCADE,
    foreign key(effect_propierty_id) references effect_propierties(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE importances
  (
    id           int    not null auto_increment,
    effect_id    int    not null,
    description  text,

    primary key(id),

    foreign key(effect_id) references effects(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE importance_cuts
  (
    id             int     not null auto_increment,
    alpha          float   not null,
    L              float   not null,
    R              float   not null,
    importance_id  int     not null,

    primary key(id),

    foreign key(importance_id) references importances(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE inputs
  (
    id            int     not null auto_increment,
    description   text,
    type          int     not null,
    crisp         float   not null,
    L             float   not null,
    R             float   not null,
    set_id        int     not null,
    modifier      int,
    propierty_id  int,

    primary key(id),

    foreign key(propierty_id) references propierties(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

CREATE TABLE input_cuts
  (
    id        int     not null auto_increment,
    alpha     float   not null,
    L         float   not null,
    R         float   not null,
    input_id  int     not null,

    primary key(id),

    foreign key(input_id) references inputs(id) on update CASCADE on delete CASCADE
  )
 ENGINE = InnoDB;

-- ======================================================================

