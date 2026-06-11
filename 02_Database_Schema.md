# 02 — Thiết kế Cơ sở Dữ liệu (PostgreSQL)

> **Dành cho AI Agent:** Tạo migration SQL theo đúng thứ tự bên dưới. Luôn dùng **Prepared Statements** qua PDO, không bao giờ nối chuỗi SQL trực tiếp.

---

## 1. Sơ đồ quan hệ (ERD tóm tắt)

```
Users ──────────── Tickets ──────────── Showtimes ──────── Movies
  │ (1)         (N) │ (N)           (1) │ (N)         (1) │
  └───── owns ──────┘                   └──── belongs ────┘
                                              │ (N)    (1) │
                                         Rooms ────────────┘
```

---

## 2. Bảng `users`

```sql
CREATE TABLE users (
    id            SERIAL PRIMARY KEY,
    username      VARCHAR(100)  NOT NULL,
    email         VARCHAR(256)  NOT NULL,
    password_hash VARCHAR(512)  NOT NULL,          -- bcrypt hash
    role          VARCHAR(20)   NOT NULL DEFAULT 'user',  -- 'admin' | 'user'
    created_at    TIMESTAMP     NOT NULL DEFAULT NOW(),
    updated_at    TIMESTAMP     NOT NULL DEFAULT NOW()
);

-- Email phải là duy nhất
CREATE UNIQUE INDEX ux_users_email ON users(email);

-- CONSTRAINT kiểm tra role hợp lệ
ALTER TABLE users
    ADD CONSTRAINT chk_users_role CHECK (role IN ('admin', 'user'));
```

**Ghi chú:**
- `password_hash`: Lưu kết quả `password_hash($password, PASSWORD_BCRYPT)`. **Không bao giờ lưu mật khẩu raw.**
- `role`: Mặc định là `'user'`. Admin phải được set thủ công hoặc qua migration seed.

---

## 3. Bảng `movies`

```sql
CREATE TABLE movies (
    id               SERIAL PRIMARY KEY,
    title            VARCHAR(256)  NOT NULL,
    poster_url       VARCHAR(512),
    genre            VARCHAR(100),
    status           VARCHAR(30)   NOT NULL DEFAULT 'coming_soon',
    duration_minutes INT           NOT NULL CHECK (duration_minutes > 0),
    description      TEXT,
    age_rating       VARCHAR(10),                   -- 'P', 'C13', 'C16', 'C18'
    created_at       TIMESTAMP     NOT NULL DEFAULT NOW()
);

-- Index cho các cột thường xuyên được filter/search
CREATE INDEX ix_movies_status ON movies(status);
CREATE INDEX ix_movies_genre  ON movies(genre);

-- CONSTRAINT kiểm tra status hợp lệ
ALTER TABLE movies
    ADD CONSTRAINT chk_movies_status
    CHECK (status IN ('now_showing', 'coming_soon', 'ended'));
```

---

## 4. Bảng `rooms`

```sql
CREATE TABLE rooms (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(50)  NOT NULL,    -- VD: 'Phòng 1', 'IMAX'
    total_rows  INT          NOT NULL CHECK (total_rows > 0),
    seats_per_row INT        NOT NULL CHECK (seats_per_row > 0)
);

-- Tên phòng phải là duy nhất
CREATE UNIQUE INDEX ux_rooms_name ON rooms(name);
```

---

## 5. Bảng `showtimes`

```sql
CREATE TABLE showtimes (
    id          SERIAL PRIMARY KEY,
    movie_id    INT       NOT NULL REFERENCES movies(id) ON DELETE CASCADE,
    room_id     INT       NOT NULL REFERENCES rooms(id),
    show_date   DATE      NOT NULL,
    start_time  TIME      NOT NULL,
    price       NUMERIC(10, 0) NOT NULL CHECK (price >= 0),  -- VNĐ
    created_at  TIMESTAMP NOT NULL DEFAULT NOW()
);

-- Composite Index — tăng tốc query "suất chiếu của phim X trong ngày Y"
CREATE INDEX ix_showtimes_movie_date ON showtimes(movie_id, show_date);

-- Tránh tạo 2 suất chiếu cùng phòng, cùng giờ
CREATE UNIQUE INDEX ux_showtimes_room_datetime
    ON showtimes(room_id, show_date, start_time);
```

---

## 6. Bảng `tickets` (Bảng quan trọng nhất)

```sql
CREATE TABLE tickets (
    id               SERIAL PRIMARY KEY,
    showtime_id      INT          NOT NULL REFERENCES showtimes(id),
    user_id          INT          NOT NULL REFERENCES users(id),
    seat_code        VARCHAR(10)  NOT NULL,   -- VD: 'A1', 'B12', 'C3'
    status           VARCHAR(20)  NOT NULL DEFAULT 'holding',
    hold_expiry_time TIMESTAMP,               -- NULL khi status = 'paid'/'cancelled'
    total_price      NUMERIC(10, 0) NOT NULL CHECK (total_price >= 0),
    promotion_code   VARCHAR(50),             -- NULL nếu không dùng mã giảm giá
    version          INT          NOT NULL DEFAULT 0,   -- Optimistic Locking
    booked_at        TIMESTAMP    NOT NULL DEFAULT NOW()
);

-- QUAN TRỌNG: Một ghế trong một suất chiếu chỉ có thể 'holding' hoặc 'paid' 1 lần
-- Partial unique index: chỉ áp dụng khi status KHÔNG phải 'cancelled'
CREATE UNIQUE INDEX ux_tickets_active_seat
    ON tickets(showtime_id, seat_code)
    WHERE status IN ('holding', 'paid');

-- CONSTRAINT kiểm tra status
ALTER TABLE tickets
    ADD CONSTRAINT chk_tickets_status
    CHECK (status IN ('holding', 'paid', 'cancelled'));

-- Index tìm kiếm vé của user
CREATE INDEX ix_tickets_user_id ON tickets(user_id);

-- Index quét vé hết hạn giữ chỗ (dùng cho Background Job)
CREATE INDEX ix_tickets_hold_expiry ON tickets(hold_expiry_time)
    WHERE status = 'holding';
```

**Giải thích `version` (Optimistic Locking):**
Khi 2 người đặt cùng ghế cùng lúc, câu UPDATE sẽ check `version`:
```sql
UPDATE tickets
SET status = 'paid', version = version + 1
WHERE id = $1 AND version = $2;
-- Nếu rowCount = 0 → người khác đã thắng → báo lỗi cho user hiện tại
```

---

## 7. Bảng `promotions` (Mã giảm giá)

```sql
CREATE TABLE promotions (
    id              SERIAL PRIMARY KEY,
    code            VARCHAR(50)   NOT NULL,
    discount_type   VARCHAR(20)   NOT NULL,   -- 'percent' | 'fixed'
    discount_value  NUMERIC(10, 2) NOT NULL CHECK (discount_value > 0),
    max_uses        INT,                       -- NULL = không giới hạn
    used_count      INT           NOT NULL DEFAULT 0,
    expires_at      TIMESTAMP,
    is_active       BOOLEAN       NOT NULL DEFAULT TRUE
);

CREATE UNIQUE INDEX ux_promotions_code ON promotions(code);
```

---

## 8. Thứ tự chạy Migration

```
001_create_users.sql
002_create_movies.sql
003_create_rooms.sql
004_create_showtimes.sql
005_create_tickets.sql
006_create_promotions.sql
007_seed_admin_user.sql      -- INSERT user admin mẫu
008_seed_rooms.sql           -- INSERT phòng chiếu mẫu
```

---

## 9. Quy ước đặt tên

| Loại | Quy ước | Ví dụ |
|------|---------|-------|
| Tên bảng | `snake_case`, số nhiều | `users`, `showtimes` |
| Tên cột | `snake_case` | `movie_id`, `hold_expiry_time` |
| Primary Key | `id` | `id SERIAL PRIMARY KEY` |
| Foreign Key | `{table_singular}_id` | `movie_id`, `user_id` |
| Index thường | `ix_{table}_{column}` | `ix_movies_status` |
| Unique Index | `ux_{table}_{column}` | `ux_users_email` |
| Constraint | `chk_{table}_{column}` | `chk_tickets_status` |

---

## 10. Câu query mẫu hay dùng

```sql
-- Lấy danh sách suất chiếu của phim X trong ngày Y (dùng Composite Index)
SELECT s.*, r.name AS room_name
FROM showtimes s
JOIN rooms r ON r.id = s.room_id
WHERE s.movie_id = $1
  AND s.show_date = $2
ORDER BY s.start_time;

-- Lấy sơ đồ ghế của một suất chiếu (ghế nào đã đặt)
SELECT seat_code, status
FROM tickets
WHERE showtime_id = $1
  AND status IN ('holding', 'paid');

-- Quét và hủy vé hết hạn giữ chỗ (Background Job)
UPDATE tickets
SET status = 'cancelled'
WHERE status = 'holding'
  AND hold_expiry_time < NOW()
RETURNING id, seat_code, showtime_id;
```
