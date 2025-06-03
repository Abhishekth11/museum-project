<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

$page_title = "Membership Management - Admin Dashboard";

// Handle membership actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_card_status':
                $membership_id = $_POST['membership_id'];
                $stmt = $pdo->prepare("UPDATE memberships SET card_mailed = 1, card_mailed_date = NOW() WHERE id = ?");
                $stmt->execute([$membership_id]);
                $message = "Card status updated successfully.";
                break;
                
            case 'send_renewal_reminder':
                $membership_id = $_POST['membership_id'];
                // Send renewal reminder email logic here
                $stmt = $pdo->prepare("UPDATE memberships SET renewal_reminder_sent = 1 WHERE id = ?");
                $stmt->execute([$membership_id]);
                $message = "Renewal reminder sent successfully.";
                break;
        }
    }
}

// Get membership statistics
$stats = [
    'total_active' => $pdo->query("SELECT COUNT(*) FROM memberships WHERE status = 'active'")->fetchColumn(),
    'expiring_soon' => $pdo->query("SELECT COUNT(*) FROM memberships WHERE status = 'active' AND end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)")->fetchColumn(),
    'new_this_month' => $pdo->query("SELECT COUNT(*) FROM memberships WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn(),
    'revenue_this_month' => $pdo->query("SELECT SUM(price_paid) FROM memberships WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn()
];

// Get recent memberships
$recent_memberships = $pdo->query("
    SELECT m.*, u.first_name, u.last_name, u.email 
    FROM memberships m 
    JOIN users u ON m.user_id = u.id 
    ORDER BY m.created_at DESC 
    LIMIT 20
")->fetchAll();

include '../admin/includes/sidebar.php';
?>

<div class="admin-content">
    <div class="admin-header">
        <h1>Membership Management</h1>
        <p>Manage member accounts, track renewals, and monitor membership statistics</p>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_active']); ?></h3>
                <p>Active Members</p>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['expiring_soon']); ?></h3>
                <p>Expiring Soon</p>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['new_this_month']); ?></h3>
                <p>New This Month</p>
            </div>
        </div>
        
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <h3>$<?php echo number_format($stats['revenue_this_month'], 2); ?></h3>
                <p>Revenue This Month</p>
            </div>
        </div>
    </div>

    <div class="admin-section">
        <h2>Recent Memberships</h2>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Type</th>
                        <th>Member ID</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Amount</th>
                        <th>Card Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_memberships as $membership): ?>
                    <tr>
                        <td>
                            <div class="member-info">
                                <strong><?php echo htmlspecialchars($membership['first_name'] . ' ' . $membership['last_name']); ?></strong>
                                <small><?php echo htmlspecialchars($membership['email']); ?></small>
                            </div>
                        </td>
                        <td>
                            <span class="membership-type <?php echo $membership['membership_type']; ?>">
                                <?php echo ucfirst($membership['membership_type']); ?>
                            </span>
                        </td>
                        <td><code><?php echo htmlspecialchars($membership['member_id']); ?></code></td>
                        <td><?php echo formatDate($membership['start_date']); ?></td>
                        <td><?php echo formatDate($membership['end_date']); ?></td>
                        <td>$<?php echo number_format($membership['price_paid'], 2); ?></td>
                        <td>
                            <?php if ($membership['card_mailed']): ?>
                                <span class="status-badge success">
                                    <i class="fas fa-check"></i> Mailed
                                </span>
                            <?php else: ?>
                                <span class="status-badge warning">
                                    <i class="fas fa-clock"></i> Pending
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php if (!$membership['card_mailed']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update_card_status">
                                    <input type="hidden" name="membership_id" value="<?php echo $membership['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success" title="Mark Card as Mailed">
                                        <i class="fas fa-mail-bulk"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <button class="btn btn-sm btn-info" onclick="viewMemberDetails(<?php echo $membership['id']; ?>)" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.stat-card.warning { border-left: 4px solid #f39c12; }
.stat-card.success { border-left: 4px solid #27ae60; }
.stat-card.primary { border-left: 4px solid #3498db; }

.stat-icon {
    font-size: 2.5rem;
    color: #3498db;
}

.stat-content h3 {
    font-size: 2rem;
    margin: 0;
    color: #2c3e50;
}

.stat-content p {
    margin: 0;
    color: #7f8c8d;
    font-size: 0.9rem;
}

.member-info strong {
    display: block;
    color: #2c3e50;
}

.member-info small {
    color: #7f8c8d;
}

.membership-type {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
    text-transform: uppercase;
}

.membership-type.individual { background: #e3f2fd; color: #1976d2; }
.membership-type.family { background: #f3e5f5; color: #7b1fa2; }
.membership-type.student { background: #e8f5e8; color: #388e3c; }
.membership-type.senior { background: #fff3e0; color: #f57c00; }
.membership-type.patron { background: #fce4ec; color: #c2185b; }

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge.success {
    background: #e8f5e8;
    color: #388e3c;
}

.status-badge.warning {
    background: #fff3e0;
    color: #f57c00;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}
</style>

<script>
function viewMemberDetails(membershipId) {
    // Implementation for viewing member details
    alert('View member details for ID: ' + membershipId);
}
</script>

<?php include '../admin/includes/footer.php'; ?>
