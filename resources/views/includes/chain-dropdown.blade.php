@php
// FGLTODO-LP das hier nur once ausführen lassen selbst bei mehreren includes. und nur title notwendig, nicht ganze models..
use App\Models\TravelChain;
use Carbon\Carbon;
$recentUnfinishedChains = TravelChain::where([
    ['user_id', '=', Auth::user()->id],
    ['finished', '=', NULL],
])->oldest()/*->take(5)*/->get();

// Vorauswahl für den Fall "neu".
$defaultChain = "";
$latestChain = $recentUnfinishedChains->first();
$plannedChecked = " checked";
if ($latestChain) {
    $defaultChain = $latestChain->id;
    $plannedChecked = "";
}

@endphp

<div class="chain-wrapper form-floating mb-2">
    <select name="chainId" class="form-select"
            id="form-status-chain-{{ $id_suffix }}">
        <optgroup label="Start einer neuen Reisekette?">
            <option value="" @if($defaultChain === "") selected @endif>Neue Reisekette anlegen</option>
        </optgroup>
        <optgroup label="Laufende Reisekette" id="form-status-chain-recents-{{ $id_suffix }}">
            @foreach($recentUnfinishedChains as $chain)
                <option value="{{ $chain->id }}" @if($defaultChain === $chain->id) selected @endif>{{ $chain->title }}</option>
            @endforeach
        </optgroup>
    </select>
    <label class="form-label" for="form-status-chain-{{ $id_suffix }}">
        Reisekette
    </label>
</div>
<div class="form-switch mb-2">
    <input type="checkbox" role="switch" class="form-check-input" name="planned" id="form-status-planned-{{ $id_suffix }}"{{ $plannedChecked }} />
    <label class="form-check-label" for="form-status-planned-{{ $id_suffix }}">Teil der Plan-Reisekette?</label>
</div>