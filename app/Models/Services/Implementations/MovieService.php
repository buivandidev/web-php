<?php
namespace App\Models\Services\Implementations;

use App\Models\Services\Interfaces\IMovieService;
use App\Models\Repository\Interfaces\IMovieRepository;
use App\Models\Repository\Interfaces\IShowtimeRepository;
use App\Models\Repository\Interfaces\ITicketRepository;
use App\Models\Domain\Movie;
use App\ViewModels\SeatMapViewModel;
use App\ViewModels\ShowtimeSummary;
use App\Core\Exceptions\NotFoundException;

class MovieService implements IMovieService
{
    public function __construct(
        private readonly IMovieRepository $movieRepo,
        private readonly IShowtimeRepository $showtimeRepo,
        private readonly ITicketRepository $ticketRepo
    ) {}

    public function getNowShowing(): array
    {
        return $this->movieRepo->getFiltered(null, 'now_showing');
    }

    public function getComingSoon(): array
    {
        return $this->movieRepo->getFiltered(null, 'coming_soon');
    }

    public function getFiltered(?string $genre, string $status): array
    {
        return $this->movieRepo->getFiltered($genre, $status);
    }

    public function getAll(): array
    {
        return $this->movieRepo->getAll();
    }

    public function getDetail(int $movieId): Movie
    {
        $movie = $this->movieRepo->findById($movieId);
        if (!$movie) {
            throw new NotFoundException("Không tìm thấy phim với ID $movieId");
        }
        return $movie;
    }

    public function getShowtimesByDate(int $movieId, string $date): array
    {
        $showtimes = $this->showtimeRepo->getByMovieAndDate($movieId, $date);

        $summaries = [];
        foreach ($showtimes as $showtime) {
            $summary = new ShowtimeSummary();
            $summary->id = $showtime->id;
            $summary->showDate = $showtime->showDate;
            $summary->startTime = $showtime->startTime;
            $summary->formattedPrice = $showtime->getFormattedPrice();
            $summary->roomName = $showtime->room->name;

            $activeTickets = $this->ticketRepo->getActiveTickets($showtime->id);
            $totalSeats = $showtime->room->getTotalSeats();
            $summary->availableSeats = max(0, $totalSeats - count($activeTickets));

            $summaries[] = $summary;
        }
        return $summaries;
    }

    public function getSeatMapViewModel(int $showtimeId): SeatMapViewModel
    {
        $showtime = $this->showtimeRepo->findById($showtimeId);
        if (!$showtime) {
            throw new NotFoundException("Không tìm thấy suất chiếu với ID $showtimeId");
        }

        $vm = new SeatMapViewModel();
        $vm->showtimeId = $showtime->id;
        $vm->movieTitle = $showtime->movie->title;
        $vm->showDate = $showtime->showDate;
        $vm->startTime = $showtime->startTime;
        $vm->roomName = $showtime->room->name;
        $vm->pricePerSeat = $showtime->price;
        $vm->totalRows = $showtime->room->totalRows;
        $vm->seatsPerRow = $showtime->room->seatsPerRow;

        $vm->seatStatuses = $this->ticketRepo->getActiveTickets($showtime->id);

        return $vm;
    }

    public function getDashboardStats(): array
    {
        $pdo = \App\Core\Database::getInstance();
        
        $movieCount = (int)$pdo->query('SELECT COUNT(*) FROM movies')->fetchColumn();
        $userCount = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE role = \'user\'')->fetchColumn();
        $ticketCount = (int)$pdo->query('SELECT COUNT(*) FROM tickets WHERE status = \'paid\'')->fetchColumn();
        $revenue = (float)$pdo->query('SELECT COALESCE(SUM(total_price), 0) FROM tickets WHERE status = \'paid\'')->fetchColumn();

        return [
            'movie_count'  => $movieCount,
            'user_count'   => $userCount,
            'ticket_count' => $ticketCount,
            'revenue'      => $revenue
        ];
    }
}
