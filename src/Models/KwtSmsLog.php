<?php

namespace KwtSMS\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $message_id
 * @property string $recipient
 * @property string|null $sender_id
 * @property string $message
 * @property string $status
 * @property string|null $event_type
 * @property string $recipient_type
 * @property bool $is_test
 * @property int $pages
 * @property int $numbers_sent
 * @property float $points_charged
 * @property float|null $balance_after
 * @property array|null $api_request
 * @property array|null $api_response
 * @property string|null $error_code
 * @property string|null $error_message
 * @property Carbon|null $sent_at
 */
class KwtSmsLog extends Model
{
    protected $table = 'kwtsms_logs';

    protected $fillable = [
        'message_id',
        'recipient',
        'sender_id',
        'message',
        'status',
        'event_type',
        'recipient_type',
        'is_test',
        'pages',
        'numbers_sent',
        'points_charged',
        'balance_after',
        'api_request',
        'api_response',
        'error_code',
        'error_message',
        'sent_at',
    ];

    public function casts(): array
    {
        return [
            'api_request' => 'array',
            'api_response' => 'array',
            'is_test' => 'boolean',
            'pages' => 'integer',
            'points_charged' => 'float',
            'balance_after' => 'float',
            'sent_at' => 'datetime',
        ];
    }
}
