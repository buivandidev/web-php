<?php
namespace App\Models\Services\Implementations;

use App\Models\Services\Interfaces\ITicketService;
use App\Models\Repository\Interfaces\ITicketRepository;
use App\Core\Database;
use App\Core\ValidationRules;
use App\Core\Exceptions\BusinessException;
use App\Core\Exceptions\SeatUnavailableException;
use App\Core\Exceptions\ConcurrencyException;
use App\Core\ValueObjects\HoldResult;
use App\ViewModels\BookingConfirmViewModel;

class TicketService implements ITicketService
{
    public function __construct(
        private readonly ITicketRepository $ticketRepo,
        private readonly Database          $db
    ) {}

    public function holdSeats(int $userId, int $showtimeId, array $seatCodes): HoldResult
    {
        $count = count($seatCodes);
        if ($count < ValidationRules::MIN_TICKETS_PER_BOOKING) {
            throw new BusinessException('Vui lòng chọn ít nhất 1 ghế.');
        }
        if ($count > ValidationRules::MAX_TICKETS_PER_BOOKING) {
            throw new BusinessException(
                'Chỉ được đặt tối đa ' . ValidationRules::MAX_TICKETS_PER_BOOKING . ' vé mỗi lần.'
            );
        }

        $takenSeats = $this->ticketRepo->getActiveSeats($showtimeId, $seatCodes);
        if (!empty($takenSeats)) {
            throw new SeatUnavailableException($takenSeats);
        }

        $pdo = $this->db::getInstance();
        $pdo->beginTransaction();

        try {
            $expiryTime = date('Y-m-d H:i:s',
                strtotime('+' . ValidationRules::HOLD_DURATION_MINUTES . ' minutes')
            );

            $ticketIds = [];
            foreach ($seatCodes as $seat) {
                $ticketIds[] = $this->ticketRepo->create([
                    'showtime_id'      => $showtimeId,
                    'user_id'          => $userId,
                    'seat_code'        => $seat,
                    'status'           => 'holding',
                    'hold_expiry_time' => $expiryTime,
                    'total_price'      => 0, // Tính sau khi áp mã giảm giá
                    'version'          => 0,
                ]);
            }

            $pdo->commit();

            return new HoldResult(
                ticketIds:  $ticketIds,
                expiryTime: $expiryTime
            );

        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function confirmPayment(array $ticketIds, int $userId, string $paymentMethod, ?float $totalPrice = null, ?string $promotionCode = null): bool
    {
        $pdo = $this->db::getInstance();
        $pdo->beginTransaction();

        try {
            $count = count($ticketIds);
            $individualPrice = $totalPrice !== null ? ($totalPrice / $count) : null;

            foreach ($ticketIds as $ticketId) {
                $ticket = $this->ticketRepo->findById($ticketId);
                if (!$ticket) {
                    throw new BusinessException("Không tìm thấy thông tin vé.");
                }

                if ($ticket->userId !== $userId) {
                    throw new BusinessException('Không có quyền xác nhận vé này.');
                }

                if ($ticket->isExpired()) {
                    throw new BusinessException(
                        'Phiên giữ chỗ đã hết hạn. Vui lòng chọn ghế lại.'
                    );
                }

                $rowsAffected = $this->ticketRepo->updateStatusWithVersion(
                    id:              $ticketId,
                    newStatus:       'paid',
                    expectedVersion: $ticket->version,
                    totalPrice:      $individualPrice,
                    promotionCode:   $promotionCode
                );

                if ($rowsAffected === 0) {
                    throw new ConcurrencyException(
                        'Ghế ' . $ticket->seatCode . ' vừa được người khác đặt. Vui lòng chọn ghế khác.'
                    );
                }
            }

            $pdo->commit();
            return true;

        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function releaseExpiredHolds(): int
    {
        return $this->ticketRepo->cancelExpiredHolds();
    }

    public function getUserTickets(int $userId): array
    {
        return $this->ticketRepo->findByUserId($userId);
    }

    public function buildConfirmViewModel(array $ticketIds): BookingConfirmViewModel
    {
        if (empty($ticketIds)) {
            throw new BusinessException('Không có vé nào được chọn.');
        }

        $selectedSeats = [];
        $subtotal = 0;
        $showtime = null;
        $holdExpiryTime = null;

        foreach ($ticketIds as $ticketId) {
            $ticket = $this->ticketRepo->findById($ticketId);
            if (!$ticket) {
                throw new BusinessException("Không tìm thấy vé với ID $ticketId");
            }

            if ($ticket->isExpired()) {
                throw new BusinessException("Thời gian giữ chỗ đã hết hạn. Vui lòng đặt lại.");
            }

            $selectedSeats[] = $ticket->seatCode;

            if ($showtime === null) {
                $showtime = $this->ticketRepo->getShowtimeByTicketId($ticketId);
                if (!$showtime) {
                    throw new BusinessException("Không tìm thấy suất chiếu cho vé này.");
                }
                $holdExpiryTime = $ticket->holdExpiryTime;
            }

            $subtotal += $showtime->price;
        }

        $vm = new BookingConfirmViewModel();
        $vm->movieTitle = $showtime->movie->title;
        $vm->showDate = $showtime->showDate;
        $vm->startTime = $showtime->startTime;
        $vm->roomName = $showtime->room->name;
        $vm->selectedSeats = $selectedSeats;
        $vm->quantity = count($ticketIds);
        $vm->subtotal = $subtotal;
        $vm->discount = 0;
        $vm->totalPrice = $subtotal;
        $vm->holdExpiryTime = $holdExpiryTime ?? '';
        $vm->promotionCode = null;
        $vm->ticketIds = $ticketIds;

        return $vm;
    }
}
