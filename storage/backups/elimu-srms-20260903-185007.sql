-- Elimu Yetu SRMS backup
-- Taken 2026-09-03 18:50:07
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `skey` varchar(60) NOT NULL,
  `svalue` text DEFAULT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('academic_year','2026');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('attendance_threshold','50');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('cert_signatory_1','Amos Kasaramba');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('cert_signatory_1_title','Director');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('cert_signatory_2','');
INSERT INTO `settings` (`skey`,`svalue`) VALUES ('cert_signatory_2_title','Director');
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('1','dev','dev@elimuyetu.org','0767711890','superadmin','$2y$12$9rP8uUM.3OdejALiTBrBLejclJJjJ7aoXLJ9ZDXp2G0aZyGTZ63My',NULL,NULL,'active','0','2026-09-03 18:48:58','2026-08-18 23:38:12');
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('2','system admin','admin@elimuyetu.org','+255 763 461 722','admin','$2y$12$mFDIMBD9JkkBVIUvfofide9WsT//sZAt9t3DV.r1DnPENk41mBn9m',NULL,NULL,'active','0','2026-09-03 18:44:08','2026-08-18 23:38:12');
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('3','Willfredy Mosses','willfredy@elimuyetu.org','+255 763 461 700','manager','$2y$12$Uc8E.FpaYiPnLMyuq1WP4u4y34hZ1HBG6pPxHfbk0eVuBtrEL6FRa','1',NULL,'active','0','2026-09-03 18:44:53','2026-08-18 23:38:12');
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('5','James','james@elimuyetu.org','+255 767 711 890','facilitator','$2y$12$dF3ScIw1vB5mmw/hushz5.X0Pyo1cu9pm30.9nCP0FReorYI1K0mO','1',NULL,'active','1','2026-09-03 18:43:11','2026-08-18 23:38:12');
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`role`,`password_hash`,`department_id`,`student_id`,`status`,`must_reset`,`last_login`,`created_at`) VALUES ('8','diggo','diggo@elimuyetu.org','+255 786 114 552','kitchen','$2y$12$rSzmcyOhjaM8pM.EdahNXOa0yQ5GbAxdJbpCCEYAaSTJWd2ywJtLi',NULL,NULL,'active','0','2026-09-03 18:09:16','2026-08-18 23:38:12');

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
INSERT INTO `monthly_reports` (`id`,`facilitator_id`,`course_id`,`month_year`,`sessions_held`,`topics_covered`,`attendance_summary`,`challenges`,`support_needed`,`comments`,`status`,`review_comment`,`reviewed_by`,`reviewed_at`,`created_at`) VALUES ('1','5','1','2026-07','12','HTML structure and semantics\nCSS layout with flexbox\nIntroduction to PHP and forms','Average attendance 86%. Two students below the 80% threshold.','Power interruptions cut two afternoon sessions short. Three students share one workstation.','Two additional workstations and a small UPS for the lab.','Group is motivated; the practical project is on schedule.','acknowledged','Noted. UPS request forwarded to the director.','1','2026-09-03 17:03:46','2026-08-18 23:38:12');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=217 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('1','1','dev','superadmin','settings','settings','','3 values changed','127.0.0.1','2026-08-18 23:51:47');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('2','1','dev','superadmin','issue','id_cards','1','EY-03-2026-0001 EYID-2026-0001','127.0.0.1','2026-08-18 23:52:06');
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
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('24','1','dev','superadmin','issue','id_cards','2','EY-03-2026-0002 EYID-2026-0002','127.0.0.1','2026-08-19 13:47:08');
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
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('43','1','dev','superadmin','backup','database','','elimu-srms-20260826-211935.sql · 76.8 KB','127.0.0.1','2026-08-26 21:19:36');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('44','1','dev','superadmin','status','students','12','active → completed','127.0.0.1','2026-08-26 21:23:11');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('45','1','dev','superadmin','status','students','12','completed → completed','127.0.0.1','2026-08-26 21:23:32');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('46','1','dev','superadmin','status','students','12','completed → active','127.0.0.1','2026-08-26 21:23:37');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('47','1','dev','superadmin','settings','settings','','1 value changed','127.0.0.1','2026-08-26 21:27:05');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('48',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-27 16:56:57');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('49',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-28 12:35:00');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('50','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-08-28 13:18:43');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('51',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-28 13:20:25');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('52','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-08-28 13:20:46');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('53',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-28 13:52:43');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('54','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-08-28 13:54:49');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('55',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-28 13:57:48');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('56','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-08-28 13:57:54');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('57',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-28 13:57:58');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('58','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-08-28 13:58:10');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('59',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-08-28 13:58:44');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('60',NULL,'guest','-','login','users','13','student','127.0.0.1','2026-08-28 15:09:49');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('61','13','Demo Learner','student','denied','route','students.form','You do not have access to that page.','127.0.0.1','2026-08-28 15:09:49');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('62',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-09-01 12:34:36');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('63','1','dev','superadmin','issue','id_cards','3','EY-03-2026-0001 EYID-2026-0003','127.0.0.1','2026-09-01 12:36:12');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('64',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-09-01 13:38:11');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('65',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-09-01 14:23:05');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('66',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-09-01 16:25:25');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('67',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-09-01 17:25:19');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('68',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-09-01 18:04:31');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('69',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-09-01 19:39:53');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('70',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-09-01 21:04:26');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('71','1','dev','superadmin','create','students','15','EY-03-2026-0002','127.0.0.1','2026-09-01 21:15:03');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('72','1','dev','superadmin','enrol','enrolments','15','WEB-101','127.0.0.1','2026-09-01 21:15:26');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('73','1','dev','superadmin','create','users','14','ey0320260002@student.elimuyetu.org · student','127.0.0.1','2026-09-01 21:18:47');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('74','1','dev','superadmin','issue','id_cards','4','EY-03-2026-0002 EYID-2026-0004','127.0.0.1','2026-09-01 21:18:58');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('75','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-01 21:19:58');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('76',NULL,'guest','-','login','users','14','student','127.0.0.1','2026-09-01 21:20:15');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('77','14','jimmy dev developer','student','denied','route','courses.view','You do not have access to that page.','127.0.0.1','2026-09-01 21:22:08');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('78','14','jimmy dev developer','student','denied','route','courses.view','You do not have access to that page.','127.0.0.1','2026-09-01 21:23:20');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('79','14','jimmy dev developer','student','logout','users','14','','127.0.0.1','2026-09-01 21:23:45');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('80',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-09-01 21:23:47');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('81','1','dev','superadmin','update','departments','2','HOSP','127.0.0.1','2026-09-01 21:25:53');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('82','1','dev','superadmin','update','students','15','EY-03-2026-0002','127.0.0.1','2026-09-01 21:27:50');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('83','1','dev','superadmin','issue','certificates','2','EY-03-2026-0002 WEB-101','127.0.0.1','2026-09-01 21:30:08');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('84','1','dev','superadmin','update','users','1','own details','127.0.0.1','2026-09-01 21:35:34');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('85',NULL,'guest','-','login','users','1','superadmin','127.0.0.1','2026-09-02 10:05:33');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('86','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-02 10:53:37');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('87','2','admin','admin','login','users','2','admin','127.0.0.1','2026-09-02 10:53:52');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('88','2','admin','admin','logout','users','2','','127.0.0.1','2026-09-02 10:54:12');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('89','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-02 10:58:21');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('90','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-02 11:00:26');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('91','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-02 11:00:52');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('92','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-02 11:01:04');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('93','14','jimmy dev developer','student','login','users','14','student','127.0.0.1','2026-09-02 11:01:14');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('94','14','jimmy dev developer','student','denied','route','certificates.index','You do not have access to that page.','127.0.0.1','2026-09-02 11:24:08');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('95','14','jimmy dev developer','student','denied','route','courses.view','You do not have access to that page.','127.0.0.1','2026-09-02 11:27:05');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('96','14','jimmy dev developer','student','password','users','14','changed own password','127.0.0.1','2026-09-02 11:30:53');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('97','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-02 12:48:12');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('98','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-02 12:48:24');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('99','14','jimmy dev developer','student','login','users','14','student','127.0.0.1','2026-09-02 12:48:32');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('100','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-02 16:48:53');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('101','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-02 19:56:50');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('102','1','dev','superadmin','update','users','4','neema@elimuyetu.org · manager','127.0.0.1','2026-09-02 20:08:30');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('103','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-02 20:08:34');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('104','4','Neema','manager','login','users','4','manager','127.0.0.1','2026-09-02 20:09:06');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('105','4','Neema','manager','export','students','','0 rows','127.0.0.1','2026-09-02 20:15:43');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('106','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 10:38:03');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('107','1','dev','superadmin','create','students','16','EY-03-2026-0003','127.0.0.1','2026-09-03 10:48:22');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('108','1','dev','superadmin','enrol','enrolments','16','WEB-101','127.0.0.1','2026-09-03 10:48:22');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('109','1','dev','superadmin','issue','certificates','3','EY-03-2026-0003 WEB-101','127.0.0.1','2026-09-03 10:50:02');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('110','1','dev','superadmin','update','students','16','EY-03-2026-0003','127.0.0.1','2026-09-03 11:45:40');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('111','1','dev','superadmin','create','users','15','ey0320260003@student.elimuyetu.org · student','127.0.0.1','2026-09-03 11:47:13');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('112','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-03 11:48:03');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('113','15','DAVID CONRAD SALEWI','student','login','users','15','student','127.0.0.1','2026-09-03 11:48:12');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('114','15','DAVID CONRAD SALEWI','student','logout','users','15','','127.0.0.1','2026-09-03 12:18:28');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('115','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 12:18:30');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('116','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-03 12:40:12');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('117','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 12:40:25');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('118','1','dev','superadmin','delete','timetable','7','BAKE-201 Pastry & cakes','127.0.0.1','2026-09-03 12:47:46');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('119','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-03 12:59:32');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('120','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 12:59:34');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('121','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-03 13:01:36');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('122','5','James','facilitator','login','users','5','facilitator','127.0.0.1','2026-09-03 13:01:54');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('123','5','James','facilitator','logout','users','5','','127.0.0.1','2026-09-03 13:06:07');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('124','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 13:06:08');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('125','1','dev','superadmin','delete','departments','3','Tailoring & Design','127.0.0.1','2026-09-03 13:07:15');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('126','1','dev','superadmin','delete','courses','3','BAKE-201 Bakery & Pastry','127.0.0.1','2026-09-03 13:07:24');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('127','1','dev','superadmin','settings','settings','','2 values changed','127.0.0.1','2026-09-03 13:08:58');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('128','1','dev','superadmin','issue','id_cards','5','EY-03-2026-0003 EYID-2026-0005','127.0.0.1','2026-09-03 13:09:40');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('129','1','dev','superadmin','delete','courses','4','FNB-101 Food & Beverage Service','127.0.0.1','2026-09-03 13:30:32');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('130','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-03 13:32:00');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('131','4','Neema','manager','login','users','4','manager','127.0.0.1','2026-09-03 13:32:14');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('132','4','Neema','manager','logout','users','4','','127.0.0.1','2026-09-03 13:35:36');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('133','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 13:35:39');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('134','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-03 13:38:31');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('135','5','James','facilitator','login','users','5','facilitator','127.0.0.1','2026-09-03 13:38:46');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('136','5','James','facilitator','logout','users','5','','127.0.0.1','2026-09-03 15:05:05');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('137','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 15:05:07');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('138','1','dev','superadmin','create','kitchen_records','12','2026-09-03','127.0.0.1','2026-09-03 15:13:08');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('139','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-03 15:25:13');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('140','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 15:25:39');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('141','1','dev','superadmin','update','users','8','diggo@elimuyetu.org · kitchen','127.0.0.1','2026-09-03 15:26:01');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('142','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-03 15:26:06');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('143','8','diggo','kitchen','login','users','8','kitchen','127.0.0.1','2026-09-03 15:26:30');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('144','8','diggo','kitchen','delete','kitchen_records','12','2026-09-03','127.0.0.1','2026-09-03 15:26:44');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('145','8','diggo','kitchen','delete','kitchen_records','1','2026-08-04','127.0.0.1','2026-09-03 15:26:48');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('146','8','diggo','kitchen','delete','kitchen_records','2','2026-08-05','127.0.0.1','2026-09-03 15:26:51');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('147','8','diggo','kitchen','delete','kitchen_records','11','2026-08-18','127.0.0.1','2026-09-03 15:26:55');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('148','8','diggo','kitchen','delete','kitchen_records','10','2026-08-17','127.0.0.1','2026-09-03 15:26:59');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('149','8','diggo','kitchen','delete','kitchen_records','9','2026-08-14','127.0.0.1','2026-09-03 15:27:03');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('150','8','diggo','kitchen','delete','kitchen_records','8','2026-08-13','127.0.0.1','2026-09-03 15:27:06');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('151','8','diggo','kitchen','delete','kitchen_records','7','2026-08-12','127.0.0.1','2026-09-03 15:27:09');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('152','8','diggo','kitchen','delete','kitchen_records','6','2026-08-11','127.0.0.1','2026-09-03 15:27:12');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('153','8','diggo','kitchen','delete','kitchen_records','5','2026-08-10','127.0.0.1','2026-09-03 15:27:15');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('154','8','diggo','kitchen','delete','kitchen_records','4','2026-08-07','127.0.0.1','2026-09-03 15:27:18');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('155','8','diggo','kitchen','delete','kitchen_records','3','2026-08-06','127.0.0.1','2026-09-03 15:27:21');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('156','8','diggo','kitchen','logout','users','8','','127.0.0.1','2026-09-03 15:28:32');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('157','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 15:28:34');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('158','1','dev','superadmin','delete','users','4','neema@elimuyetu.org','127.0.0.1','2026-09-03 15:33:21');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('159','1','dev','superadmin','delete','users','12','sample@student.local','127.0.0.1','2026-09-03 15:33:27');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('160','1','dev','superadmin','delete','users','14','ey0320260002@student.elimuyetu.org','127.0.0.1','2026-09-03 15:33:32');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('161','1','dev','superadmin','delete','users','13','demo+ey0320260001@example.local','127.0.0.1','2026-09-03 15:33:47');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('162','1','dev','superadmin','delete','users','15','ey0320260003@student.elimuyetu.org','127.0.0.1','2026-09-03 15:35:29');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('163','1','dev','superadmin','delete','users','7','john@elimuyetu.org','127.0.0.1','2026-09-03 15:35:51');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('164','1','dev','superadmin','delete','users','6','doe@elimuyetu.org','127.0.0.1','2026-09-03 15:35:58');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('165','1','dev','superadmin','delete','students','13','SAMPLE-001 Test Student','127.0.0.1','2026-09-03 15:41:47');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('166','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 17:03:34');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('167','1','dev','superadmin','review','monthly_reports','1','acknowledged','127.0.0.1','2026-09-03 17:03:46');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('168','1','dev','superadmin','delete','departments','2','Hoteli Academy','127.0.0.1','2026-09-03 17:04:35');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('169','1','dev','superadmin','update','departments','1','ICT','127.0.0.1','2026-09-03 17:17:59');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('170','1','dev','superadmin','delete','courses','2','COMP-101 Computer Literacy','127.0.0.1','2026-09-03 17:18:12');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('171','1','dev','superadmin','delete','students','14','EY-03-2026-0001 Demo Learner','127.0.0.1','2026-09-03 17:29:16');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('172','1','dev','superadmin','update','students','15','EY-03-2026-0002','127.0.0.1','2026-09-03 17:31:28');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('173','1','dev','superadmin','update','students','16','EY-03-2026-0003','127.0.0.1','2026-09-03 17:32:13');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('174','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-03 17:32:25');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('175','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 17:32:27');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('176','1','dev','superadmin','unenrol','enrolments','13','WEB-101','127.0.0.1','2026-09-03 17:35:16');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('177','1','dev','superadmin','unenrol','enrolments','14','WEB-101','127.0.0.1','2026-09-03 17:46:09');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('178','1','dev','superadmin','unissue','certificates','3','EY-CERT-2026-0003','127.0.0.1','2026-09-03 17:50:44');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('179','1','dev','superadmin','unissue','certificates','2','EY-CERT-2026-0002','127.0.0.1','2026-09-03 17:50:48');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('180','1','dev','superadmin','delete','students','16','EY-03-2026-0003 DAVID SALEWI','127.0.0.1','2026-09-03 17:51:03');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('181','1','dev','superadmin','delete','students','15','EY-03-2026-0002 jimmy developer','127.0.0.1','2026-09-03 17:51:07');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('182','1','dev','superadmin','delete','courses','1','WEB-101 Web Development','127.0.0.1','2026-09-03 17:51:16');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('183','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-03 18:07:01');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('184','5','James','facilitator','login','users','5','facilitator','127.0.0.1','2026-09-03 18:07:16');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('185','5','James','facilitator','logout','users','5','','127.0.0.1','2026-09-03 18:09:00');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('186','8','diggo','kitchen','login','users','8','kitchen','127.0.0.1','2026-09-03 18:09:16');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('187','8','diggo','kitchen','logout','users','8','','127.0.0.1','2026-09-03 18:11:16');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('188','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 18:11:19');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('189','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-03 18:11:28');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('190','5','James','facilitator','login','users','5','facilitator','127.0.0.1','2026-09-03 18:11:47');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('191','5','James','facilitator','logout','users','5','','127.0.0.1','2026-09-03 18:11:53');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('192','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 18:11:55');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('193','1','dev','superadmin','update','users','3','willfredy@elimuyetu.org · manager','127.0.0.1','2026-09-03 18:18:13');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('194','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-03 18:18:26');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('195','3','Willfredy Mosses','manager','login','users','3','manager','127.0.0.1','2026-09-03 18:18:38');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('196','3','Willfredy Mosses','manager','logout','users','3','','127.0.0.1','2026-09-03 18:30:39');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('197','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 18:30:41');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('198','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-03 18:32:44');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('199','3','Willfredy Mosses','manager','login','users','3','manager','127.0.0.1','2026-09-03 18:33:25');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('200','3','Willfredy Mosses','manager','logout','users','3','','127.0.0.1','2026-09-03 18:33:53');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('201','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 18:33:56');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('202','1','dev','superadmin','settings','settings','','1 value changed','127.0.0.1','2026-09-03 18:34:25');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('203','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-03 18:35:11');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('204','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 18:35:26');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('205','1','dev','superadmin','update','users','2','admin@elimuyetu.org · admin','127.0.0.1','2026-09-03 18:36:21');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('206','1','dev','superadmin','logout','users','1','','127.0.0.1','2026-09-03 18:36:24');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('207','2','system admin','admin','login','users','2','admin','127.0.0.1','2026-09-03 18:36:42');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('208','2','system admin','admin','logout','users','2','','127.0.0.1','2026-09-03 18:42:58');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('209','5','James','facilitator','login','users','5','facilitator','127.0.0.1','2026-09-03 18:43:11');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('210','5','James','facilitator','logout','users','5','','127.0.0.1','2026-09-03 18:43:53');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('211','2','system admin','admin','login','users','2','admin','127.0.0.1','2026-09-03 18:44:08');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('212','2','system admin','admin','logout','users','2','','127.0.0.1','2026-09-03 18:44:35');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('213','3','Willfredy Mosses','manager','login','users','3','manager','127.0.0.1','2026-09-03 18:44:53');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('214','3','Willfredy Mosses','manager','logout','users','3','','127.0.0.1','2026-09-03 18:48:56');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('215','1','dev','superadmin','login','users','1','superadmin','127.0.0.1','2026-09-03 18:48:58');
INSERT INTO `audit_logs` (`id`,`user_id`,`user_name`,`role`,`action`,`entity`,`entity_id`,`details`,`ip`,`created_at`) VALUES ('216','1','dev','superadmin','backup','database','','elimu-srms-20260903-184916.sql · 68.3 KB','127.0.0.1','2026-09-03 18:49:16');

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(160) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `tries` int(11) NOT NULL DEFAULT 0,
  `locked_till` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `login_attempts` (`id`,`email`,`ip`,`tries`,`locked_till`,`updated_at`) VALUES ('1','willie@elimuyetu.org','127.0.0.1','5','2026-08-19 07:15:53','2026-08-19 07:05:53');
INSERT INTO `login_attempts` (`id`,`email`,`ip`,`tries`,`locked_till`,`updated_at`) VALUES ('6','ey-03-2026-0007','127.0.0.1','2',NULL,'2026-08-28 13:20:16');
INSERT INTO `login_attempts` (`id`,`email`,`ip`,`tries`,`locked_till`,`updated_at`) VALUES ('7','ey-03-2026-0010','127.0.0.1','1',NULL,'2026-08-28 13:20:56');
INSERT INTO `login_attempts` (`id`,`email`,`ip`,`tries`,`locked_till`,`updated_at`) VALUES ('8','sample-001','127.0.0.1','1',NULL,'2026-08-28 13:54:55');

SET FOREIGN_KEY_CHECKS=1;
