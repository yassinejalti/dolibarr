ALTER TABLE llx_customstock ADD UNIQUE KEY uk_customstock_ref(ref);
ALTER TABLE llx_customstock ADD KEY idx_customstock_fk_project(fk_project);
ALTER TABLE llx_customstock ADD KEY idk_customstock_fk_user_author(fk_user_create);
ALTER TABLE llx_customstock ADD KEY idk_customstock_fk_user_modify(fk_user_modify);
ALTER TABLE llx_customstock ADD KEY idk_customstock_fk_user_valid(fk_user_valid);

ALTER TABLE llx_customstock ADD CONSTRAINT fk_customstock_fk_project FOREIGN KEY (fk_project) REFERENCES llx_projet (rowid);
ALTER TABLE llx_customstock ADD CONSTRAINT fk_customstock_fk_user_author FOREIGN KEY (fk_user_create) REFERENCES llx_user (rowid);
ALTER TABLE llx_customstock ADD CONSTRAINT fk_customstock_fk_user_modify FOREIGN KEY (fk_user_modify) REFERENCES llx_user (rowid);
ALTER TABLE llx_customstock ADD CONSTRAINT fk_customstock_fk_user_valid FOREIGN KEY (fk_user_valid) REFERENCES llx_user (rowid);
