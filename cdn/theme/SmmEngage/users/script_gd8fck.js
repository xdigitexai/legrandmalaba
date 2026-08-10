$(document).ready(function() {
    console.log("Manus Script Loaded");
    
    $(document).on('change', '#neworder_category', function() {
        console.log("Category Changed");
        category_detail();
    });
    
    $(document).on('change', '#neworder_services', function() {
        console.log("Service Changed");
        service_detail();
    });
    
    $(document).on('keyup input', '#neworder_quantity', function() {
        dripfeed_charge();
    });
    
    $(document).on('keyup input', '#neworder_comment', function() {
        comment_charge();
    });
    
    $(document).on('change', '#dripfeedcheckbox', function() {
        dripfeed_charge();
    });
    
    $(document).on('keyup input', '#dripfeed-runs', function() {
        dripfeed_charge();
    });

    // Initial load
    if ($('#neworder_category').val()) {
        category_detail();
    }
});

function category_detail() {
    var category_now = $('#neworder_category').val();
    if (!category_now) return;
    $.post('/ajax_data', {
        action: 'services_list',
        category: category_now
    }, function(data) {
        $('#neworder_services').html(data.services);
        $('#neworder_services').trigger('change');
    }, 'json');
}

function service_detail() {
    var service_now = $('#neworder_services').val();
    if (!service_now) return;
    $.post('/ajax_data', {
        action: 'service_detail',
        service: service_now
    }, function(data) {
        if (data.empty == 1) {
            $('#charge_div').hide();
        } else {
            $('#charge_div').show();
            $('#neworder_fields').html(data.details);
            $('#charge').val(data.price);
        }
        comment_charge();
        if ($('#dripfeedcheckbox').prop('checked')) {
            dripfeed_charge();
        }
    }, 'json');
}

function comment_charge() {
    var service = $('#neworder_services').val();
    var comments = $('#neworder_comment').val();
    if (comments) {
        $.post('/ajax_data', {
            action: 'service_price',
            service: service,
            comments: comments
        }, function(data) {
            $('#neworder_quantity').val(data.commentsCount);
            $('#charge').val(data.price);
        }, 'json');
    }
}

function dripfeed_charge() {
    var service = $('#neworder_services').val();
    var quantity = $('#neworder_quantity').val();
    var runs = $('#dripfeed-runs').val();
    var dripfeed = $('#dripfeedcheckbox').prop('checked') ? 'var' : 'bos';
    $.post('/ajax_data', {
        action: 'service_detail',
        service: service,
        quantity: quantity,
        dripfeed: dripfeed,
        runs: runs
    }, function(data) {
        $('#charge').val(data.price);
    }, 'json');
}
