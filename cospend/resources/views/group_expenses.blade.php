@extends('layouts.app')

@section('content')

<button id="loadExpenses" type="button" class="btn btn-primary">
    Load Group Expenses
</button>

 <div class="container">
    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif
    
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
{{--     @if($expenses->isEmpty())
    <div class="alert alert-info mt-3" role="alert">
        There are no expenses in this group yet.
    </div>
    @else --}}
    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th scope="col">Title</th>
                <th scope="col">Date</th>
                <th scope="col">Amount</th>
                <th scope="col">Description</th>
                
            </tr>
        </thead>
            <tbody id="expensesTableBody">
            @foreach($expenses as $key => $expense)
            <tr>
                <th scope="row">{{ $key + 1 }}</th>
                <td>{{ $expense->title }}</td>
                <td>{{ \Carbon\Carbon::parse($expense->date)->format('d-m-Y') }}</td>
                <td>{{ $expense->amount }}</td>
                <td>{{ $expense->desc ?? 'No description' }}</td>
                <td>
                    @if(auth()->id() == $expense->from) <!-- Check if the authenticated user is the creator of the expense -->
                    <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                    @endif
                </td>
                <td>
                    @if(auth()->user()->id == $expense->from)
                    @include ('layouts.expense_update')
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{-- @endif --}}
</div>
@endsection

