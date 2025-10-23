document.addEventListener('DOMContentLoaded', function() {
    // Add click event listeners to all bookmark forms
    document.querySelectorAll('.bookmark-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const response = await fetch(form.action, {
                    method: form.querySelector('input[name="_method"]') ? 'DELETE' : 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error('Network response was not ok');

                const data = await response.json();
                
                // Update the button appearance
                const button = form.querySelector('button');
                const icon = button.querySelector('i');
                
                if (form.querySelector('input[name="_method"]')) {
                    // Currently bookmarked, switching to unbookmarked
                    button.classList.replace('btn-danger', 'btn-primary');
                    form.querySelector('input[name="_method"]').remove();
                    button.innerHTML = '<i class="fas fa-bookmark"></i> Add Bookmark';
                } else {
                    // Currently unbookmarked, switching to bookmarked
                    button.classList.replace('btn-primary', 'btn-danger');
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                    button.innerHTML = '<i class="fas fa-bookmark"></i> Remove Bookmark';
                }

            } catch (error) {
                console.error('Error:', error);
                alert('Failed to update bookmark. Please try again.');
            }
        });
    });
});