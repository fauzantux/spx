$(document).ready(function() {
    $('#resiForm').on('submit', function(event) {
        event.preventDefault();
        const resi = $('#resi').val();

        $.ajax({
            url: 'track.php?resi=' + resi,
            type: 'GET',
            success: function(response) {
                $('#result').html(response.html);
                $('#resultContainer').show();
            },
            error: function() {
                alert('Nomor resi tidak ditemukan!');
            }
        });
    });
});