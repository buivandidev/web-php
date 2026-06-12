<?php
namespace App\ViewModels;

use App\Models\Domain\Movie;

class MovieDetailViewModel
{
    public int    $id;
    public string $title;
    public ?string $posterUrl;
    public ?string $genre;
    public string $status;
    public string $formattedDuration;   // VD: "2h 15p"
    public ?string $description;
    public ?string $ageRating;

    /** @var ShowtimeSummary[] */
    public array $showtimes = [];

    // Factory method — map từ Domain Model
    public static function fromMovie(Movie $movie, array $showtimes = []): self
    {
        $vm = new self();
        $vm->id                = $movie->id;
        $vm->title             = $movie->title;
        $vm->posterUrl         = $movie->posterUrl;
        $vm->genre             = $movie->genre;
        $vm->status            = $movie->status;
        $vm->formattedDuration = $movie->getFormattedDuration();
        $vm->description       = $movie->description;
        $vm->ageRating         = $movie->ageRating;
        $vm->showtimes         = $showtimes;
        return $vm;
    }
}


