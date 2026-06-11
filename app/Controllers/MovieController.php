<?php
namespace App\Controllers;

use App\Core\Container;
use App\Models\Services\Interfaces\IMovieService;
use App\Models\Services\Interfaces\ITicketService;
use App\ViewModels\MovieDetailViewModel;

class MovieController extends BaseController
{
    private IMovieService $movieService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->movieService = $container->make(IMovieService::class);
    }

    // GET /movies
    public function index(): void
    {
        $genre  = $_GET['genre'] ?? null;
        $status = $_GET['status'] ?? 'now_showing';
        $movies = $this->movieService->getFiltered($genre, $status);

        $this->render('movie.index', compact('movies', 'genre', 'status'));
    }

    // GET /movies/{id}
    public function detail(int $id): void
    {
        $movie     = $this->movieService->getDetail($id);
        $date      = $_GET['date'] ?? date('Y-m-d');
        $showtimes = $this->movieService->getShowtimesByDate($id, $date);

        $viewModel = MovieDetailViewModel::fromMovie($movie, $showtimes);

        $this->render('movie.detail', ['movie' => $viewModel, 'selectedDate' => $date]);
    }

    // GET /my-tickets
    public function myTickets(): void
    {
        $this->requireLogin();
        $ticketService = $this->container->make(ITicketService::class);
        $tickets = $ticketService->getUserTickets($this->getCurrentUserId());
        
        $this->render('movie.my_tickets', [
            'tickets'   => $tickets,
            'pageTitle' => 'Vé của tôi'
        ]);
    }
}
