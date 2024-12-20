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

class CuttingChartUpdatedAll implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $data;
    public $tglPlan;

    public function __construct($data, $tglPlan)
    {
        $this->data = $data;
        $this->tglPlan = $tglPlan;
    }

    public function broadcastOn()
    {
        return new Channel("cutting-chart-channel-all-{$this->tglPlan}");
    }

    public function broadcastAs()
    {
        return 'UpdatedAllCuttingEvent';
    }

    public function broadcastWith()
    {
        return [
            'data' => $this->data,
            'tglPlan' => $this->tglPlan,
        ];
    }
}
