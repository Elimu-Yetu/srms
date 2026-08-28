-- Elimu Yetu SRMS backup
-- Taken 2026-08-26 21:19:35
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `skey` varchar(60) NOT NULL,
  `svalue` text DEFAULT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('academic_year','2026');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('attendance_threshold','80');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('cert_signatory_1','name');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('cert_signatory_1_title','Director');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('cert_signatory_2','name');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('cert_signatory_2_title','Lead Instructor');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('id_card_validity_months','3');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('kitchen_meal_label','Afternoon meal (plates)');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('kitchen_tea_label','Morning tea (cups)');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('org_address','Elimu Yetu Kituo cha Jamii, Tanzania');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('org_email','info@elimuyetu.org');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('org_motto','Ninaweza, nitafanya, najiamini');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('org_name','ELIMU YETU ORGANIZATION');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('org_phone','+255 763 461 722');

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `status` varchar(12) NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_dept_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `departments` (`id`,`code`,`name`,`description`,`manager_id`,`status`,`created_at`) VALUES ('1','ICT','ICT / TEHAMA','Computer literacy, web development and digital skills.','3','active','2026-08-18 23:38:12');
INSERT INTO `departments` (`id`,`code`,`name`,`description`,`manager_id`,`status`,`created_at`) VALUES ('2','HOSP','Hospitality — Hoteli Academy','Kitchen, bakery, food and beverage service training.','4','active','2026-08-18 23:38:12');
INSERT INTO `departments` (`id`,`code`,`name`,`description`,`manager_id`,`status`,`created_at`) VALUES ('3','TAI','Tailoring & Design','Garment construction and fashion design.',NULL,'active','2026-08-18 23:38:12');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `role` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `status` varchar(12) NOT NULL DEFAULT 'active',
  `must_reset` int(11) NOT NULL DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('1','dev','dev@elimuyetu.org','','superadmin','$2y$12$9rP8uUM.3OdejALiTBrBLejclJJjJ7aoXLJ9ZDXp2G0aZyGTZ63My',NULL,NULL,'active','0','2026-08-26 21:08:21','2026-08-18 23:38:12');
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('2','admin','admin@elimuyetu.org','+255 763 461 722','admin','$2y$12$mFDIMBD9JkkBVIUvfofide9WsT//sZAt9t3DV.r1DnPENk41mBn9m',NULL,NULL,'active','0','2026-08-26 12:31:32','2026-08-18 23:38:12');
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('3','Willfredy Mosses','willfredy@elimuyetu.org','+255 763 461 700','manager','$2y$12$Zhr8./81gSzE7phHr3gpW.Q1t05bBe.4kjY9ari9RWbVQ35yrAmZq','1',NULL,'active','0',NULL,'2026-08-18 23:38:12');
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('4','Neema','neema@elimuyetu.org','+255 764 220 118','manager','$2y$12$Zhr8./81gSzE7phHr3gpW.Q1t05bBe.4kjY9ari9RWbVQ35yrAmZq','2',NULL,'active','0',NULL,'2026-08-18 23:38:12');
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('5','James','james@elimuyetu.org','+255 767 711 890','facilitator','$2y$12$dF3ScIw1vB5mmw/hushz5.X0Pyo1cu9pm30.9nCP0FReorYI1K0mO','1',NULL,'active','1','2026-08-26 12:28:34','2026-08-18 23:38:12');
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('6','doe','doe@elimuyetu.org','+255 762 335 441','facilitator','$2y$12$Zhr8./81gSzE7phHr3gpW.Q1t05bBe.4kjY9ari9RWbVQ35yrAmZq','1',NULL,'active','0',NULL,'2026-08-18 23:38:12');
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('7','john','john@elimuyetu.org','+255 755 909 233','facilitator','$2y$12$Zhr8./81gSzE7phHr3gpW.Q1t05bBe.4kjY9ari9RWbVQ35yrAmZq','2',NULL,'active','0',NULL,'2026-08-18 23:38:12');
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('8','diggo','diggo@elimuyetu.org','+255 786 114 552','kitchen','$2y$12$Zhr8./81gSzE7phHr3gpW.Q1t05bBe.4kjY9ari9RWbVQ35yrAmZq',NULL,NULL,'active','0',NULL,'2026-08-18 23:38:12');
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('9','Amina Juma','amina@student.elimuyetu','+255 760 100000','student','$2y$12$tlO4gYfh6yIuKTcphqhp9ODK5DD2YNarECVaEBJI8hk6enETEjHCe','1','1','active','0','2026-08-26 20:53:21','2026-08-18 23:38:12');
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('10','Baraka Mwakyusa','baraka@student.elimuyetu.org','+255 761 104231','student','$2y$12$Zhr8./81gSzE7phHr3gpW.Q1t05bBe.4kjY9ari9RWbVQ35yrAmZq','1','2','active','0',NULL,'2026-08-18 23:38:12');
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('11','Chausiku Mbwana','chausiku@student.elimuyetu.org','+255 762 108462','student','$2y$12$Zhr8./81gSzE7phHr3gpW.Q1t05bBe.4kjY9ari9RWbVQ35yrAmZq','1','3','active','0',NULL,'2026-08-18 23:38:12');

DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int(11) NOT NULL,
  `code` varchar(24) NOT NULL,
  `name` varchar(160) NOT NULL,
  `description` text DEFAULT NULL,
  `duration_weeks` int(11) NOT NULL DEFAULT 12,
  `fee_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `capacity` int(11) NOT NULL DEFAULT 30,
  `facilitator_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(12) NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_course_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `courses` (`id`,`department_id`,`code`,`name`,`description`,`duration_weeks`,`fee_amount`,`capacity`,`facilitator_id`,`start_date`,`end_date`,`status`,`created_at`) VALUES ('1','1','WEB-101','Web Development','Practical, hands-on training delivered at the centre.','24','150000.00','25','5','2026-06-18','2026-12-18','active','2026-08-18 23:38:12');
INSERT INTO `courses` (`id`,`department_id`,`code`,`name`,`description`,`duration_weeks`,`fee_amount`,`capacity`,`facilitator_id`,`start_date`,`end_date`,`status`,`created_at`) VALUES ('2','1','COMP-101','Computer Literacy','Practical, hands-on training delivered at the centre.','12','80000.00','30','6','2026-07-18','2026-10-18','active','2026-08-18 23:38:12');
INSERT INTO `courses` (`id`,`department_id`,`code`,`name`,`description`,`duration_weeks`,`fee_amount`,`capacity`,`facilitator_id`,`start_date`,`end_date`,`status`,`created_at`) VALUES ('3','2','BAKE-201','Bakery & Pastry','Practical, hands-on training delivered at the centre.','16','200000.00','20','7','2026-06-18','2026-10-18','active','2026-08-18 23:38:12');
INSERT INTO `courses` (`id`,`department_id`,`code`,`name`,`description`,`duration_weeks`,`fee_amount`,`capacity`,`facilitator_id`,`start_date`,`end_date`,`status`,`created_at`) VALUES ('4','2','FNB-101','Food & Beverage Service','Practical, hands-on training delivered at the centre.','12','180000.00','20','7','2026-09-18','2026-12-18','active','2026-08-18 23:38:12');

DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `student_no` varchar(24) NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `middle_name` varchar(60) DEFAULT NULL,
  `last_name` varchar(60) NOT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(160) DEFAULT NULL,
  `national_id` varchar(40) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `education_level` varchar(60) DEFAULT NULL,
  `guardian_name` varchar(120) DEFAULT NULL,
  `guardian_phone` varchar(30) DEFAULT NULL,
  `guardian_relation` varchar(40) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `photo` varchar(160) DEFAULT NULL,
  `status` varchar(14) NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `registered_by` int(11) DEFAULT NULL,
  `registered_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_student_no` (`student_no`),
  KEY `ix_students_dept` (`department_id`),
  KEY `ix_students_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `students` (`id`,`student_no`,`first_name`,`middle_name`,`last_name`,`gender`,`dob`,`phone`,`email`,`national_id`,`address`,`education_level`,`guardian_name`,`guardian_phone`,`guardian_relation`,`department_id`,`photo`,`status`,`notes`,`registered_by`,`registered_at`) VALUES ('1','EY-2026-0001','Amina','Hassan','Juma','Female','2008-08-18','+255 760 100000','amina.juma@example.com','199910000000','Mwanza, Tanzania','Form Four','Mzazi Juma','+255 754 100000','Father','1',NULL,'active',NULL,'1','2026-07-09 23:38:12');
INSERT INTO `students` (`id`,`student_no`,`first_name`,`middle_name`,`last_name`,`gender`,`dob`,`phone`,`email`,`national_id`,`address`,`education_level`,`guardian_name`,`guardian_phone`,`guardian_relation`,`department_id`,`photo`,`status`,`notes`,`registered_by`,`registered_at`) VALUES ('2','EY-2026-0002','Baraka','Peter','Mwakyusa','Male','2007-08-18','+255 761 104231','baraka.mwakyusa@example.com','199910077000','Mwanza, Tanzania','Standard Seven','Mzazi Mwakyusa','+255 754 100013','Mother','1',NULL,'active',NULL,'1','2026-07-10 23:38:12');
INSERT INTO `students` (`id`,`student_no`,`first_name`,`middle_name`,`last_name`,`gender`,`dob`,`phone`,`email`,`national_id`,`address`,`education_level`,`guardian_name`,`guardian_phone`,`guardian_relation`,`department_id`,`photo`,`status`,`notes`,`registered_by`,`registered_at`) VALUES ('3','EY-2026-0003','Chausiku','S','Mbwana','Female','2006-08-18','+255 762 108462','chausiku.mbwana@example.com','199910154000','Mwanza, Tanzania','Standard Seven','Mzazi Mbwana','+255 754 100026','Father','1',NULL,'active',NULL,'1','2026-07-11 23:38:12');
INSERT INTO `students` (`id`,`student_no`,`first_name`,`middle_name`,`last_name`,`gender`,`dob`,`phone`,`email`,`national_id`,`address`,`education_level`,`guardian_name`,`guardian_phone`,`guardian_relation`,`department_id`,`photo`,`status`,`notes`,`registered_by`,`registered_at`) VALUES ('4','EY-2026-0004','Daudi','John','Mahenge','Male','2005-08-18','+255 763 112693','daudi.mahenge@example.com','199910231000','Mwanza, Tanzania','Form Four','Mzazi Mahenge','+255 754 100039','Mother','1',NULL,'active',NULL,'1','2026-07-12 23:38:12');
INSERT INTO `students` (`id`,`student_no`,`first_name`,`middle_name`,`last_name`,`gender`,`dob`,`phone`,`email`,`national_id`,`address`,`education_level`,`guardian_name`,`guardian_phone`,`guardian_relation`,`department_id`,`photo`,`status`,`notes`,`registered_by`,`registered_at`) VALUES ('5','EY-2026-0005','Elizabeth','A','Nyoni','Female','2004-08-18','+255 764 116924','elizabeth.nyoni@example.com','199910308000','Mwanza, Tanzania','Standard Seven','Mzazi Nyoni','+255 754 100052','Father','1',NULL,'active',NULL,'1','2026-07-13 23:38:12');
INSERT INTO `students` (`id`,`student_no`,`first_name`,`middle_name`,`last_name`,`gender`,`dob`,`phone`,`email`,`national_id`,`address`,`education_level`,`guardian_name`,`guardian_phone`,`guardian_relation`,`department_id`,`photo`,`status`,`notes`,`registered_by`,`registered_at`) VALUES ('6','EY-2026-0006','Frank','M','Kessy','Male','2003-08-18','+255 765 121155','frank.kessy@example.com','199910385000','Mwanza, Tanzania','Standard Seven','Mzazi Kessy','+255 754 100065','Mother','1',NULL,'active',NULL,'1','2026-07-14 23:38:12');
INSERT INTO `students` (`id`,`student_no`,`first_name`,`middle_name`,`last_name`,`gender`,`dob`,`phone`,`email`,`national_id`,`address`,`education_level`,`guardian_name`,`guardian_phone`,`guardian_relation`,`department_id`,`photo`,`status`,`notes`,`registered_by`,`registered_at`) VALUES ('7','EY-2026-0007','Grace','Daniel','Shirima','Female','2002-08-18','+255 766 125386','grace.shirima@example.com','199910462000','Mwanza, Tanzania','Form Four','Mzazi Shirima','+255 754 100078','Father','2',NULL,'active',NULL,'1','2026-07-15 23:38:12');
INSERT INTO `students` (`id`,`student_no`,`first_name`,`middle_name`,`last_name`,`gender`,`dob`,`phone`,`email`,`national_id`,`address`,`education_level`,`guardian_name`,`guardian_phone`,`guardian_relation`,`department_id`,`photo`,`status`,`notes`,`registered_by`,`registered_at`) VALUES ('8','EY-2026-0008','Hamisi','Ally','Ngassa','Male','2001-08-18','+255 767 129617','hamisi.ngassa@example.com','199910539000','Mwanza, Tanzania','Standard Seven','Mzazi Ngassa','+255 754 100091','Mother','2',NULL,'active',NULL,'1','2026-07-16 23:38:12');
INSERT INTO `students` (`id`,`student_no`,`first_name`,`middle_name`,`last_name`,`gender`,`dob`,`phone`,`email`,`national_id`,`address`,`education_level`,`guardian_name`,`guardian_phone`,`guardian_relation`,`department_id`,`photo`,`status`,`notes`,`registered_by`,`registered_at`) VALUES ('9','EY-2026-0009','Irene','Joseph','Malecela','Female','2000-08-18','+255 768 133848','irene.malecela@example.com','199910616000','Mwanza, Tanzania','Standard Seven','Mzazi Malecela','+255 754 100104','Father','2',NULL,'active',NULL,'1','2026-07-17 23:38:12');
INSERT INTO `students` (`id`,`student_no`,`first_name`,`middle_name`,`last_name`,`gender`,`dob`,`phone`,`email`,`national_id`,`address`,`education_level`,`guardian_name`,`guardian_phone`,`guardian_relation`,`department_id`,`photo`,`status`,`notes`,`registered_by`,`registered_at`) VALUES ('10','EY-2026-0010','Juma','Rashid','Kiwelu','Male','2008-08-18','+255 769 138079','juma.kiwelu@example.com','199910693000','Mwanza, Tanzania','Form Four','Mzazi Kiwelu','+255 754 100117','Mother','2',NULL,'active',NULL,'1','2026-07-18 23:38:12');
INSERT INTO `students` (`id`,`student_no`,`first_name`,`middle_name`,`last_name`,`gender`,`dob`,`phone`,`email`,`national_id`,`address`,`education_level`,`guardian_name`,`guardian_phone`,`guardian_relation`,`department_id`,`photo`,`status`,`notes`,`registered_by`,`registered_at`) VALUES ('11','EY-2026-0011','Kalunde','E','Sanga','Female','2007-08-18','+255 770 142310','kalunde.sanga@example.com','199910770000','Mwanza, Tanzania','Standard Seven','Mzazi Sanga','+255 754 100130','Father','2',NULL,'active',NULL,'1','2026-07-19 23:38:12');
INSERT INTO `students` (`id`,`student_no`,`first_name`,`middle_name`,`last_name`,`gender`,`dob`,`phone`,`email`,`national_id`,`address`,`education_level`,`guardian_name`,`guardian_phone`,`guardian_relation`,`department_id`,`photo`,`status`,`notes`,`registered_by`,`registered_at`) VALUES ('12','EY-2026-0012','Lameck','Y','Mtei','Male','2006-08-18','+255 771 146541','lameck.mtei@example.com','199910847000','Mwanza, Tanzania','Standard Seven','Mzazi Mtei','+255 754 100143','Mother','1',NULL,'active',NULL,'1','2026-07-20 23:38:12');

DROP TABLE IF EXISTS `enrolments`;
CREATE TABLE `enrolments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_on` date NOT NULL,
  `status` varchar(14) NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_enrol` (`student_id`,`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `enrolments` (`id`,`student_id`,`course_id`,`enrolled_on`,`status`,`created_at`) VALUES ('1','1','1','2026-07-11','active','2026-08-18 23:38:12');
INSERT INTO `enrolments` (`id`,`student_id`,`course_id`,`enrolled_on`,`status`,`created_at`) VALUES ('2','2','1','2026-07-12','active','2026-08-18 23:38:12');
INSERT INTO `enrolments` (`id`,`student_id`,`course_id`,`enrolled_on`,`status`,`created_at`) VALUES ('3','3','1','2026-07-13','active','2026-08-18 23:38:12');
INSERT INTO `enrolments` (`id`,`student_id`,`course_id`,`enrolled_on`,`status`,`created_at`) VALUES ('4','4','1','2026-07-14','active','2026-08-18 23:38:12');
INSERT INTO `enrolments` (`id`,`student_id`,`course_id`,`enrolled_on`,`status`,`created_at`) VALUES ('5','5','2','2026-07-15','active','2026-08-18 23:38:12');
INSERT INTO `enrolments` (`id`,`student_id`,`course_id`,`enrolled_on`,`status`,`created_at`) VALUES ('6','6','2','2026-07-16','active','2026-08-18 23:38:12');
INSERT INTO `enrolments` (`id`,`student_id`,`course_id`,`enrolled_on`,`status`,`created_at`) VALUES ('7','7','3','2026-07-17','active','2026-08-18 23:38:12');
INSERT INTO `enrolments` (`id`,`student_id`,`course_id`,`enrolled_on`,`status`,`created_at`) VALUES ('8','8','3','2026-07-18','active','2026-08-18 23:38:12');
INSERT INTO `enrolments` (`id`,`student_id`,`course_id`,`enrolled_on`,`status`,`created_at`) VALUES ('9','9','3','2026-07-19','active','2026-08-18 23:38:12');
INSERT INTO `enrolments` (`id`,`student_id`,`course_id`,`enrolled_on`,`status`,`created_at`) VALUES ('10','10','4','2026-07-20','active','2026-08-18 23:38:12');
INSERT INTO `enrolments` (`id`,`student_id`,`course_id`,`enrolled_on`,`status`,`created_at`) VALUES ('11','11','4','2026-07-21','active','2026-08-18 23:38:12');
INSERT INTO `enrolments` (`id`,`student_id`,`course_id`,`enrolled_on`,`status`,`created_at`) VALUES ('12','12','2','2026-07-22','active','2026-08-18 23:38:12');

DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `enrolment_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `session_date` date NOT NULL,
  `status` varchar(1) NOT NULL,
  `remarks` varchar(160) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_attend` (`enrolment_id`,`session_date`),
  KEY `ix_attend_course_date` (`course_id`,`session_date`)
) ENGINE=InnoDB AUTO_INCREMENT=176 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('1','1','1','2026-07-29','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('2','2','1','2026-07-29','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('3','3','1','2026-07-29','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('4','4','1','2026-07-29','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('5','5','2','2026-07-29','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('6','6','2','2026-07-29','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('7','7','3','2026-07-29','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('8','8','3','2026-07-29','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('9','9','3','2026-07-29','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('10','10','4','2026-07-29','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('11','11','4','2026-07-29','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('12','12','2','2026-07-29','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('13','1','1','2026-07-30','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('14','2','1','2026-07-30','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('15','3','1','2026-07-30','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('16','4','1','2026-07-30','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('17','5','2','2026-07-30','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('18','6','2','2026-07-30','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('19','7','3','2026-07-30','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('20','8','3','2026-07-30','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('21','9','3','2026-07-30','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('22','10','4','2026-07-30','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('23','11','4','2026-07-30','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('24','12','2','2026-07-30','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('25','1','1','2026-07-31','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('26','2','1','2026-07-31','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('27','3','1','2026-07-31','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('28','4','1','2026-07-31','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('29','5','2','2026-07-31','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('30','6','2','2026-07-31','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('31','7','3','2026-07-31','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('32','8','3','2026-07-31','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('33','9','3','2026-07-31','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('34','10','4','2026-07-31','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('35','11','4','2026-07-31','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('36','12','2','2026-07-31','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('37','1','1','2026-08-03','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('38','2','1','2026-08-03','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('39','3','1','2026-08-03','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('40','4','1','2026-08-03','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('41','5','2','2026-08-03','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('42','6','2','2026-08-03','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('43','7','3','2026-08-03','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('44','8','3','2026-08-03','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('45','9','3','2026-08-03','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('46','10','4','2026-08-03','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('47','11','4','2026-08-03','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('48','12','2','2026-08-03','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('49','1','1','2026-08-04','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('50','2','1','2026-08-04','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('51','3','1','2026-08-04','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('52','4','1','2026-08-04','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('53','5','2','2026-08-04','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('54','6','2','2026-08-04','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('55','7','3','2026-08-04','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('56','8','3','2026-08-04','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('57','9','3','2026-08-04','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('58','10','4','2026-08-04','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('59','11','4','2026-08-04','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('60','12','2','2026-08-04','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('61','1','1','2026-08-05','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('62','2','1','2026-08-05','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('63','3','1','2026-08-05','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('64','4','1','2026-08-05','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('65','5','2','2026-08-05','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('66','6','2','2026-08-05','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('67','7','3','2026-08-05','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('68','8','3','2026-08-05','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('69','9','3','2026-08-05','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('70','10','4','2026-08-05','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('71','11','4','2026-08-05','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('72','12','2','2026-08-05','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('73','1','1','2026-08-06','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('74','2','1','2026-08-06','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('75','3','1','2026-08-06','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('76','4','1','2026-08-06','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('77','5','2','2026-08-06','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('78','6','2','2026-08-06','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('79','7','3','2026-08-06','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('80','8','3','2026-08-06','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('81','9','3','2026-08-06','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('82','10','4','2026-08-06','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('83','11','4','2026-08-06','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('84','12','2','2026-08-06','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('85','1','1','2026-08-07','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('86','2','1','2026-08-07','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('87','3','1','2026-08-07','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('88','4','1','2026-08-07','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('89','5','2','2026-08-07','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('90','6','2','2026-08-07','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('91','7','3','2026-08-07','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('92','8','3','2026-08-07','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('93','9','3','2026-08-07','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('94','10','4','2026-08-07','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('95','11','4','2026-08-07','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('96','12','2','2026-08-07','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('97','1','1','2026-08-10','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('98','2','1','2026-08-10','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('99','3','1','2026-08-10','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('100','4','1','2026-08-10','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('101','5','2','2026-08-10','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('102','6','2','2026-08-10','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('103','7','3','2026-08-10','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('104','8','3','2026-08-10','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('105','9','3','2026-08-10','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('106','10','4','2026-08-10','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('107','11','4','2026-08-10','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('108','12','2','2026-08-10','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('109','1','1','2026-08-11','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('110','2','1','2026-08-11','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('111','3','1','2026-08-11','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('112','4','1','2026-08-11','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('113','5','2','2026-08-11','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('114','6','2','2026-08-11','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('115','7','3','2026-08-11','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('116','8','3','2026-08-11','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('117','9','3','2026-08-11','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('118','10','4','2026-08-11','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('119','11','4','2026-08-11','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('120','12','2','2026-08-11','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('121','1','1','2026-08-12','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('122','2','1','2026-08-12','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('123','3','1','2026-08-12','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('124','4','1','2026-08-12','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('125','5','2','2026-08-12','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('126','6','2','2026-08-12','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('127','7','3','2026-08-12','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('128','8','3','2026-08-12','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('129','9','3','2026-08-12','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('130','10','4','2026-08-12','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('131','11','4','2026-08-12','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('132','12','2','2026-08-12','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('133','1','1','2026-08-13','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('134','2','1','2026-08-13','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('135','3','1','2026-08-13','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('136','4','1','2026-08-13','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('137','5','2','2026-08-13','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('138','6','2','2026-08-13','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('139','7','3','2026-08-13','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('140','8','3','2026-08-13','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('141','9','3','2026-08-13','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('142','10','4','2026-08-13','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('143','11','4','2026-08-13','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('144','12','2','2026-08-13','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('145','1','1','2026-08-14','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('146','2','1','2026-08-14','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('147','3','1','2026-08-14','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('148','4','1','2026-08-14','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('149','5','2','2026-08-14','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('150','6','2','2026-08-14','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('151','7','3','2026-08-14','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('152','8','3','2026-08-14','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('153','9','3','2026-08-14','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('154','10','4','2026-08-14','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('155','11','4','2026-08-14','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('156','12','2','2026-08-14','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('157','1','1','2026-08-17','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('158','2','1','2026-08-17','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('159','3','1','2026-08-17','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('160','4','1','2026-08-17','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('161','5','2','2026-08-17','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('162','6','2','2026-08-17','L','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('163','7','3','2026-08-17','A','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('164','8','3','2026-08-17','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('165','9','3','2026-08-17','E','Family commitment','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('166','10','4','2026-08-17','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('167','11','4','2026-08-17','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('168','12','2','2026-08-17','P','','5','2026-08-18 23:38:12');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('169','1','1','2026-08-19','A','','5','2026-08-19 07:40:02');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('170','2','1','2026-08-19','P','','5','2026-08-19 07:40:02');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('171','3','1','2026-08-19','E','','5','2026-08-19 07:40:02');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('172','4','1','2026-08-19','P','','5','2026-08-19 07:40:02');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('173','7','3','2026-08-19','A','','1','2026-08-19 13:26:30');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('174','8','3','2026-08-19','P','','1','2026-08-19 13:26:30');
INSERT INTO `attendance` (`id`,`enrolment_id`,`course_id`,`session_date`,`status`,`remarks`,`recorded_by`,`created_at`) VALUES ('175','9','3','2026-08-19','P','','1','2026-08-19 13:26:30');

DROP TABLE IF EXISTS `timetable`;
CREATE TABLE `timetable` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `day_of_week` int(11) NOT NULL,
  `start_time` varchar(5) NOT NULL,
  `end_time` varchar(5) NOT NULL,
  `subject` varchar(120) NOT NULL,
  `room` varchar(60) DEFAULT NULL,
  `facilitator_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_tt_course` (`course_id`,`day_of_week`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `timetable` (`id`,`course_id`,`day_of_week`,`start_time`,`end_time`,`subject`,`room`,`facilitator_id`,`created_at`) VALUES ('1','1','1','09:00','11:00','HTML & CSS foundations','Lab 1','5','2026-08-18 23:38:12');
INSERT INTO `timetable` (`id`,`course_id`,`day_of_week`,`start_time`,`end_time`,`subject`,`room`,`facilitator_id`,`created_at`) VALUES ('2','1','2','09:00','11:00','PHP basics','Lab 1','5','2026-08-18 23:38:12');
INSERT INTO `timetable` (`id`,`course_id`,`day_of_week`,`start_time`,`end_time`,`subject`,`room`,`facilitator_id`,`created_at`) VALUES ('3','1','4','14:00','16:00','Project practical','Lab 1','5','2026-08-18 23:38:12');
INSERT INTO `timetable` (`id`,`course_id`,`day_of_week`,`start_time`,`end_time`,`subject`,`room`,`facilitator_id`,`created_at`) VALUES ('4','2','1','11:30','13:00','Word processing','Lab 2','6','2026-08-18 23:38:12');
INSERT INTO `timetable` (`id`,`course_id`,`day_of_week`,`start_time`,`end_time`,`subject`,`room`,`facilitator_id`,`created_at`) VALUES ('5','2','3','11:30','13:00','Spreadsheets','Lab 2','6','2026-08-18 23:38:12');
INSERT INTO `timetable` (`id`,`course_id`,`day_of_week`,`start_time`,`end_time`,`subject`,`room`,`facilitator_id`,`created_at`) VALUES ('6','3','2','08:00','11:00','Bread production','Bakery','7','2026-08-18 23:38:12');
INSERT INTO `timetable` (`id`,`course_id`,`day_of_week`,`start_time`,`end_time`,`subject`,`room`,`facilitator_id`,`created_at`) VALUES ('7','3','5','08:00','11:00','Pastry & cakes','Bakery','7','2026-08-18 23:38:12');
INSERT INTO `timetable` (`id`,`course_id`,`day_of_week`,`start_time`,`end_time`,`subject`,`room`,`facilitator_id`,`created_at`) VALUES ('8','4','3','14:00','16:00','Table service','Restaurant','7','2026-08-18 23:38:12');

DROP TABLE IF EXISTS `lesson_plans`;
CREATE TABLE `lesson_plans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `facilitator_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `plan_date` date NOT NULL,
  `topic` varchar(200) NOT NULL,
  `objectives` text DEFAULT NULL,
  `activities` text DEFAULT NULL,
  `resources` text DEFAULT NULL,
  `review_status` varchar(20) NOT NULL DEFAULT 'submitted',
  `review_comment` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `lesson_plans` (`id`,`facilitator_id`,`course_id`,`plan_date`,`topic`,`objectives`,`activities`,`resources`,`review_status`,`review_comment`,`reviewed_by`,`reviewed_at`,`created_at`) VALUES ('1','5','1','2026-08-15','Forms and server-side validation','Build an HTML form.\nValidate input in PHP.\nExplain why validation cannot live in the browser alone.','Demonstration on the projector.\nPaired practical: registration form.\nGroup review of two submissions.','Projector, 12 workstations, printed handout.','reviewed','Clear objectives. Add a homework task next time.','3','2026-08-18 23:38:12','2026-08-18 23:38:12');
INSERT INTO `lesson_plans` (`id`,`facilitator_id`,`course_id`,`plan_date`,`topic`,`objectives`,`activities`,`resources`,`review_status`,`review_comment`,`reviewed_by`,`reviewed_at`,`created_at`) VALUES ('2','7','3','2026-08-20','Yeast doughs — kneading and proofing','Identify correct dough consistency.\nControl proofing time and temperature.','Weighing practical.\nHands-on kneading.\nBake and evaluate crumb structure.','Flour 10kg, yeast, two ovens, scales.','submitted',NULL,NULL,NULL,'2026-08-18 23:38:12');

DROP TABLE IF EXISTS `monthly_reports`;
CREATE TABLE `monthly_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `facilitator_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `month_year` varchar(7) NOT NULL,
  `sessions_held` int(11) NOT NULL DEFAULT 0,
  `topics_covered` text DEFAULT NULL,
  `attendance_summary` text DEFAULT NULL,
  `challenges` text DEFAULT NULL,
  `support_needed` text DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'submitted',
  `review_comment` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_monthly` (`facilitator_id`,`course_id`,`month_year`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `monthly_reports` (`id`,`facilitator_id`,`course_id`,`month_year`,`sessions_held`,`topics_covered`,`attendance_summary`,`challenges`,`support_needed`,`comments`,`status`,`review_comment`,`reviewed_by`,`reviewed_at`,`created_at`) VALUES ('1','5','1','2026-07','12','HTML structure and semantics\nCSS layout with flexbox\nIntroduction to PHP and forms','Average attendance 86%. Two students below the 80% threshold.','Power interruptions cut two afternoon sessions short. Three students share one workstation.','Two additional workstations and a small UPS for the lab.','Group is motivated; the practical project is on schedule.','acknowledged','Noted. UPS request forwarded to the director.','3','2026-08-18 23:38:12','2026-08-18 23:38:12');
INSERT INTO `monthly_reports` (`id`,`facilitator_id`,`course_id`,`month_year`,`sessions_held`,`topics_covered`,`attendance_summary`,`challenges`,`support_needed`,`comments`,`status`,`review_comment`,`reviewed_by`,`reviewed_at`,`created_at`) VALUES ('2','7','3','2026-07','9','Bread production basics\nCake sponges\nHygiene and food safety','Average attendance 91%.','Oven thermostat is unreliable, affecting practical results.','Oven service and a stock of baking paper.','Students requested an extra Saturday practical.','changes_requested','','1','2026-08-19 13:28:06','2026-08-18 23:38:12');

DROP TABLE IF EXISTS `assessments`;
CREATE TABLE `assessments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `name` varchar(140) NOT NULL,
  `kind` varchar(20) NOT NULL DEFAULT 'practical',
  `max_score` int(11) NOT NULL DEFAULT 100,
  `weight` int(11) NOT NULL DEFAULT 100,
  `due_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `assessments` (`id`,`course_id`,`name`,`kind`,`max_score`,`weight`,`due_date`,`created_by`,`created_at`) VALUES ('1','1','Practical 1 — static web page','practical','100','40','2026-08-08','5','2026-08-18 23:38:12');

DROP TABLE IF EXISTS `marks`;
CREATE TABLE `marks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `assessment_id` int(11) NOT NULL,
  `enrolment_id` int(11) NOT NULL,
  `score` decimal(6,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_mark` (`assessment_id`,`enrolment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `marks` (`id`,`assessment_id`,`enrolment_id`,`score`,`feedback`,`recorded_by`,`created_at`) VALUES ('1','1','1','78.00','Good structure and clean code.','5','2026-08-18 23:38:12');
INSERT INTO `marks` (`id`,`assessment_id`,`enrolment_id`,`score`,`feedback`,`recorded_by`,`created_at`) VALUES ('2','1','2','64.00','Revise semantic tags and spacing.','5','2026-08-18 23:38:12');
INSERT INTO `marks` (`id`,`assessment_id`,`enrolment_id`,`score`,`feedback`,`recorded_by`,`created_at`) VALUES ('3','1','3','91.00','Good structure and clean code.','5','2026-08-18 23:38:12');
INSERT INTO `marks` (`id`,`assessment_id`,`enrolment_id`,`score`,`feedback`,`recorded_by`,`created_at`) VALUES ('4','1','4','55.00','Revise semantic tags and spacing.','5','2026-08-18 23:38:12');

DROP TABLE IF EXISTS `certificates`;
CREATE TABLE `certificates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `serial_no` varchar(30) NOT NULL,
  `verify_code` varchar(20) NOT NULL,
  `grade` varchar(30) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `remarks` varchar(200) DEFAULT NULL,
  `status` varchar(12) NOT NULL DEFAULT 'valid',
  `issued_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_cert_serial` (`serial_no`),
  UNIQUE KEY `ux_cert_code` (`verify_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `certificates` (`id`,`student_id`,`course_id`,`serial_no`,`verify_code`,`grade`,`issue_date`,`remarks`,`status`,`issued_by`,`created_at`) VALUES ('1','5','2','EY-CERT-2026-0001','REA-XW9K','Credit','2026-08-13','Completed all practicals.','valid','1','2026-08-18 23:38:12');

DROP TABLE IF EXISTS `id_cards`;
CREATE TABLE `id_cards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `card_no` varchar(30) NOT NULL,
  `issued_on` date NOT NULL,
  `valid_until` date NOT NULL,
  `status` varchar(12) NOT NULL DEFAULT 'active',
  `issued_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `id_cards` (`id`,`student_id`,`card_no`,`issued_on`,`valid_until`,`status`,`issued_by`,`created_at`) VALUES ('1','1','EYID-2026-0001','2026-08-18','2026-11-18','active','1','2026-08-18 23:52:06');
INSERT INTO `id_cards` (`id`,`student_id`,`card_no`,`issued_on`,`valid_until`,`status`,`issued_by`,`created_at`) VALUES ('2','2','EYID-2026-0002','2026-08-19','2026-11-19','active','1','2026-08-19 13:47:08');

DROP TABLE IF EXISTS `kitchen_records`;
CREATE TABLE `kitchen_records` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_date` date NOT NULL,
  `tea_teachers` int(11) NOT NULL DEFAULT 0,
  `tea_students` int(11) NOT NULL DEFAULT 0,
  `meals_teachers` int(11) NOT NULL DEFAULT 0,
  `meals_students` int(11) NOT NULL DEFAULT 0,
  `notes` varchar(240) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_kitchen_date` (`service_date`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `kitchen_records` (`id`,`service_date`,`tea_teachers`,`tea_students`,`meals_teachers`,`meals_students`,`notes`,`recorded_by`,`created_at`,`updated_at`) VALUES ('1','2026-08-04','9','51','8','51','','8','2026-08-18 23:38:12','2026-08-18 23:38:12');
INSERT INTO `kitchen_records` (`id`,`service_date`,`tea_teachers`,`tea_students`,`meals_teachers`,`meals_students`,`notes`,`recorded_by`,`created_at`,`updated_at`) VALUES ('2','2026-08-05','8','50','7','48','','8','2026-08-18 23:38:12','2026-08-18 23:38:12');
INSERT INTO `kitchen_records` (`id`,`service_date`,`tea_teachers`,`tea_students`,`meals_teachers`,`meals_students`,`notes`,`recorded_by`,`created_at`,`updated_at`) VALUES ('3','2026-08-06','7','49','6','45','','8','2026-08-18 23:38:12','2026-08-18 23:38:12');
INSERT INTO `kitchen_records` (`id`,`service_date`,`tea_teachers`,`tea_students`,`meals_teachers`,`meals_students`,`notes`,`recorded_by`,`created_at`,`updated_at`) VALUES ('4','2026-08-07','9','48','8','42','','8','2026-08-18 23:38:12','2026-08-18 23:38:12');
INSERT INTO `kitchen_records` (`id`,`service_date`,`tea_teachers`,`tea_students`,`meals_teachers`,`meals_students`,`notes`,`recorded_by`,`created_at`,`updated_at`) VALUES ('5','2026-08-10','9','54','8','44','','8','2026-08-18 23:38:12','2026-08-18 23:38:12');
INSERT INTO `kitchen_records` (`id`,`service_date`,`tea_teachers`,`tea_students`,`meals_teachers`,`meals_students`,`notes`,`recorded_by`,`created_at`,`updated_at`) VALUES ('6','2026-08-11','8','53','7','52','','8','2026-08-18 23:38:12','2026-08-18 23:38:12');
INSERT INTO `kitchen_records` (`id`,`service_date`,`tea_teachers`,`tea_students`,`meals_teachers`,`meals_students`,`notes`,`recorded_by`,`created_at`,`updated_at`) VALUES ('7','2026-08-12','7','52','6','49','','8','2026-08-18 23:38:12','2026-08-18 23:38:12');
INSERT INTO `kitchen_records` (`id`,`service_date`,`tea_teachers`,`tea_students`,`meals_teachers`,`meals_students`,`notes`,`recorded_by`,`created_at`,`updated_at`) VALUES ('8','2026-08-13','9','51','8','46','Beans and rice served.','8','2026-08-18 23:38:12','2026-08-18 23:38:12');
INSERT INTO `kitchen_records` (`id`,`service_date`,`tea_teachers`,`tea_students`,`meals_teachers`,`meals_students`,`notes`,`recorded_by`,`created_at`,`updated_at`) VALUES ('9','2026-08-14','8','50','7','43','','8','2026-08-18 23:38:12','2026-08-18 23:38:12');
INSERT INTO `kitchen_records` (`id`,`service_date`,`tea_teachers`,`tea_students`,`meals_teachers`,`meals_students`,`notes`,`recorded_by`,`created_at`,`updated_at`) VALUES ('10','2026-08-17','8','47','7','45','','8','2026-08-18 23:38:12','2026-08-18 23:38:12');
INSERT INTO `kitchen_records` (`id`,`service_date`,`tea_teachers`,`tea_students`,`meals_teachers`,`meals_students`,`notes`,`recorded_by`,`created_at`,`updated_at`) VALUES ('11','2026-08-18','7','46','6','42','Beans and rice served.','8','2026-08-18 23:38:12','2026-08-18 23:38:12');

DROP TABLE IF EXISTS `notices`;
CREATE TABLE `notices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(11) DEFAULT NULL,
  `title` varchar(160) NOT NULL,
  `body` text DEFAULT NULL,
  `show_until` date DEFAULT NULL,
  `posted_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `notices` (`id`,`course_id`,`title`,`body`,`show_until`,`posted_by`,`created_at`) VALUES ('1',NULL,'Centre closed on Friday afternoon','All afternoon sessions on Friday are cancelled for the staff development meeting. Morning classes run as normal.',NULL,'2','2026-08-18 23:38:12');
INSERT INTO `notices` (`id`,`course_id`,`title`,`body`,`show_until`,`posted_by`,`created_at`) VALUES ('2','1','Bring your project files','Web Development Level 1: bring your project folder on a flash drive for Thursday practical.','2026-09-08','5','2026-08-18 23:38:12');

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(120) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `action` varchar(30) NOT NULL,
  `entity` varchar(40) DEFAULT NULL,
  `entity_id` varchar(40) DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_audit_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('1','1','dev','superadmin','settings','settings','','3 values changed','127.0.0.1','2026-08-18 23:51:47');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('2','1','dev','superadmin','issue','id_cards','1','EY-2026-0001 EYID-2026-0001','127.0.0.1','2026-08-18 23:52:06');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('3',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-19 07:16:36');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('4','1','dev','superadmin','update','users','2','admin@elimuyetu.org · admin','127.0.0.1','2026-08-19 07:18:13');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('5','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-08-19 07:18:44');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('6',NULL,'guest','-','login','users','2','admin','127.0.0.1','2026-08-19 07:19:00');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('7','2','admin','admin','logout','users','2','','127.0.0.1','2026-08-19 07:20:30');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('8',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-19 07:20:32');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('9','1','dev','superadmin','update','users','5','james@elimuyetu.org · facilitator','127.0.0.1','2026-08-19 07:37:03');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('10','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-08-19 07:37:07');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('11',NULL,'guest','-','login','users','5','facilitator','127.0.0.1','2026-08-19 07:37:33');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('12','5','James','facilitator','attendance','attendance','1','WEB-101 2026-08-19 · 4 marks','127.0.0.1','2026-08-19 07:40:02');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('13','5','James','facilitator','logout','users','5','','127.0.0.1','2026-08-19 07:50:18');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('14',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-19 07:50:22');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('15',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-19 13:23:29');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('16','1','dev','superadmin','attendance','attendance','3','BAKE-201 2026-08-19 · 3 marks','127.0.0.1','2026-08-19 13:26:30');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('17','1','dev','superadmin','review','monthly_reports','2','changes_requested','127.0.0.1','2026-08-19 13:28:01');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('18','1','dev','superadmin','review','monthly_reports','2','changes_requested','127.0.0.1','2026-08-19 13:28:06');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('19','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-08-19 13:31:59');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('20',NULL,'guest','-','login','users','5','facilitator','127.0.0.1','2026-08-19 13:32:30');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('21','5','James','facilitator','attendance','attendance','1','WEB-101 2026-08-19 · 4 marks','127.0.0.1','2026-08-19 13:33:23');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('22','5','James','facilitator','logout','users','5','','127.0.0.1','2026-08-19 13:35:45');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('23',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-19 13:46:34');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('24','1','dev','superadmin','issue','id_cards','2','EY-2026-0002 EYID-2026-0002','127.0.0.1','2026-08-19 13:47:08');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('25',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-26 12:27:12');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('26','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-08-26 12:28:11');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('27',NULL,'guest','-','login','users','5','facilitator','127.0.0.1','2026-08-26 12:28:34');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('28','5','James','facilitator','logout','users','5','','127.0.0.1','2026-08-26 12:30:10');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('29',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-26 12:30:33');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('30','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-08-26 12:31:19');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('31',NULL,'guest','-','login','users','2','admin','127.0.0.1','2026-08-26 12:31:32');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('32',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-26 16:23:23');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('33','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-08-26 16:23:25');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('34',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-26 16:26:39');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('35',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-26 18:29:32');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('36','1','dev','superadmin','export','students','','12 rows','127.0.0.1','2026-08-26 18:36:04');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('37',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-26 20:45:21');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('38','1','dev','superadmin','update','users','9','amina@student.elimuyetu · student','127.0.0.1','2026-08-26 20:52:38');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('39','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-08-26 20:52:54');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('40',NULL,'guest','-','login','users','9','student','127.0.0.1','2026-08-26 20:53:21');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('41','9','Amina Juma','student','logout','users','9','','127.0.0.1','2026-08-26 21:07:20');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('42',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-26 21:08:21');

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(160) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `tries` int(11) NOT NULL DEFAULT 0,
  `locked_till` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `login_attempts` (`id`,`email`,`ip`,`tries`,`locked_till`,`updated_at`) VALUES ('1','willie@elimuyetu.org','127.0.0.1','5','2026-08-19 07:15:53','2026-08-19 07:05:53');

SET FOREIGN_KEY_CHECKS=1;
