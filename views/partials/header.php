<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATM Data Mining</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.7/css/all.css">
    <link href="/atm-data-mining/css/style.css" rel="stylesheet">
    <link rel="shortcut icon" href="/atm_data_mining/assets/icons/icon.png" />
</head>

<body>
    <div class="d-flex flex-column min-vh-100">
        <header>
            <div class="bg-primary text-white px-4 py-3 header-title">
                <!-- <img src="/atm_data_mining/assets/icons/icon.png" alt="Logo" class="header-logo"> -->
                <h3 class="font-weight-bold mb-0">APLIKASI DATA MINING ATM</h3>
            </div>

            <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
                <div class="container">
                    <a class="navbar-brand" href="/atm_data_mining/views/index.php"><i class="fas fa-home"></i>
                        Dashboard</a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="/atm_data_mining/views/decision_tree/dataset.php"><i
                                        class="fas fa-database px-2"></i> Dataset</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/atm_data_mining/views/decision_tree/klasifikasi.php"><i
                                        class="fas fa-cogs px-2"></i> Proses Klasifikasi</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/atm_data_mining/views/decision_tree/c45.php"><i
                                        class="fas fa-tree px-2"></i> C4.5 & Pohon Keputusan</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav ml-auto">
                            <li class="nav-item dropdown">
                                <a>
                                    STMIK PROFESIONAL | JURUSAN SISTEM INFORMASI
                                </a>

                            </li>
                        </ul>
                    </div>
                </div>

            </nav>
        </header>
        <main class="container mt-4 flex-grow-1">