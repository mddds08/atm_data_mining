</main>
<footer class="footer bg-light text-center text-lg-start mt-5">
    <div class="text-center p-3 bg-dark text-white">
        Dibuat Oleh : Ilham Ihsal | © 2024 Aplikasi Data Mining ATM
    </div>
</footer>
<!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
    integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
    crossorigin="anonymous"></script> -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function () {
        // ganti label import data pada form
        $(".custom-file-input").on("change", function () {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
        // hapus dataset
        $("#deleteAllDataBtn").on("click", function () {
            $("#confirmDeleteModal").modal('show');
        });
        // menampilkan hasil dari pencarian dataset
        $("#searchInput").on("keyup", function () {
            var value = $(this).val().toLowerCase();
            $("#dataTable tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        // Inisialisasi Chart.js
        var ctx = document.getElementById('statusChart').getContext('2d');
        var locations = <?php echo json_encode(array_unique(array_column($data, 'lokasi_atm'))); ?>;
        var isiData = <?php echo json_encode(array_map(function ($loc) use ($data) {
            return count(array_filter($data, function ($row) use ($loc) {
                return $row['lokasi_atm'] === $loc && $row['status_isi'] == 1;
            }));
        }, array_unique(array_column($data, 'lokasi_atm')))); ?>;
        var tidakIsiData = <?php echo json_encode(array_map(function ($loc) use ($data) {
            return count(array_filter($data, function ($row) use ($loc) {
                return $row['lokasi_atm'] === $loc && $row['status_isi'] == 0;
            }));
        }, array_unique(array_column($data, 'lokasi_atm')))); ?>;

        var statusChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: locations,
                datasets: [
                    {
                        label: 'Isi',
                        data: isiData,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Tidak Isi',
                        data: tidakIsiData,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
</body>

</html>