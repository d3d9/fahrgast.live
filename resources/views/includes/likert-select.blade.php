@php
use App\Enum\Likert5;
@endphp

<div class="mb-2">
    <label class="form-label" style="color: inherit;" for="likert_{{ $name }}">
        {{ $label }}&nbsp;@include('includes.required-star')
    </label>
    <select name="{{ $name }}" id="likert_{{ $name }}" class="form-select" required>
        <option value="" disabled selected>Bitte auswählen</option>
        @foreach(Likert5::cases() as $case)
            <option value="{{ $case->value }}">{{ $case->getLabel($labelType ?? NULL) }}</option>
        @endforeach
    </select>
</div>
