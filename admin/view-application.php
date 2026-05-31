<?php
include '../config.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$application_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT a.*, u.tin, u.email, u.phone 
                      FROM applications a 
                      JOIN users u ON a.user_id = u.user_id 
                      WHERE a.id = ?");
$stmt->execute([$application_id]);
$application = $stmt->fetch();

if (!$application) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM uploads WHERE application_id = ?");
$stmt->execute([$application_id]);
$uploads = $stmt->fetchAll();
?>


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

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f5f7f9;
}

.admin-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    padding: 15px 0;
    position: relative;
    box-shadow: var(--box-shadow);
    margin-bottom: 30px;
}

.admin-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.admin-nav a img {
    height: 60px;
    width: auto;
    transition: transform 0.3s ease;
}

.admin-nav a img:hover {
    transform: scale(1.05);
}

.admin-nav-links ul {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 5px;
}

.admin-nav-links li {
    margin: 0;
}

.admin-nav-links a {
    color: white;
    text-decoration: none;
    padding: 12px 20px;
    font-weight: 600;
    border-radius: 6px;
    transition: var(--transition);
    display: block;
    position: relative;
    overflow: hidden;
}

.admin-nav-links a:before {
    content: "";
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 3px;
    background: #fff;
    transition: var(--transition);
    border-radius: 3px 3px 0 0;
}

.admin-nav-links a:hover:before {
    width: 70%;
}

.admin-nav-links a:hover {
    background: rgba(255, 255, 255, 0.1);
}

.admin-nav-links a.active {
    background: rgba(255, 255, 255, 0.2);
}

.admin-nav-links a.active:before {
    width: 70%;
}

.admin-text-box {
    text-align: center;
    padding: 40px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.admin-text-box h1 {
    font-size: 2.8rem;
    margin-bottom: 10px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.admin-text-box p {
    font-size: 1.2rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
}

.page-header h1 {
    color: var(--primary);
    margin: 0;
    font-size: 28px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.application-view {
    background: #fff;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    margin-bottom: 30px;
}

.view-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: var(--light);
    border-bottom: 1px solid var(--light-gray);
}

.view-header h2 {
    margin: 0;
    color: var(--primary);
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 15px;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-block;
}

.status-submitted {
    background-color: #fff3cd;
    color: #856404;
}

.status-under_review {
    background-color: #cce5ff;
    color: #004085;
}

.status-approved {
    background-color: #d4edda;
    color: #155724;
}

.status-rejected {
    background-color: #f8d7da;
    color: #721c24;
}

.status-pending_payment {
    background-color: #fff3cd;
    color: #856404;
}

.status-paid {
    background-color: #d4edda;
    color: #155724;
}

.action-buttons {
    display: flex;
    gap: 10px;
}

.application-content {
    padding: 20px;
}

.application-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--light-gray);
}

.application-section:last-child {
    border-bottom: none;
}

.application-section h2 {
    color: var(--primary);
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--light-gray);
    font-size: 22px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
}

.info-item {
    display: flex;
    flex-direction: column;
    padding: 15px;
    background: var(--light);
    border-radius: var(--border-radius);
    border-left: 4px solid var(--secondary);
}

.info-item strong {
    color: var(--gray);
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.info-item span {
    color: var(--dark);
    font-weight: 500;
    font-size: 16px;
}

.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.document-item {
    display: flex;
    flex-direction: column;
    padding: 15px;
    background: var(--light);
    border-radius: var(--border-radius);
    border-left: 4px solid var(--secondary);
    transition: var(--transition);
}

.document-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.document-item h3 {
    margin: 0 0 10px 0;
    font-size: 1rem;
    color: var(--primary);
}

.admin-actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
    padding: 20px;
    background: var(--light);
    border-radius: var(--border-radius);
    border: 1px solid var(--light-gray);
}

.admin-actions form {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.admin-actions input[type="text"] {
    padding: 12px 15px;
    border: 1px solid var(--light-gray);
    border-radius: var(--border-radius);
    font-size: 1rem;
    min-width: 300px;
    transition: var(--transition);
}

.admin-actions input[type="text"]:focus {
    outline: none;
    border-color: var(--secondary);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 24px;
    border: none;
    border-radius: var(--border-radius);
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: var(--transition);
    gap: 8px;
}

.btn-primary {
    background-color: var(--secondary);
    color: white;
}

.btn-primary:hover {
    background-color: #2980b9;
    transform: translateY(-2px);
}

.btn-secondary {
    background-color: var(--gray);
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
    transform: translateY(-2px);
}

.btn-success {
    background-color: var(--success);
    color: white;
}

.btn-success:hover {
    background-color: #218838;
    transform: translateY(-2px);
}

.btn-danger {
    background-color: var(--danger);
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
    transform: translateY(-2px);
}

.footer {
    text-align: center;
    padding: 30px 20px;
    margin-top: 40px;
    color: var(--gray);
    border-top: 1px solid var(--light-gray);
    background-color: #78b4f0ff;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.footer-links {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-bottom: 15px;
}

.footer-links a {
    color: var(--secondary);
    text-decoration: none;
    transition: var(--transition);
}

.footer-links a:hover {
    color: var(--primary);
    text-decoration: underline;
}

@media (max-width: 992px) {
    .admin-nav {
        flex-direction: column;
        gap: 15px;
    }
    
    .admin-nav-links ul {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .view-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .action-buttons {
        width: 100%;
        justify-content: flex-start;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .admin-actions form {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .admin-actions input[type="text"] {
        min-width: unset;
        width: 100%;
    }
    
    .admin-nav-links ul {
        flex-direction: column;
        gap: 10px;
    }
    
    .admin-nav-links li {
        width: 100%;
        text-align: center;
    }
    
    .admin-text-box h1 {
        font-size: 2.2rem;
    }
    
    .admin-text-box p {
        font-size: 1rem;
    }
    
    .footer-links {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<div class="admin-header">
    <nav class="admin-nav">
        <a href="../index.php"><img src="../images/tra.png"></a>
        <div class="admin-nav-links">
            <ul>
                <li><a href="../index.php">HOME</a></li>
                <li><a href="dashboard.php" class="active">DASHBOARD</a></li>
                <li><a href="logout.php">LOGOUT</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="admin-text-box">
        <h1>Admin Portal</h1>
        <p>Vehicle Registration System Management</p>
    </div>
</div>

<div class="container">
    <div class="page-header">
        <h1>Application Details</h1>
        <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <div class="application-view">
        <div class="view-header">
            <div>
                <h2><?php echo htmlspecialchars($application['reference_number']); ?></h2>
                <span class="status-badge status-<?php echo $application['status']; ?>">
                    <?php echo ucfirst($application['status']); ?>
                </span>
            </div>
        </div>

        <div class="application-content">
            <div class="application-section">
                <h2>Application Information</h2>
                <div class="info-grid">
                    <div class="info-item"><strong>Reference Number:</strong><span><?php echo htmlspecialchars($application['reference_number']); ?></span></div>
                    <div class="info-item"><strong>Applicant TIN:</strong><span><?php echo htmlspecialchars($application['tin']); ?></span></div>
                    <div class="info-item"><strong>Submission Date:</strong><span><?php echo date('M j, Y g:i A', strtotime($application['created_at'])); ?></span></div>
                    <div class="info-item"><strong>Status:</strong><span class="status-badge status-<?php echo $application['status']; ?>"><?php echo ucfirst($application['status']); ?></span></div>
                    <div class="info-item"><strong>Payment Status:</strong><span class="status-badge status-<?php echo $application['payment_status']; ?>"><?php echo ucfirst($application['payment_status']); ?></span></div>
                    <?php if ($application['registration_number']): ?>
                    <div class="info-item"><strong>Plate Number:</strong><span><?php echo htmlspecialchars($application['registration_number']); ?></span></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="application-section">
                <h2>Personal Information</h2>
                <div class="info-grid">
                    <div class="info-item"><strong>Full Name:</strong><span><?php echo htmlspecialchars($application['full_name']); ?></span></div>
                    <div class="info-item"><strong>Date of Birth:</strong><span><?php echo htmlspecialchars($application['dob']); ?></span></div>
                    <div class="info-item"><strong>ID Type:</strong><span><?php echo htmlspecialchars($application['id_type']); ?></span></div>
                    <div class="info-item"><strong>ID Number:</strong><span><?php echo htmlspecialchars($application['id_number']); ?></span></div>
                    <div class="info-item"><strong>Residential Address:</strong><span><?php echo htmlspecialchars($application['physical_address']); ?></span></div>
                    <div class="info-item"><strong>Phone Number:</strong><span><?php echo htmlspecialchars($application['primary_phone']); ?></span></div>
                    <div class="info-item"><strong>Email Address:</strong><span><?php echo htmlspecialchars($application['email']); ?></span></div>
                </div>
            </div>

            <div class="application-section">
                <h2>Vehicle Information</h2>
                <div class="info-grid">
                    <div class="info-item"><strong>VIN / Chassis Number:</strong><span><?php echo htmlspecialchars($application['vin']); ?></span></div>
                    <div class="info-item"><strong>Engine Number:</strong><span><?php echo htmlspecialchars($application['engine_number']); ?></span></div>
                    <div class="info-item"><strong>Make:</strong><span><?php echo htmlspecialchars($application['make']); ?></span></div>
                    <div class="info-item"><strong>Model:</strong><span><?php echo htmlspecialchars($application['model']); ?></span></div>
                    <div class="info-item"><strong>Year of Manufacture:</strong><span><?php echo htmlspecialchars($application['year']); ?></span></div>
                    <div class="info-item"><strong>Vehicle Type:</strong><span><?php echo htmlspecialchars($application['vehicle_type']); ?></span></div>
                    <div class="info-item"><strong>Color:</strong><span><?php echo htmlspecialchars($application['color']); ?></span></div>
                    <div class="info-item"><strong>Fuel Type:</strong><span><?php echo htmlspecialchars($application['fuel_type']); ?></span></div>
                    <div class="info-item"><strong>Transmission:</strong><span><?php echo htmlspecialchars($application['transmission']); ?></span></div>
                    <div class="info-item"><strong>Odometer:</strong><span><?php echo htmlspecialchars($application['odometer']); ?></span></div>
                </div>
            </div>

            <div class="application-section">
                <h2>Insurance Information</h2>
                <div class="info-grid">
                    <div class="info-item"><strong>Insurance Provider:</strong><span><?php echo htmlspecialchars($application['insurance_provider']); ?></span></div>
                    <div class="info-item"><strong>Policy Number:</strong><span><?php echo htmlspecialchars($application['policy_number']); ?></span></div>
                    <div class="info-item"><strong>Insurance Start Date:</strong><span><?php echo htmlspecialchars($application['insurance_start']); ?></span></div>
                    <div class="info-item"><strong>Insurance Expiry Date:</strong><span><?php echo htmlspecialchars($application['insurance_expiry']); ?></span></div>
                    <div class="info-item"><strong>Type of Cover:</strong><span><?php echo htmlspecialchars($application['cover_type']); ?></span></div>
                </div>
            </div>

            <?php if (!empty($uploads)): ?>
            <div class="application-section">
                <h2>Uploaded Documents</h2>
                <div class="documents-grid">
                    <?php foreach ($uploads as $upload): ?>
                        <div class="document-item">
                            <h3><?php echo ucfirst(str_replace('_', ' ', $upload['file_type'])); ?></h3>
                            <a href="../<?php echo htmlspecialchars($upload['file_path']); ?>" target="_blank" class="btn btn-secondary">
                                View Document
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="application-section">
                <h2>Admin Actions</h2>
                <div class="admin-actions">
                    <?php if ($application['status'] == 'submitted' || $application['status'] == 'under_review'): ?>
                        <form action="approve.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $application['id']; ?>">
                            <button type="submit" class="btn btn-success" onclick="return confirm('Approve this application?')">
                                Approve Application
                            </button>
                        </form>
                        
                        <form action="reject.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $application['id']; ?>">
                            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                <input type="text" name="rejection_reason" placeholder="Reason for rejection" required>
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this application?')">
                                    Reject Application
                                </button>
                            </div>
                        </form>
                    <?php elseif ($application['status'] == 'approved' && empty($application['registration_number'])): ?>
                        <form action="assign-plate.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $application['id']; ?>">
                            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                <input type="text" name="plate_number" placeholder="Enter plate number" required 
                                       pattern="[A-Z0-9\s]{2,10}" title="Plate number format: T 123 ABC">
                                <input type="date" name="pickup_date" required title="Select pickup date">
                                <button type="submit" class="btn btn-primary">
                                    Assign Plate Number
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-links">
            <a href="../index.php">Home</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
            <a href="#">Help</a>
            <a href="#">Contact</a>
        </div>
        <p>© <?php echo date('Y'); ?> Tanzania Revenue Authority. All rights reserved.</p>
        <p>Vehicle Registration System </p>
    </div>
</footer>

