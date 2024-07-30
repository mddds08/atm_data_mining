<?php
session_start();
include __DIR__ . '/../partials/header.php';
require '../../config/database.php';

// Koneksi ke database
$database = new Database();
$db = $database->getConnection();

// Fungsi untuk mendapatkan semua nilai atribut
function getNilaiAtribut($db)
{
    $query = "SELECT * FROM nilai_atribut";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$nilaiAtribut = getNilaiAtribut($db);
?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Nilai Atribut</h5>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                    <?php echo $_SESSION['message']; ?>
                </div>
                <?php unset($_SESSION['message']);
                unset($_SESSION['message_type']); ?>
            <?php endif; ?>

            <form method="post" action="../../controllers/NilaiAtributController.php">
                <div class="form-group">
                    <label for="label_atribut">Label Atribut</label>
                    <input type="text" class="form-control" id="label_atribut" name="label_atribut" required>
                </div>
                <div class="form-group">
                    <label for="atribut_pendukung">Atribut Pendukung</label>
                    <input type="text" class="form-control" id="atribut_pendukung" name="atribut_pendukung" required>
                </div>
                <div class="form-group">
                    <label for="nilai_atribut">Nilai Atribut</label>
                    <input type="text" class="form-control" id="nilai_atribut" name="nilai_atribut" required>
                </div>
                <button type="submit" class="btn btn-primary">Tambah Nilai Atribut</button>
            </form>

            <hr>

            <table class="table table-bordered mt-3">
                <thead class="thead-light">
                    <tr>
                        <th>Label Atribut</th>
                        <th>Atribut Pendukung</th>
                        <th>Nilai Atribut</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($nilaiAtribut as $nilai): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($nilai['label_atribut']); ?></td>
                            <td><?php echo htmlspecialchars($nilai['atribut_pendukung']); ?></td>
                            <td><?php echo htmlspecialchars($nilai['nilai_atribut']); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm"
                                    onclick="editNilaiAtribut(<?php echo htmlspecialchars(json_encode($nilai)); ?>)">Edit</button>
                                <form method="post" action="../../controllers/NilaiAtributController.php"
                                    style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?php echo $nilai['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Anda yakin ingin menghapus nilai atribut ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="../../controllers/NilaiAtributController.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Nilai Atribut</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="form-group">
                        <label for="editLabelAtribut">Label Atribut</label>
                        <input type="text" class="form-control" id="editLabelAtribut" name="label_atribut" required>
                    </div>
                    <div class="form-group">
                        <label for="editAtributPendukung">Atribut Pendukung</label>
                        <input type="text" class="form-control" id="editAtributPendukung" name="atribut_pendukung"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="editNilaiAtribut">Nilai Atribut</label>
                        <input type="text" class="form-control" id="editNilaiAtribut" name="nilai_atribut" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="edit">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editNilaiAtribut(nilai) {
        document.getElementById('editId').value = nilai.id;
        document.getElementById('editLabelAtribut').value = nilai.label_atribut;
        document.getElementById('editAtributPendukung').value = nilai.atribut_pendukung;
        document.getElementById('editNilaiAtribut').value = nilai.nilai_atribut;
        $('#editModal').modal('show');
    }
</script>

<?php
include __DIR__ . '/../partials/footer.php';
?>