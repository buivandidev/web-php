CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(100)  NOT NULL,
    email         VARCHAR(256)  NOT NULL,
    password_hash VARCHAR(512)  NOT NULL,          -- bcrypt hash
    role          VARCHAR(20)   NOT NULL DEFAULT 'user',  -- 'admin' | 'user'
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY ux_users_email (email),
    CONSTRAINT chk_users_role CHECK (role IN ('admin', 'user'))
);
