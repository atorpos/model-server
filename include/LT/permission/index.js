
//https://johnny.github.io/jquery-sortable/#docs
require(['jquery-sortable'], function () {
	$("#permissions").sortable({
		handle: 'i.fa',
		onDragStart: function ($item, container, _super) {
			// Duplicate items of the no drop area
			if (!container.options.drop)
				$item.clone().insertAfter($item);
			_super($item, container);
		},
		onDrop: function ($item, container, _super, event) {
			$item.removeClass(container.group.options.draggedClass).removeAttr("style");
			$("body").removeClass(container.group.options.bodyClass);

			var ids = [];
			$item.parent().children("li").each(function () {
				ids.push($(this).data("id"));
			});
			LT.ajax('index?action=sorting', {ids: ids});
		}
	});
});