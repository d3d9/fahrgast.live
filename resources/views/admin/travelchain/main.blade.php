@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="fs-5 card-title mb-4">View chain</h2>
                    <form method="GET" action="{{route('admin.chain.edit')}}">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label class="form-label" for="form-chainId">Chain ID?</label>
                            </div>
                            <div class="col-auto">
                                <input type="number" id="form-chainId" class="form-control" name="chainId"/>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">Bearbeiten</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="fs-5 card-title mb-4" id="h-last-journeys">Last journeys</h2>

                    <hr/>
                    <form method="GET">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="inputUsername" name="userQuery"
                                   value="{{request()->get('userQuery')}}"
                                   placeholder="Username / Displayname"/>
                            <label for="inputUsername" class="form-label">
                                Username / Displayname
                            </label>
                        </div>
                    </form>
                    <hr/>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped" aria-labelledby="h-last-journeys">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Title</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lastChains as $chain)
                                    <tr>
                                        <td>
                                            <a href="{{route('admin.chain.edit', ['chainId' => $chain->id])}}">
                                                {{$chain->id}}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{route('admin.users.user', ['id' => $chain->user->id])}}">
                                                {{'@'.$chain->user->username}}
                                            </a>
                                            <br/>
                                            <small>{{$chain->user->name}}</small>
                                        </td>
                                        <td>
                                            {{ $chain->title }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
