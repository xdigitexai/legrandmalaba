<div class="modal fade" id="staticBackdropModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
  aria-labelledby="staticBackdropModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropModalLabel"></h5><button type="button" class="btn-close"
          data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body"></div>
    </div>
  </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script src="https://unpkg.com/sortablejs-make/Sortable.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-sortablejs@latest/jquery-sortable.js"></script>
<script src="https://cdn.jsdelivr.net/gh/mdbassit/FancySelect@latest/dist/fancyselect.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="public/admin/iziToast.min.js"></script>
<script src="public/admin/js/rwd-table.min.js"></script>
<script>
  const panel_categories = <?= $categories ?>;
  const panel_services = <?= $services ?>;
  const users = <?= $users ?>;
  const panel_settings = <?= $panel_settings ?>;
  const users_grouped_by_client_id = groupByKey(users, "client_id");
  const panel_services_grouped_by_service_id = groupByKey(panel_services, "service_id");
  const Route = window.location.pathname.split("/")[2];
  const settingsRoute = window.location.pathname.split("/")[3];
  var category_selector;
  var service_selector;
  var user_selector;
  var data;
  const page_loader = $("#page-loader");

  if ($(".fsb-ignore.multiple").length) {
    new TomSelect(".fsb-ignore.multiple", {
      plugins: ['remove_button', 'clear_button'],
      persist: false,
      createOnBlur: true,
      hidePlaceholder: true,
      selectOnTab: true,
      closeAfterSelect: true
    });
  }

  if ($(".fsb-ignore.not-multiple").length) {
    new TomSelect(".fsb-ignore.not-multiple", {
      persist: false,
      createOnBlur: true,
      hidePlaceholder: true,
      selectOnTab: true,
      closeAfterSelect: true
    });
  }

  function groupByKey(array, key) {
    return array
      .reduce((hash, obj) => {
        if (obj[key] === undefined) return hash;
        return Object.assign(hash, {
          [obj[key]]: (hash[obj[key]] || []).concat(obj)
        })
      }, {})
  }


  function getChangedElements(array1, array2) {
    const changedElements = [];
    for (let i = 0; i < array1.length; i++) {
      if (array1[i] !== array2[i]) {
        changedElements.push(array2[i]);
      }
    }
    return changedElements;
  }


  function initialize_tomselect() {
    user_selector = new TomSelect('#select-user', {
      persist: false,
      createOnBlur: true,
      hidePlaceholder: true,
      selectOnTab: true,
      closeAfterSelect: true,
      maxOptions: 100,
      valueField: 'client_id',
      labelField: 'name',
      searchField: ['name', 'username'],
      options: users,
      render: {
        loading: function (data, escape) {
          return '<center><svg class="spinner small" viewBox="0 0 48 48"><circle class="path" cx="24" cy="24" r="20" fill="none" stroke-width="3"></circle></svg> Loading users...</center>';
        },
        option: function (data) {
          return '<div>' + data.client_id + ' - ' + data.name + '<small> (' + data.username + ')</small></div>';
        },
        item: function (data) {
          return '<div><span style="margin-right:5px;color:#000;" class="badge bg-warning">' + data.client_id + '</span>' + data.name + '</div>';
        }
      }
    });

    category_selector = new TomSelect('#select-category', {
      persist: false,
      createOnBlur: true,
      hidePlaceholder: true,
      selectOnTab: true,
      closeAfterSelect: true,
      maxOptions: 50,
      plugins: ["no_backspace_delete"],
      valueField: 'category_id',
      labelField: 'category_name',
      searchField: 'category_name',
      options: panel_categories,
      render: {
        loading: function (data, escape) {

          return '<center><svg class="spinner small" viewBox="0 0 48 48"><circle class="path" cx="24" cy="24" r="20" fill="none" stroke-width="3"></circle></svg> Loading Categories...</center>';

        },
        option: function (data) {
          return '<div>' + data.category_id + ' - ' + data.category_name + '</div>';
        },
        item: function (data) {
          return '<div><span style="margin-right:5px;color:#000;" class="badge bg-warning">' + data.category_id + '</span>' + data.category_name + '</div>';
        }
      }
    });

    service_selector = new TomSelect('#select-service', {
      persist: false,
      createOnBlur: true,
      hidePlaceholder: true,
      selectOnTab: true,
      closeAfterSelect: true,
      plugins: ["no_backspace_delete"],
      valueField: 'service_id',
      labelField: 'service_name',
      searchField: 'service_name',
      options: [],
      render: {
        loading: function (data, escape) {

          return '<center><svg class="spinner small" viewBox="0 0 48 48"><circle class="path" cx="24" cy="24" r="20" fill="none" stroke-width="3"></circle></svg> Loading Services...</center>';

        },
        option: function (data) {
          return '<div>' + data.service_id + ' - ' + data.service_name + '</div>';
        },
        item: function (data) {
          return '<div><span style="margin-right:5px;color:#000;" class="badge bg-warning">' + data.service_id + '</span>' + data.service_name + '</div>';
        }
      }
    });
  }



  $(document).on("change", "#select-service", function () {
    if ($(this).val()) {
      $("#special_pricing_service_info").show();
      var service = panel_services.filter(item => item.service_id == $("#select-service").val());
      $("#special_pricing_selected_service_price").val(service[0].price);

      $("#special_pricing_selected_service_cost").val(service[0].cost);
    }
  });

  function show_table_loader() {

    $(".table").addClass("loading");

    $(".table").parent().removeClass("table-responsive");
    $(".table tbody").html('<tr><td colspan="6"><center><svg class="spinner medium" viewBox="0 0 48 48"><circle class="path" cx="24" cy="24" r="20" fill="none" stroke-width="5"></circle></svg> Loading...</center></td></tr>');
  }

  function no_data_table() {
    $(".table").removeClass("loading");
    $(".table").parent().removeClass("table-responsive");
    $(".table tbody").html('<tr><td colspan="6"><center><b>No Data</b></center></td></tr>');
  }

  function ready_table() {
    $(".table").removeClass("loading");
    $(".table").parent().addClass("table-responsive");
  }

  function load_page(page) {
    if (page == "special-pricing") {
      close_modal();
      populate_special_prices();
    }
  }

  function populate_special_prices() {
    var special_prices = "";
    $.ajax({
      url: "admin/special-pricing/data",
      type: "GET",
      success: function (response) {
        special_prices = JSON.parse(response);
        var tbody = "";
        if (special_prices.length > 0) {
          for (i = 0; i < special_prices.length; i++) {
            var client = users_grouped_by_client_id[special_prices[i].client_id][0];
            var service = panel_services_grouped_by_service_id[special_prices[i].service_id][0];
            tbody += "<tr><td><span class=\"badge bg-secondary\">" + client.client_id + "</span> " + client.name + "</td><td><div class=\"special_pricing_service_name\"><span class=\"badge text-bg-warning\">" + service.service_id + "</span> " + service.service_name + "</div></td><td>" + panel_settings.site_currency_symbol + service.cost + "</td><td>" + panel_settings.site_currency_symbol + service.price + "</td><td>" + panel_settings.site_currency_symbol + special_prices[i].service_price + "</td><td><span class=\"fa-stack\"><button type=\"button\" class=\"btn btn-outline-secondary btn-sm\" data-special-price-id=\"" + special_prices[i].id + "\" data-category-id=\"" + service.cid + "\" data-service-id=\"" + service.service_id + "\" data-client-id=\"" + client.client_id + "\" data-special-price=\"" + special_prices[i].service_price + "\"  data-bs-toggle=\"modal\" data-form=\"edit_special_price\" data-bs-target=\"#staticBackdropModal\"><i class=\"bi bi-pencil-square\"></i></button></span><span class=\"fa-stack\"><button type=\"button\" class=\"btn btn-outline-danger btn-sm\" data-ajax=\"true\" data-action-ajax=\"admin/special-pricing/delete/" + special_prices[i].id + "\"><i class=\"bi bi-trash3\"></i></button></span></td></tr>";
          }

          ready_table();
          $(".table tbody").html(tbody);
        } else {
          no_data_table();
        }
      }
    });
  }





  function open_modal(title, body) {
    $(".modal-title").html(title);
    $(".modal-body").html(body);

  }

  function initialize_quillEditor() {
    toolbarOptions = [
      ['bold', 'italic', 'underline', 'strike'],
      ['blockquote', 'code-block'], ["link", "image"],
      [{ 'header': 1 }, { 'header': 2 }],
      [{ 'list': 'ordered' }, { 'list': 'bullet' }],
      [{ 'script': 'sub' }, { 'script': 'super' }],
      [{ 'indent': '-1' }, { 'indent': '+1' }],
      [{ 'direction': 'rtl' }],
      [{ 'size': ['small', false, 'large', 'huge'] }],
      [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
      [{ 'color': [] }, { 'background': [] }],
      [{ 'font': [] }],
      [{ 'align': [] }],
      ['clean']
    ];
    var quill = new Quill('#editor', {
      theme: 'snow',
      modules: {
        toolbar: toolbarOptions
      }
    });
  }

  function close_modal() {
    $("[data-bs-dismiss='modal']").trigger("click");
  }

  $(document).on("click", "[data-bs-toggle='modal']", function () {
    var form = $(this).attr("data-form");
    if (form == "create_special_price") {
      var client_id = window.location.pathname.split("/")[3];
      var title = "Create Special Price";
      var body = '<form method="POST" action="admin/special-pricing/create-new"><div class="mb-3"><label class="form-label">User</label><select id="select-user" name="special_price_user" class="fsb-ignore" placeholder="Type to Search..."></select></div><div class="mb-3"><label class="form-label">Category</label><select id="select-category" class="fsb-ignore" placeholder="Type to Search..."></select></div><div style="display:none;" class="mb-3"><label class="form-label">Service</label><select id="select-service" class="fsb-ignore" name="special_price_service" placeholder="Type to Search..."></select></div><div id="special_pricing_service_info" style="display:none;" class="row"><div class="col"><label class="form-label">Price for 1000</label><div class="input-group mb-3"><span class="input-group-text">' + panel_settings.site_currency_symbol + '</span><input type="number" id="special_pricing_selected_service_price" class="form-control" disabled readonly></div></div><div class="col"><label class="form-label">Cost for 1000</label><div class="input-group mb-3"><span class="input-group-text">' + panel_settings.site_currency_symbol + '</span><input id="special_pricing_selected_service_cost" type="number" class="form-control" disabled readonly></div></div><div class="form-group"><label class="form-label">Special Price for 1000</label><div class="input-group mb-3"><span class="input-group-text">' + panel_settings.site_currency_symbol + '</span><input type="number" step="0.01" name="special_price_for_service" id="special_price_for_service"  class="form-control" placeholder="Enter special price for the service..."></div></div></div><div class="custom-modal-footer"><button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>&nbsp;&nbsp;<button type="submit" data-loading-text="Creating..." class="btn btn-primary">Create</button></div></form>';
      open_modal(title, body);
      initialize_tomselect();
      if (client_id) {
        user_selector.addItem(client_id);
      }
    }
    if (form == "edit_special_price") {
      var special_price_id = $(this).attr("data-special-price-id");
      var client_id = $(this).attr("data-client-id");
      var category = $(this).attr("data-category-id");
      var service = $(this).attr("data-service-id");
      var special_price = $(this).attr("data-special-price");
      var title = "Edit Special Price";
      var body = '<form method="POST" action="admin/special-pricing/edit/' + special_price_id + '"><div class="mb-3"><label class="form-label">User</label><select id="select-user" name="special_price_user" class="fsb-ignore" placeholder="Type to Search..."></select></div><div class="mb-3"><label class="form-label">Category</label><select id="select-category" class="fsb-ignore" placeholder="Type to Search..."></select></div><div style="display:none;" class="mb-3"><label class="form-label">Service</label><select id="select-service" class="fsb-ignore" name="special_price_service" placeholder="Type to Search..."></select></div><div id="special_pricing_service_info" style="display:none;" class="row"><div class="col"><label class="form-label">Price for 1000</label><div class="input-group mb-3"><span class="input-group-text">' + panel_settings.site_currency_symbol + '</span><input type="number" id="special_pricing_selected_service_price" class="form-control" disabled readonly></div></div><div class="col"><label class="form-label">Cost for 1000</label><div class="input-group mb-3"><span class="input-group-text">' + panel_settings.site_currency_symbol + '</span><input id="special_pricing_selected_service_cost" type="number" class="form-control" disabled readonly></div></div><div class="form-group"><label class="form-label">Special Price for 1000</label><div class="input-group mb-3"><span class="input-group-text">' + panel_settings.site_currency_symbol + '</span><input type="number" step="0.01" name="special_price_for_service" id="special_price_for_service"  class="form-control" placeholder="Enter special price for the service..."></div></div></div><div class="custom-modal-footer"><button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>&nbsp;&nbsp;<button type="submit" data-loading-text="Updating..." class="btn btn-primary">Edit</button></div></form>';
      open_modal(title, body);
      initialize_tomselect();
      user_selector.addItem(client_id);
      user_selector.clearOptions();
      category_selector.addItem(category);
      service_selector.addItem(service);
      $("#special_price_for_service").val(special_price);
    }
    if (form == "edit_payment_method") {
      title = "Edit payment method";
      body = "";
      var methodId = $(this).attr("data-method-id");
      $.ajax({
        url: "admin/settings/paymentMethods/getForm",
        data: "methodId=" + methodId,
        type: "POST",
        success: function (json) {
          body = json.content;
          open_modal(title, body);
          FancySelect.init(".form-select");
          initialize_quillEditor();
        }
      });

    }
    if (form == "add_remove_balance"){
      title = "Add / Deduct Balance";
      body = "";
      $.ajax({
      url: "admin/fund-add-history?action=add_remove_balance",
        type: "GET",
        success: function (json) {
          body = json.content;
          open_modal(title, body);
          //FancySelect.init(".form-select");
        }
      });
    }
  });
  $(document).on("change", "#select-category", function () {

    $("#select-service").parent().show();

    $("#special_pricing_service_info").hide();
    service_selector.clear();
    service_selector.clearOptions();
    service_selector.addOptions(panel_services.filter(item => item.cid == $("#select-category").val()), user_created = false);
  });

  $(document).ready(function () {
    var paymentMethodsSequence = [];
    var sidebarBox = $('.mobile-navbar');

    var sidebarBtn = $('.mobile-nav-close-btn');
    sidebarBtn.click(function () {
      $(this).toggleClass("active");
      sidebarBox.toggleClass("active");
    });
    var nav_dropdown = $(".mobile-navbar li.dropdown");
    nav_dropdown.each(function () {
      if ($(this).hasClass("open-dropdown")) {
        $(this).find(".content").slideToggle(280);
      }
    });
    $("[data-bs-dismiss='modal']").click(function () {
      $(".modal-title").html("");
      $(".modal-body").html("");
    });

    $(".mobile-navbar li.dropdown").click(function () {
      $(this).toggleClass("open-dropdown");
      $(this).find(".content").slideToggle(280);
    });

    $(document).on("click", ".method-status", function () {
      var element = $(this);
      var isGreen = element.hasClass("green-circle");
      var methodId = element.attr("data-method-id");
      if (isGreen) {
        $.ajax({
          url: "admin/settings/paymentMethods/deactivate",
          data: "methodId=" + methodId,
          type: "POST",
          success: function (response) {
            element.removeClass("green-circle");
            element.addClass("red-circle");
          }
        });
      } else {
        $.ajax({
          url: "admin/settings/paymentMethods/activate",
          data: "methodId=" + methodId,
          type: "POST",
          success: function (response) {
            element.removeClass("red-circle");
            element.addClass("green-circle");
          }
        });
      }
    });

    if (Route == "special-pricing") {
      populate_special_prices();
    }

    if (Route == "settings" && settingsRoute == "paymentMethods") {
      $.ajax({
        url: "admin/settings/paymentMethods?action=getData",
        type: "GET",
        success: function (response) {
          data = response;
          page_loader.hide();
          var page = $(".page-content");
          var pageContent = '';
          for (i = 0; i < data.length; i++) {
            if (data[i].status == 1) {
              var circle = "green-circle";
            pageContent += '<div class="payment-card col-md-4" data-method-id="' + data[i].id + '" data-method-position="' + (i + 1) + '"><div data-method-id="' + data[i].id + '" class="method-status ' + circle + '"></div><div class="method-sort-handle">=</div><div class="method-logo"><img width="130" height="30" src="' + data[i].logo + '" alt="method Logo"></div><div class="method_name">' + data[i].name + '</div><div class="method_min_max"><span class="min">' + data[i].min + '</span>-<span class="max">' + data[i].max + '</span></div><div class="vertical-line"></div><div class="actions"><button type="button" class="btn btn-labeled btn-primary" data-bs-toggle="modal" data-form="edit_payment_method" data-method-id="' + data[i].id + '" data-bs-target="#staticBackdropModal"><span class="btn-label"><i class="bi bi-pencil-fill"></i></span>Edit</button></div></div>';
          } else {
              var circle = "red-circle";
            }}
          page.html(pageContent);
          count = 0;
          $(".payment-card").each(function () {
            paymentMethodsSequence[count] = "" + $(this).data("method-id") + "";
            count++;
          });
        }
      });
    }
$('button[data-form="add_new_method"]').click(function() {
    $.ajax({url: "admin/settings/paymentMethods?action=getData",
        type: "GET",
        success: function(response) {
            var data = response;
            var modal = $('#staticBackdropModal');
            var modalTitle = modal.find('.modal-title');
            var modalBody = modal.find('.modal-body');
            var methodsHTML = '';
            for (var i = 0; i < data.length; i++) {
                var circle = (data[i].status == 1) ? "green-circle" : "red-circle";
                methodsHTML += '<div class="payment-card col-md-4" data-method-id="' + data[i].id + '" data-method-position="' + (i + 1) + '">' +
                    '<div data-method-id="' + data[i].id + '" class="method-status ' + circle + '"></div>' +
                    '<div class="method-sort-handle">=</div>' +
                    '<div class="method-logo"><img width="130" height="30" src="' + data[i].logo + '" alt="method Logo"></div>' +
                    '<div class="method_name">' + data[i].name + '</div>' +
                    '<div class="method_min_max"><span class="min">' + data[i].min + '</span>-<span class="max">' + data[i].max + '</span></div>' +
                    '<div class="vertical-line"></div>' +
                    '<div class="actions"><button type="button" class="btn btn-labeled btn-primary" data-bs-toggle="modal" data-form="edit_payment_method" data-method-id="' + data[i].id + '" data-bs-target="#staticBackdropModal"><span class="btn-label"><i class="bi bi-pencil-fill"></i></span>Edit</button></div>' +
                    '</div>';}
            modalBody.html(methodsHTML);
            modalTitle.text("Active Method To Add");
           modalBody.css({'overflow-x': 'scroll', 'display': 'flex','align-items': 'center'});
        },error: function(xhr, status, error) {}
    });
});

    if (Route == "fund-add-history") {
      $.ajax({
        url: "admin/fund-add-history?action=getData",
        type: "GET",
        success: function (json) {
          data = json;
          page_loader.hide();
          tbody = '';
          if (data.length > 0) {
            for (i = 0; i < data.length; i++) {
              if(data[i].mode == "Automatic"){
                mode = '<span class="badge bg-warning text-dark">Automatic</span>';
              } else {
                mode = '<span class="badge bg-secondary">Manual</span>';
            }
              tbody += '<tr><td>'+data[i].id+'</td><td><span class="badge bg-secondary">'+data[i].cid+'</span> '+data[i].username+'</td><td>'+data[i].method+' '+mode+'</td><td>'+panel_settings.site_currency_symbol+" "+data[i].user_balance+'</td><td>'+panel_settings.site_currency_symbol+" "+data[i].amount+'</td><td>'+data[i].status+'</td><td>'+data[i].created_at+'</td><td><div class="btn-group dropstart"><button type="button" class="btn btn-secondary dropdown-toggle btn-sm" data-bs-toggle="dropdown" aria-expanded="false">Actions</button><ul class="dropdown-menu"><li><a class="dropdown-item">Details</a></li></ul></div></td></tr>';
            }
            ready_table();
            $(".table tbody").html(tbody);
          } else {

          }


        }

      });
    }

    $(document).on("click", "#editor > div.ql-editor > p > img", function () {
      var height = prompt('Enter the height of the image (e.g., 200px) , (Enter 0 to remove image):', $(this).attr("height"));
      var width = prompt('Enter the width of the image (e.g., 300px) ,  (Enter 0 to remove image):', $(this).attr("width"));
      if (height != 0 && width != 0) {
        $(this).attr({
          height: height,
          width: width
        });
      } else {
        $(this).parent().remove();
      }
    });

    $("#payment_methods_search").keyup(function () {
      var text = $(this).val();
      count = 0;
      $('.page-content > .payment-card').each(function () {
        var method_name = $(this).find(".method_name").text();
        if (method_name.search(new RegExp(text, "i")) < 0) {
          $(this).hide();
        } else {
          $(this).show();
          count++;
        }
      });
    });


    $("#special_pricing_service_type").change(function () {
      var val = $(this).val();
      var selector = $("#special-pricing-seller-select-div");
      if (val == "seller_services") {
        selector.show();
      } else {
        selector.hide();
      }
    });

    $("#profit-percent-value").keyup(function () {
      var percent = $(this).val();
      if (percent) {
        var html = '<div class="mb-3"><label for="action_type" class="form-label">Action</label><select name="action_type" id="action_type"><option value="set_profit">Set profit to ' + percent + '%</option><option value="increase_profit">Increase profit by ' + percent + '%</option><option value="decrease_profit">Decrease profit by ' + percent + '%</option></select></div>';
        $("#action_type_div").html(html);
        FancySelect.init("#action_type");
      } else {
        $("#action_type_div").empty();
      }
    });


    $(document).on("click", "[data-ajax='true']", function () {
      var url = $(this).attr("data-action-ajax");
      $.ajax({
        url: url,
        type: 'GET',
        success: function (json) {
          json = JSON.parse(json);
          if (json.success == true) {
            iziToast.show({
              icon: 'bi bi-check2',
              title: json.message,
              message: '',
              color: 'green',
              position: 'topCenter'
            });
            if (Route == "special-pricing") {
              show_table_loader();
              load_page(Route);
            }
          }
          if (json.success == false) {
            iziToast.show({
              icon: 'bi bi-x',
              title: json.message,
              message: '',
              color: 'red',
              position: 'topCenter'
            });
          }
        }
      });
    });

    $(document).on("submit", "form", function (e) {
      e.preventDefault();
      var action_url = $(this).attr("action");
      var method = $(this).attr("method");
      var post_data = $(this).serialize();
      if ($(".extraContents").length) {
        var quill = new Quill('#editor');
        var key = $(".extraContents").attr("name");
        var value = encodeURIComponent(quill.root.innerHTML);
        post_data += "&" + key + "=" + value;
      }
      var btn = $(this).find("button[type='submit']");
      var loader_after_text = btn.attr("data-loading-text");
      var btn_html = btn.html();
      btn.attr("disabled", true);
      btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + loader_after_text);
      $.ajax({
        url: action_url,
        type: method,
        data: post_data,
        success: function (json) {
          json = JSON.parse(json);
          if (json.success == true) {
            iziToast.show({
              icon: 'bi bi-check2',
              title: json.message,
              message: '',
              color: 'green',
              position: 'topCenter'
            });
            if (Route == "special-pricing") {
              show_table_loader();
              load_page(Route);
            }
          }
          if (json.success == false) {
            iziToast.show({
              icon: 'bi bi-x',
              title: json.message,
              message: '',
              color: 'red',
              position: 'topCenter'
            });
          }
          btn.removeAttr("disabled");
          btn.html(btn_html);
        }
      });
    });


    $("#category-list").sortable({
      group: "list",
      handle: ".category-sort-handle",
      draggable: ".list-group-item",
      animation: 150,
      scroll: true,
      scrollSensitivity: 30,
      scrollSpeed: 10,
      ghostClass: "blue-background-class",
      dataIdAttr: "data-category-id",
      onUpdate: function (evt) {
        var arr = $("#category-list").sortable('toArray');
        var data = window.btoa(JSON.stringify(arr));
        $.ajax({
          url: 'admin/category-sort',
          data: 'action=sort_category&category_list=' + data,
          type: 'POST',
          success: function (response) {
            iziToast.show({
              icon: 'fa fa-check',
              title: 'Category Positions Updated',
              message: '',
              color: 'green',
              position: 'topCenter'
            });
          }
        });
      }
    });

    $("#paymentMethods").sortable({
      group: "list",
      handle: ".method-sort-handle",
      draggable: ".payment-card",
      animation: 150,
      scroll: true,
      scrollSensitivity: 100,
      scrollSpeed: 8,
      ghostClass: "",
      dataIdAttr: "data-method-id",
      onUpdate: function (evt) {
        var updatedSequence = [];
        updatedSequence = $("#paymentMethods").sortable('toArray');
        var updatedSequence = getChangedElements(paymentMethodsSequence, updatedSequence);
        var methodPositions = window.btoa(JSON.stringify(updatedSequence));
        $.ajax({
          url: 'admin/settings/paymentMethods/sort',
          data: 'sortData=' + methodPositions,
          type: 'POST',
          success: function (response) {
            count = 0;
            $(".payment-card").each(function () {
              paymentMethodsSequence[count] = "" + $(this).data("method-id") + "";
              count++;
            });
          }
        })
      }
    });
  });

</script>
</body>

</html>