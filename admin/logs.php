<?php 
$page_title = 'Admin Logs';

// Get the directory where this file is located
$includes_dir = dirname(__FILE__) . '/../includes';
require_once $includes_dir . '/functions.php'; 
require_once $includes_dir . '/header.php';

// Check if user is admin
if (!isAdmin()) {
    redirect(getBasePath() . 'index.php', 'Access denied. Admin privileges required.', 'error');
}

// Handle log clearing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clear_logs'])) {
    try {
        $pdo->query("DELETE FROM admin_logs");
        logAdminAction($_SESSION['user_id'], "Cleared all admin logs");
        redirect('logs.php', 'All logs cleared successfully!', 'success');
    } catch (PDOException $e) {
        redirect('logs.php', 'Error clearing logs.', 'error');
    }
}

// Get admin logs with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Get total count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM admin_logs");
$total_logs = $stmt->fetch()['total'];
$total_pages = ceil($total_logs / $per_page);

// Get logs for current page
$stmt = $pdo->prepare("SELECT al.*, u.name as admin_name, u.email as admin_email 
                       FROM admin_logs al 
                       LEFT JOIN users u ON al.admin_id = u.id 
                       ORDER BY al.timestamp DESC 
                       LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

// Get log statistics
$stmt = $pdo->query("SELECT COUNT(*) as total_actions FROM admin_logs");
$total_actions = $stmt->fetch()['total_actions'];

$stmt = $pdo->query("SELECT COUNT(DISTINCT admin_id) as unique_admins FROM admin_logs");
$unique_admins = $stmt->fetch()['unique_admins'];

$stmt = $pdo->query("SELECT DATE(timestamp) as date, COUNT(*) as count 
                     FROM admin_logs 
                     GROUP BY DATE(timestamp) 
                     ORDER BY date DESC 
                     LIMIT 7");
$recent_activity = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-history me-2"></i>Admin Logs</h2>
            <div>
                <form method="POST" style="display: inline;" 
                      onsubmit="return confirm('Are you sure you want to clear all logs? This action cannot be undone.')">
                    <button type="submit" name="clear_logs" class="btn btn-outline-danger">
                        <i class="fas fa-trash me-2"></i>Clear All Logs
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Log Statistics -->
<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="admin-card text-center">
            <div class="card-icon text-primary">
                <i class="fas fa-list"></i>
            </div>
            <div class="card-number"><?php echo $total_actions; ?></div>
            <h6>Total Actions</h6>
        </div>
    </div>
    
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="admin-card text-center">
            <div class="card-icon text-success">
                <i class="fas fa-users"></i>
            </div>
            <div class="card-number"><?php echo $unique_admins; ?></div>
            <h6>Active Admins</h6>
        </div>
    </div>
    
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="admin-card text-center">
            <div class="card-icon text-info">
                <i class="fas fa-calendar"></i>
            </div>
            <div class="card-number"><?php echo count($recent_activity); ?></div>
            <h6>Active Days</h6>
        </div>
    </div>
</div>

<!-- Recent Activity Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Activity (Last 7 Days)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_activity)): ?>
                <p class="text-muted text-center py-3">No recent activity</p>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($recent_activity as $activity): ?>
                    <div class="col-md-2 col-4 mb-3">
                        <div class="text-center">
                            <div class="bg-primary text-white rounded p-2 mb-2">
                                <strong><?php echo $activity['count']; ?></strong>
                            </div>
                            <small class="text-muted"><?php echo date('M d', strtotime($activity['date'])); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Logs Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Admin Action Logs</h5>
    </div>
    <div class="card-body">
        <?php if (empty($logs)): ?>
        <div class="text-center py-5">
            <i class="fas fa-history fa-3x text-muted mb-3"></i>
            <h4>No logs found</h4>
            <p class="text-muted">Admin actions will be logged here.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Timestamp</th>
                        <th>Time Ago</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo $log['id']; ?></td>
                        <td>
                            <div>
                                <strong><?php echo sanitize($log['admin_name']); ?></strong>
                                <?php if ($log['admin_id'] == $_SESSION['user_id']): ?>
                                <span class="badge bg-primary ms-2">You</span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted"><?php echo sanitize($log['admin_email']); ?></small>
                        </td>
                        <td>
                            <span class="text-wrap"><?php echo sanitize($log['action']); ?></span>
                        </td>
                        <td><?php echo date('M d, Y H:i:s', strtotime($log['timestamp'])); ?></td>
                        <td>
                            <?php
                            $time_diff = time() - strtotime($log['timestamp']);
                            if ($time_diff < 60) {
                                echo $time_diff . ' seconds ago';
                            } elseif ($time_diff < 3600) {
                                echo floor($time_diff / 60) . ' minutes ago';
                            } elseif ($time_diff < 86400) {
                                echo floor($time_diff / 3600) . ' hours ago';
                            } else {
                                echo floor($time_diff / 86400) . ' days ago';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Logs pagination">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        
        <div class="text-center mt-3">
            <small class="text-muted">
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $per_page, $total_logs); ?> of <?php echo $total_logs; ?> logs
            </small>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Log Export -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Export Logs</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Export admin logs for backup or analysis purposes.</p>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <a href="export-logs.php?format=csv" class="btn btn-outline-primary w-100">
                            <i class="fas fa-file-csv me-2"></i>Export as CSV
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="export-logs.php?format=json" class="btn btn-outline-success w-100">
                            <i class="fas fa-file-code me-2"></i>Export as JSON
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="export-logs.php?format=pdf" class="btn btn-outline-danger w-100">
                            <i class="fas fa-file-pdf me-2"></i>Export as PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh logs every 30 seconds
setInterval(function() {
    // Only refresh if user is on the logs page and not viewing a specific page
    if (window.location.pathname.includes('logs.php') && !window.location.search.includes('page=')) {
        window.location.reload();
    }
}, 30000);
</script>

<?php require_once '../includes/footer.php'; ?> 