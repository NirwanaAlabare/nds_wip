<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use DB;

class TriggerWipLine implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $data;
    public $lineId;

    public function __construct($data, $lineId)
    {
        $this->data = $data;
        $this->lineId = $lineId;
    }

   public function broadcastOn()
    {
        return new Channel("dashboard-wip-line-channel-" . $this->lineId);
    }

    public function broadcastAs()
    {
        return 'UpdatedDashboardWipLineEvent';
    }

    public function broadcastWith()
    {

        return [
            'data' => $this->data,
        ];
    }
}
