<?php include 'header.php'; ?>

<style>
.container-fluid {
    width: 100%;
    padding: 10px 20px;
    background-color: #f8f9fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* === Header Buttons & Search Row === */
.top-controls {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 15px;
}

.left-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

/* === Modern Search Bar === */
.search-container {
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1px solid #dce0e5;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.2s ease;
}

.search-container:focus-within {
    box-shadow: 0 0 0 2px #007bff33;
    border-color: #007bff;
}

.search-container input[type="text"] {
    border: none;
    outline: none;
    padding: 8px 12px;
    font-size: 14px;
    flex: 1;
    min-width: 200px;
}

.search-container select {
    border: none;
    border-left: 1px solid #e5e7eb;
    background: #fafafa;
    color: #333;
    font-size: 14px;
    padding: 8px 12px;
    cursor: pointer;
}

.search-container button {
    background-color: #fff;
    color: #3f4149;
    border: none;
    padding: 8px 14px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.search-container button:hover {
    background-color: #0069d9;
}

/* Buttons */
.btn-default {
    padding: 6px 12px;
    font-size: 13px;
    background-color: #ffffff;
    border: 1px solid #dcdfe3;
    color: #333;
    border-radius: 6px;
    transition: background 0.2s ease;
}

.btn-default:hover {
    background-color: #e9ecef;
}

/* === Table === */
.table-responsive-wrapper {
    overflow-x: auto;
    overflow-y: visible;
    position: relative;
    z-index: 1;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    background: #fff;
    padding: 5px;
}

.table {
    width: 100%;
    min-width: 950px;
    border-collapse: collapse;
    background-color: #fff;
}

.table th,
.table td {
    padding: 8px 10px;
    text-align: left;
    vertical-align: middle;
}

.table thead th {
    background-color: #f1f3f5;
    font-weight: 600;
    border-bottom: 1px solid #dee2e6;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

/* Pagination */
.pagination li a {
    padding: 5px 8px;
    font-size: 13px;
    margin: 1px;
}

/* Alerts */
.alert {
    border-radius: 6px;
    padding: 10px 15px;
    font-size: 14px;
    margin-bottom: 12px;
}
.alert-success {
    background-color: #d1e7dd;
    color: #0f5132;
    border: 1px solid #badbcc;
}
.alert-danger {
    background-color: #f8d7da;
    color: #842029;
    border: 1px solid #f5c2c7;
}
</style>

<div class="container-fluid">
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $successText; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $errorText; ?></div>
    <?php endif; ?>

    <!-- === Top Buttons + Search Bar === -->
    <div class="top-controls">
        <div class="left-controls">
            <button class="btn btn-default" data-toggle="modal" data-target="#modalDiv" data-action="payment_new">
                Add/Remove Balance
            </button>
            <a class="btn btn-default" href="<?= site_url('admin/fund-add-history') ?>">
                <i class="fas fa-plus-circle"></i> Old add fund page
            </a>
        </div>

<div class="right-search">
    <form class="form-inline" action="<?= site_url('admin/payments/online') ?>" method="get">
        <div class="search-container">
            <input type="text" name="search" value="<?= $search_word ?>" placeholder="Search payments...">
            <button type="submit"><i class="fa fa-search"></i></button>
        </div>
    </form>
</div>
</div>

    <!-- === Table === -->
    <div class="table-responsive-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Balance</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Details</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>

            <form id="changebulkForm" action="<?= site_url('admin/payments/online/multi-action') ?>" method="post">
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= $payment["payment_id"] ?></td>
                            <td>
                                <span class="label-id"><?= $payment["client_id"] ?></span>
                                <?= $payment["username"] ?>
                            </td>
                            <td><?= $payment["client_balance"] ?></td>
                            <td><?= $payment["payment_amount"] ?></td>
                            <td>
                                <?php if ($payment['payment_status'] == 1): ?>
                                    Pending
                                <?php elseif ($payment['payment_status'] == 3): ?>
                                    <strong style="background-color:green;color:white;">Completed</strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                Manual - Manual<br>
                                Order ID: <?= $payment["payment_extra"] ?><br>
                                Special Note: <?= $payment["payment_note"] ?: "No" ?>
                            </td>
                            <td><?= $payment["payment_create_date"] ?></td>
                            <td>
                                <div class="dropdown pull-right">
                                    <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                        Actions <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php if ($payment["payment_mode"] == "Auto"): ?>
                                            <li>
                                                <a href="#" data-toggle="modal" data-target="#modalDiv"
                                                   data-action="payment_detail" data-id="<?= $payment["payment_id"] ?>">
                                                   Payment details
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <input type="hidden" name="bulkStatus" id="bulkStatus" value="0">
            </form>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
