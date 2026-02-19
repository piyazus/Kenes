<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'consultant') {
    header('Location: login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_service'])) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $icon = trim($_POST['icon']);

        $stmt = $pdo->prepare("INSERT INTO services (title, description, icon_class) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $icon]);
    } elseif (isset($_POST['delete_service'])) {
        $id = intval($_POST['service_id']);
        $stmt = $pdo->prepare("UPDATE services SET active = 0 WHERE id = ?"); // Soft delete
        $stmt->execute([$id]);
    }
}

$stmt = $pdo->query("SELECT * FROM services WHERE active = 1");
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Services - Kenes</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/tables.css">
</head>

<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand"><a href="index.html">K</a></div>
            <ul class="navbar-menu navbar-menu-left">
                <li><a href="consultant-dashboard.php">Dashboard</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <h1>Manage Services</h1>

        <div class="auth-card">
            <h3>Add New Service</h3>
            <form method="POST">
                <input type="hidden" name="add_service" value="1">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                    <label>Icon Class (FontAwesome)</label>
                    <input type="text" name="icon" class="form-control" value="fas fa-briefcase">
                </div>
                <button type="submit" class="btn btn-primary">Add Service</button>
            </form>
        </div>

        <h3>Existing Services</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($service['title']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($service['description']); ?>
                        </td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="delete_service" value="1">
                                <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 4px 8px;">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>