CREATE TABLE showtimes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    movie_id    INT       NOT NULL,
    room_id     INT       NOT NULL,
    show_date   DATE      NOT NULL,
    start_time  TIME      NOT NULL,
    price       DECIMAL(10, 0) NOT NULL CHECK (price >= 0),  -- VNĐ
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    UNIQUE KEY ux_showtimes_room_datetime (room_id, show_date, start_time),
    KEY ix_showtimes_movie_date (movie_id, show_date)
);
