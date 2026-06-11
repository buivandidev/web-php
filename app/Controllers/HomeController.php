<?php
namespace App\Controllers;

use App\Core\Container;
use App\Models\Services\Interfaces\IMovieService;

class HomeController extends BaseController
{
    private IMovieService $movieService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->movieService = $container->make(IMovieService::class);
    }

    // GET /
    public function index(): void
    {
        $nowShowing = $this->movieService->getNowShowing();
        $comingSoon = $this->movieService->getComingSoon();

        $this->render('home.index', [
            'nowShowing' => $nowShowing,
            'comingSoon' => $comingSoon,
            'pageTitle'  => 'CinemaX — Đặt vé trực tuyến',
        ]);
    }
}
