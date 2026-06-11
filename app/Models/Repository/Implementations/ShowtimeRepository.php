<?php
namespace App\Models\Repository\Implementations;

use App\Models\Repository\Interfaces\IShowtimeRepository;
use App\Models\Domain\Showtime;
use App\Models\Domain\Room;
use App\Models\Domain\Movie;
use PDO;

class ShowtimeRepository implements IShowtimeRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function getByMovieAndDate(int $movieId, string $date): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT s.*, r.name AS room_name, r.total_rows, r.seats_per_row
             FROM showtimes s
             JOIN rooms r ON r.id = s.room_id
             WHERE s.movie_id = ?
               AND s.show_date = ?
             ORDER BY s.start_time'
        );
        $stmt->execute([$movieId, $date]);
        $rows = $stmt->fetchAll();

        $showtimes = [];
        foreach ($rows as $row) {
            $showtime = Showtime::fromArray($row);

            $room = new Room();
            $room->id = $row['room_id'];
            $room->name = $row['room_name'];
            $room->totalRows = $row['total_rows'];
            $room->seatsPerRow = $row['seats_per_row'];
            $showtime->room = $room;

            $showtimes[] = $showtime;
        }
        return $showtimes;
    }

    public function findById(int $id): ?Showtime
    {
        $stmt = $this->pdo->prepare(
            'SELECT s.*, r.name AS room_name, r.total_rows, r.seats_per_row,
                    m.title AS movie_title, m.poster_url, m.genre, m.status AS movie_status,
                    m.duration_minutes, m.description AS movie_description, m.age_rating
             FROM showtimes s
             JOIN rooms r ON r.id = s.room_id
             JOIN movies m ON m.id = s.movie_id
             WHERE s.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $showtime = Showtime::fromArray($row);

        $room = new Room();
        $room->id = $row['room_id'];
        $room->name = $row['room_name'];
        $room->totalRows = $row['total_rows'];
        $room->seatsPerRow = $row['seats_per_row'];
        $showtime->room = $room;

        $movie = new Movie();
        $movie->id = $row['movie_id'];
        $movie->title = $row['movie_title'];
        $movie->posterUrl = $row['poster_url'];
        $movie->genre = $row['genre'];
        $movie->status = $row['movie_status'];
        $movie->durationMinutes = $row['duration_minutes'];
        $movie->description = $row['movie_description'];
        $movie->ageRating = $row['age_rating'];
        $showtime->movie = $movie;

        return $showtime;
    }
}
