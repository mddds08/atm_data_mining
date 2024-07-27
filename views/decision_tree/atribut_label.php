<?php
session_start();
include __DIR__ . '/../partials/header.php';
require '../../config/database.php';
require '../../models/atmData.php';

// Instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// Initialize object
$atmData = new ATMData($db);

// Fetch all attributes
$stmt = $atmData->getAttributes();
$attributes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle form submission for adding, editing, and deleting attributes
    if (isset($_POST['add'])) {
        $atmData->addAttribute($_POST['attribute_name']);
    } elseif (isset($_POST['edit'])) {
        $atmData->editAttribute($_POST['attribute_id'], $_POST['attribute_name']);
    } elseif (isset($_POST['delete'])) {
        $atmData->deleteAttribute($_POST['attribute_id']);
    }
    header("Location: atribut_label.php");
    exit();
}

?>
<div class="container mt-5">
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="#" class="list-group-item list-group-item-action active">Atribut Label</a>
                <a href="klasifikasi.php" class="list-group-item list-group-item-action">Klasifikasi</a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Tambah Atribut</h5>
                    <form method="post" action="atribut_label.php">
                        <div class="form-group">
                            <label for="attribute_name">Nama Atribut</label>
                            <input type="text" class="form-control" id="attribute_name" name="attribute_name" required>
                        </div>
                        <button type="submit" name="add" class="btn btn-primary">Tambah</button>
                    </form>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Daftar Atribut</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Atribut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attributes as $attribute): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($attribute['id']); ?></td>
                                    <td><?php echo htmlspecialchars($attribute['name']); ?></td>
                                    <td>
                                        <form method="post" action="atribut_label.php" class="d-inline">
                                            <input type="hidden" name="attribute_id"
                                                value="<?php echo $attribute['id']; ?>">
                                            <input type="text" name="attribute_name"
                                                value="<?php echo $attribute['name']; ?>" required>
                                            <button type="submit" name="edit" class="btn btn-warning btn-sm">Edit</button>
                                        </form>
                                        <form method="post" action="atribut_label.php" class="d-inline">
                                            <input type="hidden" name="attribute_id"
                                                value="<?php echo $attribute['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include __DIR__ . '/../partials/footer.php';
?>