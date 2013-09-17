var DataGrid = new Object({
    checkAll: function(sender) {
        $('.atf-grid table').find('.grid-table-checker').attr('checked', sender.checked);
    },

    action: function(action, msg, callback) {
        var checkers = $('.atf-grid table input[name="items[]"]:checked');

        if (checkers.length > 0) {
            msg = msg ? msg : '';

            if (msg != '' && !confirm(msg)) {
                $('#datagrid-list-form').get(0).reset();
                return false;
            } else {
                var form = $('#datagrid-list-form');
                if (typeof callback != 'undefined') {
                    callback(form, action, msg);
                } else {
                    $('#datagrid-list-form-action').val(action);
                    form.submit();
                }
            }
        } else {
            alert('Выберите одну или несколько строк.');
        }
    },

    ajaxAction: function(url, callback) {
        $.ajax({
            type: 'GET',
            url: url,
            cache: true,
            dataType: 'html',
            success: callback
        });
    },

    confirmAction: function(sender, message) {
        message = message || "Are you sure?";
        return confirm(message)
    },

    loadDataPanel: function(url, panel) {
        $.ajax({
            type: 'GET',
            url: url,
            cache: true,
            data: 'panel=' + panel,
            dataType: 'html',
            success: function (result) {
                $('#' + panel).html(result);
            },
            error: function(){
                $('#' + panel).html('Oops! Error occured.');
            }
        });
    }
});

jQuery(function($) {
	$('.can-hide-check').on('click', '.icon-check', function(e) {
		var columnId = $(this).closest('[data-columnid]').data('columnid');
		var th = $('th[data-columnid="'+columnId+'"]');
		var position = th.closest('tr').find('th').index(th);
		th.hide();
		th.closest('table').find('tbody tr').each(function() {
			$(this).find('td:eq('+position+')').hide();
		});
		$.cookie('columnshide-'+columnId, '');
	});
	
	$('.can-hide-check').on('click', '.icon-check-empty', function() {
		var columnId = $(this).closest('[data-columnid]').data('columnid');
		var th = $('th[data-columnid="'+columnId+'"]');
		var position = th.closest('tr').find('th').index(th);
		th.show();
		th.closest('table').find('tbody tr').each(function() {
			$(this).find('td:eq('+position+')').show();
		});
		$.cookie('columnshide-'+columnId, 'show');
	});
	
});