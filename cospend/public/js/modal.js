function base64ToArrayBuffer(base64) {
    var binaryString = atob(base64);
    var len = binaryString.length;
    var bytes = new Uint8Array(len);
    for (var i = 0; i < len; i++) {
        bytes[i] = binaryString.charCodeAt(i);
    }
    return bytes.buffer;
}

$(document).ready(function () {
    $('#balanceModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var groupId = button.data('group-id');

        $.get('/groups/' + groupId + '/balances', function (data) {
            var tableBody = $('#balanceTable tbody');
            tableBody.empty();

            $.each(data, function (memberName, balance) {
                var row = $('<tr>').append(
                    $('<td>').text(memberName),
                    $('<td>').text(balance)
                );

                tableBody.append(row);
            });
        });
    });


    $('#to').on('change', function () {
        var userId = $('meta[name="user-id"]').attr('content');
        if ($(this).val().indexOf(userId) === -1) {
            $(this).val($(this).val().concat(userId));
        }
    });


    document.getElementById('loadExpenses').addEventListener('click', function (event) {
        event.preventDefault();
        var email = $('meta[name="user-email"]').attr('content');
        var userId = $('meta[name="user-id"]').attr('content');
        var groupId = $('meta[name="group-id"]').attr('content');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      
        console.log('email: ', email);
        console.log('userId: ', userId);
        console.log('groupId: ', groupId);

        const privateKeyString = localStorage.getItem(`${email}_privateKey`);
        const privateKey = JSON.parse(privateKeyString);

        window.crypto.subtle.importKey(
            "jwk", // can be "jwk" (public or private), "spki" (public only), or "pkcs8" (private only)
            privateKey,
            {   //these are the algorithm options
                name: "RSA-OAEP",
                hash: { name: "SHA-256" }, //can be "SHA-1", "SHA-256", "SHA-384", or "SHA-512"
            },
            true, //whether the key is extractable (i.e. can be used in exportKey)
            ["decrypt"] //"encrypt" or "wrapKey" for public key import or
            //"decrypt" or "unwrapKey" for private key imports
        )
            .then(function (privateKey) {
                fetch(`/api/user/${userId}/group/${groupId}/expenses`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('API response:', data);
                        // Check if data is defined and is an array
                        if (Array.isArray(data)) {
                            // Select the table body
                            const tableBody = document.getElementById('expensesTableBody');

                            // Clear the table body
                            while (tableBody.firstChild) {
                                tableBody.removeChild(tableBody.firstChild);
                            }

                            // Move the expenses processing code here
                            data.forEach(expense => {
                                const decodedTitle = base64ToArrayBuffer(expense.title);
                                const decodedDate = base64ToArrayBuffer(expense.date);
                                const decodedAmount = base64ToArrayBuffer(expense.amount);
                                const decodedDesc = base64ToArrayBuffer(expense.desc);

                                Promise.all([
                                    window.crypto.subtle.decrypt({ name: "RSA-OAEP" }, privateKey, decodedTitle),
                                    window.crypto.subtle.decrypt({ name: "RSA-OAEP" }, privateKey, decodedDate),
                                    window.crypto.subtle.decrypt({ name: "RSA-OAEP" }, privateKey, decodedAmount),
                                    window.crypto.subtle.decrypt({ name: "RSA-OAEP" }, privateKey, decodedDesc)
                                ]).then(decryptedData => {
                                    // Decrypt the data
                                    const title = new TextDecoder().decode(decryptedData[0]);
                                    const date = new TextDecoder().decode(decryptedData[1]);
                                    const amount = new TextDecoder().decode(decryptedData[2]);
                                    const desc = new TextDecoder().decode(decryptedData[3]);

                                    console.log('Decrypted data:', { title, date, amount, desc });

                                    // Create a new table row and append it to the table body
                                    const row = document.createElement('tr');
                                    tableBody.appendChild(row);

                                    // Create and append table data for each piece of decrypted data
                                    //check if authenticated user is the one who created the expense(expense.from==userId)

                                    [title, date, amount, desc].forEach(text => {
                                        const td = document.createElement('td');
                                        td.textContent = text;
                                        row.appendChild(td);
                                    });

                                    // Check if the authenticated user is the creator of the expense
                                    if (expense.from == userId) {
                                        // Create a new table data for the delete button
                                        const tdDelete = document.createElement('td');

                                        // Create the delete button
                                        const deleteButton = document.createElement('button');
                                        deleteButton.textContent = 'Delete';
                                        deleteButton.className = 'btn btn-danger';
                                        deleteButton.addEventListener('click', function () {

                                            fetch(`/expenses/${expense.expense_id}`, {
                                                method: 'DELETE',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    // Add your CSRF token to the request header
                                                    'X-CSRF-TOKEN': csrfToken
                                                }
                                            })
                                                .then(response => {
                                                    if (!response.ok) {
                                                        throw new Error('Network response was not ok');
                                                    }
                                                    return response.json();
                                                })
                                                .then(data => {
                                                    // Handle the response from the server
                                                    if (data.success) {
                                                        // If the expense was deleted successfully, remove the row from the table
                                                        row.remove();
                                                    } else {
                                                        // If there was an error, log it to the console
                                                        console.error(data.error);
                                                    }
                                                })
                                                .catch(error => {
                                                    // Log any errors with the fetch request to the console
                                                    console.error('Error:', error);
                                                });

                                        });

                                        // Append the delete button to the table data
                                        tdDelete.appendChild(deleteButton);

                                        // Append the table data to the row
                                        row.appendChild(tdDelete);
                                    }



                                })
                                    .catch(error => {
                                        console.error('Decryption error:', error);
                                        console.error('Private key:', privateKey);
                                        console.error('Decoded title:', decodedTitle);
                                        console.error('Decoded date:', decodedDate);
                                        console.error('Decoded amount:', decodedAmount);
                                        console.error('Decoded desc:', decodedDesc);
                                    });
                            });
                        }
                        else {
                            console.error('Unexpected API response:', data);
                        }
                    })
                    .catch(error => console.error('Error parsing JSON:', error));
            });
    });

});






