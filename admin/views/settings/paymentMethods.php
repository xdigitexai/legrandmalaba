<div class="container-fluid margin-top-container">
    <div class="row">
        <div class="col mb-3 col-12 col-md-4">
            <div class="input-group mb-3">
                <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                <input type="text" id="payment_methods_search" class="form-control" placeholder="Search payment methods..." >
                <style>.modal-body .payment-card{margin:0px 10px;min-width:310px;}</style>
                <button class="btn btn-labeled btn-primary" type="button" data-bs-toggle="modal" data-form="add_new_method"  data-bs-target="#staticBackdropModal"><span class="btn-label"><i class="bi bi-plus-lg"></i></span>Add new method</button>
            </div>
        </div>
            <div id="page-loader">
                <center><svg class="spinner large" viewBox="0 0 48 48">
                        <circle class="path" cx="24" cy="24" r="20" fill="none" stroke-width="5"></circle>
                    </svg></center>
            </div>
            <div id="paymentMethods" class="page-content col col-lg-12">
            </div>
        </div>
    </div>