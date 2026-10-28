<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class databaseConnection {
    public function __construct() {
        global $conn;

        try {
            $conn = new mysqli(
                "sql113.infinityfree.com",
                "if0_42499654",
                "XB4iB13DZ9mep",
                "if0_42499654_uni"
            );

            $conn->set_charset("utf8");
        } catch (mysqli_sql_exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}
?>