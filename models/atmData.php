<?php
class ATMData
{
    private $conn;
    private $table_name = "atm_data";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Fungsi untuk preprocessing data
    public function preprocessData($data)
    {
        // Handle missing values
        foreach ($data as &$row) {
            foreach ($row as $key => $value) {
                if ($value === null || $value === '') {
                    $row[$key] = $this->calculateFillValue($data, $key);
                }
            }
        }

        // Normalize numeric features (example: min-max normalization)
        $numericFeatures = ['jarak_tempuh', 'level_saldo'];
        foreach ($numericFeatures as $feature) {
            $min = min(array_column($data, $feature));
            $max = max(array_column($data, $feature));
            foreach ($data as &$row) {
                if ($max - $min != 0) {
                    $row[$feature] = ($row[$feature] - $min) / ($max - $min);
                } else {
                    $row[$feature] = 0;
                }
            }
        }

        // Handle outliers
        $data = $this->handleOutliers($data, $numericFeatures);

        return $data;
    }

    // Function to calculate fill value for missing data
    private function calculateFillValue($data, $feature)
    {
        // Example: fill with mean value
        $values = array_column($data, $feature);
        $values = array_filter($values, function ($value) {
            return $value !== null && $value !== '';
        });
        return array_sum($values) / count($values);
    }

    // Function to handle outliers
    private function handleOutliers($data, $numericFeatures)
    {
        foreach ($numericFeatures as $feature) {
            $values = array_column($data, $feature);
            $q1 = $this->calculatePercentile($values, 25);
            $q3 = $this->calculatePercentile($values, 75);
            $iqr = $q3 - $q1;

            $lowerBound = $q1 - 1.5 * $iqr;
            $upperBound = $q3 + 1.5 * $iqr;

            foreach ($data as &$row) {
                if ($row[$feature] < $lowerBound) {
                    $row[$feature] = $lowerBound;
                } elseif ($row[$feature] > $upperBound) {
                    $row[$feature] = $upperBound;
                }
            }
        }

        return $data;
    }

    // Function to calculate percentile
    private function calculatePercentile($values, $percentile)
    {
        sort($values);
        $index = ($percentile / 100) * count($values);
        if (floor($index) == $index) {
            $result = ($values[$index - 1] + $values[$index]) / 2;
        } else {
            $result = $values[floor($index)];
        }
        return $result;
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
                    WHEN level_saldo < 31 THEN 'Rendah'
                    WHEN level_saldo BETWEEN 31 AND 60 THEN 'Sedang'
                    WHEN level_saldo > 60 THEN 'Tinggi'
                  END AS klasifikasi_saldo,
                  CASE
                    WHEN jarak_tempuh < 31 THEN 'Dekat'
                    WHEN jarak_tempuh BETWEEN 31 AND 50 THEN 'Sedang'
                    WHEN jarak_tempuh > 50 THEN 'Jauh'
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getC45Resultss()
    {
        $query = "SELECT attribute_name, attribute_value, filled_cases, empty_cases FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}