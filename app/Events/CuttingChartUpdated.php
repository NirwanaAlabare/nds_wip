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

class CuttingChartUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $data;
    public $mejaId;
    public $tglPlan;

    public function __construct($data, $mejaId, $tglPlan)
    {
        $this->data = $data;
        $this->mejaId = $mejaId;
        $this->tglPlan = $tglPlan;
    }

   public function broadcastOn()
    {
        return new Channel("cutting-chart-channel-{$this->mejaId}-{$this->tglPlan}");
    }

    public function broadcastAs()
    {
        return 'UpdatedCuttingEvent';
    }

    public function broadcastWith()
    {
        return [
            'data' => $this->data,
            'mejaId' => $this->mejaId,
            'tglPlan' => $this->tglPlan,
        ];
    }
}
