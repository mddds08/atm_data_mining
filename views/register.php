<?php
include __DIR__ . '/partials/header.php';
?>
<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Register</h5>
            <form action="../../controllers/auth.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary" name="register">Register</button>
            </form>
        </div>
    </div>
</div>
<?php
include __DIR__ . '/partials/footer.php';
?>
