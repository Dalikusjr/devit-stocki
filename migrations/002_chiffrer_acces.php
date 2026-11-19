<?php
// Chiffre les accès des projets encore stockés en clair (à lancer une seule fois ; relançable sans risque).
// Usage : php migrations/002_chiffrer_acces.php
require __DIR__ . '/../config.php';

use App\Config\Database;
use App\Security\Crypto;

$db = Database::getInstance()->getConnection();
$rows = $db->query("SELECT idproj, acces FROM projects WHERE acces <> '' AND acces NOT LIKE 'enc:v1:%'")->fetchAll(PDO::FETCH_ASSOC);
$stmt = $db->prepare("UPDATE projects SET acces=:acces WHERE idproj=:id");
foreach ($rows as $r) {
    $enc = Crypto::encrypt($r['acces']);
    if (Crypto::decrypt($enc) !== $r['acces']) {
        fwrite(STDERR, "Vérification échouée pour le projet {$r['idproj']}, arrêt.\n");
        exit(1);
    }
    $stmt->execute([':acces' => $enc, ':id' => $r['idproj']]);
}
echo count($rows) . " accès chiffré(s).\n";
