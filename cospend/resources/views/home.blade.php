@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
            </div>
        </div>
    </div>
    
    @include('layouts.group_form')

    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    
    @if (session('success'))
        <div class="alert alert-danger">
            {{ session('success') }}
        </div>
    @endif

    @if($groups->count() == 0)
    <div class="alert alert-info mt-3" role="alert">
        You don't have any groups yet.
    </div>
    @else
    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Group Name</th>
                <th scope="col">Group Details</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groups as $key => $group)
                <tr>
                    <th scope="row">{{ $key + 1 }}</th>
                    <td>{{ $group->name }}</td>
                    <td><a href="{{ route('groups.show',$group->id) }}"> {{  $group->name }}</a></td>
                    <td>
                        @if(auth()->user()->id == $group->user_id)
                            <form action="{{ route('groups.destroy',$group->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete Group</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endempty
</div>
@endsection

