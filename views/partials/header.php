<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATM Data Mining</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.7/css/all.css">
    <link href="/atm_data_mining/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://d3js.org/d3.v5.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    

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
                                <a class="nav-link px-3" href="/atm_data_mining/views/decision_tree/dataset.php"><i
                                        class="fas fa-database pr-1"></i> Dataset</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="/atm_data_mining/views/decision_tree/klasifikasi.php"><i
                                        class="fas fa-cogs pr-1"></i> Proses Klasifikasi</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="/atm_data_mining/views/decision_tree/c45.php"><i
                                        class="fas fa-tree pr-1"></i> C4.5 & Pohon Keputusan</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav ml-auto">
                            <?php if (isset($_SESSION['username'])): ?>
                                <li class="nav-item">
                                    <span class="nav-link"><i class="fas fa-user"></i> Hello,
                                        <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link px-3" href="/atm_data_mining/views/logout.php"><i
                                            class="fas fa-sign-out-alt pr-1"></i> Logout</a>
                                </li>
                            <?php else: ?>
                                <li class="nav-item">
                                    <a class="nav-link px-3" href="/atm_data_mining/views/login.php"><i
                                            class="fas fa-sign-in-alt pr-1"></i> Login</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

            </nav>
        </header>
        <main class="container mt-4 flex-grow-1">