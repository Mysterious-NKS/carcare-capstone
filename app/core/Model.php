<?php
// app/core/Model.php
abstract class Model {
    protected static function pdo(): PDO {
        return DB::pdo();
    }
}
