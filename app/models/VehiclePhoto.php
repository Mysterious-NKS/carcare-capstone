<?php
// app/models/VehiclePhoto.php
class VehiclePhoto extends Model
{
   public static function add(int $vehicleId, string $file): void {
    $pdo = DB::pdo();
    $stmt = $pdo->prepare("INSERT INTO vehicle_photos (vehicle_id, file) VALUES (?, ?)");
    $stmt->execute([$vehicleId, $file]);
  }

    public static function forVehicle(int $vehicleId): array {
        $pdo = self::pdo();
        $stmt = $pdo->prepare(
            "SELECT id, file, created_at FROM vehicle_photos
             WHERE vehicle_id = ? ORDER BY id DESC"
        );
        $stmt->execute([$vehicleId]);
        return $stmt->fetchAll();
    }

    public static function deleteAllFor(int $vehicleId): void {
        $pdo = self::pdo();
        $pdo->prepare("DELETE FROM vehicle_photos WHERE vehicle_id=?")
            ->execute([$vehicleId]);
    }
    
     public static function byVehicle(int $vehicleId): array {
    $pdo = DB::pdo();
    $stmt = $pdo->prepare("SELECT * FROM vehicle_photos WHERE vehicle_id = ? ORDER BY id DESC");
    $stmt->execute([$vehicleId]);
    return $stmt->fetchAll();
  }
}
