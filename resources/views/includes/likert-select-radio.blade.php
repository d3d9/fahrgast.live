@php
use App\Enum\Likert5;
@endphp

<div class="mt-4 mb-2">
    <label class="form-label" style="color: inherit;" for="likert_{{ $name }}">
        {{ $label }}&nbsp;@include('includes.required-star')
    </label>
    @php
        $cases = Likert5::cases()
    @endphp
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <small style="hyphens: auto;" class="text-muted">{{ reset($cases)->getLabel($labelType ?? NULL) }}</small>
        <div id="likert_{{ $name }}" style="display: flex; gap: .5em; flex: 0; margin-left: 4px; margin-right: 4px;">
            @foreach($cases as $case)
                <input class="form-check-input" required="required" type="radio" name="{{ $name }}" value="{{ $case->value }}" aria-label="{{ $case->getLabel($labelType ?? NULL) }}" />
            @endforeach
        </div>
        <small style="hyphens: auto;" class="text-muted">{{ end($cases)->getLabel($labelType ?? NULL) }}</small>
    </div>
</div>
