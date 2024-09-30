CREATE TABLE llx_customstock (
    rowid INT PRIMARY KEY AUTO_INCREMENT,
    ref VARCHAR(25) NOT NULL,
    fk_project INT,
    object_demande TEXT,
    date_demande DATETIME,
    type_demande INT,
    desired_date DATETIME,
    fk_warehouse INT,
    fk_user_create INT,
    fk_user_modify INT,
    fk_user_valid INT,
    date_valid DATETIME,
    date_creation DATETIME,
    fk_statut INT,
    tms TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    note_private TEXT,
    note_public TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
