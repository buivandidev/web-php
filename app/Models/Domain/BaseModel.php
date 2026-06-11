<?php
namespace App\Models\Domain;

abstract class BaseModel
{
    /**
     * Map array (từ DB fetch) sang properties của class.
     * Dùng: $movie = Movie::fromArray($row);
     */
    public static function fromArray(array $data): static
    {
        $instance = new static();
        foreach ($data as $key => $value) {
            // Chuyển snake_case → camelCase: movie_id → movieId
            $property = lcfirst(str_replace('_', '', ucwords($key, '_')));
            if (property_exists($instance, $property)) {
                $instance->$property = $value;
            }
        }
        return $instance;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
