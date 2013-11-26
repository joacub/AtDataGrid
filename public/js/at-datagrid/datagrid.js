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
	if($('.todo-list').length)
	$('.todo-list').sortable({
		start: function(event, ui) { 
            jQuery.data( ui.item, 'previndex',  ui.item.index());
        },
		update: function( event, ui ) {
			  var columnId = ui.item.data('columnid');
			  var position = ui.item.closest('ul').find('li').index(ui.item);
			  var th = $('th[data-columnid="'+columnId+'"]');
			  var thposition = th.closest('tr').find('th').index(th);
			  var method = jQuery.data( ui.item, 'previndex') > ui.item.index() ? 'before' : 'after';
			  th.closest('table').find('tbody tr').each(function() {
				  var e = $(this).find('td:eq('+thposition+')');
				  if(method == 'after' ) {
					  $(this).find('td:eq('+(position+1)+')').after(e);
				  } else {
					  $(this).find('td:eq('+(position+1)+')').before(e);
				  }
				});
			  th.closest('table').find('thead tr').each(function() {
				  var e = $(this).find('th:eq('+thposition+')');
				  if(method == 'after' ) {
					  $(this).find('th:eq('+(position+1)+')').after(e);
				  } else {
					  $(this).find('th:eq('+(position+1)+')').before(e);
				  }
				  
				});
			  
			  
			  var data = [];
			  $( ".todo-list li" ).each(function() {
				  var columnId = $(this).data('columnid');
				  var position = $(this).closest('ul').find('li').index($(this));
				  var state = ($(this).hasClass('icon-check') ? 0 : 1);
				  data.push({'column':columnId, 'position':position, 'state':state});
			  });
			  $.ajax({
		            type: 'POST',
		            url: $(this).closest('ul').data('url'),
		            cache: true,
		            data: {columns:data, dataGridColumnState:true},
		            dataType: 'html',
		            success: function (result) {
		                //$('#' + panel).html(result);
		            },
		            error: function(){
		                //$('#' + panel).html('Oops! Error occured.');
		            }
		        });
		  }
	});
	
	$('.can-hide-check').on('click', '.icon-check', function(e) {
		var columnId = $(this).closest('[data-columnid]').data('columnid');
		var th = $('th[data-columnid="'+columnId+'"]');
		var position = th.closest('tr').find('th').index(th);
		th.hide();
		th.closest('table').find('tbody tr').each(function() {
			$(this).find('td:eq('+position+')').hide();
		});
//		$.cookie('columnshide-'+columnId, '');
		var column = [];
		column.push({'column':columnId, 'position':position, 'state':0});
		$.ajax({
            type: 'POST',
            url: $(this).closest('ul').data('url'),
            data: {columns:column, dataGridColumnState:true},
            dataType: 'html',
            success: function (result) {
                //$('#' + panel).html(result);
            },
            error: function(){
                //$('#' + panel).html('Oops! Error occured.');
            }
        });
	});
	
	$('.can-hide-check').on('click', '.icon-check-empty', function() {
		var columnId = $(this).closest('[data-columnid]').data('columnid');
		var th = $('th[data-columnid="'+columnId+'"]');
		var position = th.closest('tr').find('th').index(th);
		th.show();
		th.closest('table').find('tbody tr').each(function() {
			$(this).find('td:eq('+position+')').show();
		});
//		$.cookie('columnshide-'+columnId, 'show');
		var column = [];
		column.push({'column':columnId, 'position':position, 'state':1});
		$.ajax({
            type: 'POST',
            url: $(this).closest('ul').data('url'),
            data: {columns:column, dataGridColumnState:true},
            dataType: 'html',
            success: function (result) {
                //$('#' + panel).html(result);
            },
            error: function(){
                //$('#' + panel).html('Oops! Error occured.');
            }
        });
	});
	
});