<?php
namespace App\Core\Exceptions;

class SeatUnavailableException extends BusinessException
{
    public function __construct(
        public readonly array $unavailableSeats
    ) {
        parent::__construct(
            'Ghế ' . implode(', ', $unavailableSeats) . ' đã có người đặt.'
        );
    }
}
