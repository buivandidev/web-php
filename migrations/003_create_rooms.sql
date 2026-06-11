CREATE TABLE rooms (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(50)  NOT NULL,    -- VD: 'Phòng 1', 'IMAX'
    total_rows    INT          NOT NULL CHECK (total_rows > 0),
    seats_per_row INT          NOT NULL CHECK (seats_per_row > 0),
    UNIQUE KEY ux_rooms_name (name)
);
