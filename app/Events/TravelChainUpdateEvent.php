<?php

namespace App\Events;

use App\Models\TravelChain;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TravelChainUpdateEvent
{
    use Dispatchable, SerializesModels;

    public TravelChain $chain;

    public function __construct(TravelChain $chain) {
        $this->chain = $chain;
        Log::debug("Dispatching TravelChainUpdateEvent event for chain#" . $chain->id);
    }
}
