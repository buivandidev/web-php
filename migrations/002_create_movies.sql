CREATE TABLE movies (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    title            VARCHAR(256)  NOT NULL,
    poster_url       VARCHAR(512),
    genre            VARCHAR(100),
    status           VARCHAR(30)   NOT NULL DEFAULT 'coming_soon',
    duration_minutes INT           NOT NULL CHECK (duration_minutes > 0),
    description      TEXT,
    age_rating       VARCHAR(10),                   -- 'P', 'C13', 'C16', 'C18'
    created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_movies_status CHECK (status IN ('now_showing', 'coming_soon', 'ended')),
    KEY ix_movies_status (status),
    KEY ix_movies_genre (genre)
);
