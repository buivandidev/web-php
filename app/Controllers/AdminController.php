<?php
namespace App\Controllers;

use App\Core\Container;
use App\Models\Services\Interfaces\IMovieService;
use App\Models\Repository\Interfaces\IMovieRepository;
use App\Core\Session;

class AdminController extends BaseController
{
    private IMovieService $movieService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->movieService = $container->make(IMovieService::class);
    }

    // GET /admin/dashboard  [Authorize: admin]
    public function dashboard(): void
    {
        $this->requireAdmin();

        $stats = $this->movieService->getDashboardStats();
        $this->render('admin.dashboard', ['stats' => $stats]);
    }

    // GET /admin/movies [Authorize: admin]
    public function movies(): void
    {
        $this->requireAdmin();
        $movies = $this->movieService->getAll();
        $this->render('admin.movies.index', compact('movies'));
    }

    // POST /admin/movies  [Authorize: admin]
    public function storeMovie(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $title = trim($_POST['title'] ?? '');
        $genre = trim($_POST['genre'] ?? '');
        $status = trim($_POST['status'] ?? 'coming_soon');
        $durationMinutes = (int)($_POST['duration_minutes'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $ageRating = trim($_POST['age_rating'] ?? 'P');

        $errors = [];
        if (empty($title)) {
            $errors['title'] = 'Tiêu đề không được để trống.';
        }
        if ($durationMinutes <= 0) {
            $errors['duration_minutes'] = 'Thời lượng phải lớn hơn 0.';
        }

        // Handle file upload
        $posterUrl = null;
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['poster']['tmp_name'];
            $name = basename($_FILES['poster']['name']);
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $newName = uniqid() . '.' . $ext;
            $uploadDir = ROOT_PATH . '/public/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (move_uploaded_file($tmpName, $uploadDir . '/' . $newName)) {
                $posterUrl = '/uploads/' . $newName;
            }
        }

        if (!empty($errors)) {
            $movies = $this->movieService->getAll();
            $this->render('admin.movies.index', compact('movies', 'errors'));
            return;
        }

        try {
            $movieRepo = $this->container->make(IMovieRepository::class);
            $movieRepo->create([
                'title'            => $title,
                'poster_url'       => $posterUrl,
                'genre'            => $genre,
                'status'           => $status,
                'duration_minutes' => $durationMinutes,
                'description'      => $description,
                'age_rating'       => $ageRating
            ]);

            Session::setFlash('success', 'Thêm phim mới thành công!');
            $this->redirect('/admin/movies');

        } catch (\Exception $e) {
            $errors['general'] = 'Lỗi hệ thống: ' . $e->getMessage();
            $movies = $this->movieService->getAll();
            $this->render('admin.movies.index', compact('movies', 'errors'));
        }
    }
}
