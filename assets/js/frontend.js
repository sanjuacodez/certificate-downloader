jQuery(document).ready(function($) {
	$('#sjs-cert-lookup-form').on('submit', function(e) {
		e.preventDefault();
		
		var admission_number = $('input[name="admission_number"]').val();
		var email = $('input[name="email"]').val();
		var result_div = $('#sjs-cert-result');
		
		result_div.html('<p>Verifying certificate...</p>');
		
		$.ajax({
			url: sjs_cert_frontend.ajax_url,
			type: 'POST',
			data: {
				action: 'sjs_cert_lookup',
				admission_number: admission_number,
				email: email,
				nonce: sjs_cert_frontend.nonce
			},
			success: function(response) {
				if (response.success) {
					result_div.html(response.data.html);
				} else {
					result_div.html('<p class="error">' + response.data + '</p>');
				}
			},
			error: function() {
				result_div.html('<p class="error">An error occurred. Please try again.</p>');
			}
		});
	});
});
