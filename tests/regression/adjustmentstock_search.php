<?php
if (PHP_SAPI !== 'cli') exit;

function check_search($ok, $label) {
    if (!$ok) throw new RuntimeException($label);
}

$host = getenv('BOOKING_DB_HOST');
if (!$host) $host = 'metalsindo_db';
$user = getenv('BOOKING_DB_USER') ?: 'root';
$pass = getenv('BOOKING_DB_PASSWORD') ?: 'root';

$pdo = new PDO("mysql:host={$host};dbname=metalsindo_dev;charset=utf8", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// 1. Verify exact count of active category3
$totalActive = (int)$pdo->query("SELECT COUNT(*) FROM ms_inventory_category3 WHERE deleted = '0'")->fetchColumn();
check_search($totalActive > 0, "Total active category3 must be greater than 0");

// 2. Test search by ID
$firstMat = $pdo->query("SELECT id_category3, nama FROM ms_inventory_category3 WHERE deleted = '0' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
check_search(!empty($firstMat), "Must find at least one active material");

$idSearch = $firstMat['id_category3'];
$supplierSubquery = '(SELECT cs.id_category3, GROUP_CONCAT(DISTINCT ms.name_suplier SEPARATOR ", ") AS supplier_names FROM child_inven_suplier cs JOIN master_supplier ms ON ms.id_suplier = cs.id_suplier GROUP BY cs.id_category3) sup';

$sqlSearch = "
SELECT a.id_category3, a.nama, c.nama as nama_category1, f.nm_bentuk, sup.supplier_names
FROM ms_inventory_category3 a
LEFT JOIN ms_inventory_type b ON b.id_type = a.id_type
LEFT JOIN ms_inventory_category1 c ON c.id_category1 = a.id_category1
LEFT JOIN ms_inventory_category2 d ON d.id_category2 = a.id_category2
LEFT JOIN ms_bentuk f ON f.id_bentuk = a.id_bentuk
LEFT JOIN {$supplierSubquery} ON sup.id_category3 = a.id_category3
WHERE a.deleted = '0'
AND (
    a.id_category3 LIKE :s
    OR a.nama LIKE :s
    OR c.nama LIKE :s
    OR d.nama LIKE :s
    OR b.nama LIKE :s
    OR f.nm_bentuk LIKE :s
    OR sup.supplier_names LIKE :s
    OR a.maker LIKE :s
    OR a.spek LIKE :s
    OR a.thickness LIKE :s
)
";

$stmt = $pdo->prepare($sqlSearch);
$stmt->execute([':s' => "%{$idSearch}%"]);
$resId = $stmt->fetchAll(PDO::FETCH_ASSOC);
check_search(count($resId) >= 1, "Search by ID '{$idSearch}' must return at least 1 record");
check_search($resId[0]['id_category3'] === $idSearch, "First returned ID must match searched ID");

// 3. Test search by Supplier
$supRow = $pdo->query("
    SELECT ms.name_suplier 
    FROM child_inven_suplier cs 
    JOIN master_supplier ms ON ms.id_suplier = cs.id_suplier 
    JOIN ms_inventory_category3 a ON a.id_category3 = cs.id_category3 
    WHERE a.deleted = '0' AND TRIM(ms.name_suplier) != '' 
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

if ($supRow) {
    $supName = trim($supRow['name_suplier']);
    $stmt->execute([':s' => "%{$supName}%"]);
    $resSup = $stmt->fetchAll(PDO::FETCH_ASSOC);
    check_search(count($resSup) >= 1, "Search by supplier '{$supName}' must return results");
}

// 4. Test search by Shape / Bentuk
$stmt->execute([':s' => '%ROLL%']);
$resRoll = $stmt->fetchAll(PDO::FETCH_ASSOC);
check_search(count($resRoll) > 0, "Search by bentuk 'ROLL' must return results");

// 5. Test search by Name
$stmt->execute([':s' => '%HOT ROLLED%']);
$resName = $stmt->fetchAll(PDO::FETCH_ASSOC);
check_search(count($resName) > 0, "Search by material name 'HOT ROLLED' must return results");

// 6. Test cardinality (no duplicate row explosion)
$countDistinct = (int)$pdo->query("
    SELECT COUNT(*) FROM (
        SELECT a.id_category3
        FROM ms_inventory_category3 a
        LEFT JOIN ms_inventory_type b ON b.id_type = a.id_type
        LEFT JOIN ms_inventory_category1 c ON c.id_category1 = a.id_category1
        LEFT JOIN ms_inventory_category2 d ON d.id_category2 = a.id_category2
        LEFT JOIN ms_bentuk f ON f.id_bentuk = a.id_bentuk
        LEFT JOIN {$supplierSubquery} ON sup.id_category3 = a.id_category3
        WHERE a.deleted = '0'
    ) tbl
")->fetchColumn();
check_search($countDistinct === $totalActive, "Joined total ({$countDistinct}) must equal totalActive ({$totalActive}) without duplication");

echo "PASS adjustment stock datatable search: ID, name, bentuk, supplier, and cardinality\n";

