CREATE TABLE tickets (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    showtime_id      INT          NOT NULL,
    user_id          INT          NOT NULL,
    seat_code        VARCHAR(10)  NOT NULL,   -- VD: 'A1', 'B12', 'C3'
    status           VARCHAR(20)  NOT NULL DEFAULT 'holding',
    hold_expiry_time TIMESTAMP    NULL DEFAULT NULL,               -- NULL khi status = 'paid'/'cancelled'
    total_price      DECIMAL(10, 0) NOT NULL CHECK (total_price >= 0),
    promotion_code   VARCHAR(50)  NULL DEFAULT NULL,             -- NULL nếu không dùng mã giảm giá
    version          INT          NOT NULL DEFAULT 0,   -- Optimistic Locking
    booked_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    active_seat_code VARCHAR(10)  GENERATED ALWAYS AS (IF(status IN ('holding', 'paid'), seat_code, NULL)) STORED,
    FOREIGN KEY (showtime_id) REFERENCES showtimes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY ux_tickets_active_seat (showtime_id, active_seat_code),
    CONSTRAINT chk_tickets_status CHECK (status IN ('holding', 'paid', 'cancelled')),
    KEY ix_tickets_user_id (user_id),
    KEY ix_tickets_hold_expiry (status, hold_expiry_time)
);
