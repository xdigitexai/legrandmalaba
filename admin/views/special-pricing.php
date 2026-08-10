<?php include("new-header.php"); ?>

<div class="container-fluid margin-top-container">
    <div class="row">
        <div class="col">
            <button class="btn btn-labeled btn-primary" type="button" data-bs-toggle="modal"
                data-form="create_special_price" data-bs-target="#staticBackdropModal"><span class="btn-label"><i
                        class="bi bi-plus-lg"></i></span>Create Special Price</button>

            <button class="btn btn-labeled btn-danger float-end" type="button" data-ajax="true"
                data-action-ajax="admin/special-pricing/delete-all"><span class="btn-label"><i
                        class="bi bi-trash3"></i></span>Delete All</button>

            <div data-pattern="priority-columns" class="mt-2">
                <table cellspacing="0"
                    class="table table-small-font table-tighten table-bordered  table-striped table-hover loading">
                    <thead class="table-secondary">
                        <tr>
                            <th>User</th>
                            <th>Service</th>
                            <th>Cost Price</th>
                            <th>Selling Price</th>
                            <th>Special Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6">
                                <center><svg class="spinner medium" viewBox="0 0 48 48">
                                        <circle class="path" cx="24" cy="24" r="20" fill="none" stroke-width="5">
                                        </circle>
                                    </svg> Loading...</center>
                            </td>
                        </tr>
                    </tbody>
                </table>


            </div>
        </div>
    </div>

    <?php include("new-footer.php"); ?>