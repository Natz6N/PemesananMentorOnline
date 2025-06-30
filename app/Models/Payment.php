<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;
     protected $fillable = [
        'booking_id', 'payment_code', 'amount', 'payment_method',
        'status', 'external_id', 'payment_details', 'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Helper methods
    public function generatePaymentCode()
    {
        $this->payment_code = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -8));
        return $this->payment_code;
    }

    public function markAsPaid($externalId = null, $details = null)
    {
        $this->status = 'paid';
        $this->paid_at = now();
        if ($externalId) $this->external_id = $externalId;
        if ($details) $this->payment_details = $details;
        $this->save();
    }
}
