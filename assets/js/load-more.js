jQuery(function ($) {
	let loading = false;
	let page = 2;

	$('.load-more').click(function (e) {
		if (!loading) {
			let query = beloadmore.query;
			query.paged = page;

			const data = {
				action: "be_ajax_load_more",
				nonce: beloadmore.nonce,
				query: query,
			};

			$.ajax({
				type: "POST",
				url: beloadmore.url,
				data: data,
				beforeSend: function() {
					loading = true;

					$(".load-more")
						.addClass("no-underline")
						.html('<div class="loader"></div>');
				},
				success: function(res) {
					if (res.success) {
						$(".post-listing").append(res.data);
						++page;
					}
				},
				complete: function() {
					loading = false;

					$(".load-more")
						.removeClass("no-underline")
						.html("<span>View more stories</span>");
				},
			});
		}
	});
});
