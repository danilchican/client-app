<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',

        'src_payment_id',
        'dst_payment_id',

        'amount',
        'notification',
        'status'
    ];

    /**
     * A bill is owned by a sender user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A bill is owned by a receiver user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receiver()
    {
        return $this->belongsTo(User::class);
    }

    /**
     *  Get a source payment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getSourcePayment()
    {
        return $this->belongsTo(Payment::class, 'src_payment_id');
    }

    /**
     * Get a destination payment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getDestinationPayment()
    {
        return $this->belongsTo(Payment::class, 'dst_payment_id');
    }
}
