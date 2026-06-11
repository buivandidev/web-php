<?php
namespace App\Models\Repository\Implementations;

use App\Models\Repository\Interfaces\ITicketRepository;
use App\Models\Domain\Ticket;
use App\Models\Domain\Showtime;
use App\Models\Domain\Room;
use App\Models\Domain\Movie;
use PDO;

class TicketRepository implements ITicketRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function getActiveSeats(int $showtimeId, array $seatCodes): array
    {
        if (empty($seatCodes)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($seatCodes), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT seat_code FROM tickets
             WHERE showtime_id = ?
               AND seat_code IN ($placeholders)
               AND status IN ('holding', 'paid')"
        );
        $stmt->execute([$showtimeId, ...$seatCodes]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getActiveTickets(int $showtimeId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT seat_code, status FROM tickets
             WHERE showtime_id = ?
               AND status IN ('holding', 'paid')"
        );
        $stmt->execute([$showtimeId]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }

    public function updateStatusWithVersion(
        int     $id,
        string  $newStatus,
        int     $expectedVersion,
        ?float  $totalPrice = null,
        ?string $promotionCode = null
    ): int {
        $sql = "UPDATE tickets
                SET status  = :status,
                    version = version + 1";
        $params = [
            ':status'  => $newStatus,
            ':id'      => $id,
            ':version' => $expectedVersion,
        ];

        if ($totalPrice !== null) {
            $sql .= ", total_price = :total_price";
            $params[':total_price'] = $totalPrice;
        }
        if ($promotionCode !== null) {
            $sql .= ", promotion_code = :promotion_code";
            $params[':promotion_code'] = $promotionCode;
        }

        // When status is paid or cancelled, hold_expiry_time should be set to null
        if ($newStatus === 'paid' || $newStatus === 'cancelled') {
            $sql .= ", hold_expiry_time = NULL";
        }

        $sql .= " WHERE id = :id AND version = :version";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function cancelExpiredHolds(): int
    {
        $stmt = $this->pdo->prepare(
            "UPDATE tickets
             SET status = 'cancelled',
                 hold_expiry_time = NULL
             WHERE status = 'holding'
               AND hold_expiry_time < NOW()"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO tickets
             (showtime_id, user_id, seat_code, status, hold_expiry_time, total_price, version)
             VALUES (:showtime_id, :user_id, :seat_code, :status, :hold_expiry_time, :total_price, :version)"
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?Ticket
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tickets WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? Ticket::fromArray($row) : null;
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.*, s.show_date, s.start_time, m.title AS movie_title
             FROM tickets t
             JOIN showtimes s ON s.id = t.showtime_id
             JOIN movies m ON m.id = s.movie_id
             WHERE t.user_id = ? AND t.status = \'paid\'
             ORDER BY t.booked_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getShowtimeByTicketId(int $ticketId): ?Showtime
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.showtime_id FROM tickets t WHERE t.id = ?'
        );
        $stmt->execute([$ticketId]);
        $showtimeId = $stmt->fetchColumn();
        if (!$showtimeId) return null;

        $stmt = $this->pdo->prepare(
            'SELECT s.*, r.name AS room_name, r.total_rows, r.seats_per_row,
                    m.title AS movie_title, m.poster_url, m.genre, m.status AS movie_status,
                    m.duration_minutes, m.description AS movie_description, m.age_rating
             FROM showtimes s
             JOIN rooms r ON r.id = s.room_id
             JOIN movies m ON m.id = s.movie_id
             WHERE s.id = ?'
        );
        $stmt->execute([$showtimeId]);
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
