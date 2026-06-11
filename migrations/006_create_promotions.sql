CREATE TABLE promotions (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(50)   NOT NULL,
    discount_type   VARCHAR(20)   NOT NULL,   -- 'percent' | 'fixed'
    discount_value  DECIMAL(10, 2) NOT NULL CHECK (discount_value > 0),
    max_uses        INT           NULL DEFAULT NULL,                       -- NULL = không giới hạn
    used_count      INT           NOT NULL DEFAULT 0,
    expires_at      TIMESTAMP     NULL DEFAULT NULL,
    is_active       BOOLEAN       NOT NULL DEFAULT TRUE,
    UNIQUE KEY ux_promotions_code (code)
);
