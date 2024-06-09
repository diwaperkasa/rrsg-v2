jQuery(function ($) {
	var button = $(".load-more");
	var loading = false;
	let page = 2;

	$('.load-more').click(function () {
		if (!loading) {
			loading = true;
			$(".load-more").addClass("no-underline");
			$("body.category-business .post-listing").addClass("hidelast");
			$(".load-more").html('<div class="loader"><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>');

			let query = beloadmore.query;
			query.paged = page;

			var data = {
				action: "be_ajax_load_more",
				nonce: beloadmore.nonce,
				query: query,
			};

			$.post(beloadmore.url, data, function (res) {
				if (res.success) {
					$(".post-listing").append(res.data);
					$(".button-container").append(button);
					$(".load-more").removeClass("no-underline");
					$(".load-more").html("<span>View more stories</span>");
					++page;
					loading = false;
				} else {
					console.log(res);
				}
			}).fail(function (xhr, textStatus, e) {
				console.log(xhr.responseText);
			});
		}
	});
});
