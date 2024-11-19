var searchbar = document.getElementById('search');
searchbar.addEventListener('input', function() {
    var search = searchbar.value.toLowerCase();
    var rows = document.querySelectorAll('tbody tr');

    rows.forEach(function(row) {
        var prenom = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        var nom = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        var email = row.querySelector('td:nth-child(4)').textContent.toLowerCase();

        if (prenom.includes(search) || nom.includes(search) || email.includes(search)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});