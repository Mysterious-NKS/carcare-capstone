<?php

class User
{
    public static function find(int $id): ?array
    {
        $pdo = DB::pdo();
        $st = $pdo->prepare("SELECT id, full_name, email, phone, role, status, created_at FROM users WHERE id=? LIMIT 1");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function update(int $id, array $data): void
    {
        $pdo = DB::pdo();

        $fields = [];
        $vals   = [];
        foreach ($data as $k => $v) {
            $fields[] = "$k = ?";
            $vals[]   = $v;
        }
        $vals[] = $id;

        $sql = "UPDATE users SET ".implode(',', $fields)." WHERE id=?";
        $st  = $pdo->prepare($sql);
        $st->execute($vals);
    }
}
