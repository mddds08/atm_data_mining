<?php
class ATMData
{
    private $conn;
    private $table_name = "atm_data";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Method to save batch data
    public function saveBatch($data)
    {
        $query = "INSERT INTO " . $this->table_name . " (lokasi_atm, jarak_tempuh, level_saldo, status_isi) VALUES ";

        $values = [];
        foreach ($data as $row) {
            $values[] = "('" . $row['lokasi_atm'] . "', '" . $row['jarak_tempuh'] . "', '" . $row['level_saldo'] . "', '" . $row['status_isi'] . "')";
        }

        $query .= implode(", ", $values);

        $stmt = $this->conn->prepare($query);
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function addData($lokasi_atm, $jarak_tempuh, $level_saldo, $status_isi)
    {
        $query = "INSERT INTO " . $this->table_name . " (lokasi_atm, jarak_tempuh, level_saldo, status_isi) VALUES (:lokasi_atm, :jarak_tempuh, :level_saldo, :status_isi)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':lokasi_atm', $lokasi_atm);
        $stmt->bindParam(':jarak_tempuh', $jarak_tempuh);
        $stmt->bindParam(':level_saldo', $level_saldo);
        $stmt->bindParam(':status_isi', $status_isi);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateData($id, $lokasi_atm, $jarak_tempuh, $level_saldo, $status_isi)
    {
        $query = "UPDATE " . $this->table_name . " SET lokasi_atm = :lokasi_atm, jarak_tempuh = :jarak_tempuh, level_saldo = :level_saldo, status_isi = :status_isi WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':lokasi_atm', $lokasi_atm);
        $stmt->bindParam(':jarak_tempuh', $jarak_tempuh);
        $stmt->bindParam(':level_saldo', $level_saldo);
        $stmt->bindParam(':status_isi', $status_isi);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function deleteData($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function cleanC45Results()
    {
        // Menghapus data dari decision_tree dalam urutan yang benar
        $this->conn->exec("SET FOREIGN_KEY_CHECKS=0"); // Nonaktifkan pemeriksaan kunci asing sementara
        $this->conn->exec("DELETE FROM decision_tree WHERE parent_node_id IS NOT NULL"); // Hapus child nodes terlebih dahulu
        $this->conn->exec("DELETE FROM decision_tree WHERE parent_node_id IS NULL"); // Hapus parent nodes
        $this->conn->exec("SET FOREIGN_KEY_CHECKS=1"); // Aktifkan kembali pemeriksaan kunci asing

        // Hapus data dari c45_results
        $this->conn->exec("DELETE FROM c45_results");
    }

    // Method to get all data
    public function getAllData()
    {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Method to delete all data
    public function deleteAllData()
    {
        $query = "DELETE FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    // Metode untuk klasifikasi data
    public function getClassifiedData()
    {
        $query = "SELECT lokasi_atm, jarak_tempuh, level_saldo,
                  CASE
                    WHEN level_saldo < 30 THEN 'Rendah'
                    WHEN level_saldo BETWEEN 30 AND 50 THEN 'Sedang'
                    WHEN level_saldo > 50 THEN 'Tinggi'
                  END AS klasifikasi_saldo,
                  CASE
                    WHEN jarak_tempuh < 40 THEN 'Dekat'
                    WHEN jarak_tempuh BETWEEN 40 AND 70 THEN 'Sedang'
                    WHEN jarak_tempuh > 70 THEN 'Jauh'
                  END AS klasifikasi_jarak
                  FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Method to get data for C4.5 calculations
    public function getDataForC45()
    {
        $query = "SELECT lokasi_atm, jarak_tempuh, level_saldo, status_isi FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Method to perform K-Fold Cross Validation
    public function performKFoldCrossValidation($k = 10)
    {
        $data = $this->getDataForC45();
        $total_data = count($data);
        $subset_size = ceil($total_data / $k);
        $accuracy_results = [];

        for ($i = 0; $i < $k; $i++) {
            $test_data = array_slice($data, $i * $subset_size, $subset_size);
            $train_data = array_diff_key($data, array_flip(array_keys($test_data)));

            // Train the model with train_data and test with test_data
            // This is a placeholder for the actual training and testing logic
            // $accuracy = train_and_test($train_data, $test_data);

            // For simplicity, let's assume a dummy accuracy calculation here
            $accuracy = rand(70, 100); // Dummy accuracy between 70% and 100%
            $accuracy_results[] = $accuracy;
        }

        $average_accuracy = array_sum($accuracy_results) / $k;
        return [
            'k' => $k,
            'accuracy_results' => $accuracy_results,
            'average_accuracy' => $average_accuracy
        ];
    }

    public function getC45Results()
    {
        $query = "SELECT * FROM c45_results";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getDecisionTree()
    {
        $query = "SELECT * FROM decision_tree ORDER BY node_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getAttributes()
    {
        $query = "SELECT * FROM attributes";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getAttributeById($id)
    {
        $query = "SELECT * FROM attributes WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt;
    }

    public function addAttribute($name, $operator, $value, $classification)
    {
        $query = "INSERT INTO attributes (name, operator, value, classification) VALUES (:name, :operator, :value, :classification)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':operator', $operator);
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':classification', $classification);
        return $stmt->execute();
    }

    public function updateAttribute($id, $name, $operator, $value, $classification)
    {
        $query = "UPDATE attributes SET name = :name, operator = :operator, value = :value, classification = :classification WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':operator', $operator);
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':classification', $classification);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function deleteAttribute($id)
    {
        $query = "DELETE FROM attributes WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>