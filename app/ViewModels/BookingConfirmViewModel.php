<?php
namespace App\ViewModels;

class BookingConfirmViewModel
{
    public string  $movieTitle;
    public string  $showDate;
    public string  $startTime;
    public string  $roomName;

    /** @var string[] */
    public array   $selectedSeats;      // ['A1', 'A2']

    public int     $quantity;           // Số ghế
    public float   $subtotal;           // Giá gốc
    public float   $discount;           // Số tiền giảm
    public float   $totalPrice;         // Thành tiền
    public string  $holdExpiryTime;     // Thời gian hết hạn giữ chỗ (countdown)
    public ?string $promotionCode = null;

    /** @var int[] Danh sách ticket ID đang giữ */
    public array   $ticketIds;
}
