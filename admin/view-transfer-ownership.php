<?php
include '../config.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$transfer_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT t.*, u.email as submitted_by_email FROM transfer_ownership t JOIN users u ON t.user_id = u.user_id WHERE t.id = ?");
$stmt->execute([$transfer_id]);
$transfer = $stmt->fetch();

if (!$transfer) {
    header("Location: dashboard.php");
    exit;
}
?>
<?php include 'includes/header.php'; ?>
<style>
:root {
    --primary: #2c3e50;
    --secondary: #3498db;
    --success: #28a745;
    --danger: #dc3545;
    --warning: #ffc107;
    --light: #f8f9fa;
    --dark: #343a40;
    --gray: #6c757d;
    --light-gray: #e9ecef;
    --border-radius: 8px;
    --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}
body { font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f7f9; }
.container { max-width: 1200px; margin: 0 auto; padding: 20px; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding: 20px; background: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow); }
.application-view { background: #fff; border-radius: var(--border-radius); box-shadow: var(--box-shadow); overflow: hidden; margin-bottom: 30px; }
.application-section { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid var(--light-gray); }
.application-section:last-child { border-bottom: none; }
.application-section h2 { color: var(--primary); margin-top: 0; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--light-gray); font-size: 22px; }
.info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px; }
.info-item { display: flex; flex-direction: column; padding: 15px; background: var(--light); border-radius: var(--border-radius); border-left: 4px solid var(--secondary); }
.info-item strong { color: var(--gray); font-size: 0.9rem; margin-bottom: 5px; }
.info-item span { color: var(--dark); font-weight: 500; font-size: 16px; }
.documents-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; }
.document-item { display: flex; flex-direction: column; padding: 15px; background: var(--light); border-radius: var(--border-radius); border-left: 4px solid var(--secondary); transition: var(--transition); }
.document-item:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
.document-item h3 { margin: 0 0 10px 0; font-size: 1rem; color: var(--primary); }
.admin-actions { display: flex; flex-direction: column; gap: 15px; padding: 20px; background: var(--light); border-radius: var(--border-radius); border: 1px solid var(--light-gray); }
.admin-actions form { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.btn { display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border: none; border-radius: var(--border-radius); font-weight: 600; text-decoration: none; cursor: pointer; transition: var(--transition); gap: 8px; }
.btn-success { background-color: var(--success); color: white; }
.btn-success:hover { background-color: #218838; transform: translateY(-2px); }
.btn-danger { background-color: var(--danger); color: white; }
.btn-danger:hover { background-color: #c82333; transform: translateY(-2px); }
.btn-primary { background-color: var(--secondary); color: white; }
.btn-primary:hover { background-color: #2980b9; transform: translateY(-2px); }
.btn-secondary { background-color: var(--gray); color: white; }
.btn-secondary:hover { background-color: #5a6268; transform: translateY(-2px); }
</style>
<div class="container">
    <div class="page-header">
        <h1>Transfer Ownership Details</h1>
        <a href="dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
    </div>
    <div class="application-view">
        <div class="application-section">
            <h2>Seller Information</h2>
            <div class="info-grid">
                <div class="info-item"><strong>Full Name:</strong><span><?php echo htmlspecialchars($transfer['seller_full_name']); ?></span></div>
                <div class="info-item"><strong>NIN/Passport:</strong><span><?php echo htmlspecialchars($transfer['seller_id']); ?></span></div>
                <div class="info-item"><strong>Phone:</strong><span><?php echo htmlspecialchars($transfer['seller_phone']); ?></span></div>
                <div class="info-item"><strong>Email:</strong><span><?php echo htmlspecialchars($transfer['seller_email']); ?></span></div>
                <div class="info-item"><strong>Address:</strong><span><?php echo htmlspecialchars($transfer['seller_address']); ?></span></div>
            </div>
        </div>
        <div class="application-section">
            <h2>Buyer Information</h2>
            <div class="info-grid">
                <div class="info-item"><strong>Full Name:</strong><span><?php echo htmlspecialchars($transfer['buyer_full_name']); ?></span></div>
                <div class="info-item"><strong>NIN/Passport:</strong><span><?php echo htmlspecialchars($transfer['buyer_id']); ?></span></div>
                <div class="info-item"><strong>Date of Birth:</strong><span><?php echo htmlspecialchars($transfer['buyer_dob']); ?></span></div>
                <div class="info-item"><strong>Phone:</strong><span><?php echo htmlspecialchars($transfer['buyer_phone']); ?></span></div>
                <div class="info-item"><strong>Email:</strong><span><?php echo htmlspecialchars($transfer['buyer_email']); ?></span></div>
                <div class="info-item"><strong>Address:</strong><span><?php echo htmlspecialchars($transfer['buyer_address']); ?></span></div>
            </div>
        </div>
        <div class="application-section">
            <h2>Vehicle Information</h2>
            <div class="info-grid">
                <div class="info-item"><strong>Registration Number:</strong><span><?php echo htmlspecialchars($transfer['vehicle_reg_number']); ?></span></div>
                <div class="info-item"><strong>VIN/Chassis:</strong><span><?php echo htmlspecialchars($transfer['vehicle_vin']); ?></span></div>
                <div class="info-item"><strong>Engine Number:</strong><span><?php echo htmlspecialchars($transfer['engine_number']); ?></span></div>
                <div class="info-item"><strong>Make:</strong><span><?php echo htmlspecialchars($transfer['make']); ?></span></div>
                <div class="info-item"><strong>Model:</strong><span><?php echo htmlspecialchars($transfer['model']); ?></span></div>
                <div class="info-item"><strong>Year of Manufacture:</strong><span><?php echo htmlspecialchars($transfer['year_manufacture']); ?></span></div>
                <div class="info-item"><strong>Color:</strong><span><?php echo htmlspecialchars($transfer['color']); ?></span></div>
                <div class="info-item"><strong>Odometer:</strong><span><?php echo htmlspecialchars($transfer['odometer']); ?></span></div>
            </div>
        </div>
        <div class="application-section">
            <h2>Supporting Documents</h2>
            <div class="documents-grid">
                <div class="document-item">
                    <h3>Sale Agreement</h3>
                    <a href="../<?php echo htmlspecialchars($transfer['sale_agreement']); ?>" target="_blank" class="btn btn-secondary">View Document</a>
                </div>
                <div class="document-item">
                    <h3>Previous Registration Card</h3>
                    <a href="../<?php echo htmlspecialchars($transfer['prev_reg_card']); ?>" target="_blank" class="btn btn-secondary">View Document</a>
                </div>
            </div>
        </div>
        <div class="application-section">
            <h2>Payment & Submission</h2>
            <div class="info-grid">
                <div class="info-item"><strong>Transfer Fee:</strong><span><?php echo number_format($transfer['transfer_fee']); ?> TZS</span></div>
                <div class="info-item"><strong>Payment Method:</strong><span><?php echo htmlspecialchars($transfer['payment_method']); ?></span></div>
                <div class="info-item"><strong>Submitted By (User Email):</strong><span><?php echo htmlspecialchars($transfer['submitted_by_email']); ?></span></div>
                <div class="info-item"><strong>Submitted At:</strong><span><?php echo htmlspecialchars($transfer['created_at']); ?></span></div>
                <div class="info-item"><strong>Status:</strong><span><?php echo ucfirst($transfer['status']); ?></span></div>
                <?php if ($transfer['reviewed_by']): ?>
                <div class="info-item"><strong>Reviewed By:</strong><span><?php echo htmlspecialchars($transfer['reviewed_by']); ?></span></div>
                <div class="info-item"><strong>Reviewed At:</strong><span><?php echo htmlspecialchars($transfer['reviewed_at']); ?></span></div>
                <?php endif; ?>
                <?php if ($transfer['status'] === 'rejected' && !empty($transfer['rejection_reason'])): ?>
                <div class="info-item"><strong>Rejection Reason:</strong><span><?php echo htmlspecialchars($transfer['rejection_reason']); ?></span></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="application-section">
            <h2>Admin Actions</h2>
            <div class="admin-actions">
                <form method="post" action="approve-transfer.php" style="display:inline-block;">
                    <input type="hidden" name="id" value="<?php echo $transfer_id; ?>">
                    <button type="submit" name="action" value="approve" class="btn btn-success" onclick="return confirm('Approve this transfer application?')">Approve</button>
                </form>
                <form method="post" action="approve-transfer.php" style="display:inline-block; margin-left:10px;">
                    <input type="hidden" name="id" value="<?php echo $transfer_id; ?>">
                    <input type="text" name="rejection_reason" placeholder="Reason for rejection" required style="min-width:200px;">
                    <button type="submit" name="action" value="reject" class="btn btn-danger" onclick="return confirm('Reject this transfer application?')">Reject</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
