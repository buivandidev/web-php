<?php
namespace App\Models\Domain;

class Movie extends BaseModel
{
    public int    $id;
    public string $title;
    public ?string $posterUrl = null;
    public ?string $genre = null;
    public string $status;           // 'now_showing' | 'coming_soon' | 'ended'
    public int    $durationMinutes;
    public ?string $description = null;
    public ?string $ageRating = null;        // 'P' | 'C13' | 'C16' | 'C18'
    public string $createdAt;

    public function isNowShowing(): bool
    {
        return $this->status === 'now_showing';
    }

    public function getFormattedDuration(): string
    {
        $h = intdiv($this->durationMinutes, 60);
        $m = $this->durationMinutes % 60;
        return $h > 0 ? "{$h}h {$m}p" : "{$m}p";
    }
}
