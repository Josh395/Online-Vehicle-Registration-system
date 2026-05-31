<?php
include '../config.php';
requireAdmin();

$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

$query = "SELECT a.*, u.tin, u.email, u.phone 
          FROM applications a 
          JOIN users u ON a.user_id = u.user_id 
          WHERE 1=1";
$params = [];

if ($status_filter !== 'all') {
    $query .= " AND a.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $query .= " AND (a.reference_number LIKE ? OR a.full_name LIKE ? OR u.tin LIKE ? OR a.registration_number LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
}

$query .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Count applications by status
$status_counts = [];
$statuses = ['all', 'draft', 'submitted', 'under_review', 'approved', 'rejected'];
foreach ($statuses as $status) {
    if ($status === 'all') {
        $stmt = $pdo->query("SELECT COUNT(*) FROM applications");
        $count = $stmt->fetchColumn();
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE status = ?");
        $stmt->execute([$status]);
        $count = $stmt->fetchColumn();
    }
    $status_counts[$status] = $count;
}

//transfer ownership requests
$transfer_status_filter = $_GET['transfer_status'] ?? 'pending';
$transfer_query = "SELECT t.*, u.email as submitted_by_email FROM transfer_ownership t JOIN users u ON t.user_id = u.user_id WHERE 1=1";
$transfer_params = [];
if ($transfer_status_filter !== 'all') {
    $transfer_query .= " AND t.status = ?";
    $transfer_params[] = $transfer_status_filter;
}
$transfer_query .= " ORDER BY t.created_at DESC";
$transfer_stmt = $pdo->prepare($transfer_query);
$transfer_stmt->execute($transfer_params);
$transfer_requests = $transfer_stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<div class="admin-container">
    <div class="admin-content">
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?php echo $_SESSION['admin_username']; ?> (<?php echo $_SESSION['admin_role']; ?>)</p>

        <div class="admin-stats">
            <div class="stat-card">
                <h3>Total Applications</h3>
                <p class="stat-number"><?php echo $status_counts['all']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Pending Review</h3>
                <p class="stat-number"><?php echo $status_counts['submitted']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Approved</h3>
                <p class="stat-number"><?php echo $status_counts['approved']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Rejected</h3>
                <p class="stat-number"><?php echo $status_counts['rejected']; ?></p>
            </div>
        </div>

        <div class="admin-filters">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" onchange="this.form.submit()">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Applications (<?php echo $status_counts['all']; ?>)</option>
                        <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft (<?php echo $status_counts['draft']; ?>)</option>
                        <option value="submitted" <?php echo $status_filter === 'submitted' ? 'selected' : ''; ?>>Submitted (<?php echo $status_counts['submitted']; ?>)</option>
                        <option value="under_review" <?php echo $status_filter === 'under_review' ? 'selected' : ''; ?>>Under Review (<?php echo $status_counts['under_review']; ?>)</option>
                        <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved (<?php echo $status_counts['approved']; ?>)</option>
                        <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected (<?php echo $status_counts['rejected']; ?>)</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Search applications..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="admin-btn">Search</button>
                </div>
            </form>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Applicant</th>
                    <th>Vehicle</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($applications)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No applications found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($app['reference_number']); ?></td>
                            <td>
                                <div><?php echo htmlspecialchars($app['full_name']); ?></div>
                                <small>TIN: <?php echo htmlspecialchars($app['tin']); ?></small>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($app['make'] . ' ' . $app['model']); ?></div>
                                <small>VIN: <?php echo htmlspecialchars($app['vin']); ?></small>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($app['created_at'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $app['status']; ?>">
                                    <?php echo ucfirst($app['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $app['payment_status']; ?>">
                                    <?php echo ucfirst($app['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view-application.php?id=<?php echo $app['id']; ?>" class="admin-btn" style="background: #3498db; color: #fff; font-weight: bold; padding: 6px 16px; border-radius: 4px; text-decoration: none; box-shadow: 0 2px 6px rgba(52,152,219,0.15); transition: background 0.2s;">View</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Transfer Ownership Requests Section -->
        <div class="admin-section">
            <h2>Transfer Ownership Requests</h2>
            <form method="GET" class="filter-form">
                <label for="transfer_status">Status:</label>
                <select id="transfer_status" name="transfer_status" onchange="this.form.submit()">
                    <option value="all" <?php if($transfer_status_filter==='all') echo 'selected'; ?>>All</option>
                    <option value="pending" <?php if($transfer_status_filter==='pending') echo 'selected'; ?>>Pending</option>
                    <option value="approved" <?php if($transfer_status_filter==='approved') echo 'selected'; ?>>Approved</option>
                    <option value="rejected" <?php if($transfer_status_filter==='rejected') echo 'selected'; ?>>Rejected</option>
                </select>
            </form>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Seller</th>
                        <th>Buyer</th>
                        <th>Vehicle</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($transfer_requests)): ?>
                    <tr><td colspan="7" class="text-center">No transfer requests found</td></tr>
                <?php else: foreach ($transfer_requests as $tr): ?>
                    <tr>
                        <td><?php echo $tr['id']; ?></td>
                        <td><?php echo htmlspecialchars($tr['seller_full_name']); ?></td>
                        <td><?php echo htmlspecialchars($tr['buyer_full_name']); ?></td>
                        <td><?php echo htmlspecialchars($tr['vehicle_reg_number']); ?></td>
                        <td><?php echo htmlspecialchars($tr['created_at']); ?></td>
                        <td><?php echo ucfirst($tr['status']); ?></td>
                        <td><a href="view-transfer-ownership.php?id=<?php echo $tr['id']; ?>" class="btn btn-primary">View</a></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
