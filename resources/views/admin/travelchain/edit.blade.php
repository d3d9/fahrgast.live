@php
    use Illuminate\Database\Eloquent\Model;
@endphp

@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4 fs-5">{{ $chain->title }}</h2>
                    <div class="row">
                        <div class="col-4">
                            <label class="form-label" for="form-origin">Benutzer</label>
                        </div>
                        <div class="col-8">
                            {{$chain->user->name}}
                            <small>
                                <a href="{{route('admin.users.user', ['id' => $chain->user->id])}}">
                                    {{'@'.$chain->user->username}}
                                </a>
                            </small>
                        </div>

                        <div class="col-4">
                            <label class="form-label" for="form-origin">Angelegt</label>
                        </div>
                        <div class="col-8">
                            {{$chain->created_at->format('d.m.Y H:i:s')}}
                        </div>

                        <div class="col-4">
                            <label class="form-label" for="form-origin">Fahrt</label>
                        </div>
                        <div class="col-8">
                        </div>
                    </div>
                    <hr/>
                    <div class="row">
                        <div class="col-4">
                            <label class="form-label" for="form-origin">Beschreibung</label>
                        </div>
                        <div class="col-8">
                            <textarea readonly class="form-control" name="body">{{$chain->body}}</textarea>
                        </div>
                    </div>
                    <div class="row">
                        @php dump($chain->attributesToArray()); @endphp
                        FGLTODO: mehr attribute business etc.
                        <br/>FGLTODO: status tabelle inkl neue attrs
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
