<?php
include __DIR__ . '/partials/header.php';
session_start();
?>
<div class="container">
    <h1 class="mt-5">Algoritma Decision Tree C45</h1>
    <div class="row">
        <div class="col-md-3">
            <nav class="nav flex-column">
                <a class="nav-link" href="../index.php">Dashboard</a>
                <a class="nav-link" href="decision_tree/dataset.php">Dataset Tabel</a>
                <a class="nav-link" href="decision_tree/attributes.php">Atribut Label</a>
                <a class="nav-link" href="decision_tree/classification.php">Klasifikasi</a>
                <a class="nav-link" href="decision_tree/prediction.php">Prediksi</a>
            </nav>
        </div>
        <div class="col-md-9">
            <!-- Content for Decision Tree algorithm -->
        </div>
    </div>
</div>
<?php
include __DIR__ . '/partials/footer.php';
?>
