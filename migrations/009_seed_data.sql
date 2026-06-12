-- migrations/009_seed_data.sql

INSERT INTO movies (title, poster_url, genre, status, duration_minutes, description, age_rating) VALUES
('Dune: Part Two', '/assets/images/dune_poster.png', 'Khoa học viễn tưởng', 'now_showing', 166, 'Hành trình tiếp theo của Paul Atreides trên hành tinh cát Arrakis chống lại gia tộc Harkonnen...', 'C13'),
('Oppenheimer', '/assets/images/oppenheimer_poster.png', 'Chính kịch', 'now_showing', 180, 'Câu chuyện về cuộc đời và những tranh cãi xung quanh cha đẻ của bom nguyên tử J. Robert Oppenheimer...', 'C16'),
('The Nighthawk', '/assets/images/spider_hero_poster.png', 'Hành động', 'coming_soon', 124, 'Người hùng bóng đêm với bộ giáp công nghệ tối tân đứng lên chống lại liên minh tội phạm ngầm...', 'P'),
('Neon Jungle', '/assets/images/sci_fi_forest_poster.png', 'Khoa học viễn tưởng', 'coming_soon', 118, 'Một chuyến hành trình khám phá hệ sinh thái sinh học kỳ lạ đầy ánh sáng huyền ảo ngoài vũ trụ...', 'P');

-- Showtimes for Dune (movie_id = 1) in Room 1 (room_id = 1) and Room 3 (room_id = 3)
INSERT INTO showtimes (movie_id, room_id, show_date, start_time, price) VALUES
(1, 1, CURDATE(), '14:00:00', 90000),
(1, 3, CURDATE(), '19:30:00', 140000),
(1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', 90000);

-- Showtimes for Oppenheimer (movie_id = 2) in Room 2 (room_id = 2) and Room 3 (room_id = 3)
INSERT INTO showtimes (movie_id, room_id, show_date, start_time, price) VALUES
(2, 2, CURDATE(), '16:00:00', 85000),
(2, 3, CURDATE(), '21:00:00', 140000),
(2, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '13:30:00', 85000);
