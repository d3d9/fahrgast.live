@php
    use App\Enum\TravelChainFinished;
    $endDataPending = $chain->endDataPending($totalCount, $pendingCount, $undefCount);
@endphp
<i class="fas fa-train" style="color: inherit;"></i>&nbsp;<span class="badge badge-light">{{ $plannedCount }}</strong> geplant</span>
<span class="badge badge-success">{{ $takenCount }}</strong> gefahren</span>
@if($pendingCount)
    <span class="badge badge-secondary">{{ $pendingCount }}</strong> ausstehend</span>
@endif
@if($undefCount)
    <span class="badge badge-secondary">{{ $undefCount }}</strong> unbestimmt</span>
@endif
<br/>
@if(isset($chain->finished))
    <span class="badge badge-success">Erfassung abgeschlossen</span>
    <span class="badge badge-{{ $chain->finished->isArrived() ? "success" : "danger" }}">{{ $chain->finished->getReason() }}</span>
@else
    <span class="badge badge-info">Erfassung im Gange</span>
@endif
@if($chain->dataPending())
    <span class="badge badge-primary">Angaben ausstehend</span>
@elseif($endDataPending)
    <span class="badge badge-primary">{{ isset($chain->finished) ? 'Angaben zur ' : '' }}Beendigung ausstehend</span>
@endif
