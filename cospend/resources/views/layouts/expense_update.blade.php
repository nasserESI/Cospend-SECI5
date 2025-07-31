<!-- Button trigger modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateExpenseModal{{ $expense->id }}">
    Update Expense
</button>

<!-- Modal -->

    <div class="modal fade" id="updateExpenseModal{{ $expense->id }}" tabindex="-1" aria-labelledby="updateExpenseModalLabel{{ $expense->id }}" aria-hidden="true">

    
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateExpenseModalLabel">Update Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('expenses.update', $expense->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" class="form-control" id="title" name="title" value="{{ $expense->title }}">
                    </div>
                    <div class="form-group">
                        <label for="date">Date:</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ $expense->date }}">
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount:</label>
                        <input type="number" class="form-control" id="amount" name="amount" value="{{ $expense->amount }}" min="0.01" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="desc">Description:</label>
                        <textarea class="form-control" id="description" name="desc">{{ $expense->desc ?? '' }}</textarea>
                    </div>
                    
                    <!-- Rest of your form fields -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>