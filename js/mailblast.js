(function($){
	$(function(){
		if (!$('.mailblast').length) return;

		$.fn.textWidth = function(text, font) {
			if (!$.fn.textWidth.fakeEl) $.fn.textWidth.fakeEl = $('<span>').hide().appendTo(document.body);
			$.fn.textWidth.fakeEl.text(text || this.val() || this.text() || this.attr('placeholder')).css('font', font || this.css('font'));
			return $.fn.textWidth.fakeEl.width();
		};

		$('.wpcf7-mb-tag').each(function(){
			$(this).css('width', $(this).textWidth());
		});

		$('.mb-cf-list').each(function(){
			if (!$(this).find('li:not(.mb-cf-list-template)').length) {
				var template = $(this).find('.mb-cf-list-template');
				template.clone().removeClass('mb-cf-list-template').appendTo($(this));
			}
		});

		$('.mb-cf-add').click(function(){
			var list = $(this).prev('.mb-cf-list');
			var template = list.find('.mb-cf-list-template');
			template.clone().removeClass('mb-cf-list-template').appendTo(list);
		});

		$('.mb-cf-list').on('click', 'button', function(){
			$(this).parents('li').first().remove();
		});


		$('[name^="use_custom_fields"]').each(function(){
			if ($(this).is(':checked')) {
				$(this).parents('li').first().next('.mb-custom-fields').show();
			} else {
				$(this).parents('li').first().next('.mb-custom-fields').hide();
			}
		});

		$('[name^="use_custom_fields"]').change(function(){
			if ($(this).is(':checked')) {
				$(this).parents('li').first().next('.mb-custom-fields').show();
			} else {
				$(this).parents('li').first().next('.mb-custom-fields').hide();
			}
		});

		$('.mailblast button').each(function(){
			$(this)[0].defaultValue = $(this).html();
			$(this).val($(this).html());
		});

		if ($('[name="second_list"]').is(':checked')) {
			$('.second-list').show();
		} else {
			$('.second-list').hide();
		}

		$('[name="second_list"]').change(function(){
			if ($(this).is(':checked')) {
				$('.second-list').show();
			} else {
				$('.second-list').hide();
			}
		});
	});
})(jQuery)
