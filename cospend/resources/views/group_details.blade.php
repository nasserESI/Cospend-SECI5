@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    {{ __('GROUP MEMBERS of ') }}<strong><em>{{ $group_name }}</em></strong>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <div class="d-flex align-items-center">
            @if(auth()->id() == $user_id)
                @include('layouts.member_form')
            @endif
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#balanceModal" data-group-id="{{ $id }}">
                View Members Balances
            </button>
            <form   {{-- id="groupExpensesForm"  --}} action="{{ route('groups.expenses', ['group' => $id]) }}" method="GET">
            {{-- <form   id="groupExpensesForm" > --}}
                <button type="submit" class="btn btn-primary">
                    View Group Expenses
                </button>
            </form>
            @include('layouts.balance_table')
            @include('layouts.expense_form')
        </div>
    </div> 
    
    
    @if (session('error'))
    <div class="alert alert-danger mt-3">
        {{ session('error') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if($members->isEmpty())
    <div class="alert alert-info mt-3" role="alert">
        There are no members in this group yet.
    </div>

@else
<table class="table table-striped mt-4">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                @if(auth()->id() == $user_id)
                    <th scope="col">Delete</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($members as $key => $member)
            <tr>
                <th scope="row">{{ $key + 1 }}</th>
                <td class="{{ $member->id == $user_id ? 'text-primary' : '' }}">
                    {{ $member->name }} {{ $member->id == $user_id ? '(owner)' : '' }}
                </td>
                <td class="{{ $member->id == $user_id ? 'text-primary' : '' }}">{{ $member->email }}</td> 
                <td>
                    @if(auth()->id() == $user_id && auth()->id() != $member->id)
                    <form action="{{ route('groups.removeUser', ['group' => $id, 'user' => $member->id]) }}" method="POST">
                        
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                            </svg>
                        </button>
                    </form>
                    @endif
                </td> 
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
</div>
@endsection

