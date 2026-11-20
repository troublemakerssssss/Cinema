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

$success = isset($_GET['success']) ? 'Action completed successfully.' : '';

$page_title         = 'Manage Users';
$current_admin_page = 'users';
require_once __DIR__ . '/partials/admin-header.php';
?>

<div class="cb-admin-layout">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <main class="cb-admin-main">
        <!-- Page Header -->
        <div class="mb-4">
            <h2 class="fw-bold mb-1" style="color:var(--cb-text)">
                <i class="bi bi-people me-2" style="color:var(--cb-text)"></i>Users
            </h2>
            <p class="text-muted-cb mb-0" style="font-size:.875rem"><?= count($users) ?> user<?= count($users) !== 1 ? 's' : '' ?> found</p>
        </div>

        <?php if ($success): ?>
        <div class="cb-alert-success mb-3" data-auto-dismiss="4000">
            <i class="bi bi-check-circle-fill"></i><?= sanitize($success) ?>
        </div>
        <?php endif; ?>

        <!-- Search -->
        <div class="cb-card mb-4">
            <div class="p-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="cb-label">Search Users</label>
                        <div class="input-group">
                            <span class="input-group-text cb-ig-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control cb-form-control"
                                   placeholder="Search by name or email..." value="<?= sanitize($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-cb-primary w-100">
                            <i class="bi bi-search me-1"></i>Search
                        </button>
                    </div>
                    <?php if ($search): ?>
                    <div class="col-md-2">
                        <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-cb-secondary w-100">
                            <i class="bi bi-x-lg me-1"></i>Clear
                        </a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="cb-card">
            <div class="card-header">
                <strong>All Users</strong>
                <span class="text-muted-cb ms-1" style="font-size:.8rem">(<?= count($users) ?>)</span>
            </div>
            <div class="table-responsive">
                <table class="table cb-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted-cb">No users found.</td></tr>
                        <?php else: ?>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td style="color:var(--cb-text-muted);font-size:.82rem"><?= $u['id'] ?></td>
                            <td style="font-weight:600"><?= sanitize($u['name']) ?></td>
                            <td style="color:var(--cb-text-muted);font-size:.85rem"><?= sanitize($u['email']) ?></td>
                            <td style="color:var(--cb-text-muted);font-size:.85rem"><?= sanitize($u['phone'] ?? '—') ?></td>
                            <td>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span style="font-size:.72rem;font-weight:700;padding:.25rem .75rem;border-radius:var(--cb-radius-pill);background:var(--cb-button);color:#fff">Admin</span>
                                <?php else: ?>
                                    <span style="font-size:.72rem;font-weight:700;padding:.25rem .75rem;border-radius:var(--cb-radius-pill);background:var(--cb-section);color:var(--cb-text);border:1px solid var(--cb-border)">User</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:.82rem;color:var(--cb-text-muted)"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                                <div class="d-flex gap-1">
                                    <a href="?action=toggle_role&id=<?= $u['id'] ?>"
                                       class="btn btn-sm py-1 px-2" style="background:#FFFBEB;border:1px solid #FDE68A;color:#92400E"
                                       onclick="return confirm('Toggle role for <?= addslashes(sanitize($u['name'])) ?>?')"
                                       title="Toggle Role">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </a>
                                    <?php if ($u['role'] !== 'admin'): ?>
                                    <form id="del-u-<?= $u['id'] ?>" method="GET" style="display:inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button type="button" class="btn btn-sm py-1 px-2" style="background:#FEF2F2;border:1px solid #FECACA;color:var(--cb-danger)"
                                                onclick="confirmDelete('del-u-<?= $u['id'] ?>', 'Delete user <?= addslashes(sanitize($u['name'])) ?>?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <span style="font-size:.78rem;color:var(--cb-text-muted);font-style:italic">You</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/partials/admin-footer.php'; ?>
