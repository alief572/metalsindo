<?php
// CLI only. Default: preflight. --apply requires an existing private audit directory.
// php scripts/correct_dpil_invoice_1094.php [--apply --backup-dir=/private/audit]
// Rollback preflight: --rollback=/private/audit/before.json
// Rollback apply: --rollback=PATH --apply --backup-dir=/private/audit
if (PHP_SAPI !== 'cli') exit;

class DpilInvoice1094Correction
{
    private $db;
    private $database;
    private $accounting;
    private $fields = array('ppn', 'nilai_ppn', 'nilai_invoice', 'grand_total', 'sisa_invoice_idr');

    public function __construct($db, $database = 'metalsindo_live', $accounting = 'gl_metalsindo_live')
    {
        self::check(preg_match('/^[a-zA-Z0-9_]+$/', $database) && preg_match('/^[a-zA-Z0-9_]+$/', $accounting), 'Invalid schema name.');
        $this->db = $db;
        $this->database = $database;
        $this->accounting = $accounting;
    }

    private static function check($condition, $message)
    {
        if (!$condition) throw new Exception($message);
    }

    private static function amount($actual, $expected)
    {
        return is_numeric($actual) && abs((float) $actual - $expected) < 0.005;
    }

    private function rows($sql)
    {
        $result = $this->db->query($sql);
        self::check($result !== false, 'Database query failed: ' . $this->db->error);
        $rows = array();
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        $result->free();
        return $rows;
    }

    private function capture()
    {
        $refs = "('I2603468','INV-MP/26/IX/1094')";
        return array(
            'header' => $this->rows("SELECT * FROM tr_invoice WHERE no_invoice='I2603468' FOR UPDATE"),
            'customer' => $this->rows("SELECT id_customer,name_customer,facility FROM master_customers WHERE id_customer='MC2400160'"),
            'details' => $this->rows("SELECT * FROM tr_invoice_detail WHERE no_invoice='I2603468' ORDER BY id_invoice_detail"),
            'payments' => $this->rows("SELECT * FROM tr_invoice_payment_detail WHERE no_invoice='I2603468'"),
            'discounts' => $this->rows("SELECT * FROM dt_spkmarketing WHERE id_spkmarketing='P2602716' AND deal='1'"),
            'exports' => $this->rows("SELECT * FROM faktur_e_logs WHERE invoice_no='INV-MP/26/IX/1094' ORDER BY id"),
            'journal' => $this->rows("SELECT * FROM {$this->accounting}.jurnal WHERE no_reff IN $refs OR keterangan LIKE '%INV-MP/26/IX/1094%' OR keterangan LIKE '%I2603468%'"),
            'jarh' => $this->rows("SELECT * FROM {$this->accounting}.jarh WHERE no_reff IN $refs OR note LIKE '%INV-MP/26/IX/1094%' OR note LIKE '%I2603468%'")
        );
    }

    private function validate($data)
    {
        self::check(count($data['header']) === 1 && count($data['customer']) === 1, 'Expected exactly one invoice and customer.');
        $h = $data['header'][0];
        foreach (array('id' => '3482', 'no_invoice' => 'I2603468', 'no_surat' => 'INV-MP/26/IX/1094', 'id_customer' => 'MC2400160', 'no_so' => 'P2602716', 'id_do' => 'DO2603556', 'no_do' => 'DO-MP/26/IX/1095', 'no_po' => 'POP260001541', 'tgl_invoice' => '2026-09-18', 'status_jurnal' => 'OPN', 'status_close' => '0', 'stat_efaktur' => '1') as $field => $value) {
            self::check(isset($h[$field]) && (string) $h[$field] === $value, 'Invoice identity/status changed: ' . $field);
        }
        $customer = $data['customer'][0];
        self::check($customer['id_customer'] === 'MC2400160' && trim($customer['name_customer']) === 'PT.LIMA TEKNO INDONESIA' && trim($customer['facility']) === 'DPIL', 'Customer identity/facility changed.');
        foreach (array('nilai_produk' => 600000, 'total' => 600000, 'dpp' => 600000, 'diskon' => 0, 'total_bayar' => 0, 'total_bayar_idr' => 0) as $field => $value) {
            self::check(isset($h[$field]) && self::amount($h[$field], $value), 'Invoice base/payment changed: ' . $field);
        }
        self::check(count($data['details']) === 1, 'Expected one detail.');
        $detail = $data['details'][0];
        foreach (array('id_invoice_detail' => '10342', 'no_invoice' => 'I2603468', 'no_so' => 'P2602716', 'id_category3' => 'I2000010') as $field => $value) {
            self::check(isset($detail[$field]) && (string) $detail[$field] === $value, 'Detail identity changed: ' . $field);
        }
        foreach (array('qty_invoice' => 2.4, 'harga_satuan' => 250000, 'total_harga' => 600000, 'nilai_diskon' => 0, 'freight_cost' => 0) as $field => $value) {
            self::check(isset($detail[$field]) && self::amount($detail[$field], $value), 'Detail amount changed: ' . $field);
        }
        foreach ($data['discounts'] as $discount) self::check(self::amount($discount['nominal_discount'], 0), 'SPK discount changed.');
        self::check(empty($data['payments']) && empty($data['journal']) && empty($data['jarh']), 'Payment or accounting journal found; abort.');
        $old = $this->matches($h, array(0, 0, 600000, 600000, 600000));
        $corrected = $this->matches($h, array(11, 66000, 666000, 666000, 666000));
        self::check($old || $corrected, 'Invoice tax/total values changed.');
        return $corrected;
    }

    private function matches($header, $values)
    {
        foreach ($this->fields as $i => $field) {
            if (!isset($header[$field]) || !self::amount($header[$field], $values[$i])) return false;
        }
        return true;
    }

    private static function privatePath($path, $directory)
    {
        $real = realpath($path);
        self::check($real !== false && ($directory ? is_dir($real) : is_file($real)), 'Audit path must already exist.');
        $roots = array(dirname(__DIR__));
        if (!empty($_SERVER['DOCUMENT_ROOT'])) $roots[] = $_SERVER['DOCUMENT_ROOT'];
        // The Docker deployment serves /var/www; CLI has no DOCUMENT_ROOT.
        if (is_dir('/var/www')) $roots[] = '/var/www';
        $normalized = strtolower(str_replace('\\', '/', $real));
        foreach ($roots as $root) {
            $root = realpath($root);
            if ($root === false) continue;
            $root = rtrim(strtolower(str_replace('\\', '/', $root)), '/');
            self::check($normalized !== $root && strpos($normalized, $root . '/') !== 0, 'Audit path must be outside web document root.');
        }
        if ($directory) self::check(is_writable($real), 'Audit directory is not writable.');
        return $real;
    }

    private static function writeAudit($path, $data)
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        self::check($json !== false, 'Cannot encode audit snapshot.');
        $handle = @fopen($path, 'x');
        self::check($handle !== false, 'Cannot create audit snapshot.');
        @chmod($path, 0600);
        $payload = $json . PHP_EOL;
        $ok = fwrite($handle, $payload) === strlen($payload) && fflush($handle);
        fclose($handle);
        self::check($ok, 'Cannot persist audit snapshot.');
    }

    public function run($apply = false, $backupDir = null, $rollback = null)
    {
        $auditDir = $apply ? self::privatePath($backupDir, true) : null;
        $saved = null;
        if ($rollback !== null) {
            $rollback = self::privatePath($rollback, false);
            self::check(is_file($rollback . '.committed'), 'Rollback requires committed correction snapshot.');
            $saved = json_decode(file_get_contents($rollback), true);
            self::check(is_array($saved) && isset($saved['database'], $saved['operation'], $saved['before'], $saved['after']) && $saved['database'] === $this->database && $saved['operation'] === 'correct', 'Invalid rollback snapshot.');
            self::check(!$this->validate($saved['before']) && $this->validate($saved['after']), 'Invalid rollback before/after values.');
            $expectedAfter = $saved['before'];
            foreach ($this->fields as $field) $expectedAfter['header'][0][$field] = $saved['after']['header'][0][$field];
            self::check($expectedAfter === $saved['after'], 'Rollback snapshot contains unrelated changes.');
        }
        $inTransaction = false;
        $tablesLocked = false;
        try {
            // Journal tables are MyISAM on this deployment. Explicit READ locks
            // keep journal checks stable through commit. LOCK TABLES implicitly
            // commits; disable autocommit FIRST and do not call START TRANSACTION
            // afterwards (it would release the explicit locks).
            self::check($this->db->query('SET SESSION lock_wait_timeout=10'), 'Cannot set lock timeout.');
            self::check($this->db->autocommit(false), 'Cannot disable autocommit.');
            $inTransaction = true;
            self::check($this->db->query("LOCK TABLES tr_invoice WRITE,master_customers READ,tr_invoice_detail READ,tr_invoice_payment_detail READ,dt_spkmarketing READ,faktur_e_logs READ,{$this->accounting}.jurnal READ,{$this->accounting}.jarh READ"), 'Cannot lock invoice and accounting tables: ' . $this->db->error);
            $tablesLocked = true;
            $before = $this->capture();
            $corrected = $this->validate($before);
            if ($saved !== null) {
                self::check($before === $saved['after'], 'Invoice or related data changed since correction; rollback refused.');
            } elseif ($corrected) {
                $this->db->rollback();
                return array('status' => 'already-corrected', 'backup' => null);
            }
            if (!$apply) {
                $this->db->rollback();
                return array('status' => 'preflight', 'backup' => null);
            }
            $afterExpected = $before;
            $values = $saved === null ? array('11', '66000.00', '666000.00', '666000.00', '666000.00') : array();
            foreach ($this->fields as $i => $field) {
                if ($saved !== null) $values[$i] = $saved['before']['header'][0][$field];
                $afterExpected['header'][0][$field] = $values[$i];
            }
            $backup = $auditDir . '/invoice1094_' . ($saved === null ? 'correct_' : 'rollback_') . date('Ymd_His') . '_' . bin2hex(openssl_random_pseudo_bytes(8)) . '.json';
            self::writeAudit($backup, array('captured_at' => date('c'), 'database' => $this->database, 'operation' => $saved === null ? 'correct' : 'rollback', 'source' => $rollback, 'before' => $before, 'after' => $afterExpected));
            $statement = $this->db->prepare("UPDATE tr_invoice SET ppn=?,nilai_ppn=?,nilai_invoice=?,grand_total=?,sisa_invoice_idr=? WHERE id=3482 AND no_invoice='I2603468' AND no_surat='INV-MP/26/IX/1094' AND id_customer='MC2400160'");
            self::check($statement !== false, 'Cannot prepare correction.');
            $statement->bind_param('sssss', $values[0], $values[1], $values[2], $values[3], $values[4]);
            self::check($statement->execute() && $statement->affected_rows === 1, 'Expected exactly one changed invoice.');
            $statement->close();
            $after = $this->capture();
            $this->validate($after);
            self::check($after === $afterExpected, 'Post-update verification failed: unexpected changes.');
            self::writeAudit($backup . '.verified-after.json', array('verified_at' => date('c'), 'affected_rows' => 1, 'data' => $after));
            self::check($this->db->commit(), 'Commit failed.');
            $inTransaction = false;
            // A missing marker is reported as committed; never suggest the DB was rolled back.
            $marker = @file_put_contents($backup . '.committed', date('c') . PHP_EOL, LOCK_EX);
            if ($marker !== false) @chmod($backup . '.committed', 0600);
            return array('status' => 'committed', 'backup' => $backup, 'marker_saved' => $marker !== false);
        } catch (Exception $error) {
            if ($inTransaction) $this->db->rollback();
            throw $error;
        } finally {
            if ($tablesLocked) $this->db->query('UNLOCK TABLES');
            $this->db->autocommit(true);
        }
    }
}

// Including this file in tests does not connect to the application database.
if (realpath(isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '') === realpath(__FILE__)) {
    date_default_timezone_set('Asia/Jakarta');
    $connection = null;
    try {
        $apply = false; $backupDir = null; $rollback = null;
        foreach (array_slice($argv, 1) as $argument) {
            if ($argument === '--apply') $apply = true;
            elseif (strpos($argument, '--backup-dir=') === 0) $backupDir = substr($argument, 13);
            elseif (strpos($argument, '--rollback=') === 0) $rollback = substr($argument, 11);
            else throw new Exception('Unknown argument: ' . $argument);
        }
        if ($apply && empty($backupDir)) throw new Exception('Apply requires --backup-dir=PATH outside web document root.');
        define('BASEPATH', dirname(__DIR__) . '/system/');
        define('ENVIRONMENT', 'development');
        require dirname(__DIR__) . '/application/config/development/database.php';
        $settings = $db['default'];
        if ($settings['database'] !== 'metalsindo_live') throw new Exception('Unexpected database; abort.');
        mysqli_report(MYSQLI_REPORT_OFF);
        $connection = mysqli_init();
        $connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
        if (!@$connection->real_connect($settings['hostname'], $settings['username'], $settings['password'], $settings['database'], isset($settings['port']) ? $settings['port'] : 3306)) throw new Exception('Database connection failed.');
        if (!$connection->set_charset('utf8')) throw new Exception('Cannot set charset.');
        $engines = $connection->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA='metalsindo_live' AND TABLE_NAME IN ('tr_invoice','tr_invoice_detail','tr_invoice_payment_detail')");
        if (!$engines || $engines->num_rows !== 3) throw new Exception('Missing transaction tables.');
        while ($engine = $engines->fetch_assoc()) if ($engine['ENGINE'] !== 'InnoDB') throw new Exception('Transactional tables required.');
        $fix = new DpilInvoice1094Correction($connection);
        $result = $fix->run($apply, $backupDir, $rollback);
        echo strtoupper($result['status']) . ': INV-MP/26/IX/1094' . PHP_EOL;
        if ($result['backup']) echo 'Backup: ' . $result['backup'] . PHP_EOL;
        if (isset($result['marker_saved']) && !$result['marker_saved']) echo 'WARNING: committed, but audit commit marker could not be saved.' . PHP_EOL;
        echo 'E-Faktur export history preserved; review export dated 2026-09-25 separately.' . PHP_EOL;
        $connection->close();
    } catch (Exception $error) {
        if ($connection) $connection->close();
        fwrite(STDERR, 'ABORTED: ' . $error->getMessage() . PHP_EOL);
        exit(1);
    }
}
