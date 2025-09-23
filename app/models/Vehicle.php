<?php
/**
 * Vehicle model — tiny and focused on what we actually need today.
 * Plain PDO + prepared statements. Readable over fancy.
 */
class Vehicle {

  /** return all vehicles owned by a user (for the grid) */
  public static function byUser(int $userId): array {
    $pdo = DB::pdo();
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE user_id=? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
  }

  /** return one vehicle if it belongs to this user; otherwise null */
  public static function findOwned(int $id, int $userId): ?array {
    $pdo = DB::pdo();
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id=? AND user_id=?");
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch();
    return $row ?: null;
  }

  /** insert a vehicle and return new id */
  public static function create(int $userId, array $v): int {
    $pdo = DB::pdo();
    $sql = "INSERT INTO vehicles
      (user_id, make, model, year, color, plate_no, vin, mileage, last_service_date,
       insurance_provider, policy_number, notes, created_at)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $userId,
      $v['make'], $v['model'], $v['year'] ?: null, $v['color'] ?: null,
      $v['plate_no'], $v['vin'] ?: null, $v['mileage'] ?: null,
      $v['last_service_date'] ?: null,
      $v['insurance_provider'] ?: null, $v['policy_number'] ?: null,
      $v['notes'] ?: null
    ]);
    return (int)$pdo->lastInsertId();
  }

  /** update a vehicle that belongs to this user */
  public static function update(int $id, int $userId, array $v): bool {
    $pdo = DB::pdo();
    $sql = "UPDATE vehicles
            SET make=?, model=?, year=?, color=?, plate_no=?, vin=?, mileage=?,
                last_service_date=?, insurance_provider=?, policy_number=?, notes=?
            WHERE id=? AND user_id=?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
      $v['make'], $v['model'], $v['year'] ?: null, $v['color'] ?: null,
      $v['plate_no'], $v['vin'] ?: null, $v['mileage'] ?: null,
      $v['last_service_date'] ?: null,
      $v['insurance_provider'] ?: null, $v['policy_number'] ?: null,
      $v['notes'] ?: null,
      $id, $userId
    ]);
  }

  /** delete a vehicle the user owns */
  public static function delete(int $id, int $userId): bool {
    $pdo = DB::pdo();
    $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id=? AND user_id=?");
    return $stmt->execute([$id, $userId]);
  }
}
