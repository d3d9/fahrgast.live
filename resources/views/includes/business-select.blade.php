@php
use App\Enum\Business;
@endphp

<div class="form-floating mb-2">
    <select name="business_check" id="chain_business_select" class="form-select" required>
        <option value="" disabled selected>Bitte auswählen</option>
        @foreach(Business::cases() as $case)
            @if($case !== Business::PRIVATE)
                <option value="{{ $case->value }}">{{ $case->title() }}</option>
            @endif
        @endforeach
    </select>
    <label class="form-label" for="chain_business_select">
        Wegezweck&nbsp;@include('includes.required-star')
    </label>
</div>
