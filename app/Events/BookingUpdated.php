<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Http\Resources\BookingResource;

class BookingUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $booking;
    public $oldStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Booking $booking, string $oldStatus)
    {
        $this->booking = $booking;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('mentor.' . $this->booking->mentor_id),
            new PrivateChannel('student.' . $this->booking->student_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'booking.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'booking' => (new BookingResource($this->booking->load(['student', 'mentor', 'mentorProfile'])))->resolve(),
            'old_status' => $this->oldStatus,
            'new_status' => $this->booking->status,
            'message' => 'Status booking telah berubah dari ' . $this->oldStatus . ' menjadi ' . $this->booking->status
        ];
    }
}
