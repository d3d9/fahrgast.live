<?php

namespace App\Events;

use App\Models\TravelChain;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TravelChainDeleteEvent {
    use Dispatchable, SerializesModels;

    public TravelChain $chain;
    public function __construct(TravelChain $chain) {
        $this->chain               = $chain;
        Log::debug("Dispatching TravelChainDeleteEvent event for chain#" . $chain->id);
    }
}
