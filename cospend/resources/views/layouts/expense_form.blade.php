<!-- Button trigger modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
    Create Expense
</button>

<!-- Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addExpenseModalLabel">Create Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('expenses.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="group_id">Group ID:</label>
                        <input type="hidden" class="form-control" id="group_id" name="group_id" value="{{ $id }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" class="form-control" id="title" name="title">
                    </div>
                    <div class="form-group">
                        <label for="date">Date:</label>
                        <input type="date" class="form-control" id="date" name="date">
                    </div>
                    <div class="form-group">
                        <label for="from">From:</label>
                        <input type="hidden" class="form-control" id="from" name="from" value="{{ auth()->id() }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="to">To:</label>
                        <select multiple class="form-control" id="to" name="to[]" data-user-id="{{ $user_id }}">
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" {{ $member->id == auth()->id() ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount:</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount">
                    </div>
                    <div class="form-group">
                        <label for="desc">Description:</label>
                        <textarea class="form-control" id="desc" name="desc"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>