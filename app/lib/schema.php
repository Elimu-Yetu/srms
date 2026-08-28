<?php
/**
 * Schema. Written once, emitted for whichever driver is configured.
 * {PK}  -> auto-increment primary key
 * {SFX} -> table suffix (storage engine / charset on MySQL)
 */

function schema_sql(): array
{
    $t = [];

    $t['settings'] = "CREATE TABLE settings (
        skey        VARCHAR(60) NOT NULL PRIMARY KEY,
        svalue      TEXT
    ){SFX}";

    $t['departments'] = "CREATE TABLE departments (
        id          {PK},
        code        VARCHAR(20)  NOT NULL,
        name        VARCHAR(120) NOT NULL,
        description TEXT,
        manager_id  INT NULL,
        status      VARCHAR(12)  NOT NULL DEFAULT 'active',
        created_at  DATETIME     NOT NULL
    ){SFX}";

    $t['users'] = "CREATE TABLE users (
        id            {PK},
        name          VARCHAR(120) NOT NULL,
        email         VARCHAR(160) NOT NULL,
        phone         VARCHAR(30),
        role          VARCHAR(20)  NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        department_id INT NULL,
        student_id    INT NULL,
        status        VARCHAR(12)  NOT NULL DEFAULT 'active',
        must_reset    INT          NOT NULL DEFAULT 0,
        last_login    DATETIME NULL,
        created_at    DATETIME     NOT NULL
    ){SFX}";

    $t['courses'] = "CREATE TABLE courses (
        id             {PK},
        department_id  INT NOT NULL,
        code           VARCHAR(24)  NOT NULL,
        name           VARCHAR(160) NOT NULL,
        description    TEXT,
        duration_weeks INT          NOT NULL DEFAULT 12,
        fee_amount     DECIMAL(12,2) NOT NULL DEFAULT 0,
        capacity       INT          NOT NULL DEFAULT 30,
        facilitator_id INT NULL,
        start_date     DATE NULL,
        end_date       DATE NULL,
        status         VARCHAR(12)  NOT NULL DEFAULT 'active',
        created_at     DATETIME     NOT NULL
    ){SFX}";

    $t['students'] = "CREATE TABLE students (
        id               {PK},
        student_no       VARCHAR(24)  NOT NULL,
        first_name       VARCHAR(60)  NOT NULL,
        middle_name      VARCHAR(60),
        last_name        VARCHAR(60)  NOT NULL,
        gender           VARCHAR(10),
        dob              DATE NULL,
        phone            VARCHAR(30),
        email            VARCHAR(160),
        national_id      VARCHAR(40),
        address          VARCHAR(200),
        education_level  VARCHAR(60),
        guardian_name    VARCHAR(120),
        guardian_phone   VARCHAR(30),
        guardian_relation VARCHAR(40),
        department_id    INT NULL,
        photo            VARCHAR(160),
        status           VARCHAR(14)  NOT NULL DEFAULT 'pending',
        notes            TEXT,
        registered_by    INT NULL,
        registered_at    DATETIME     NOT NULL
    ){SFX}";

    $t['enrolments'] = "CREATE TABLE enrolments (
        id          {PK},
        student_id  INT NOT NULL,
        course_id   INT NOT NULL,
        enrolled_on DATE NOT NULL,
        status      VARCHAR(14) NOT NULL DEFAULT 'active',
        created_at  DATETIME NOT NULL
    ){SFX}";

    $t['attendance'] = "CREATE TABLE attendance (
        id           {PK},
        enrolment_id INT NOT NULL,
        course_id    INT NOT NULL,
        session_date DATE NOT NULL,
        status       VARCHAR(1) NOT NULL,
        remarks      VARCHAR(160),
        recorded_by  INT NULL,
        created_at   DATETIME NOT NULL
    ){SFX}";

    $t['timetable'] = "CREATE TABLE timetable (
        id             {PK},
        course_id      INT NOT NULL,
        day_of_week    INT NOT NULL,
        start_time     VARCHAR(5) NOT NULL,
        end_time       VARCHAR(5) NOT NULL,
        subject        VARCHAR(120) NOT NULL,
        room           VARCHAR(60),
        facilitator_id INT NULL,
        created_at     DATETIME NOT NULL
    ){SFX}";

    $t['lesson_plans'] = "CREATE TABLE lesson_plans (
        id             {PK},
        facilitator_id INT NOT NULL,
        course_id      INT NOT NULL,
        plan_date      DATE NOT NULL,
        topic          VARCHAR(200) NOT NULL,
        objectives     TEXT,
        activities     TEXT,
        resources      TEXT,
        review_status  VARCHAR(20) NOT NULL DEFAULT 'submitted',
        review_comment TEXT,
        reviewed_by    INT NULL,
        reviewed_at    DATETIME NULL,
        created_at     DATETIME NOT NULL
    ){SFX}";

    $t['monthly_reports'] = "CREATE TABLE monthly_reports (
        id                 {PK},
        facilitator_id     INT NOT NULL,
        course_id          INT NOT NULL,
        month_year         VARCHAR(7) NOT NULL,
        sessions_held      INT NOT NULL DEFAULT 0,
        topics_covered     TEXT,
        attendance_summary TEXT,
        challenges         TEXT,
        support_needed     TEXT,
        comments           TEXT,
        status             VARCHAR(20) NOT NULL DEFAULT 'submitted',
        review_comment     TEXT,
        reviewed_by        INT NULL,
        reviewed_at        DATETIME NULL,
        created_at         DATETIME NOT NULL
    ){SFX}";

    $t['assessments'] = "CREATE TABLE assessments (
        id         {PK},
        course_id  INT NOT NULL,
        name       VARCHAR(140) NOT NULL,
        kind       VARCHAR(20) NOT NULL DEFAULT 'practical',
        max_score  INT NOT NULL DEFAULT 100,
        weight     INT NOT NULL DEFAULT 100,
        due_date   DATE NULL,
        created_by INT NULL,
        created_at DATETIME NOT NULL
    ){SFX}";

    $t['marks'] = "CREATE TABLE marks (
        id            {PK},
        assessment_id INT NOT NULL,
        enrolment_id  INT NOT NULL,
        score         DECIMAL(6,2) NULL,
        feedback      TEXT,
        recorded_by   INT NULL,
        created_at    DATETIME NOT NULL
    ){SFX}";

    $t['certificates'] = "CREATE TABLE certificates (
        id          {PK},
        student_id  INT NOT NULL,
        course_id   INT NOT NULL,
        serial_no   VARCHAR(30) NOT NULL,
        verify_code VARCHAR(20) NOT NULL,
        grade       VARCHAR(30),
        issue_date  DATE NOT NULL,
        remarks     VARCHAR(200),
        status      VARCHAR(12) NOT NULL DEFAULT 'valid',
        issued_by   INT NULL,
        created_at  DATETIME NOT NULL
    ){SFX}";

    $t['id_cards'] = "CREATE TABLE id_cards (
        id          {PK},
        student_id  INT NOT NULL,
        card_no     VARCHAR(30) NOT NULL,
        issued_on   DATE NOT NULL,
        valid_until DATE NOT NULL,
        status      VARCHAR(12) NOT NULL DEFAULT 'active',
        issued_by   INT NULL,
        created_at  DATETIME NOT NULL
    ){SFX}";

    $t['kitchen_records'] = "CREATE TABLE kitchen_records (
        id              {PK},
        service_date    DATE NOT NULL,
        tea_teachers    INT NOT NULL DEFAULT 0,
        tea_students    INT NOT NULL DEFAULT 0,
        meals_teachers  INT NOT NULL DEFAULT 0,
        meals_students  INT NOT NULL DEFAULT 0,
        notes           VARCHAR(240),
        recorded_by     INT NULL,
        created_at      DATETIME NOT NULL,
        updated_at      DATETIME NOT NULL
    ){SFX}";

    $t['notices'] = "CREATE TABLE notices (
        id         {PK},
        course_id  INT NULL,
        title      VARCHAR(160) NOT NULL,
        body       TEXT,
        show_until DATE NULL,
        posted_by  INT NULL,
        created_at DATETIME NOT NULL
    ){SFX}";

    $t['audit_logs'] = "CREATE TABLE audit_logs (
        id         {PK},
        user_id    INT NULL,
        user_name  VARCHAR(120),
        role       VARCHAR(20),
        action     VARCHAR(30) NOT NULL,
        entity     VARCHAR(40),
        entity_id  VARCHAR(40),
        details    VARCHAR(255),
        ip         VARCHAR(45),
        created_at DATETIME NOT NULL
    ){SFX}";

    $t['login_attempts'] = "CREATE TABLE login_attempts (
        id         {PK},
        email      VARCHAR(160) NOT NULL,
        ip         VARCHAR(45),
        tries      INT NOT NULL DEFAULT 0,
        locked_till DATETIME NULL,
        updated_at DATETIME NOT NULL
    ){SFX}";

    return $t;
}

function schema_indexes(): array
{
    return [
        'CREATE UNIQUE INDEX ux_users_email      ON users (email)',
        'CREATE UNIQUE INDEX ux_dept_code        ON departments (code)',
        'CREATE UNIQUE INDEX ux_course_code      ON courses (code)',
        'CREATE UNIQUE INDEX ux_student_no       ON students (student_no)',
        'CREATE UNIQUE INDEX ux_enrol            ON enrolments (student_id, course_id)',
        'CREATE UNIQUE INDEX ux_attend           ON attendance (enrolment_id, session_date)',
        'CREATE UNIQUE INDEX ux_monthly          ON monthly_reports (facilitator_id, course_id, month_year)',
        'CREATE UNIQUE INDEX ux_mark             ON marks (assessment_id, enrolment_id)',
        'CREATE UNIQUE INDEX ux_cert_serial      ON certificates (serial_no)',
        'CREATE UNIQUE INDEX ux_cert_code        ON certificates (verify_code)',
        'CREATE UNIQUE INDEX ux_kitchen_date     ON kitchen_records (service_date)',
        'CREATE INDEX ix_students_dept           ON students (department_id)',
        'CREATE INDEX ix_students_status         ON students (status)',
        'CREATE INDEX ix_attend_course_date      ON attendance (course_id, session_date)',
        'CREATE INDEX ix_tt_course               ON timetable (course_id, day_of_week)',
        'CREATE INDEX ix_audit_created           ON audit_logs (created_at)',
    ];
}

/** Swap placeholders for the configured driver. */
function schema_for_driver(string $sql): string
{
    if (DB_DRIVER === 'sqlite') {
        return str_replace(['{PK}', '{SFX}'], ['INTEGER PRIMARY KEY AUTOINCREMENT', ''], $sql);
    }
    return str_replace(
        ['{PK}', '{SFX}'],
        ['INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY', ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'],
        $sql
    );
}

function default_settings(): array
{
    return [
        'org_name'            => ORG_NAME,
        'org_motto'           => ORG_MOTTO,
        'org_phone'           => '+255 763 461 722',
        'org_email'           => 'info@elimuyetu.org',
        'org_address'         => 'Elimu Yetu Kituo cha Jamii, Tanzania',
        'academic_year'       => date('Y'),
        'attendance_threshold' => '80',
        'cert_signatory_1'    => 'Willfredy Mosses',
        'cert_signatory_1_title' => 'Director',
        'cert_signatory_2'    => 'James Francis',
        'cert_signatory_2_title' => 'Lead Instructor',
        'id_card_validity_months' => '12',
        'kitchen_tea_label'   => 'Morning tea (cups)',
        'kitchen_meal_label'  => 'Afternoon meal (plates)',
    ];
}
