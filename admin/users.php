<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db      = getDB();
$error   = '';
$success = '';

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// Delete (non-admin only)
if ($action === 'delete' && $id > 0) {
    $db->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'")->execute([$id]);
    header('Location: ' . BASE_URL . '/admin/users.php?success=1');
    exit;
}

// Toggle role
if ($action === 'toggle_role' && $id > 0) {
    $user = $db->prepare("SELECT role FROM users WHERE id = ?");
    $user->execute([$id]);
    $user = $user->fetch();
    if ($user) {
        $newRole = $user['role'] === 'admin' ? 'user' : 'admin';
        $db->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$newRole, $id]);
    }
    header('Location: ' . BASE_URL . '/admin/users.php?success=1');
    exit;
}

// Fetch all users
$search = trim($_GET['search'] ?? '');
$params = [];
$where  = "WHERE 1=1";
if ($search !== '') {
    $where   .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmt = $db->prepare("SELECT * FROM users $where ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

$success = isset($_GET['success']) ? 'Action completed.' : '';

$page_title         = 'Manage Users';
$current_admin_page = 'users';
require_once __DIR__ . '/partials/admin-header.php';
?>

<div class="d-flex" style="min-height:calc(100vh - 60px)">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <main class="flex-grow-1 p-4">
        <h2 class="text-white fw-bold mb-4"><i class="bi bi-people me-2 text-warning"></i>Users</h2>

        <?php if ($success): ?><div class="cb-alert-success mb-3" data-auto-dismiss="4000"><i class="bi bi-check-circle me-2"></i><?= sanitize($success) ?></div><?php endif; ?>

        <!-- Search -->
        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control cb-form-control"
                       placeholder="Search by name or email..." value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-cb-primary w-100">Search</button>
            </div>
            <?php if ($search): ?>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
            <?php endif; ?>
        </form>

        <!-- Table -->
        <div class="cb-card">
            <div class="card-header">Total: <?= count($users) ?> user(s)</div>
            <div class="table-responsive">
                <table class="table cb-table mb-0">
                    <thead><tr>
                        <th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="text-muted-cb"><?= $u['id'] ?></td>
                            <td class="text-white fw-semibold"><?= sanitize($u['name']) ?></td>
                            <td class="text-muted-cb small"><?= sanitize($u['email']) ?></td>
                            <td class="text-muted-cb small"><?= sanitize($u['phone'] ?? '—') ?></td>
                            <td>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="badge bg-danger">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">User</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted-cb"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                                <a href="?action=toggle_role&id=<?= $u['id'] ?>"
                                   class="btn btn-sm btn-outline-warning me-1"
                                   onclick="return confirm('Toggle role for <?= addslashes(sanitize($u['name'])) ?>?')"
                                   title="Toggle Role">
                                    <i class="bi bi-arrow-repeat"></i>
                                </a>
                                <?php if ($u['role'] !== 'admin'): ?>
                                <form id="del-u-<?= $u['id'] ?>" method="GET" style="display:inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete('del-u-<?= $u['id'] ?>', 'Delete user <?= addslashes(sanitize($u['name'])) ?>?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-muted-cb small">You</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/partials/admin-footer.php'; ?>
