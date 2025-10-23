document.addEventListener('DOMContentLoaded', function() {
    // Add click event listeners to all bookmark forms
    document.querySelectorAll('form[action*="bookmarks"]').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: new FormData(form)
                });

                if (!response.ok) throw new Error('Network response was not ok');

                const data = await response.json();
                
                // Update the form action based on the current state
                const currentAction = form.action;
                if (currentAction.includes('bookmarks/store')) {
                    form.action = currentAction.replace('store', 'destroy');
                    form.querySelector('button').innerHTML = '<i class="fas fa-bookmark me-2"></i>Remove Bookmark';
                    form.querySelector('button').classList.replace('btn-primary', 'btn-danger');
                } else {
                    form.action = currentAction.replace('destroy', 'store');
                    form.querySelector('button').innerHTML = '<i class="far fa-bookmark me-2"></i>Add Bookmark';
                    form.querySelector('button').classList.replace('btn-danger', 'btn-primary');
                }

            } catch (error) {
                console.error('Error:', error);
            }
        });
    });
});