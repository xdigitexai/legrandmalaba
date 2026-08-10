function generatePassword() {
  var length = 8,
    charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
    retVal = "";
  for (var i = 0, n = charset.length; i < length; ++i) {
    retVal += charset.charAt(Math.floor(Math.random() * n));
  }
  return retVal;
}
function UserPassword() {
  $("#user_password").val(generatePassword());
}

$(document).ready(function () {
  var site_url = $("head base").attr("href");

  $("#taskStatus").click(function () {
    $("#taskStatusContent").html(
      '<center><div class="modal-body"><div class="fa-3x"><i border="0" alt="loading" class="fas fa-spinner fa-spin"></i></div></div></center>'
    );
    var href = $(this).attr("data-href");
    var active = $(this).attr("data-active");
    $.post(
      site_url + href,
      {
        active: active,
      },
      function (data) {
        $("#taskStatusContent").html(data);
      }
    );
  });

  $("#serviceList").click(function () {
    $("#serviceListContent").html(
      '<center><div class="modal-body"><div class="fa-3x"><i border="0" alt="loading" class="fas fa-spinner fa-spin"></i></div></div></center>'
    );
    var href = $(this).attr("data-href");
    var active = $(this).attr("data-active");
    $.post(site_url + href, { active: active }, function (data) {
      $("#serviceListContent").html(data);
    });
  });

  $("#addCheckBoxes").click(function (e) {
    e.preventDefault();
    var checkedInputs = $("tbody input:checkbox:checked");
    var selectedServices = [];
    checkedInputs.map((idx, input) => {
      selectedServices.push($(input).data("id").trim());
    });
    $("#addCheckBoxes").attr("data-id", JSON.stringify(selectedServices));
  });

  $("#userOrders").click(function () {
    var href = $(this).attr("data-href");
    var active = $(this).attr("data-active");
    $.post(site_url + href, { active: active }, function (data) {
      $("#userOrdersContent").html(data);
    });
  });

  $("#paymentMethodsList").click(function () {
    $("#paymentMethodListContent").html(
      '<center><div class="modal-body"><div class="fa-3x"><i border="0" alt="loading" class="fas fa-spinner fa-spin"></i></div></div></center>'
    );
    var href = $(this).attr("data-href");
    var active = $(this).attr("data-active");
    $.post(site_url + href, { active: active }, function (data) {
      $("#paymentMethodListContent").html(data);
    });
  });

  $(".customDatadropdown").click(function () {
    var container = $(this).attr("data-content");
    $("#" + container).html(
      '<center><div class="modal-body"><div class="fa-3x"><i border="0" alt="loading" class="fas fa-spinner fa-spin"></i></div></div></center>'
    );
    var href = $(this).attr("data-href");
    var active = $(this).attr("data-active");
    $.post(site_url + href, { active: active }, function (data) {
      $("#" + container).html(data);
    });
  });

  $("#paymentStatusList").click(function () {
    $("#paymentStatusListContent").html(
      '<center><div class="modal-body"><div class="fa-3x"><i border="0" alt="loading" class="fas fa-spinner fa-spin"></i></div></div></center>'
    );
    var href = $(this).attr("data-href");
    var active = $(this).attr("data-active");
    $.post(site_url + href, { active: active }, function (data) {
      $("#paymentStatusListContent").html(data);
    });
  });

  $("#statusList").click(function () {
    // new
    $("#statusListContent").html(
      '<center><div class="modal-body"><div class="fa-3x"><i border="0" alt="loading" class="fas fa-spinner fa-spin"></i></div></div></center>'
    );
    var href = $(this).attr("data-href");
    var active = $(this).attr("data-active");
    $.post(site_url + href, { active: active }, function (data) {
      $("#statusListContent").html(data);
    });
  });

  $("#modalDiv").on("hidden.bs.modal", function () {
    $("#modalTitle").html("");
    $("#modalContent").html("");
  });

  $("#subsDiv").on("show.bs.modal", function (e) {
    $.post(
      site_url + "admin/ajax_data",
      {
        action: $(e.relatedTarget).data("action"),
        id: $(e.relatedTarget).data("id"),
      },
      function (data) {
        $("#subsTitle").html(data.title);
        $("#subsContent").html(data.content);
        $(".datetime")
          .datepicker({
            format: "yyyy/mm/dd",
            language: "en",
            startDate: new Date(),
          })
          .on("change", function (ev) {
            $(".datetime").datepicker("hide");
          });
      },
      "json"
    );
  });

  $('[id^="delete_rate_button-"]').click(function () {
    var id = $(this).attr("data-service");
    $("#rate-" + id).val("");
    $("#delete_rate_button-" + id).css("visibility", "hidden");
  });

  $('[id^="delete_rate_button-"]').each(function () {
    var id = $(this).attr("data-service");
    var price = $("#rate-" + id).val().length;
    if (price > 0) {
      $("#delete_rate_button-" + id).css("visibility", "visible");
    }
  });

  $('[id^="rate-"]').on("keyup", function () {
    var id = $(this).attr("data-service");
    var price = $("#rate-" + id).val().length;
    if (price > 0) {
      $("#delete_rate_button-" + id).css("visibility", "visible");
    } else {
      $("#delete_rate_button-" + id).css("visibility", "hidden");
    }
  });

  $('[id^="collapedAdd-"]').click(function () {
    var id = $(this).attr("data-category");
    if ($(this).attr("class") == "service-block__collapse-button") {
      $(".Service" + id).hide();
      $(this).addClass(" collapsed");
    } else {
      $(".Service" + id).show();
      $(this).removeClass(" collapsed");
    }
  });

  $("#allServices").click(function () {
    if ($(this).attr("class") == "service-block__hide-all fa fa-compress") {
      $("#allServices").removeClass("fa fa-compress");
      $("#allServices").addClass("fa fa-expand");
      $('[class^="Servicecategory-"]').each(function () {
        $(this).hide();
      });
      $('[id^="collapedAdd-"]').each(function () {
        $(this).addClass(" collapsed");
      });
    } else {
      $("#allServices").removeClass("fa fa-expand");
      $("#allServices").addClass("fa fa-compress");
      $('[class^="Servicecategory-"]').each(function () {
        $(this).show();
      });
      $('[id^="collapedAdd-"]').each(function () {
        $(this).removeClass(" collapsed");
      });
    }
  });

  $("#priceSearch").on("keyup", function () {
    var search = $(this).val();
    var filter = search.toUpperCase();
    var i = 0;
    $('[id^="servicepriceList-"]').each(function () {
      i++;
      var name = $(this).attr("data-name");
      var txtValue = name.textContent || name.innerText;
      if (name.toUpperCase().indexOf(filter) > -1) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  });

  $("#priceService").on("keyup", function () {
    var search = $(this).val();
    var filter = search.toUpperCase();

    var i = 0;
    $('[data-id^="service-"]').each(function () {
      var name = $(this).attr("data-service");
      var category = $(this).attr("data-category");
      var txtValue = name.textContent || name.innerText;
      if (name.toUpperCase().indexOf(filter) > -1) {
        $(this).show();
        $(this).attr("id", "serviceshow" + category);
      } else {
        $(this).hide();
        $(this).attr("id", "servicehide");
      }
    });
    $('[id^="Servicecategory-"]').each(function () {
      var id = $(this).attr("data-id");
      var rowCount = $(
        "#servicesTableList > tbody > tr#serviceshow" + id
      ).length;
      if (rowCount == 0) {
        $("#" + id).hide();
      } else {
        $("#" + id).show();
      }
    });
  });
  $(".tiny-toggle").tinyToggle({
    onCheck: function () {
      var id = $(this).attr("data-id");
      var action = $(this).attr("data-url") + "?type=on&id=" + id;
      $.ajax({
        url: action,
        type: "GET",
        dataType: "json",
        cache: false,
        contentType: false,
        processData: false,
      })
        .done(function (result) {
          if (result == 1) {
            $('[data-toggle="' + id + '"]').removeClass("grey");
          } else {
            $.toast({
              heading: "Failed",
              text: "Failed",
              icon: "error",
              loader: true,
              loaderBg: "#9EC600",
            });
          }
        })
        .fail(function () {
          $.toast({
            heading: "Failed",
            text: "Failed",
            icon: "error",
            loader: true,
            loaderBg: "#9EC600",
          });
        });
    },
    onUncheck: function () {
      var id = $(this).attr("data-id");
      var action = $(this).attr("data-url") + "?type=off&id=" + id;
      $.ajax({
        url: action,
        type: "GET",
        dataType: "json",
        cache: false,
        contentType: false,
        processData: false,
      })
        .done(function (result) {
          if (result == 1) {
            $('[data-toggle="' + id + '"]').addClass("grey");
          } else {
            $.toast({
              heading: "Failed",
              text: "Failed",
              icon: "error",
              loader: true,
              loaderBg: "#9EC600",
            });
          }
        })
        .fail(function () {
          $.toast({
            heading: "Failed",
            text: "Failed",
            icon: "error",
            loader: true,
            loaderBg: "#9EC600",
          });
        });
    },
  });

  $("#provider").change(function () {
    var provider = $(this).val();
    getProviderServices(provider, site_url);
  });

  getProvider();
  $("#serviceMode").change(function () {
    getProvider();
  });

  getSalePrice();
  $("#saleprice_cal").change(function () {
    getSalePrice();
  });

  getSubscription();
  $("#subscription_package").change(function () {
    getSubscription();
  });

  $("#confirmChange").on("show.bs.modal", function (e) {
    $(this).find("#confirmYes").attr("href", $(e.relatedTarget).data("href"));
  });
  $("#confirmYes").click(function () {
    if ($(this).attr("href") == null) {
      $("#changebulkForm").submit();
      return false;
    }
  });
  $(".bulkorder").click(function () {
    var status = $(this).attr("data-type");
    $("#bulkStatus").val(status);
    $("#confirmYes").removeAttr("href");
    $("#confirmChange").modal("show");
  });

  $("#checkAll").click(function () {
    if ($(this).prop("checked") == true) {
      $(".selectOrder").not(this).prop("checked", true);
    } else {
      $(".selectOrder").not(this).prop("checked", false);
    }
    var count = $(".selectOrder").filter(":checked").length;
    $(".countOrders").html(count);
    if (count > 0) {
      $(".checkAll-th").addClass("show-action-menu");
    } else {
      $(".checkAll-th").removeClass("show-action-menu");
    }
  });
  $(".selectOrder").click(function () {
    var count = $(".selectOrder").filter(":checked").length;
    if (count > 0) {
      $(".checkAll-th").addClass("show-action-menu");
    } else {
      $(".checkAll-th").removeClass("show-action-menu");
    }
    $(".countOrders").html(count);
  });
});

function getProviderServices(provider, site_url) {
  if (provider == 0) {
    $("#provider_service").hide();
  } else {
    $.post(site_url + "admin/ajax_data", {
      action: "providers_list",
      provider: provider,
    })
      .done(function (data) {
        $("#provider_service").show();
        $("#provider_service").html(data);
        $("#selectpicker").selectpicker();
      })
      .fail(function () {
        alert("Some error occured!");
      });
  }
}

function getProvider() {
  var mode = $("#serviceMode").val();
  if (mode == 1) {
    $("#autoMode").hide();
  } else {
    $("#autoMode").show();
  }
}

function getSalePrice() {
  var type = $("#saleprice_cal").val();
  if (type == "normal") {
    $("#saleprice").hide();
    $("#servicePrice").show();
  } else {
    $("#saleprice").show();
    $("#servicePrice").hide();
  }
}

$(".getAllCheckboxes").click(function (e) {
  e.preventDefault();
  var action = $(this).data("action");

  var selectClass = $(this).attr("data-check-box");
  allSelectBoxes = $("." + selectClass + ":checked");

  var ids = [];
  allSelectBoxes.each(function (_, el) {
    if (action == "copyIds") {
      var attrToCp = "name";
    } else if (action == "copyExternalIds") {
      var attrToCp = "data-external-id";
    }
    var id = $(el).attr(attrToCp);
    id = id.split("[");
    id = id[1].split("]");
    if (id[0] && id[0] !== 0 && id[0] !== "0") {
      ids.push(id[0]);
    }
  });

  if (action == "copyIds" || action == "copyExternalIds") {
    navigator.clipboard.writeText(ids);
  }
});

function getSubscription() {
  var type = $("#subscription_package").val();
  if (type == "11" || type == "12") {
    $("#unlimited").show();
    $("#limited").hide();
  } else {
    $("#unlimited").hide();
    $("#limited").show();
  }
}

$(document).ready(function () {
  var site_url = $("head base").attr("href");

  $(document).on("submit", "form[data-xhr]", function (event) {
    event.preventDefault();

    $("#loadingImage").hide();
    $("#mainSubmitButton").hide();

    var action = $(this).attr("action");
    var method = $(this).attr("method");
    var formData = new FormData($(this)[0]);

    let old_html = $("#modalContent").html();

    $(".modalLoader").show();

    $.ajax({
      url: action,
      type: method,
      dataType: "json",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
    })
      .done(function (result) {
        if (result.s == "error") {
          $(".modalLoader").hide();
          var heading = "Failed";
        } else {
          $(".modalLoader").hide();
          $(".modalSuccess").show();
          var heading = "Success";
        }

        $.toast({
          heading: heading,
          text: result.m,
          icon: result.s,
          loader: true,
          loaderBg: "#9EC600",
        });

        if (heading == "Failed") {
          $("#loadingImage").hide();
          $("#mainSubmitButton").show();
          $(".modalLoader").hide();
        }

        if (result.r != null) {
          if (result.time == null) {
            result.time = 3;
          }

          setTimeout(function () {
            window.location.reload();
          }, result.time * 100);
        }
      })
      .fail(function () {
        /* Ajax işlemi Failed, hata bas */

        $.toast({
          heading: "An error occurred!",
          text: "The request could not be fulfilled",
          icon: "error",
          loader: true,
          loaderBg: "#9EC600",
        });

        $("#loadingImage").hide();
        $(".modalLoader").hide();
        $("#mainSubmitButton").show();
      });
  });

  $("#delete-row").click(function () {
    var action = $(this).attr("data-url");
    swal({
      title: "Are you sure you want to delete?",
      text: "If you confirm, this content will be deleted, it may not be possible to restore it.",
      icon: "warning",
      buttons: true,
      dangerMode: true,
      buttons: ["Cancel", "Yes , I am sure!"],
    }).then((willDelete) => {
      if (willDelete) {
        $.ajax({
          url: action,
          type: "GET",
          dataType: "json",
          cache: false,
          contentType: false,
          processData: false,
        })
          .done(function (result) {
            if (result.s == "error") {
              var heading = "Failed";
            } else {
              var heading = "Success";
            }
            $.toast({
              heading: heading,
              text: result.m,
              icon: result.s,
              loader: true,
              loaderBg: "#9EC600",
            });
            if (result.r != null) {
              if (result.time == null) {
                result.time = 3;
              }
              setTimeout(function () {
                window.location.href = result.r;
              }, result.time * 1000);
            }
          })
          .fail(function () {
            $.toast({
              heading: "Failed",
              text: "The request could not be fulfilled",
              icon: "error",
              loader: true,
              loaderBg: "#9EC600",
            });
          });
        /* İçerik silinmesi onaylandı */
      } else {
        $.toast({
          heading: "Failed",
          text: "Deletion request denied",
          icon: "error",
          loader: true,
          loaderBg: "#9EC600",
        });
      }
    });
  });
});

("use strict");

$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});

function getCookie(cname) {
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(";");
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == " ") {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function setNMode(n) {
  console.log(n);
  if (n == 1) {
    $("body").addClass("dark-mode");
    document.getElementById("dark_symbol").innerHTML =
      '<i class="fas fa-sun"></i>';
  } else {
    $("body").removeClass("dark-mode");
    document.getElementById("dark_symbol").innerHTML =
      '<i class="fas fa-moon"></i>';
  }
}

function night_mode() {
  var night = getCookie("night_mode");
  if (night == 1) {
    //night
    setNMode("1");
  } else {
    setNMode("0");
  }
}

var dark_symbol = document.getElementById("dark_symbol");
if (dark_symbol != null) {
  night_mode();
}

(function ($) {
  $(document).ready(function () {
    $("ul.dropdown-menu [data-toggle=dropdown]").on("click", function (event) {
      event.preventDefault();
      event.stopPropagation();
      $(this).parent().siblings().removeClass("open");
      $(this).parent().toggleClass("open");
    });
  });

  $("#dark").on("click", function (e) {
    e.preventDefault();
    var t = $("body"),
      a = !t.hasClass("dark-mode");
    $("i", this).attr("class", a ? "fa fa-sun" : "fas fa-moon"),
      t.toggleClass("dark-mode", a),
      void 0 !== window.CodeMirrorEditor &&
        window.CodeMirrorEditor.setOption(
          "theme",
          a ? "material-darker" : "default"
        ),
      $.ajax({
        url:
          "ajax_settings_update?method=dark-mode&type=update&now=" +
          (a ? 1 : 0),
        type: "GET",
        success: function (e) {},
        error: function (e, t, a) {
          console.log("Something was wrong...", t, a, e);
        },
      });
  });
})(jQuery);
$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});

var checkAll_div = document.getElementsByClassName("checkAll");

if (checkAll_div) {
  var checkAll = document.querySelector(".checkAll"),
    allCheckBox = document.querySelectorAll(".selectOrder"),
    countOrders = document.querySelector(".countOrders"),
    counter = 0;

  if (typeof checkAll !== "undefined" && checkAll != null) {
    var checkAllParentTh = checkAll.closest("tr");
  }
}

var orderPriceSpan = document.getElementsByClassName("orderPriceSpan")
  ? document.querySelector(".orderPriceSpan")
  : false;
var orderQuantitySpan = document.getElementsByClassName("orderQuantitySpan")
  ? document.querySelector(".orderQuantitySpan")
  : false;
var orderRemainingSpan = document.getElementsByClassName("orderRemainingSpan")
  ? document.querySelector(".orderRemainingSpan")
  : false;

var servicePriceSpan = document.getElementsByClassName("servicePriceSpan")
  ? document.querySelector(".servicePriceSpan")
  : false;
var serviceRefillSpan = document.getElementsByClassName("serviceRefillSpan")
  ? document.querySelector(".serviceRefillSpan")
  : false;
var serviceRefillerSpan = document.getElementsByClassName("serviceRefillerSpan")
  ? document.querySelector(".serviceRefillerSpan")
  : false;
var serviceCountSpan = document.getElementsByClassName("serviceCountSpan")
  ? document.querySelector(".serviceCountSpan")
  : false;
var serviceAutoCSpan = document.getElementsByClassName("serviceAutoCSpan")
  ? document.querySelector(".serviceAutoCSpan")
  : false;

//console.log(orderPriceSpan);

function selectAllCheckbox() {
  //alert();
  allCheckBox = document.querySelectorAll(".selectOrder");

  counter = 0;
  amount = 0;
  remaining = 0;
  pricee = 0;

  auto_refill = 0;
  auto_refiller = 0;
  auto_complete = 0;
  auto_start_count = 0;

  if (this.checked) {
    allCheckBox.forEach(function (oneCheckBox) {
      if (!oneCheckBox.disabled) {
        oneCheckBox.closest("tr").classList.add("active");
        checkAllParentTh.classList.add("show-action-menu");
        //oneCheckBox.checked = true;
        $(".selectOrder").prop("checked", true);
        counter++;
        if (oneCheckBox.classList.contains("orderPageBox")) {
          //console.log('yes');
          //console.log(oneCheckBox);
          pricee = pricee + parseFloat(oneCheckBox.getAttribute("data-price"));
          amount = amount + parseInt(oneCheckBox.getAttribute("data-amount"));
          remaining =
            remaining + parseInt(oneCheckBox.getAttribute("data-remaining"));
          //console.log(amount);
        }

        if (oneCheckBox.classList.contains("servicesPageBox")) {
          //console.log('yes');
          //console.log(oneCheckBox);

          var raw_auto_refill = parseFloat(
            oneCheckBox.getAttribute("data-auto_refill")
          );
          if (raw_auto_refill == 1) {
            auto_refill = auto_refill + 1;
          }

          var raw_auto_refiller = parseFloat(
            oneCheckBox.getAttribute("data-auto_refiller")
          );
          if (raw_auto_refiller == 1) {
            auto_refiller = auto_refiller + 1;
          }

          pricee =
            pricee + parseFloat(oneCheckBox.getAttribute("data-service-price"));

          var raw_auto_complete = parseFloat(
            oneCheckBox.getAttribute("data-auto_complete")
          );
          if (raw_auto_complete == 1) {
            auto_complete = auto_complete + 1;
          }

          var raw_start_count_type = parseFloat(
            oneCheckBox.getAttribute("data-start_count_type")
          );
          if (raw_start_count_type == 1) {
            auto_start_count = auto_start_count + 1;
          }
        }
      }
    });

    //countOrders.innerText = counter + ' users ';
    countOrders.innerText = counter + " ";

    // if(countOrders.innerText > 20){
    //     Swal.fire({
    //         title: 'Error!',
    //         text: 'You can only select max 20 categories',
    //         icon: 'error'
    //       })
    //       allCheckBox.forEach(function (oneCheckBox) {
    //         oneCheckBox.closest("tr").classList.remove("active");
    //         checkAllParentTh.classList.remove("show-action-menu");
    //         oneCheckBox.checked = false;
    //     });
    //       counter = 0;
    //       amount = 0;
    //       remaining = 0;
    //       this.checked = false;
    //       countOrders.innerText = "";

    // }

    try {
      if (typeof orderPriceSpan !== "undefined" && orderPriceSpan != null) {
        orderPriceSpan.innerText = pricee == 1 ? pricee + "  " : pricee + "  ";
      }
      if (
        typeof orderQuantitySpan !== "undefined" &&
        orderQuantitySpan != null
      ) {
        orderQuantitySpan.innerText =
          amount == 1 ? amount + "  " : amount + "  ";
      }
      if (
        typeof orderRemainingSpan !== "undefined" &&
        orderRemainingSpan != null
      ) {
        orderRemainingSpan.innerText =
          remaining == 1 ? remaining + "  " : remaining + "  ";
      }

      if (typeof servicePriceSpan !== "undefined" && servicePriceSpan != null) {
        servicePriceSpan.innerText =
          pricee == 1 ? pricee + "  " : pricee + "  ";
      }
      if (
        typeof serviceRefillSpan !== "undefined" &&
        serviceRefillSpan != null
      ) {
        serviceRefillSpan.innerText =
          auto_refill == 1 ? auto_refill + "  " : auto_refill + "  ";
      }
      if (
        typeof serviceRefillerSpan !== "undefined" &&
        serviceRefillerSpan != null
      ) {
        serviceRefillerSpan.innerText =
          auto_refiller == 1 ? auto_refiller + "  " : auto_refiller + "  ";
      }
      if (typeof serviceCountSpan !== "undefined" && serviceCountSpan != null) {
        serviceCountSpan.innerText =
          auto_start_count == 1
            ? auto_start_count + "  "
            : auto_start_count + "  ";
      }
      if (typeof serviceAutoCSpan !== "undefined" && serviceAutoCSpan != null) {
        serviceAutoCSpan.innerText =
          auto_complete == 1 ? auto_complete + "  " : auto_complete + "  ";
      }
    } catch (err) {}
  } else {
    allCheckBox.forEach(function (oneCheckBox) {
      oneCheckBox.closest("tr").classList.remove("active");
      checkAllParentTh.classList.remove("show-action-menu");
      oneCheckBox.checked = false;
    });

    counter = 0;
    amount = 0;
    remaining = 0;
    this.checked = false;
    countOrders.innerText = "";
  }
}

function selectOneCheckbox() {
  counter = 0;
  amount = 0;
  remaining = 0;
  pricee = 0;

  auto_refill = 0;
  auto_refiller = 0;
  auto_complete = 0;
  auto_start_count = 0;

  var trigger = false;
  allCheckBox.forEach(function (oneCheckBox) {
    var trParent = oneCheckBox.closest("tr");
    if (oneCheckBox.checked) {
      if (oneCheckBox.classList.contains("orderPageBox")) {
        //console.log('yes');
        //console.log(oneCheckBox);
        pricee = pricee + parseFloat(oneCheckBox.getAttribute("data-price"));
        amount = amount + parseInt(oneCheckBox.getAttribute("data-amount"));
        remaining =
          remaining + parseInt(oneCheckBox.getAttribute("data-remaining"));
        //console.log(amount);
      }

      if (oneCheckBox.classList.contains("servicesPageBox")) {
        //console.log('yes');
        //console.log(oneCheckBox);

        var raw_auto_refill = parseFloat(
          oneCheckBox.getAttribute("data-auto_refill")
        );
        if (raw_auto_refill == 1) {
          auto_refill = auto_refill + 1;
        }

        var raw_auto_refiller = parseFloat(
          oneCheckBox.getAttribute("data-auto_refiller")
        );
        if (raw_auto_refiller == 1) {
          auto_refiller = auto_refiller + 1;
        }

        pricee =
          pricee + parseFloat(oneCheckBox.getAttribute("data-service-price"));

        var raw_auto_complete = parseFloat(
          oneCheckBox.getAttribute("data-auto_complete")
        );
        if (raw_auto_complete == 1) {
          auto_complete = auto_complete + 1;
        }

        var raw_start_count_type = parseFloat(
          oneCheckBox.getAttribute("data-start_count_type")
        );
        if (raw_start_count_type == 1) {
          auto_start_count = auto_start_count + 1;
        }
      }

      counter++;
      trigger = true;
      trParent.classList.add("active");
    } else {
      trParent.classList.remove("active");
    }
  });

  // if(counter > 20){
  //     Swal.fire({
  //         title: 'Error!',
  //         text: 'You can only max select 20 categories at once.',
  //         icon: 'error'
  //       })
  //       $(this).prop('checked', false);
  //       if($('input.selectOrder').filter(':checked').length == 1){
  //       $('input.selectOrder:not(:checked)').attr('disabled', 'disabled');
  //     } else{
  //       $('input.selectOrder').removeAttr('disabled');
  //     }
  // }

  if (trigger) {
    checkAll.checked = true;
    //countOrders.innerText = counter == 1?counter + ' user ':counter + ' users ';
    countOrders.innerText = counter == 1 ? counter + "  " : counter + "  ";
    try {
      //console.log('yes');
      //

      //alert();
      // && servicePriceSpan !== false
      if (typeof servicePriceSpan !== "undefined" && servicePriceSpan != null) {
        servicePriceSpan.innerText = pricee + "  ";
      }
      if (
        typeof serviceRefillSpan !== "undefined" &&
        serviceRefillSpan != null
      ) {
        serviceRefillSpan.innerText = auto_refill + "  ";
      }
      if (
        typeof serviceRefillerSpan !== "undefined" &&
        serviceRefillerSpan != null
      ) {
        serviceRefillerSpan.innerText = auto_refiller + "  ";
      }
      if (typeof serviceCountSpan !== "undefined" && serviceCountSpan != null) {
        serviceCountSpan.innerText = auto_start_count + "  ";
      }
      if (typeof serviceAutoCSpan !== "undefined" && serviceAutoCSpan != null) {
        serviceAutoCSpan.innerText = auto_complete + "  ";
      }

      if (typeof orderPriceSpan !== "undefined" && orderPriceSpan != null) {
        orderPriceSpan.innerText = pricee == 1 ? pricee + "  " : pricee + "  ";
      }
      if (
        typeof orderQuantitySpan !== "undefined" &&
        orderQuantitySpan != null
      ) {
        orderQuantitySpan.innerText =
          amount == 1 ? amount + "  " : amount + "  ";
      }
      if (
        typeof orderRemainingSpan !== "undefined" &&
        orderRemainingSpan != null
      ) {
        orderRemainingSpan.innerText =
          remaining == 1 ? remaining + "  " : remaining + "  ";
      }
    } catch (err) {}
    checkAllParentTh.classList.add("show-action-menu");
    return true;
  } else {
    checkAll.checked = false;
    checkAllParentTh.classList.remove("show-action-menu");
    return false;
  }
}

if (typeof checkAll !== "undefined" && checkAll != null) {
  checkAll.addEventListener("change", selectAllCheckbox);
}
allCheckBox.forEach(function (oneCheckBox) {
  oneCheckBox.addEventListener("change", selectOneCheckbox);
});

function re_initialize_listeners() {
  allCheckBox = document.querySelectorAll(".selectOrder");
  allCheckBox.forEach(function (oneCheckBox) {
    oneCheckBox.addEventListener("change", selectOneCheckbox);
  });
  //alert();
  var checkAll = document.querySelector(".checkAll");
  if (typeof checkAll !== "undefined" && checkAll != null) {
    checkAll.addEventListener("change", selectAllCheckbox);
  }

  $(document).ready(function () {
    $("body").tooltip({
      selector: '[data-toggle="tooltip"]',
    });
  });
}

$(document).ready(function () {
  $("#resetrates").click(function () {
    $("#bulkStatus").val("3");
    document.getElementById("bulkStatus").value = "3";
    //alert(document.getElementById('bulkStatus').value);
    $("#confirmChangeBulk").modal("show");

    return false;
  });

  //confirmEditService
  //editDescription
  //editdescriptionBody

  $("#editDescription").on("show.bs.modal", function (e) {
    document.getElementById("editdescriptionBody").style.display = "none";
    document.getElementById("editdescriptionBody2").style.display = "block";
    num = $(e.relatedTarget).data("href");

    var post_data = {
      method: "description",
      id: num,
    };
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
      //console.log(this);
      if (this.readyState == 4 && this.status == 200) {
        document.getElementById("editdescriptionBody").style.display = "block";
        document.getElementById("editdescriptionBody2").style.display = "none";

        var response = JSON.parse(this.responseText);
        if (response.status == 1) {
          document.getElementById("dessscription").value = urldecode(
            response.message
          );
          document.getElementById("start_id").value = num;
        } else {
          document.getElementById("editdescriptionBody").innerHTML =
            "<h2>Error.! reload page or contact administrator</h2>";
        }

        //$('#dessscription').val( 'Yeees' );
        //document.getElementById("editdescriptionBody").innerHTML = this.responseText;
      }
    };
    xhttp.open("POST", "ajax_services_details", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(JSON.stringify(post_data));

    //alert(num);
    //return false;
  });

  $("#enabledservices").click(function () {
    //alert('1');
    $("#bulkStatus").val("1");
    $("#confirmChangeBulk").modal("show");
    return false;
  });

  $("#disabledservices").click(function () {
    //alert('2');
    $("#bulkStatus").val("2");
    $("#confirmChangeBulk").modal("show");
    return false;
  });

  $("#assign_category_selected").click(function () {
    $("#bulkStatus").val("11");
    $("#assign_category_modal").modal("show");
    return false;
  });

  $("#editServicesBulkActions").click(function () {
    $("#bulkStatus").val("15");
    $("#assign_bulkaction_modal").modal("show");
    return false;
  });

  $("#deleteServices").click(function () {
    $("#bulkStatus").val("10");
    $("#confirmChangeBulk").modal("show");
    return false;
  });
});

function map_categories() {
  $("#bulkStatus").val("1");
  $("#confirmChangeBulk").modal("show");

  return false;
}

function map_services() {
  // $("#bulkStatus").val("2");
  // $("#confirmChangeBulk").modal("show");
  // return false;

  document.getElementById("confirmYesBulk").disabled = true;

  let import_action = $("#importValue").val();

  if (import_action == "categories") {
    var frm = $("#changebulkForm");
    var formData = JSON.stringify(frm.serialize());
    $("#hidden_data").attr("value", formData);
    frm.submit();
  } else if (import_action == "services") {
    var frm = $("#changebulkForm");

    // console.log(frm.serializeArray());

    var formData = JSON.stringify(frm.serializeArray());
    $("#services_data").attr("value", formData);
    var frm2 = $("#servicesAddForm");
    frm2.submit();

    // $.ajax({
    //   type: "post",
    //   url: "admin/api-services/ajax_services_add",
    //   data: frm.serialize(),
    //   success : function (data) {
    //       console.log(data);
    //   }
    // });
  } else {
  }

  //   frm.submit(function (ev) {
  //     $.ajax({
  //       type: "post",
  //       url: "admin/api-services/ajax_services_add",
  //       data: frm.serialize(),
  //       success: function (data) {
  //         // if (typeof data.status !== "undefined") {
  //         //     if (data.status == 1) {
  //         //         var message = data.message;
  //         //         document.getElementById("demo").innerHTML =
  //         //             '<div class="alert alert-success alert-dismissible" ><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>' +
  //         //             message +
  //         //             "</strong></div>";
  //         //         //document.getElementById("demo").innerHTML = '<div class="alert alert-success alert-dismissible" ><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Successfuly updated!</strong></div>';
  //         //     } else {
  //         //         var message = data.message;
  //         //         document.getElementById("demo").innerHTML =
  //         //             '<div class="alert alert-danger alert-dismissible" ><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>' +
  //         //             message +
  //         //             "</strong></div>";
  //         //         //document.getElementById("demo").innerHTML = '<div class="alert alert-danger alert-dismissible" ><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Error. Please contact adminsitrator!</strong></div>';
  //         //     }
  //         // } else {
  //         //     //console.log('no');
  //         //     document.getElementById("demo").innerHTML =
  //         //         '<div class="alert alert-danger alert-dismissible" ><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Error. Please contact adminsitrator!</strong></div>';
  //         // }

  //         $("#confirmChangeBulk").modal("hide");
  //       },
  //       error: function (XMLHttpRequest, textStatus, errorThrown) {
  //         //alert("Status: " + textStatus); alert("Error: " + errorThrown);
  //         document.getElementById("demo").innerHTML =
  //           '<div class="alert alert-danger alert-dismissible" ><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>There is communication problem. Please refresh page and try again!</strong></div>';
  //         $("#confirmChangeBulk").modal("hide");
  //       },
  //     });
  //     ev.preventDefault();
  //   });
  return false;
}

function urldecode(str) {
  if (typeof str != "string") {
    return str;
  }
  return decodeURIComponent(str.replace(/\+/g, " "));
}

function copy_to_clipboard(value) {
  var tempInput = document.createElement("input");
  tempInput.value = value;
  document.body.appendChild(tempInput);
  tempInput.select();
  document.execCommand("copy");
  document.body.removeChild(tempInput);
}

document.addEventListener("DOMContentLoaded", function (event) {
  var bs = BreakpointSwitcher.create({
    "768px": function (e) {
      e
        ? window.navPriority('[data-nav="navbar-priority"]', {
            containerSelector: null,
            containerWidthOffset: 180,
            dropdownLabel: '<span class="fa fa-ellipsis-v"></span>',
          })
        : window.navPriority('[data-nav="navbar-priority"]', "destroy");
    },
  });
});
