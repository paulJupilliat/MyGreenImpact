// allows me to do the auto-completion for the company proposition
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchEnterprise');
    searchInput.addEventListener('keyup', function() {
        const suggestionsDiv = document.getElementById('suggestions');
        const term = this.value.trim();

        if (term.length < 1) {
            suggestionsDiv.innerHTML = '';
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'choose_entreprise.php?term=' + encodeURIComponent(term), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                let data = [];
                try {
                    data = JSON.parse(xhr.responseText);
                } catch (e) {
                    console.error('JSON invalide:', e);
                }

                suggestionsDiv.innerHTML = '';
                if (data.length > 0) {
                    const ul = document.createElement('ul');
                    data.forEach(function(item) {
                        const li = document.createElement('li');
                        li.style.cursor = 'pointer';
                        li.textContent = item.nom;
                        li.addEventListener('click', function() {
                            document.getElementById('enterprise_id').value = item.entreprise_id;
                            searchInput.value = item.nom;
                            suggestionsDiv.innerHTML = '';
                        });
                        ul.appendChild(li);
                    });
                    suggestionsDiv.appendChild(ul);
                }
            }
        };
        xhr.send();
    });
});