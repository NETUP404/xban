jQuery(document).ready(function($) {
    $('.delete-banner-button').on('click', function(e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this banner?')) {
            return;
        }

        var bannerId = $(this).closest('.delete-form').data('banner-id');
        var nonce = ajax_delete_params.nonce;

        $.ajax({
            url: ajax_delete_params.ajax_url,
            type: 'POST',
            data: {
                action: 'delete_banner',
                banner_id: bannerId,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Banner deleted successfully.');
                    location.reload();
                } else {
                    alert('Failed to delete banner: ' + response.data);
                }
            }
        });
    });
});