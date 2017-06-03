truncate atw_io;
alter table atw_io AUTO_INCREMENT=1000;
truncate atw_item;
alter table atw_item AUTO_INCREMENT=1000;
truncate atw_po;
alter table atw_po AUTO_INCREMENT=1000;
truncate atw_po_item;
alter table atw_po_item AUTO_INCREMENT=1000;
truncate atw_po_record;
alter table atw_po_record AUTO_INCREMENT=1000;
truncate atw_supplier_info;
alter table atw_supplier_info AUTO_INCREMENT=1000;

truncate atw_supplier_qualification;
alter table atw_supplier_qualification AUTO_INCREMENT=1000;
truncate atw_supplier_tendency;
alter table atw_supplier_tendency AUTO_INCREMENT=1000;
truncate atw_system_user;
alter table atw_system_user AUTO_INCREMENT=1000;
truncate atw_system_log;
alter table atw_system_log AUTO_INCREMENT=1000;
truncate atw_u9_pr;
alter table atw_u9_pr AUTO_INCREMENT=1000;
truncate atw_u9_supplier;
alter table atw_u9_supplier AUTO_INCREMENT=1000;
truncate atw_system_banner;
alter table atw_system_banner AUTO_INCREMENT=1000;
INSERT INTO `db_atw_wg`.`atw_system_banner` (`id`, `name`, `url`, `src`, `sort`, `create_at`, `update_at`) VALUES ('1000', '安特威物供', NULL, 'http://opmnz562z.bkt.clouddn.com/f215fe93ee5186e9/b21e7e6e85a11cb8.jpg', NULL, '0', '0');
INSERT INTO `db_atw_wg`.`atw_system_banner` (`id`, `name`, `url`, `src`, `sort`, `create_at`, `update_at`) VALUES ('1001', '安特威物供', NULL, 'http://opmnz562z.bkt.clouddn.com/ec935889543f9851/7998afa950b272b7.png', NULL, '0', '0');

truncate atw_ask_reply;
alter table atw_ask_reply AUTO_INCREMENT=1000;

truncate atw_u9_sup_item;
